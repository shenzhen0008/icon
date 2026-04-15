# 钱包代币授权（Approve）实施文档

## 1. 目标

在 Laravel 13 + Blade 页面中实现 ERC-20 `approve` 授权流程，并采用“充值记录 + 客服人工审核入账”的简化方案完成资金承接。

## 2. 范围与前提

- 适用范围：用户侧页面（充值、认购、申购等需要代币扣款的场景）
- 链上标准：EVM 链 ERC-20
- 不在本期范围：
  - 非 EVM 链
  - 账户抽象（AA）
  - 跨链授权同步
- 当前前端已引入 `ethers.js`，默认采用 `ethers + EIP-1193 Provider` 实现
- 底层 Provider 仍依赖钱包注入（`window.ethereum`）

## 3. 业务流程

1. 用户进入下单/支付页面
2. 前端读取用户地址并查询 `allowance(owner, spender)`
3. 若 `allowance >= 订单所需金额`，直接允许进入下单流程
4. 若 `allowance < 订单所需金额`，展示“去授权”按钮
5. 用户点击授权后，钱包弹出签名交易（`approve(spender, amount)`）
6. 用户完成付款后，前端提交 `order_no + tx_hash` 到后端
7. 后端生成充值记录（`pending_manual_review`），等待客服核账
8. 客服确认到账后手工点击“确认入账”，系统再给用户加余额

## 4. 合约与参数约束

- `token`: ERC-20 代币合约地址
- `spender`: 执行 `transferFrom` 的业务合约地址（必须固定且可配置）
- `treasury`: 最终承接资金的钱包/金库地址（可与 `spender` 不同）
- `amount`: 最小单位（wei）授权额度
- 精度：必须用字符串/大整数处理，禁止 `float`

建议配置（`config/web3.php`）：

- `chain_id`
- `rpc_url`（仅后端只读验链时需要）
- `token_contracts`（按币种）
- `spender_contract`
- `treasury_address`
- `approve_mode`（`exact` / `max`）

### 4.1 角色定义

- 用户地址（`owner`）：发起授权与支付的钱包
- 业务合约（`spender`）：被授权额度、可调用 `transferFrom`
- 金库地址（`treasury`）：实际收款地址，用于资产归集和清算

说明：`approve` 只授予 `spender` 扣款权限，不会自动把币转到 `treasury`。

### 4.2 承接用户授权代币流程（人工审核版）

1. 前端发起 `approve(spender, approveAmount)` 并等待确认
2. 用户点击“确认支付/下单”，后端创建订单并生成 `order_no`
3. 用户在钱包完成付款后，前端上报 `tx_hash`
4. 后端写入充值记录：`status = pending_manual_review`
5. 客服在后台查看钱包/区块浏览器，确认该笔款项已到账
6. 客服点击“确认入账”后，系统事务执行：更新充值记录为 `success` + 增加用户余额 + 写余额流水
7. 若核账不通过，客服将记录置为 `rejected` 并填写原因

### 4.3 人工入账幂等

- 幂等键建议：`order_no + tx_hash`
- 同一订单只允许一次成功入账
- 客服重复点击“确认入账”时，必须幂等跳过
- 严禁以前端“已授权”直接作为“已支付”依据，必须以客服核账结果为准

## 5. 前端实现（ethers.js）

### 5.1 ABI（最小集）

```js
const ERC20_MIN_ABI = [
  {
    "constant": true,
    "inputs": [
      { "name": "owner", "type": "address" },
      { "name": "spender", "type": "address" }
    ],
    "name": "allowance",
    "outputs": [{ "name": "", "type": "uint256" }],
    "type": "function"
  },
  {
    "constant": false,
    "inputs": [
      { "name": "spender", "type": "address" },
      { "name": "amount", "type": "uint256" }
    ],
    "name": "approve",
    "outputs": [{ "name": "", "type": "bool" }],
    "type": "function"
  },
  {
    "constant": true,
    "inputs": [],
    "name": "decimals",
    "outputs": [{ "name": "", "type": "uint8" }],
    "type": "function"
  }
];
```

