# 2026-04-17 个人中心银行卡资金流程设计稿

## 1. 目标与范围

### 1.1 目标
在个人中心「银行卡」入口实现如下闭环：
1. 用户在个人中心选择「银行卡」并点击「下一步」。
2. 后端不再检查“是否已绑定银行卡”，直接进入银行卡资金页（含充值/提现两个页签，交互类似 `/recharge`）。
3. 提现时由用户在当次提现表单填写银行卡信息，并与提现订单一起提交。
4. 充值和提现提交后进入现有人工审核流，管理员在后台审批处理。

### 1.2 本次范围
1. 新增“银行卡资金页”（充值 + 提现，结构对齐 `/recharge`）。
2. 充值申请写入 `recharge_payment_requests`。
3. 提现申请继续写入 `withdrawal_requests`，并新增 `meta_json` 存储银行卡快照。
4. 个人中心银行卡入口改造（不再分流到绑卡页）。

### 1.3 非目标
1. 不做自动到账（仍为人工审核）。
2. 不引入新前端组件库或后台框架。
3. 不改变现有链上充值流程与加密货币提现流程。
4. 不做独立“用户银行卡管理中心”（MVP 阶段）。

## 2. 实施方案（方案 A）

### 方案 A：无绑卡表的订单快照方案（MVP）
1. 新建 `app/Modules/BankRecharge/*`。
2. 不新增 `user_bank_cards`。
3. 银行卡充值申请仍写入 `recharge_payment_requests`，通过 `channel=bank_card_manual` 区分。
4. 银行卡提现复用现有 `POST /withdrawal-requests`、`SubmitWithdrawalRequestService` 与 `WithdrawalRequestResource`。
5. 在 `withdrawal_requests` 增加 `meta_json`（nullable），用于存银行卡信息快照。

优点：
1. 结构最轻，开发最快，符合 MVP。
2. 与现有人工审核链路兼容，改造面最小。
3. 不引入额外“绑卡维护”交互和数据生命周期问题。

缺点：
1. 用户每次提现需要重复填写银行卡信息。
2. 后续若要支持“默认卡/多卡”，需要再补独立银行卡模型。

## 3. 用户流程与页面交互

### 3.1 入口流程（个人中心）
1. 用户在 `/me` 选择 `payment-method=bank-card` 并点击“下一步”。
2. 请求 `GET /recharge/bank/entry`（需登录）。
3. 后端固定 302 到 `GET /recharge/bank`，不再做绑卡存在性判断。

### 3.2 银行卡资金页（充值 + 提现）
页面结构（类似 `/recharge` 的 receive/send）：
1. 顶部模式切换：`充值` / `提现` 两个页签。
2. 充值页签：
- 步骤提示：`1.复制平台收款卡信息 -> 2.去银行App转账 -> 3.上传截图提交`
- 平台收款信息卡片（可复制）：银行名称、户名、卡号、支行（可选）
3. 充值申请表单：
- 联系账号（只读，当前用户名）
- 付款金额（必填）
- 转账截图（必填，image<=5MB）
- 用户备注（可选）
4. 充值提交 `POST /recharge/bank/requests`，成功后提示“充值申请已提交，等待管理员审核”。
5. 提现页签（每次填写银行卡信息）：
- 可提现余额（只读）
- 开户银行（必填）
- 持卡人姓名（必填）
- 银行卡号（必填）
- 开户支行（可选）
- 预留手机号（可选）
- 提现金额（必填）
- 备注（可选）
6. 提现提交统一走现有 `POST /withdrawal-requests`，`network=BANK_CARD`，成功后提示“提现申请已提交，等待管理员审核”。

## 4. 数据设计

### 4.1 复用表：`recharge_payment_requests`
新增/约定：
1. `channel` 使用新值：`bank_card_manual`。
2. `asset_code` 固定写 `BANK`（或 `CNY_BANK`，二选一并全局统一）。
3. `currency` 固定写 `CNY`。
4. `network` 固定写 `BANK_TRANSFER`。
5. `receipt_address` 存平台收款卡号。
6. `user_note` 可附加用户填写的说明。

### 4.2 复用表：`withdrawal_requests`（银行卡提现）
新增字段：
1. `meta_json` nullable（MySQL 5.7 可用 `json`，如受限则用 `text`）。

约定：
1. `asset_code` 固定写 `BANK`（或 `CNY_BANK`，与充值保持一致）。
2. `network` 固定写 `BANK_CARD`。
3. `destination_address` 存用户当次提交的银行卡号（用于沿用现有字段语义）。
4. `meta_json` 存银行卡提现快照（示例键）：
- `bank_name`
- `account_name`
- `card_number`
- `branch_name`
- `reserved_phone`
- `submitted_from`（固定 `bank_recharge_page`）
5. `amount`、`status`、`review_note` 继续复用现有提现审核流程。
6. 余额扣减与驳回退回逻辑继续复用现有 `SubmitWithdrawalRequestService` / 审核服务。

