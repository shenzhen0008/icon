# 产品预订方案设计稿（单表 trade_mode MVP）

## 一、目标

为快速 MVP 上线，采用单表方案：
- 不新增预订商品表
- 在现有 `products` 上增加交易模式字段 `trade_mode`
- 前端（含详情页）统一按 `trade_mode` 渲染“立即购买”或“立即预订”

## 二、核心决策

- 商品数据仍使用 `products` 单表。
- 新增字段：`trade_mode`，取值：
  - `direct`：正式商品，立即购买
  - `reserve`：预订商品，立即预订
- 预订提交不扣款。
- `reserve` 商品用户只能提交预订，不能自行购买。
- 管理员可选择“审批并直接转正式订单”（系统立即尝试真实购买）。
- 价格按管理员执行“转正式订单”当下的 `products.unit_price`。

## 三、数据模型（MVP）

### 3.1 `products` 增量字段

- `trade_mode`（string/enum，默认 `direct`）

建议约束：
- 索引：`(is_active, trade_mode, sort, id)`

### 3.2 新增 `product_reservations`

- `id`
- `user_id`（FK）
- `product_id`（FK，直接指向 `products.id`）
- `shares`（int）
- `status`（`pending|approved|rejected|converted|cancelled`）
- `reviewed_by`（nullable user id）
- `reviewed_at`（nullable datetime）
- `review_note`（nullable text）
- `approved_at`（nullable datetime）
- `converted_at`（nullable datetime）
- `converted_position_id`（nullable，关联转化后的正式订单/持仓ID）
- `created_at`, `updated_at`

建议索引：
- `(user_id, status, created_at)`
- `(product_id, status, created_at)`
- `(status, created_at)`

## 四、页面与交互

### 4.1 商品列表页 `/products`

- 复用现有产品卡片。
- 根据 `trade_mode` 渲染按钮文案：
  - `direct`：立即购买
  - `reserve`：立即预订

### 4.2 商品详情页 `/products/{product}`

- 复用现有详情页模板（不新增预订详情页）。
- **统一按 `trade_mode` 判断渲染行为**：
  - `direct`：展示购买表单并提交到购买接口
  - `reserve`：展示预订表单并提交到预订接口
- 样式继续复用现有购买区 UI 结构与 class。

### 4.3 订单页 `/me/orders`

- 预订订单并入现有订单页（不新增独立预订页）。
- 复用现有订单卡片风格，按状态展示“待审核/已通过/已拒绝/已转购买/已取消”。
- 订单卡片根据关联商品 `trade_mode` 渲染订单类型：
  - `direct`：真实订单
  - `reserve`：预订订单
- `reserve` 订单不向用户展示“去购买”入口。

## 五、业务流程

1. 用户进入商品详情页。
2. 页面读取 `trade_mode`：
   - `direct`：用户下单即走真实购买链路（扣余额、建持仓）。
   - `reserve`：用户提交预订单，生成 `pending`。
3. 管理员审核预订单：可“通过”“拒绝”或“审批并直接转正式订单”。
4. 用户在 `/me/orders` 查看预订状态。
5. 用户不能对 `reserve` 商品发起购买。
6. 若管理员执行“审批并直接转正式订单”，则后台直接调用现有购买事务；成功后置为 `converted` 并记录 `converted_position_id`。

## 六、路由与接口草案

用户侧：
- `POST /positions/purchase`（现有）
- `POST /products/{product}/reservations`（新增）
- `POST /me/reservations/{reservation}/cancel`（新增）

后台（Filament）：
- 预订订单管理（新增资源）
  - 审核通过 / 审核拒绝 / 审批并直接转正式订单
- 商品管理（现有资源）
  - 新增/编辑 `trade_mode` 字段

## 七、规则与校验

### 7.1 下单分流规则

- `trade_mode=direct`：允许调用购买服务。
- `trade_mode=reserve`：禁止直接购买，必须走预订接口。

### 7.2 预订提交校验

- 商品存在、启用且 `trade_mode=reserve`
- `shares` 为 `>=1` 整数

### 7.3 预订订单状态校验

- `pending` 才允许审核动作
- `approved` 仅表示“审核通过待管理员转正式”
- `converted` 不可再次转换

### 7.4 审批并直接转正式订单校验

- 仅管理员可执行
- 预订单状态必须为 `pending` 或 `approved`
- 转换必须复用现有购买服务事务（余额/限额校验、扣款、建仓）
- 若余额不足或限额不满足，转换失败，预订单保持 `approved`（待管理员重试）或按后台规则回退为 `pending`
- 已 `converted` 的预订单不可重复转换

## 八、后台管理要求

- 必须新增“预订订单管理”用于审核。
- 商品管理中必须可配置 `trade_mode`。
- 建议审核通过前置校验：目标商品仍为启用状态。
- 预订订单管理需提供两个通过路径：
  - 审核通过（待管理员转正式）
  - 审批并直接转正式订单（后台立即尝试扣款购买）

## 九、测试策略（MVP）

每个新增能力至少覆盖：成功、无权限、非法输入。

- 商品详情页渲染：
  - `direct` 显示“立即购买”
  - `reserve` 显示“立即预订”
- 订单页渲染：
  - `direct` 商品订单显示“真实订单”类型标识
  - `reserve` 商品订单显示“预订订单”类型标识
- 预订提交流程：成功/游客拒绝/非法份数
- 预订审核流程：管理员成功/非管理员拒绝/非法状态流转
- 审批并直接转正式订单：
  - 成功：管理员操作后完成扣款并生成正式订单，预订单置 `converted`
  - 失败：余额不足/限额不满足时不生成正式订单，状态按设计保留可追踪
- 防呆：`trade_mode=reserve` 商品用户端不能走直接购买

## 十、数据变更同步

本需求涉及数据库变更，实施时必须同步：
- migration（`products.trade_mode` + `product_reservations`）
- `database/sql/mvp_schema.sql`

## 十一、实施影响范围（预估）

- `app/Modules/Product/**`（补 `trade_mode` 映射）
- `app/Modules/Reservation/**`（新增）
- `app/Filament/Resources/Products/**`（增加 `trade_mode` 字段）
- `app/Filament/Resources/**`（新增预订订单管理资源）
- `resources/views/products/index.blade.php`（卡片按钮按 `trade_mode`）
- `resources/views/products/show.blade.php`（详情页按 `trade_mode` 渲染购买/预订）
- `resources/views/orders/index.blade.php`（并入预订订单）
- `routes/web.php`
- `database/migrations/**`
- `database/sql/mvp_schema.sql`
- `tests/Feature/**`

## 十二、验收标准

- 商品可通过 `trade_mode` 控制“立即购买/立即预订”前端行为。
- 详情页复用同一模板并按 `trade_mode` 渲染对应动作。
- 订单页按 `trade_mode` 正确渲染“预订订单/真实订单”类型。
- `reserve` 商品不能直接扣款购买。
- 预订订单并入 `/me/orders` 并可审核、管理员转正式。
- 管理员可执行“审批并直接转正式订单”，且转换过程有失败保护与留痕（`converted_position_id`）。
- 管理员后台可配置商品 `trade_mode` 且可审核预订订单。
