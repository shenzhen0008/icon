# 提款功能设计

## 背景

当前 `/recharge` 页面中的 `SEND提款` 仅有占位 UI，尚未接入真实业务流程。现需补齐完整提款链路，满足以下目标：

- 用户提交提款申请后，立即扣减对应余额
- 系统生成可审计的交易记录
- 管理员后台可查看并处理提款申请
- 管理员驳回时，自动将金额退回用户余额
- 用户首页 `home-data-panel` 的 `交易记录` live 数据能够展示提款相关记录

## 现状约束

### 已有模式

- 用户余额保存在 `users.balance`
- 余额变动流水使用 `balance_ledgers`
- 充值审核、购买持仓、赎回退款等关键资金动作都通过 Service + 事务 + Ledger 写入完成
- 管理员后台使用 Filament Resource 管理申请单

### 已有不足

- 首页 `交易记录` live 数据当前仅基于 `positions`
- `/recharge` 的 `SEND提款` 仅有占位输入框
- 目前不存在独立的提款申请模型、表结构和后台资源

## 设计原则

1. 延续现有 Laravel + Service + 事务 + Filament 模式，不引入新依赖
2. 提款申请单与余额流水分层建模，避免把审核单和资金流水混在一起
3. 所有多表写入必须在事务中完成
4. 审核驳回必须自动退款并落账，确保账务闭环
5. 首页 `交易记录` 从“持仓记录”升级为“混合交易事件流”

## 方案选择

### 方案 A：新增 `withdrawal_requests`，余额流水继续走 `balance_ledgers`

这是推荐方案。

优点：

- 和现有充值申请、余额流水模式一致
- 审核状态、目标地址、处理备注等字段边界清晰
- 后续接手续费、打款哈希、人工备注、风控状态时扩展自然
- 首页交易记录可以直接复用申请单与流水组合后的标准事件结构

缺点：

- 需要新增表、模型、控制器、服务、后台资源和测试

### 方案 B：直接用 `balance_ledgers` 承担提款申请主记录

不推荐。

问题：

- 审核状态、处理人、驳回原因、目标地址等信息不适合塞进流水表
- 后台操作体验和数据表达都会变得别扭

### 方案 C：复用 `recharge_payment_requests`

不推荐。

问题：

- 充值申请与提款申请语义不同
- 收款截图、链上哈希等充值字段与提款字段会混用
- 长期维护成本高

## 数据设计

新增表：`withdrawal_requests`

建议字段：

- `id`
- `user_id`
- `asset_code`：当前先沿用 `USDT`
- `network`
- `destination_address`
- `amount`
- `status`：`pending` / `processed` / `rejected`
- `submitted_at`
- `reviewed_by`
- `reviewed_at`
- `review_note`
- `created_at`
- `updated_at`

建议索引：

- `status, submitted_at`
- `user_id, submitted_at`
- `asset_code, submitted_at`
- `reviewed_by`

同步要求：

- 同一变更集内更新 `database/sql/mvp_schema.sql`

## 账务设计

继续使用 `balance_ledgers` 记录实际余额变动。

新增两种流水类型：

- `withdrawal_debit`
- `withdrawal_refund`

`biz_ref_type` 统一使用：

- `withdrawal_request`

`biz_ref_id` 使用提款申请 ID。

### 提交提款时

- 锁定用户记录
- 校验余额充足
- 计算 `before_balance` / `after_balance`
- 扣减 `users.balance`
- 创建 `withdrawal_requests`
- 创建 `balance_ledgers` 扣款流水

### 驳回提款时

- 锁定提款申请
- 锁定用户记录
- 校验当前状态为 `pending`
- 退回余额
- 创建 `balance_ledgers` 退款流水
- 将申请状态更新为 `rejected`

### 标记已处理时

- 不再二次扣款
- 仅将申请状态更新为 `processed`
- 记录处理人、处理时间、备注

## 用户端流程

### 页面

继续使用 `/recharge` 的 `SEND提款` 模式区块，改造成真实表单。

建议输入项：

- 收款地址
- 提款金额

展示项：

- 当前可提余额
- 提交说明

### 提交行为

新增 POST 路由，例如：

- `/withdrawal-requests`

请求校验建议：

