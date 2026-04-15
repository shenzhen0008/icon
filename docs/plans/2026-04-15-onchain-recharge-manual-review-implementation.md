# 链上充值通道（人工审核）实施细节 Implementation Plan（高聚集版）

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** 实现“前端授权+付款一体提交、后端生成待审充值记录、客服人工核账后入账”的链上充值通道，并将业务代码高聚集到独立模块。

**Architecture:** 新增独立业务模块 `app/Modules/OnchainRecharge` 承载链上充值的 Request/Controller/Service/Support，减少散落在 `Balance` 下的实现。数据层复用现有 `recharge_payment_requests` 表，仅新增链上字段；余额入账继续复用 `Balance` 账本能力。后台管理新增链上充值专用 Filament 资源，避免与传统截图充值逻辑耦合。

**Tech Stack:** Laravel 13, PHP 8.3, Blade, Tailwind, Filament 4, MySQL 5.7, PHPUnit, Vite, ethers.js

---

## 0. 高聚集目录规划

### 0.1 新增模块目录（核心）

- `app/Modules/OnchainRecharge/Http/Controllers/SubmitOnchainRechargeRequestController.php`
- `app/Modules/OnchainRecharge/Http/Requests/StoreOnchainRechargeRequest.php`
- `app/Modules/OnchainRecharge/Services/CreateOnchainRechargeRequestService.php`
- `app/Modules/OnchainRecharge/Services/VerifyOnchainRechargeTxService.php`
- `app/Modules/OnchainRecharge/Services/ReviewOnchainRechargeRequestService.php`
- `app/Modules/OnchainRecharge/Support/TxHashNormalizer.php`
- `app/Modules/OnchainRecharge/Support/OnchainRechargeStatus.php`

### 0.2 最小跨模块触点（允许）

- 复用数据模型：`app/Modules/Balance/Models/RechargePaymentRequest.php`
- 复用余额账本：`app/Modules/Balance/Models/BalanceLedger.php`
- 用户余额字段：`users.balance`
- Filament 管理入口（仅路由与资源注册层）

### 0.3 视图与测试高聚集

- 视图：`resources/views/onchain-recharge/*`
- 测试：`tests/Feature/OnchainRecharge/*`
- 管理测试：`tests/Feature/Admin/OnchainRechargeRequestManagementPageTest.php`

---

## 1. 数据模型实施细节（复用现有表）

### 1.1 表结构变更（`recharge_payment_requests`）

新增字段：
- `channel` `varchar(20)`：`manual_transfer` / `onchain_wallet`
- `tx_hash` `varchar(100)` nullable
- `chain_id` `varchar(32)` nullable
- `from_address` `varchar(64)` nullable
- `to_address` `varchar(64)` nullable
- `tx_submitted_at` `timestamp` nullable

索引/约束：
- 索引：`(channel, status, submitted_at)`
- 唯一：`(channel, tx_hash)`

### 1.2 SQL 快照同步

- 修改：`database/sql/mvp_schema.sql`

### 1.3 模型改动（仅扩字段）

- 修改：`app/Modules/Balance/Models/RechargePaymentRequest.php`
- 扩充 `$fillable` / `casts`，不在模型层增加链上业务判断（保持单一职责）

---

## 2. 路由与接口（收口到 OnchainRecharge 模块）

### 2.1 路由

- 修改：`routes/web.php`
- 新增：`POST /recharge/onchain/requests` -> `SubmitOnchainRechargeRequestController`
- 保持 `auth` 中间件

### 2.2 入参校验

- 文件：`app/Modules/OnchainRecharge/Http/Requests/StoreOnchainRechargeRequest.php`
- 规则：
  - `asset_code` required + active
  - `payment_amount` numeric + `min:0.01`
  - `tx_hash` required + `0x` 66长度
  - `chain_id` required|string|max:32
  - `from_address` nullable|地址格式
  - `user_note` nullable|max:500

### 2.3 控制器职责

- 文件：`app/Modules/OnchainRecharge/Http/Controllers/SubmitOnchainRechargeRequestController.php`
- 只做：鉴权用户 + 请求验证 + 调用服务 + 返回响应
- 不做：余额入账、不做复杂业务分支

---

## 3. 服务边界（高聚集核心）

### 3.1 创建申请服务

- 文件：`app/Modules/OnchainRecharge/Services/CreateOnchainRechargeRequestService.php`
- 责任：
  - 归一化 `tx_hash`（小写、去空白）
  - 读取配置收款地址快照写入 `to_address`
  - 创建 `channel=onchain_wallet`、`status=pending`
  - 检查重复哈希并抛业务异常

### 3.2 初判服务（可选）

- 文件：`app/Modules/OnchainRecharge/Services/VerifyOnchainRechargeTxService.php`
- 责任：
  - 按 `tx_hash` 进行基础核验（交易存在、状态、金额、收款地址）
  - 仅返回判定结果，**不自动入账**

### 3.3 审核入账服务