## 5. 配置与安全

### 5.1 平台收款银行卡配置
复用现有“平台收款配置”体系（与当前收款钱包地址同源配置管理），不新增独立配置入口。
1. 现有收款配置来源（需在实现中明确标记并复用）：
- 主要数据源：`recharge_receivers` 表（字段：`asset_code`, `network`, `address`, `is_active`, `sort`）
- 对应模型：`app/Modules/Balance/Models/RechargeReceiver.php`
- 用户端读取：`app/Modules/Balance/Http/Controllers/RechargePageController.php`
- 提交时读取：`app/Modules/Balance/Http/Controllers/SubmitRechargePaymentRequestController.php`
- 后台维护入口：`app/Filament/Resources/RechargeReceivers/RechargeReceiverResource.php`
2. 收款地址配置初始化来源：
- 配置文件：`config/recharge.php`（`recharge.assets.*.address`）
- 初始化迁移：`database/migrations/2026_04_08_020000_create_recharge_receivers_table.php`（从 `config('recharge.assets')` 灌入 `recharge_receivers`）
3. 在上述现有收款配置体系中扩展银行卡收款字段：
- `receiver_bank_name`
- `receiver_account_name`
- `receiver_card_number`
- `receiver_branch_name`
- `bank_recharge_enabled`
4. 银行卡充值页面与提交服务统一从该现有配置源读取。
5. 若现有收款配置缺失或银行卡开关关闭，则前台禁止提交并提示“收款账户暂不可用”。

### 5.2 安全要求（MVP）
1. 写接口全部 `auth`。
2. 充值申请与提现申请都使用 FormRequest 校验。
3. 后台仅授权角色可查看银行卡相关字段。
4. 上传截图沿用 `public` 磁盘策略与格式/大小校验。
5. 本期不增加复杂加密/脱敏策略，后续安全增强可在二期补充。

## 6. 路由与后端设计

### 6.1 路由（建议）
1. `GET /recharge/bank/entry`：银行卡入口（固定跳转银行卡资金页）。
2. `GET /recharge/bank`：银行卡资金页（`?mode=receive|send`）。
3. `POST /recharge/bank/requests`：提交银行卡充值申请。
4. （不新增独立提现提交路由）银行卡提现提交复用 `POST /withdrawal-requests`。

说明：
1. 现有 `/recharge` 提现后端已存在：`POST /withdrawal-requests`（`SubmitWithdrawalRequestController`）。
2. 银行卡提现必须复用该提交入口与现有 `SubmitWithdrawalRequestService`，确保与当前后台“提款申请”同一数据流、同一审核流。
3. 区分方式：`withdrawal_requests.network = BANK_CARD`，银行卡扩展信息写入 `withdrawal_requests.meta_json`。

全部放在 `auth` 中间件组。

### 6.2 控制器职责（分层约束）
1. Controller：只做请求接收、调用 Service、返回视图/重定向。
2. Service：
- `ResolveBankRechargeEntryService`（入口跳转）
- `CreateBankRechargeRequestService`（充值申请写入）
- `BuildBankWithdrawalPayloadService`（将提现表单银行卡字段映射为 `/withdrawal-requests` 所需 payload，含 `meta_json`）
3. Policy/Gate：
- `WithdrawalRequestPolicy`（如需细化银行卡字段可见范围）
- `RechargePaymentRequestPolicy`（如需细化用户查看自身记录）

## 7. 前端/Blade 设计

### 7.1 个人中心入口改造
文件：`resources/views/components/me/payment-method-panel.blade.php`
1. 现有 form action 从固定 `/recharge` 改为 `/recharge/entry`。
2. 根据 radio 值分流：
- `crypto` -> `/recharge`
- `bank-card` -> `/recharge/bank/entry`
3. 访客点击银行卡时，引导登录（与现有登录流程一致）。

### 7.2 银行卡资金页面视觉
遵循现有 Tailwind + 主题变量：
1. 使用与 `/recharge` 同等级的卡片布局。
2. 顶部提供 `充值/提现` 双页签，样式与 `/recharge` 模式切换一致。
3. 充值页签提供“复制卡号”“复制户名”按钮。
4. 提现页签改为“每次填写银行卡信息 + 提现金额”的单页表单。
5. 表单与错误提示风格复用现有 `recharge/index.blade.php`。