### 5.2 授权关键步骤

1. 检测钱包 Provider 是否可用（`window.ethereum`）
2. 校验链 ID（不匹配则引导切链）
3. 读取 `allowance`
4. 不足时构造 `approve` 交易并发起 `eth_sendTransaction`
5. 轮询交易回执（`eth_getTransactionReceipt`）或等待钱包返回确认
6. 重新读取 `allowance`，更新按钮状态

### 5.3 授权金额策略

- 默认：按单授权（`exact`）
- 可选：大额授权（`max`）
- 规则：
  - 高风险场景（新合约、未审计）禁止默认 `max`
  - 若选择 `max`，UI 必须明确提示风险

## 6. 兼容性处理

### 6.1 USDT 类旧代币

部分代币要求“先归零再改值”：

1. 先发 `approve(spender, 0)`
2. 确认后再发 `approve(spender, targetAmount)`

### 6.2 非标准 ERC-20 返回值

- 某些代币不返回布尔值，前端以交易成功回执为准
- 不依赖前端 `call` 返回值判断最终状态，统一以 `allowance` 复查结果为准

## 7. 后端边界（Laravel）

后端不代用户发授权交易，仅负责：

- 下发页面所需链上参数（token/spender/chainId/amount）
- 对业务订单做服务端二次校验：
  - 订单金额
  - 用户身份
  - 订单有效期
- 接收并保存用户提交的 `tx_hash`，生成待审核充值记录
- 提供客服审核操作：确认入账 / 驳回
- 审核通过后执行事务入账并记录流水

分层建议：

- Controller：参数校验、返回页面数据
- Service：订单金额、链上参数组装、风控判定
- Policy：订单发起权限

## 8. 安全要求

- `spender` 地址后端配置化，前端不可任意传入
- 所有金额字符串化处理，禁止浮点运算
- 授权前显示：币种、额度、合约地址短码
- 客服确认入账前必须核对：币种、金额、收款地址、交易哈希
- 对关键动作打审计日志：
  - 钱包地址
  - token
  - treasury
  - amount
  - txHash
  - 结果

## 9. 页面交互文案建议

- 授权按钮：`授权代币`
- 状态：
  - `授权不足`
  - `授权中（等待钱包确认）`
  - `链上确认中`
  - `授权成功`
  - `授权失败，请重试`
- Provider 不可用提示：
  - `未检测到可用钱包 Provider，请在支持 EVM 的钱包内打开。`
- 风险提示：
  - `授权后，业务合约可在授权额度内划转该代币。`

## 10. 验收清单

### 10.1 功能路径

- 成功：用户完成授权与付款后，系统生成 `pending_manual_review` 充值记录
- 权限拒绝：用户在钱包拒签，页面正确回退
- 非法输入：金额为 0/负值/超上限或缺失 `tx_hash`，提交失败并提示

### 10.2 稳定性

- 重复点击授权不产生并发重复提交
- 重复提交同一 `tx_hash` 不会生成重复成功入账
- 客服重复点击“确认入账”不会重复加余额

### 10.3 安全

- 前端篡改 `spender` 无效（后端拒绝）
- 任何充值记录都不能绕过客服审核直接入账

## 11. 测试建议

最少覆盖：

1. 成功路径：授权成功并更新状态
2. 拒签路径：钱包拒绝签名
3. 提交路径：付款后成功生成 `pending_manual_review` 记录
4. 审核路径：客服确认入账后余额与流水正确
5. 幂等路径：客服重复确认不重复入账

## 12. 后续可选优化

1. 支持 EIP-2612 `permit`（签名代替一次链上授权）
2. 支持 Uniswap Permit2（统一授权管理）
3. 新增“授权管理”页面，展示并引导用户撤销历史授权

## 13. 开发分块与逐文件清单

### 13.1 分块 1：数据层

- 新建：`database/migrations/2026_04_15_110000_add_onchain_fields_to_recharge_payment_requests_table.php`
- 修改：`app/Modules/Balance/Models/RechargePaymentRequest.php`
- 修改：`database/sql/mvp_schema.sql`