- 文件：`app/Modules/OnchainRecharge/Services/ReviewOnchainRechargeRequestService.php`
- 责任：
  - `markProcessed` / `reject`
  - 事务内：锁申请 + 锁用户 + 写余额 + 写 ledger + 更新状态
  - 幂等：仅 `pending` 可处理

说明：为高聚集，本期不再直接扩写 `ReviewRechargePaymentRequestService`，而是链上通道走专属服务。

---

## 4. 页面与前端实施（模块化）

### 4.1 页面入口

- 新增控制器（可选）：`app/Modules/OnchainRecharge/Http/Controllers/OnchainRechargePageController.php`
- 新增视图：`resources/views/onchain-recharge/index.blade.php`

### 4.2 交互目标

- 一个主按钮：`授权并充值`
- 同页完成：授权 -> 付款 -> 上报 `tx_hash`
- 成功后创建待审核记录：`pending_manual_review`

### 4.3 前端脚本

- 新增：`resources/js/onchain-recharge.js`
- `resources/js/app.js` 仅做入口挂载，避免业务脚本堆积

---

## 5. 管理后台实施（独立资源）

### 5.1 新增 Filament 资源（推荐）

- `app/Filament/Resources/OnchainRechargeRequests/OnchainRechargeRequestResource.php`
- `app/Filament/Resources/OnchainRechargeRequests/Pages/ListOnchainRechargeRequests.php`
- `app/Filament/Resources/OnchainRechargeRequests/Tables/OnchainRechargeRequestsTable.php`

### 5.2 展示列

- `id`, `user`, `asset_code`, `payment_amount`, `channel`, `tx_hash`, `chain_id`, `from_address`, `to_address`, `status`, `submitted_at`

### 5.3 操作

- `确认入账` -> 调用 `ReviewOnchainRechargeRequestService::markProcessed`
- `驳回` -> 调用 `reject`

---

## 6. 配置文件

- 新增：`config/web3.php`

建议键：
- `default_chain_id`
- `supported_assets`
- `supported_networks`
- `treasury_addresses`

---

## 7. 测试分层（高聚集）

### 7.1 模块测试

- `tests/Feature/OnchainRecharge/SubmitOnchainRechargeRequestTest.php`
- `tests/Feature/OnchainRecharge/ReviewOnchainRechargeRequestServiceTest.php`
- `tests/Feature/OnchainRecharge/VerifyOnchainRechargeTxServiceTest.php`（如实现初判）

### 7.2 后台测试

- `tests/Feature/Admin/OnchainRechargeRequestManagementPageTest.php`

覆盖最低要求：
- 成功提交
- 权限拒绝
- 非法输入
- 重复哈希幂等
- 审核入账成功
- 重复审核不重复入账

---

## 8. 任务拆解（按模块所有权）

### Task 1：数据层

**Files:**
- Create: `database/migrations/2026_04_15_110000_add_onchain_fields_to_recharge_payment_requests_table.php`
- Modify: `app/Modules/Balance/Models/RechargePaymentRequest.php`
- Modify: `database/sql/mvp_schema.sql`

### Task 2：OnchainRecharge 提交链路

**Files:**
- Create: `app/Modules/OnchainRecharge/Http/Requests/StoreOnchainRechargeRequest.php`
- Create: `app/Modules/OnchainRecharge/Http/Controllers/SubmitOnchainRechargeRequestController.php`
- Create: `app/Modules/OnchainRecharge/Services/CreateOnchainRechargeRequestService.php`
- Create: `app/Modules/OnchainRecharge/Support/TxHashNormalizer.php`
- Modify: `routes/web.php`

### Task 3：OnchainRecharge 审核链路

**Files:**
- Create: `app/Modules/OnchainRecharge/Services/ReviewOnchainRechargeRequestService.php`
- Create: `app/Modules/OnchainRecharge/Support/OnchainRechargeStatus.php`

### Task 4：页面与前端

**Files:**
- Create: `resources/views/onchain-recharge/index.blade.php`
- Create: `resources/js/onchain-recharge.js`
- Modify: `resources/js/app.js`

### Task 5：后台资源

**Files:**
- Create: `app/Filament/Resources/OnchainRechargeRequests/OnchainRechargeRequestResource.php`
- Create: `app/Filament/Resources/OnchainRechargeRequests/Pages/ListOnchainRechargeRequests.php`
- Create: `app/Filament/Resources/OnchainRechargeRequests/Tables/OnchainRechargeRequestsTable.php`

### Task 6：测试与验证

**Files:**
- Create: `tests/Feature/OnchainRecharge/SubmitOnchainRechargeRequestTest.php`
- Create: `tests/Feature/OnchainRecharge/ReviewOnchainRechargeRequestServiceTest.php`
- Create: `tests/Feature/Admin/OnchainRechargeRequestManagementPageTest.php`

Run:
- `php artisan test`
- `npm run build`

---

## 9. 交付边界

- 前端可完成授权+付款+提交 `tx_hash`
- 系统生成待审核链上充值申请
- 客服人工核账后可确认入账
- 全链路幂等，不重复加余额