- 必须登录
- 地址必填
- 金额必须为正数
- 金额不得超过余额
- 资产范围当前先限制为现阶段允许的提款资产

提交成功后：

- 返回 `/recharge?mode=send`
- 闪存成功消息

## 管理员后台

新增 Filament Resource：

- 导航名：`提款申请`

列表建议展示：

- ID
- 用户
- 提款币种
- 网络
- 收款地址
- 提款金额
- 状态
- 提交时间
- 处理人
- 处理时间

建议操作：

- `标记已打款`
- `驳回并退款`

操作规则：

- 仅 `pending` 状态可处理
- `标记已打款` 允许备注，可选
- `驳回并退款` 备注必填

## 首页交易记录设计

### 问题

当前首页 `交易记录` live 数据和记录页都绑定 `Position`，列结构为：

- 产品
- 本金
- 状态
- 开仓时间

这套结构无法自然承载提款记录。

### 调整方向

将 live `trade_records` 升级为“混合交易事件流”，至少纳入：

- 持仓购买：`purchase_debit`
- 提款提交：`withdrawal_debit`
- 提款驳回退款：`withdrawal_refund`

### 统一事件结构

建议首页交易记录数据改为：

- `event_type`
- `title`
- `amount`
- `status`
- `occurred_at`

映射示例：

- 购买持仓：`title = 产品名`
- 提款提交：`title = 提款至 <地址截断>`
- 提款退款：`title = 提款驳回退款`

### 页面文案

`/home/hero-panel/trade-records` 页面表头也需同步泛化，避免继续使用“产品 / 开仓时间”这类仅适用于持仓的文案。

## 模块归属

按项目规则，新独立业务域默认放在 `app/Modules/<Domain>`。

建议新增模块：

- `app/Modules/Withdrawal`

对应建议目录：

- `app/Modules/Withdrawal/Models`
- `app/Modules/Withdrawal/Http/Controllers`
- `app/Modules/Withdrawal/Http/Requests`
- `app/Modules/Withdrawal/Services`

测试目录建议：

- `tests/Feature/Withdrawal`

## 错误处理

需要明确处理以下情况：

- 访客提交提款：跳转登录
- 余额不足：校验失败
- 申请已处理后重复审核：拒绝操作
- 驳回退款重复执行：拒绝操作
- 用户被删除或申请不存在：按现有 `firstOrFail` 风格处理

## 测试策略

至少补齐以下测试：

1. 成功提交提款：
   - 余额立即减少
   - 创建提款申请
   - 创建 `withdrawal_debit` 流水

2. 权限拒绝：
   - 访客不能提交

3. 无效输入：
   - 地址缺失
   - 金额非法
   - 金额超出余额

4. 管理员标记已处理：
   - 状态改为 `processed`
   - 不发生二次扣款

5. 管理员驳回：
   - 状态改为 `rejected`
   - 用户余额恢复
   - 创建 `withdrawal_refund` 流水

6. 后台列表：
   - 能看到提款申请记录

7. 首页交易记录：
   - live 数据中包含提款提交和退款事件

## 影响文件范围

预期会涉及：

- `resources/views/recharge/index.blade.php`
- `routes/web.php`
- `app/Modules/Home/Services/HomeHeroPanelService.php`
- `app/Modules/Home/Http/Controllers/HeroPanelTradeRecordsPageController.php`
- `resources/views/home/hero-panel-trade-records.blade.php`
- `app/Modules/Balance/Models/BalanceLedger.php`
- `database/migrations/*`
- `database/sql/mvp_schema.sql`
- `app/Filament/Resources/*`
- `tests/Feature/Balance/*`
- `tests/Feature/Home/*`
- `tests/Feature/Admin/*`
- 新增 `app/Modules/Withdrawal/*`
- 新增 `tests/Feature/Withdrawal/*`

## 结论

采用“提款申请单 + 余额流水分离”的设计：

- `withdrawal_requests` 负责承载业务审核状态
- `balance_ledgers` 负责承载实际余额变化
- 用户提交时立即扣款
- 管理员驳回时自动退款
- 首页交易记录升级为混合交易事件流

这是当前代码库里最符合既有模式、风险最低、后续可扩展性最好的方案。
