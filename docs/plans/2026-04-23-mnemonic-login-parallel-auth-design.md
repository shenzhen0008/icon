# 助记词登录（并行于账号密码）方案设计稿 v0.2

## 1. 目标与边界
1. 在不影响现有“账号+密码”登录的前提下，新增“仅助记词登录”方式。
2. 助记词登录采用固定词表 + 10 词顺序组合。
3. 本方案优先“实现简单、兼容现有架构”，不做高强度安全扩展。

## 2. 设计原则
1. 复用 Laravel 现有认证体系，不重写 Auth 核心流程。
2. 与现有登录链路解耦：新增独立路由与控制器。
3. 数据库仅新增最小字段，不破坏现有用户模型和历史数据。
4. 保持回滚安全：migration 可回滚，旧登录方式零改动可用。

## 3. 实施边界（必须遵守）
1. 不修改现有 `POST /login` 的路由、控制器、Request 校验规则与成功/失败行为。
2. 不改动现有账号密码登录相关类的对外方法签名：
   - `AuthenticatedSessionController::store`
   - `AuthenticationService::attemptLogin`
3. 新增独立登录入口：`POST /login/mnemonic`，仅处理助记词登录。
4. 不改变原 `users` 表既有字段语义（`username`、`password`、`remember_token`），仅新增助记词查询字段。
5. 不在旧登录表单中替换原字段，仅新增第二登录入口（并行展示）。

## 4. 登录模式说明
1. 现有模式：账号 + 密码（保留不变）。
2. 新增模式：助记词（10 个词，顺序敏感）直接登录。
3. 两条登录链路并行存在，互不覆盖。

## 5. 助记词规则（业务层）
1. 系统内置固定词表（建议 100 个常用英文词，放 `config/mnemonic.php`）。
2. 每个用户拥有一组 10 词顺序组合（示例：`1-4-2-7-5-11-19-23-44-10` 对应词序）。
3. 输入规范化规则：
   - 全部转小写
   - 连续空白压缩为单空格
   - 去掉首尾空格
4. 规范化后形成短语字符串：`word1 word2 ... word10`。

## 6. 数据结构设计
1. 在 `users` 表新增字段：
   - `mnemonic_lookup` `char(64)` nullable unique
2. `mnemonic_lookup` 用于存储规范化短语的 `sha256` 值（十六进制 64 位）。
3. 约束：
   - 唯一索引：避免两个用户拥有相同助记词组合。
   - 可空：兼容老用户未配置助记词场景。
4. 同步更新：
   - `database/sql/mvp_schema.sql`（按项目规则必须同步）。

## 7. 后端流程设计

### 7.1 设置/重置助记词（登录后）
1. 用户在安全设置页点击“生成/重置助记词”。
2. 服务层从 `config/mnemonic.php` 的固定词表中随机抽取 10 个词（顺序敏感，建议不重复）。
3. 将生成短语展示给用户确认并提示自行保存。
4. 服务层对生成短语做规范化并计算 `sha256`，写入 `users.mnemonic_lookup`。
5. 记录审计日志（可复用现有日志机制）。

### 7.2 助记词登录（匿名）
1. 新增接口 `POST /login/mnemonic`。
2. FormRequest 校验输入格式（字符串非空，最小长度等）。
3. 服务层执行规范化 + `sha256`。
4. 按 `mnemonic_lookup` 查询用户。
5. 命中则 `Auth::login($user)` + `session()->regenerate()`，跳转首页/控制台。
6. 未命中返回通用错误（“凭证不正确”）。

## 8. 分层落位（符合项目分层约束）
1. Controller：只做请求校验、调用 Service、响应映射。
2. Service：助记词规范化、查找用户、登录流程。
3. Policy/Gate：本方案新增登录接口不涉及额外授权；“生成/重置助记词”受 `auth` 保护。
4. Blade：仅负责展示两个登录入口（账号密码 / 助记词），不写业务判断。

## 9. 路由与页面建议
1. 保留现有 `/login` 页面和提交逻辑。
2. 在同一登录页增加 Tab 或折叠块：
   - 账号密码登录
   - 助记词登录
3. 新增助记词提交目标：`POST /login/mnemonic`。
4. 用户设置页新增“生成/重置助记词”入口（登录后可访问）。

## 10. 配置设计
1. 新增 `config/mnemonic.php`：
   - `wordlist`：固定词表
   - `phrase_words_count`：默认 10
2. 避免魔法数字和硬编码词表散落在控制器。

## 11. 风险清单与防护
1. 路由/控制器冲突风险：
   - 风险：把助记词逻辑并入原 `/login`，导致原登录校验和错误提示回归。
   - 防护：助记词使用独立路由和独立控制器，不改旧登录入口。
2. 视图回归风险：
   - 风险：改登录页时破坏原 `username/password/remember` 提交流程。
   - 防护：旧表单完整保留，仅新增并行助记词表单区块。
3. 会话一致性风险：
   - 风险：新登录流程未做 `session()->regenerate()`，与原流程行为不一致。
   - 防护：助记词登录成功后必须执行 `session()->regenerate()`。
4. 数据迁移风险：
   - 风险：新增字段与唯一索引可能影响线上迁移稳定性。
   - 防护：字段先 `nullable`，且本次不做历史回填；migration 与 `database/sql/mvp_schema.sql` 同步提交。
5. Remember Me 行为分裂风险：
   - 风险：两种登录方式对 remember 行为不一致，引发用户体验混乱。
   - 防护：设计阶段明确助记词登录是否支持 remember；若支持则对齐现有逻辑。

## 12. 测试方案（最小质量门）
1. 成功路径：正确助记词可登录。
2. 权限拒绝路径：未登录用户不能访问“生成/重置助记词”接口。
3. 非法输入路径：助记词登录时词数不对、包含词表外单词、空输入，返回验证错误。
4. 兼容性路径：原账号密码登录仍正常（成功登录、错误登录、remember me、logout）。
5. 唯一性路径：重复助记词组合被拒绝（唯一索引或业务错误）。
6. 回归范围：现有 `tests/Feature/Auth/AuthenticationFlowTest.php` 全量通过。

## 13. 影响范围（预估文件）
1. `database/migrations/*_add_mnemonic_lookup_to_users_table.php`
2. `database/sql/mvp_schema.sql`
3. `config/mnemonic.php`
4. `app/Modules/User/Http/Controllers/Auth/MnemonicLoginController.php`
5. `app/Modules/User/Http/Requests/Auth/MnemonicLoginRequest.php`
6. `app/Modules/User/Services/MnemonicAuthService.php`
7. `routes/web.php`
8. `resources/views/auth/login.blade.php`（或现有登录 Blade）
9. `resources/views/.../security/*.blade.php`（设置助记词页，如已有安全设置模块）
10. `tests/Feature/Auth/MnemonicLoginTest.php`

## 14. 实施步骤（建议）
1. 先做数据库字段与配置文件。
2. 实现服务层与登录接口。
3. 接入登录页面第二入口。
4. 增加“生成/重置助记词”页面与接口。
5. 执行回归：先跑助记词新增测试，再跑现有 `AuthenticationFlowTest`。
6. 执行质量门命令：`php artisan test`、`npm run build`。

## 15. 已知限制（按当前目标接受）
1. 助记词本质是另一种口令形态，不是钱包签名认证。
2. 不做复杂风控与多因子（后续可增量加入）。
3. 若用户忘记助记词，只能走重置流程，不能找回明文。
