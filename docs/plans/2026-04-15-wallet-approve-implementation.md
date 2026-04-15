# 钱包链上充值实施文档（方案2：直接转账）

## 1. 目标

在 Laravel 13 + Blade 页面中实现 ERC-20 直接转账充值流程（`transfer`），并采用“充值记录 + 客服人工审核入账”的简化方案完成资金承接。

说明：本方案不使用 `approve/transferFrom`。

## 2. 范围与前提

- 适用范围：用户侧链上充值页面
- 链上标准：EVM 链 ERC-20
- 不在本期范围：
  - 非 EVM 链
  - 账户抽象（AA）
  - 自动链上监听入账
- 当前前端已引入 `ethers.js`，采用 `ethers + EIP-1193 Provider` 实现
- 底层 Provider 仍依赖钱包注入（`window.ethereum`）

## 3. 业务流程

1. 用户从首页点击“直接付款（链上充值）”，进入 `/recharge/onchain`
2. 用户选择币种/网络并填写付款金额
3. 用户点击“拉起钱包直接付款（USDT）”
4. 前端连接钱包并发起 `token.transfer(to, amount)`
5. 交易确认后，前端自动回填 `tx_hash`、`from_address`、`chain_id`
6. 用户提交链上充值申请
7. 后端生成充值记录（`pending_manual_review`），等待客服核账
8. 客服确认到账后手工点击“确认入账”，系统再给用户加余额

## 4. 合约与参数约束

- `token`: ERC-20 代币合约地址（如 BSC USDT）
- `to_address`: 收款地址（当前来自 `recharge_receivers` 配置）
- `amount`: 最小单位（wei）付款金额
- 精度：必须用字符串/大整数处理，禁止 `float`

建议配置（`config/web3.php`）：

- `chain_id`
- `token_address`
- `treasury_addresses`（按币种+网络）

### 4.1 角色定义

- 用户地址（`from`）：发起付款的钱包
- 收款地址（`to`）：业务收款钱包
- 平台后端：只负责收单、审核、入账，不代用户发交易

### 4.2 资金承接流程（人工审核版）

1. 用户在钱包完成 `transfer`
2. 前端提交 `tx_hash + from_address + chain_id + amount + asset_code`
3. 后端写入充值记录：`status = pending_manual_review`
4. 客服在后台查看钱包/区块浏览器，确认该笔款项已到账
5. 客服点击“确认入账”后，系统事务执行：更新充值记录为 `success` + 增加用户余额 + 写余额流水
6. 若核账不通过，客服将记录置为 `rejected` 并填写原因

### 4.3 人工入账幂等

- 幂等键建议：`channel + tx_hash`
- 同一笔交易只允许一次成功入账
- 客服重复点击“确认入账”时，必须幂等跳过
- 严禁以前端“已提交”直接作为“已支付”依据，必须以客服核账结果为准

## 5. 前端实现（ethers.js）

### 5.1 ABI（最小集）

```js
const ERC20_MIN_ABI = [
  'function decimals() view returns (uint8)',
  'function transfer(address to, uint256 value) returns (bool)'
];
```

### 5.2 付款关键步骤

1. 检测钱包 Provider（`window.ethereum`）
2. 连接钱包并读取地址、链 ID
3. 读取 `decimals`，将输入金额转为最小单位
4. 发起 `transfer(to, amount)`
5. 等待交易确认
6. 自动回填 `tx_hash/from_address/chain_id`
7. 用户提交申请进入人工审核

## 6. 兼容性处理

### 6.1 USDT 类代币

- USDT 在不同链地址不同，必须按链配置正确 `token_address`
- 不同链 gas 币不同（如 BSC 需要 BNB），用户钱包需有足够手续费

### 6.2 非标准 ERC-20 返回值

- 某些代币返回值不标准，前端以交易回执成功为准
- 最终入账不依赖前端状态，仍以客服核账为准

## 7. 后端边界（Laravel）

后端不代用户发链上交易，仅负责：

- 下发页面所需链上参数（token/chainId）
- 接收并保存用户提交的 `tx_hash`，生成待审核充值记录
- 提供客服审核操作：确认入账 / 驳回
- 审核通过后执行事务入账并记录流水

分层建议：

- Controller：参数校验、返回页面数据
- Service：创建申请、幂等判重、审核入账
- Policy：操作权限控制

## 8. 安全要求

- 收款地址必须后端配置化，前端不可任意指定
- 所有金额字符串化处理，禁止浮点运算
- 付款前显示：币种、金额、收款地址短码
- 客服确认入账前必须核对：币种、金额、收款地址、交易哈希
- 对关键动作打审计日志：
  - 钱包地址
  - token
  - to_address
  - amount
  - txHash
  - 结果

## 9. 页面交互文案建议

- 首页按钮：`直接付款（链上充值）`
- 充值页按钮：`拉起钱包直接付款（USDT）`
- 状态：
  - `付款处理中...`
  - `交易已上链，交易哈希已自动填充，请提交申请。`
  - `付款失败，请重试`
- Provider 不可用提示：
  - `未检测到可用钱包 Provider，请在支持 EVM 的钱包内打开。`