## 8. 后台审批与审计

1. 充值审核继续使用 `RechargePaymentRequestResource`。
2. 充值列表增加 `channel` badge（manual_transfer / onchain_wallet / bank_card_manual）。
3. 当 `channel=bank_card_manual` 时：
- `asset_code/currency/network` 显示银行卡语义值
- 继续通过“标记已处理/驳回”完成审核
4. 提现审核继续使用现有 `WithdrawalRequestResource`（与 `/recharge` 提现完全同一后台入口，通过 `network=BANK_CARD` 区分银行卡提现）。
5. 后台在银行卡提现场景可读取 `meta_json` 展示银行卡快照字段，便于审核。
6. 现有提现审核动作服务为 `ReviewWithdrawalRequestService`，银行卡提现沿用该服务，不新增重复审核逻辑。
7. 审计：沿用 `review_note/reviewed_by/reviewed_at`。

## 9. 校验与错误处理

### 9.1 提现申请校验（银行卡）
1. `network` 必须为 `BANK_CARD`（银行卡提现场景）。
2. `amount` 必填 numeric min 0.01，且不得超过当前可提现余额。
3. `bank_name` 必填，`string|max:100`。
4. `account_name` 必填，`string|max:100`。
5. `card_number` 必填，`digits_between:12,30`。
6. `branch_name` 可选，`string|max:120`。
7. `reserved_phone` 可选，`string|max:20`。
8. 若银行卡相关字段缺失，返回校验错误并停留提现页。

### 9.2 充值申请校验
1. `payment_amount` 必填 numeric min 0.01。
2. `receipt_image` 必填 image max 5120。
3. 若平台收款配置缺失：禁止提交并提示“收款账户暂不可用”。

## 10. 测试设计（质量门）

按项目规则最少覆盖：成功、无权限、无效输入。

### 10.1 Feature Tests
1. 登录用户访问 `/recharge/bank/entry` 跳转到 `/recharge/bank`（成功路径）。
2. 未登录访问上述路径返回登录重定向（权限拒绝路径）。
3. 提交银行卡充值申请成功写入 `recharge_payment_requests`，`channel=bank_card_manual`。
4. 提交充值申请缺少截图/金额非法时返回校验错误。
5. 从银行卡资金页提交提现后，成功写入 `withdrawal_requests`，且记录为 `network=BANK_CARD`。
6. 银行卡提现时 `meta_json` 正确包含银行卡快照字段。
7. 提现余额不足/金额非法时返回校验错误。
8. 银行卡字段缺失时返回校验错误（无效输入路径）。

### 10.2 验证命令
1. `php artisan test`
2. `npm run build`

## 11. 受影响文件清单（实施阶段预估）

1. `routes/web.php`
2. `resources/views/components/me/payment-method-panel.blade.php`
3. `resources/views/recharge/bank-index.blade.php`（含充值/提现双页签）
4. `app/Modules/BankRecharge/Http/Controllers/*`
5. `app/Modules/BankRecharge/Http/Requests/*`
6. `app/Modules/BankRecharge/Services/*`
7. `app/Modules/Withdrawal/Http/Controllers/SubmitWithdrawalRequestController.php`
8. `app/Modules/Withdrawal/Http/Requests/StoreWithdrawalRequest.php`
9. `app/Modules/Withdrawal/Services/SubmitWithdrawalRequestService.php`
10. `database/migrations/*(新增 withdrawal_requests.meta_json 字段)`
11. `database/sql/mvp_schema.sql`
12. `lang/zh-CN/pages/*`、`lang/en/pages/*`
13. `app/Filament/Resources/RechargePaymentRequests/Tables/RechargePaymentRequestsTable.php`
14. `app/Filament/Resources/WithdrawalRequests/*`（如需显示 `meta_json` 快照字段）

## 12. 风险与边界

1. MySQL 5.7 下 `json`/高级约束受限，字段与索引需保持简单。
2. 用户每次提现重复填写银行卡信息，体验弱于“已绑卡自动带出”。
3. `meta_json` 为半结构化字段，后续统计分析成本会高于独立明细表。
4. 后续若要支持“多卡/默认卡”，建议升级为 `withdrawal_request_bank_details` 或 `user_bank_cards`。

## 13. MVP交付建议

第一期最小可用：
1. 不做独立绑卡表。
2. 银行卡充值申请 + 银行卡提现申请（均为人工审核）。
3. 银行卡信息仅随提现订单提交并保存在 `withdrawal_requests.meta_json`。
4. 不做自动到账/自动打款，不做绑卡历史管理。

这样可以在最小改造下完成银行卡充值/提现的人工审核闭环。
