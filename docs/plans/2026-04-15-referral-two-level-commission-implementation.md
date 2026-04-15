# 两级推荐提成实施方案

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**目标：** 实现两级推荐提成系统：对上线后产生的每笔正向结算收益，按后台可配置比例（例如一级 5%、二级 2%）发放提成。

**架构：** 保持现有收益结算流程不变，在 Referral 模块新增独立批处理发放链路。提成发放以 `(settlement_id, level)` 为幂等键，入账复用现有余额流水模型。推荐关系通过邀请码在注册时绑定，采用 session + 客户端兜底双通道，且仅允许单一直接上级。提成发放采用状态机（`pending/processing/success/failed`）和事务内原子入账，支持失败重试并避免重复发放。

**技术栈：** Laravel 13、PHP 8.3、MySQL 5.7、Blade、PHPUnit、Filament、Vite

---

### 任务 1：先补失败测试，定义推荐绑定与两级提成行为

**文件：**
- 新建：`tests/Feature/Referral/BindReferrerOnRegisterTest.php`
- 新建：`tests/Feature/Referral/ProcessReferralCommissionBatchServiceTest.php`
- 新建：`tests/Feature/Referral/ReferralCommissionCommandTest.php`

**步骤 1：编写注册绑定邀请码失败测试**

覆盖：
- 成功：注册时带有效邀请码，`referrer_id` 成功绑定且只绑定一次
- 非法输入：邀请码不存在/格式非法时不绑定
- 权限/约束：自邀不绑定
- 稳定性：注册页预填来源优先级为 session > signed cookie
- 稳定性：用户修改预填邀请码后，以用户输入为最终绑定值
- 稳定性：用户输入为空时回落 session/cookie 兜底绑定
- 稳定性：跨页面弹窗注册（`/products/*`、`/me`、`/recharge`）不丢邀请码

**步骤 2：编写两级提成失败测试**

覆盖：
- 成功：`A <- B <- C`，当 C 出现正收益结算时，B 拿一级、A 拿二级
- 非法输入：`profit <= 0` 不产生提成
- 边界：`settlement_date < go_live_date` 的结算不产生提成（按业务时区）
- 幂等：批处理重复执行不会重复发放同一 `(settlement_id, level)`
- 稳定性：同一批次并发触发两次，余额与流水保持唯一
- 稳定性：单层发放中途异常后可重试成功且不重复入账

**步骤 3：编写命令触发批处理失败测试**

覆盖：
- 正常执行可写入预期提成记录
- 重复执行保持幂等
- 开关关闭（`config('referral.enabled') = false`）时不发放
- 并发：命令重入时被互斥锁拒绝第二个执行实例

**步骤 4：运行聚焦测试并确认失败基线**

运行：`php artisan test tests/Feature/Referral/BindReferrerOnRegisterTest.php tests/Feature/Referral/ProcessReferralCommissionBatchServiceTest.php tests/Feature/Referral/ReferralCommissionCommandTest.php`
预期：FAIL（当前尚无推荐关系字段、服务与命令实现）

### 任务 2：新增推荐关系与提成记录数据结构

**文件：**
- 新建：`database/migrations/2026_04_15_030000_add_referral_columns_to_users_table.php`
- 新建：`database/migrations/2026_04_15_040000_create_referral_commission_records_table.php`
- 新建：`database/migrations/2026_04_15_050000_create_referral_commission_settings_table.php`
- 修改：`database/sql/mvp_schema.sql`
- 修改：`tests/Feature/Reservation/ReservationSchemaTest.php`

**步骤 1：为 `users` 增加推荐字段**

新增：
- `invite_code`（唯一，可空，便于历史数据兼容）
- `referrer_id`（可空，外键指向 `users.id`，`nullOnDelete`，并建立索引）

**步骤 2：新增提成明细表**

创建 `referral_commission_records`，字段包括：
- `settlement_id`、`level`、`referrer_id`、`referred_user_id`、`product_id`、`position_id`、`settlement_date`
- `base_profit`、`commission_rate`、`commission_amount`
- `status`、`granted_at`、`batch_no`、时间戳