## 10. 验收清单

### 10.1 功能路径

- 成功：用户完成付款并提交后，系统生成 `pending_manual_review` 充值记录
- 权限拒绝：用户在钱包拒签，页面正确回退
- 非法输入：金额为 0/负值/超上限或缺失 `tx_hash`，提交失败并提示

### 10.2 稳定性

- 重复点击付款按钮不产生并发重复提交
- 重复提交同一 `tx_hash` 不会生成重复成功入账
- 客服重复点击“确认入账”不会重复加余额

### 10.3 安全

- 前端篡改收款地址无效（后端以配置和资产通道为准）
- 任何充值记录都不能绕过客服审核直接入账

## 11. 测试建议

最少覆盖：

1. 成功路径：钱包付款成功后可提交待审核记录
2. 拒签路径：钱包拒绝签名
3. 提交路径：`tx_hash` 唯一约束生效
4. 审核路径：客服确认入账后余额与流水正确
5. 幂等路径：客服重复确认不重复入账

## 12. 后续可选优化

1. 增加链上自动校验服务（按 `tx_hash` 校验收款地址/金额）
2. 增加客服“核账辅助”字段（浏览器链接、解析金额）
3. 引入事件监听任务，降低人工核账成本

## 13. 开发分块与逐文件清单

### 13.1 分块 1：数据层

- 新建：`database/migrations/2026_04_15_110000_add_onchain_fields_to_recharge_payment_requests_table.php`
- 修改：`app/Modules/Balance/Models/RechargePaymentRequest.php`
- 修改：`database/sql/mvp_schema.sql`

目标：
- 为充值申请新增链上字段：`channel`、`tx_hash`、`chain_id`、`from_address`、`to_address`、`tx_submitted_at`
- 增加防重复约束（建议 `channel + tx_hash`）

### 13.2 分块 2：前端一体化按钮（直接付款）

- 修改：`resources/views/components/home/hero.blade.php`
- 修改：`resources/views/onchain-recharge/index.blade.php`
- 修改：`resources/js/onchain-recharge.js`

目标：
- 首页进入链上充值页
- 在充值页完成：连接钱包 -> 直接付款 -> 自动回填交易信息
- 提交参数包含：`tx_hash`、`amount`、`from_address`、`chain_id`、`asset_code`

### 13.3 分块 3：后端提交接口（接单）

- 新建：`app/Modules/OnchainRecharge/Http/Requests/StoreOnchainRechargeRequest.php`
- 新建：`app/Modules/OnchainRecharge/Http/Controllers/SubmitOnchainRechargeRequestController.php`
- 修改：`routes/web.php`

目标：
- `POST /recharge/onchain/requests`
- 实现鉴权、参数校验、记录落库
- 重复 `tx_hash` 拒绝并返回友好提示

### 13.4 分块 4：后端初判层（不直接入账）

- 新建：`app/Modules/OnchainRecharge/Services/VerifyOnchainRechargeTxService.php`
- 修改：`config/web3.php`

目标：
- 按 `tx_hash` 做基础核验（可选）
- 标记初判结果，仅用于审核辅助
- 不自动增加用户余额

### 13.5 分块 5：客服审核入账层（最终入账）

- 修改：`app/Modules/OnchainRecharge/Services/ReviewOnchainRechargeRequestService.php`
- 修改：`app/Filament/Resources/OnchainRechargeRequests/Tables/OnchainRechargeRequestsTable.php`

目标：
- 后台展示链上关键字段（`channel`、`tx_hash`、`from/to`）
- 客服执行 `确认入账 / 驳回`
- 事务入账 + 幂等控制，避免重复加余额

### 13.6 分块 6：测试与验收

- 新建：`tests/Feature/OnchainRecharge/SubmitOnchainRechargeRequestTest.php`
- 新建/修改：`tests/Feature/OnchainRecharge/ReviewOnchainRechargeRequestServiceTest.php`

最小验证命令：
- `php artisan test`
- `npm run build`

验收目标：
- 用户端可完成直接付款并生成待审核记录
- 客服确认后正确入账
- 重复提交/重复审核不重复入账

## 14. 高聚集模块边界

为减少跨文件散落，链上充值统一收口到独立模块：

- `app/Modules/OnchainRecharge/Http/*`：链上充值请求入口与校验
- `app/Modules/OnchainRecharge/Services/*`：创建申请、交易初判、审核入账
- `app/Modules/OnchainRecharge/Support/*`：状态常量、交易哈希归一化
- `resources/views/onchain-recharge/*`：链上充值页面视图
- `resources/js/onchain-recharge.js`：链上充值前端交互脚本
- `tests/Feature/OnchainRecharge/*`：模块内测试

跨模块只保留最小依赖：

- 复用 `app/Modules/Balance/Models/RechargePaymentRequest.php` 作为数据承载
- 复用 `app/Modules/Balance/Models/BalanceLedger.php` 与 `users.balance` 做最终入账
- Filament 管理入口使用独立资源 `OnchainRechargeRequests`，避免与传统截图充值耦合

说明：具体实施步骤以 `docs/plans/2026-04-15-onchain-recharge-manual-review-implementation.md` 为准。
