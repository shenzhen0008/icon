# 后台鉴权 MVP 设计稿（仅管理员可进入后台）

## 1. 背景与目标

当前后台缺少鉴权，存在未授权访问风险。  
本次仅做 MVP：实现“管理员可进入后台，非管理员不可进入”，不做细粒度角色权限。

目标：

1. 后台页面统一要求登录。
2. 仅 `is_admin=1` 用户可访问后台入口与后台页面。
3. 保持方案简单、可快速上线，并为后续扩展 RBAC 预留路径。

## 2. 约束与原则

遵循项目工程规则：

1. Laravel 官方能力优先：认证使用 Laravel 第一方方案，授权使用 `Policy/Gate`。
2. 控制器保持轻量：校验 + 调用 + 响应，不承载复杂业务。
3. 所有写接口要求认证；后台入口同时要求授权。
4. 涉及数据库结构变更时，必须同步更新 `database/sql/mvp_schema.sql`。
5. 验收前必须执行：`php artisan test`、`npm run build`。

## 3. 方案比较

### 方案 A：`users.is_admin` + `Gate`（推荐）

设计：

1. 在 `users` 表新增 `is_admin`（`tinyint(1) default 0`）。
2. 定义 `Gate::define('access-admin', ...)` 判断是否管理员。
3. 后台路由统一挂载 `auth` + `can:access-admin`。

优点：

1. 改动最小，满足 MVP。
2. 语义清晰，后续可平滑演进到多角色模型。

缺点：

1. 仅支持二元权限（管理员/非管理员）。

### 方案 B：配置白名单（管理员邮箱/ID）

优点：实现最快。  
缺点：可维护性差，不建议作为正式方案。

### 方案 C：完整 RBAC（roles + permissions）

优点：扩展性最佳。  
缺点：明显超出 MVP 范围，增加迁移与测试复杂度。

结论：采用方案 A。

## 4. 详细设计

### 4.1 认证（AuthN）

1. 后台路由组统一要求 `auth`。
2. 未登录访问后台，按 Laravel 默认行为跳转登录页。

### 4.2 授权（AuthZ）

1. 在授权提供者中定义 `access-admin` Gate。
2. 规则：仅当用户 `is_admin === 1` 时允许访问后台。
3. 后台路由组增加 `can:access-admin` 中间件。
4. 非管理员访问后台返回 `403`。

### 4.3 数据模型

`users` 表新增：

1. `is_admin` `tinyint(1)` `not null` `default 0`。

建议：

1. 添加索引 `idx_users_is_admin`（可选，便于后台用户筛选/统计）。

## 5. 路由与访问边界

1. 后台入口建议固定为 `/admin`（或现有后台前缀）。
2. 后台路由中间件顺序：`auth` -> `can:access-admin`。
3. 公共页面（首页、公开内容）不受该 Gate 影响。

## 6. 安全与审计（MVP）

1. 所有后台变更接口继续要求 `auth` + 对应授权。
2. 关键后台操作（删除、封禁、结算、权限变更）后续接入审计日志。
3. 本次先完成“后台入口访问控制”作为第一道防线。

## 7. 测试设计

至少覆盖三类场景：

1. 成功路径：管理员访问后台返回 `200`。
2. 权限拒绝：普通用户访问后台返回 `403`。
3. 未登录：访问后台重定向到登录页。

建议新增：

1. `tests/Feature/AdminAccessTest.php`（或项目现有同类测试文件）。

## 8. 变更清单（实施范围）

预计涉及文件：

1. `database/migrations/*_add_is_admin_to_users_table.php`
2. `database/sql/mvp_schema.sql`
3. `app/Providers/AuthServiceProvider.php`（或 Laravel 13 对应授权注册位置）
4. `routes/web.php`（后台路由中间件）
5. `tests/Feature/AdminAccessTest.php`

## 9. 上线与回滚

上线步骤：

1. 执行 migration，初始化管理员账号的 `is_admin=1`。
2. 执行测试与构建：`php artisan test`、`npm run build`。
3. 发布后验证管理员和普通用户访问行为。

回滚策略：

1. 路由中间件可先回退到仅 `auth`（紧急兜底）。
2. migration 可按回滚脚本撤销 `is_admin` 字段（需评估数据影响）。

## 10. 后续演进（非本次）

当出现“运营/审核/财务”等差异化权限诉求时，再升级为 RBAC：

1. 引入角色与权限表。
2. 将 `access-admin` 从单字段判断迁移到角色能力判断。
3. 保持现有路由与 Gate 调用点不变，减少改造面。