约束包括：
- 唯一键 `(settlement_id, level)`（幂等）
- 外键关联 `daily_settlements`、`users`、`products`、`positions`
- 状态枚举：`pending`、`processing`、`success`、`failed`
- 增加 `failed_reason`、`retry_count`、`last_retry_at` 审计字段
- 增加索引：`(status, id)`、`(referred_user_id, settlement_date)` 提升批处理扫描与后台查询性能

**步骤 3：新增提成比例配置表**

创建 `referral_commission_settings`，至少包含：
- `level_1_rate`（如 0.05）
- `level_2_rate`（如 0.02）
- `is_active`
- 时间戳

约束：
- 仅允许一条有效配置（MySQL 5.7 下通过“单行配置 + 固定主键策略”或应用层事务约束实现）
- 比例范围限制：`0 <= level_2_rate <= level_1_rate < 1`

**步骤 4：同步 SQL 快照**

同一变更集同步更新 `database/sql/mvp_schema.sql`（项目规则强制）。

**步骤 5：补充结构断言测试**

扩展现有 schema 测试，断言新字段/新表/索引存在。

**步骤 6：运行聚焦测试**

运行：`php artisan test tests/Feature/Reservation/ReservationSchemaTest.php`
预期：PASS

### 任务 3：实现注册链路的邀请码捕获与推荐绑定

**文件：**
- 新建：`app/Modules/Referral/Http/Middleware/CaptureInviteCodeMiddleware.php`
- 新建：`app/Modules/Referral/Services/BindReferrerOnRegisterService.php`
- 新建：`app/Modules/Referral/Support/InviteCodeGenerator.php`
- 修改：`bootstrap/app.php`
- 修改：`routes/web.php`
- 修改：`app/Models/User.php`
- 修改：`app/Modules/User/Http/Controllers/Auth/RegisteredUserController.php`

**步骤 1：实现邀请码 session 捕获中间件**

行为：
- 读取 query 参数 `invite_code`
- 做格式校验
- session 为空时写入（只写一次）
- 同时写入短期 signed cookie 作为兜底（只写一次，防覆盖）
- 中间件内不做数据库写入

**步骤 2：实现注册后绑定服务**

服务行为：
- 注册页展示时先读取 session 邀请码，无值再读取 signed cookie，用于预填输入框
- 优先从用户提交的 `POST invite_code` 读取（允许用户修改预填值）
- 若 `POST invite_code` 为空，则读取 session；session 为空再读取 signed cookie
- 按 `users.invite_code` 查找邀请人
- 防止自邀与重复绑定
- 写入 `referrer_id`（仅首次）
- 为新用户生成唯一 `invite_code`
- 绑定成功后清理 session/cookie 邀请码
- 记录绑定来源：`manual`、`prefill_session`、`prefill_cookie`、`fallback_session`、`fallback_cookie`

**步骤 3：接入注册控制器**

在用户创建后、跳转前调用绑定服务，并确保 `session()->regenerate()` 不会提前清掉邀请码来源。

**步骤 4：接入中间件到公共路由**

在 `bootstrap/app.php` 注册中间件并于 `routes/web.php` 应用，保证注册流程可读取邀请码。

**步骤 5：运行聚焦测试**

运行：`php artisan test tests/Feature/Referral/BindReferrerOnRegisterTest.php tests/Feature/Auth/AuthenticationFlowTest.php`
预期：PASS

### 任务 4：实现提成领域模型与发放服务

**文件：**
- 新建：`app/Modules/Referral/Models/ReferralCommissionRecord.php`
- 新建：`app/Modules/Referral/Services/GrantReferralCommissionForSettlementService.php`
- 新建：`app/Modules/Referral/Services/ProcessReferralCommissionBatchService.php`
- 修改：`app/Modules/Balance/Models/BalanceLedger.php`
- 新建：`app/Modules/Referral/Services/GetReferralCommissionSettingService.php`

**步骤 1：新增 referral 配置（非比例项）**

定义：
- `enabled=true`
- `go_live_date`（日期口径）
- `business_timezone`（建议 `Asia/Shanghai`）
- `batch_chunk_size`
- `batch_lock_ttl_seconds`

**步骤 2：实现单结算发放服务**