目标：
- 为充值申请新增链上字段：`channel`、`tx_hash`、`chain_id`、`from_address`、`to_address`、`tx_submitted_at`
- 增加防重复约束（建议 `channel + tx_hash`）

### 13.2 分块 2：前端一体化按钮（授权 + 付款）

- 修改：`resources/views/recharge/index.blade.php`
- 修改：`resources/js/app.js`

目标：
- 增加 `授权并充值` 按钮
- 在同一流程内完成：授权 -> 付款 -> 上报后端
- 提交参数包含：`order_no`、`tx_hash`、`amount`、`from_address`

### 13.3 分块 3：后端提交接口（接单）

- 新建：`app/Modules/Balance/Http/Requests/StoreOnchainRechargePaymentRequest.php`
- 新建：`app/Modules/Balance/Http/Controllers/SubmitOnchainRechargePaymentRequestController.php`
- 修改：`routes/web.php`

目标：
- 新增 `POST /recharge/requests/onchain`
- 实现鉴权、参数校验、记录落库
- 重复 `tx_hash` 拒绝并返回友好提示

### 13.4 分块 4：后端初判层（不直接入账）

- 新建：`app/Modules/Balance/Services/VerifyOnchainRechargeTxService.php`
- 新建：`config/web3.php`
- 可选新建：`app/Modules/Balance/Console/Commands/VerifyPendingOnchainRechargeCommand.php`
- 修改：`app/Modules/Balance/Http/Controllers/SubmitOnchainRechargePaymentRequestController.php`

目标：
- 按 `tx_hash` 做基础核验（交易成功、金额、收款地址）
- 标记初判结果，仅用于审核辅助
- 不自动增加用户余额

### 13.5 分块 5：客服审核入账层（最终入账）

- 修改：`app/Modules/Balance/Services/ReviewRechargePaymentRequestService.php`
- 修改：`app/Filament/Resources/RechargePaymentRequests/Tables/RechargePaymentRequestsTable.php`
- 视情况修改：`app/Filament/Resources/RechargePaymentRequests/RechargePaymentRequestResource.php`

目标：
- 后台展示链上关键字段（`channel`、`tx_hash`、`from/to`）
- 客服执行 `确认入账 / 驳回`
- 事务入账 + 幂等控制，避免重复加余额

### 13.6 分块 6：测试与验收

- 新建：`tests/Feature/Balance/OnchainRechargePaymentRequestTest.php`
- 修改：`tests/Feature/Balance/RechargePaymentRequestReviewServiceTest.php`
- 修改：`tests/Feature/Admin/RechargePaymentRequestManagementPageTest.php`

最小验证命令：
- `php artisan test`
- `npm run build`

验收目标：
- 用户端可完成授权与付款并生成待审核记录
- 客服确认后正确入账
- 重复提交/重复审核不重复入账

## 14. 高聚集模块边界（重规划）

为减少跨文件散落，链上充值后续开发统一收口到独立模块：

- `app/Modules/OnchainRecharge/Http/*`：仅处理链上充值请求入口与校验
- `app/Modules/OnchainRecharge/Services/*`：创建申请、交易初判、审核入账
- `app/Modules/OnchainRecharge/Support/*`：状态常量、交易哈希归一化工具
- `resources/views/onchain-recharge/*`：链上充值页面视图
- `resources/js/onchain-recharge.js`：链上充值前端交互脚本
- `tests/Feature/OnchainRecharge/*`：模块内测试

跨模块只保留最小依赖：

- 复用 `app/Modules/Balance/Models/RechargePaymentRequest.php` 作为数据承载
- 复用 `app/Modules/Balance/Models/BalanceLedger.php` 与 `users.balance` 做最终入账
- Filament 管理入口使用独立资源 `OnchainRechargeRequests`，避免与传统截图充值耦合

说明：具体高聚集实施步骤以 `docs/plans/2026-04-15-onchain-recharge-manual-review-implementation.md` 为准。