针对单条结算：
- `profit <= 0` 跳过
- 早于 `go_live_date` 跳过（基于业务时区和 `settlement_date`）
- 根据 `referrer_id` 解析一级和二级上级
- 从有效配置读取一级/二级比例（例如 5%/2%）
- 金额计算使用 decimal/BCMath（禁止 float）
- 对每个有效层级在事务内执行发放
- 更新上级余额并写余额流水
- 插入/更新提成记录状态机：`pending -> processing -> success`，异常标记 `failed` 可重试
- 插入提成记录，依赖 `(settlement_id, level)` 唯一约束实现幂等
- 防御性校验：同一结算中 `level_1_referrer_id` 与 `level_2_referrer_id` 不能相同，且均不能等于收益来源用户

**步骤 3：实现批处理服务**

按块扫描候选结算记录并调用单条发放服务，仅扫描“未成功发放”候选集，支持失败重试并记录批次号。

**步骤 4：统一余额流水类型约定**

提成流水固定：
- `type=referral_commission_credit`
- `biz_ref_type=referral_commission`

**步骤 5：运行聚焦测试**

运行：`php artisan test tests/Feature/Referral/ProcessReferralCommissionBatchServiceTest.php`
预期：PASS

### 任务 5：增加命令与定时调度

**文件：**
- 新建：`app/Modules/Referral/Console/Commands/ProcessReferralCommissionCommand.php`
- 修改：`routes/console.php`
- 修改：`bootstrap/app.php`

**步骤 1：注册 Artisan 命令**

命令示例：`referral:commission-process`

职责：
- 触发批处理服务
- 输出成功/跳过/失败统计

**步骤 2：注册调度**

在 Laravel 13 调度入口配置（例如每 5 分钟执行一次），并加互斥保护（`withoutOverlapping` / 分布式锁）避免并发重入。

**步骤 3：运行命令测试**

运行：`php artisan test tests/Feature/Referral/ReferralCommissionCommandTest.php`
预期：PASS

### 任务 6：增加后台审计视图与提成比例配置

**文件：**
- 新建：`app/Filament/Resources/ReferralCommissionRecords/ReferralCommissionRecordResource.php`
- 新建：`app/Filament/Resources/ReferralCommissionRecords/Tables/ReferralCommissionRecordsTable.php`
- 新建：`app/Filament/Resources/ReferralCommissionRecords/Pages/ListReferralCommissionRecords.php`
- 新建：`app/Filament/Resources/ReferralCommissionSettings/ReferralCommissionSettingResource.php`
- 新建：`tests/Feature/Admin/ReferralCommissionRecordManagementPageTest.php`
- 新建：`tests/Feature/Admin/ReferralCommissionSettingManagementPageTest.php`

**步骤 1：增加提成记录列表页**

展示字段：收益来源用户、获奖用户、层级、基准收益、比例、提成金额、发放时间、状态。

**步骤 2：增加后台比例配置页（管理员可编辑）**

要求：
- 管理员可设置一级、二级比例（例如一级 5%、二级 2%）
- 保存时校验范围：`0 <= 二级 <= 一级 < 1`
- 支持启用/停用配置，系统仅使用当前有效配置

**步骤 3：限制提成记录资源为只读**

提成记录资源不开放新建/编辑/删除。

**步骤 4：运行后台功能测试**

运行：`php artisan test tests/Feature/Admin/ReferralCommissionRecordManagementPageTest.php tests/Feature/Admin/ReferralCommissionSettingManagementPageTest.php`
预期：PASS

### 任务 7：全量验证与交付检查

**文件：**
- 可能修改：`README.md`（若新增配置或运维说明）
- 可能修改：`docs/plans/推荐返利-邀请裂变.md`（若最终比例或口径有调整）

**步骤 1：运行全量测试**

运行：`php artisan test`
预期：PASS

**步骤 2：运行前端构建（项目门禁）**

运行：`npm run build`
预期：PASS

**步骤 3：手工冒烟**

- 使用邀请链接注册并确认 `referrer_id` 绑定
- 准备结算数据后执行 `php artisan referral:commission-process`
- 校验提成记录与余额流水一致
- 重复执行命令，确认无重复发放

**步骤 4：整理交付说明**

至少包含：
- 修改文件清单
- 执行命令清单
- 验证结果
- 已知限制（如有）
