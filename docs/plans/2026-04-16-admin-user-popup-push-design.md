# 管理员定向用户页面弹窗推送设计稿

## 1. 背景与目标

目标是支持管理员向“指定用户”下发页面弹窗，并可追踪投放与用户确认状态。

本设计遵循当前工程约束：

1. Laravel 官方能力优先（Notification / Queue / Policy / FormRequest）。
2. 管理端使用 Filament。
3. 用户侧页面使用 Blade + Tailwind + Flowbite Modal。
4. 新独立业务域放到 `app/Modules/<Domain>`。

## 2. 需求边界

### 2.1 必须支持

1. 管理员选择指定用户（可多选）发起弹窗。
2. 弹窗即时发送（不配置开始/结束时间）。
3. 弹窗内容无需标题，仅正文。
4. 用户端可看到弹窗并关闭/确认。
5. 管理员可审计：发送对象、发送时间、展示/确认情况。

### 2.2 暂不包含（V1 外）

1. 人群规则圈选（标签/条件筛选）。
2. A/B 实验。
3. 多语言模板系统。
4. 复杂频控（如每 N 小时最多 1 次）。
5. 全站统一弹窗覆盖（MVP 仅覆盖首页轮询链路）。

## 3. 方案设计（方案 A）

### Campaign + Receipt（业务表）+ Queue（可选 Broadcast）

核心思路：

1. 弹窗任务单独建模为 `popup_campaigns`。
2. 指定用户关系放 `popup_campaign_user`。
3. 用户行为回执放 `popup_receipts`。
4. 用户前端通过接口拉取“当前有效且未处理弹窗”。

优点：

1. 业务语义清晰，审计字段完整。
2. 扩展性高（撤回、优先级、强制确认、重复策略）。
3. 与模块化目录和服务分层匹配度高。

缺点：

1. 比直接用 `notifications` 表多 2 张业务表。

### 实施结论

采用方案 A，并按 MVP 分阶段推进：

1. `Phase 1 (MVP)`：复用现有 `/home-summary` 轮询通道承载弹窗数据（不新增前端轮询请求）+ 数据库真相源。
2. `Phase 2`：在不改主数据模型前提下追加 Broadcast 实时触发。

说明：

1. 当前仓库首页已存在 `/home-summary` 轮询（用于人数和获利总额）。
2. MVP 阶段将弹窗数据作为可选字段并入该响应，降低额外系统开销。
3. 该方案默认覆盖首页；是否扩展到全站轮询，作为后续迭代项。
4. 队列不作为用户可见弹窗的前置依赖，队列仅用于异步统计/审计增强。

## 4. 模块与目录设计

新增业务域 `PopupPush`：

1. `app/Modules/PopupPush/Models/PopupCampaign.php`
2. `app/Modules/PopupPush/Models/PopupCampaignUser.php`
3. `app/Modules/PopupPush/Models/PopupReceipt.php`
4. `app/Modules/PopupPush/Http/Controllers/*`
5. `app/Modules/PopupPush/Http/Requests/*`
6. `app/Modules/PopupPush/Services/PopupCampaignService.php`
7. `app/Policies/PopupCampaignPolicy.php`
8. `app/Filament/Resources/Users/Tables/UsersTable.php`（新增批量操作入口：发送弹窗）
9. `app/Filament/Resources/Users/Pages/ListUsers.php`（承载批量操作提交反馈）

路由：

1. 管理端入口合并到现有 Filament 用户管理列表页（勾选用户后批量发送）。
2. 用户侧提供回执接口（Web 路由 + auth 中间件）。

## 5. 数据库设计

> 注：按项目规则，新增 migration 时同步更新 `database/sql/mvp_schema.sql`。

### 5.1 `popup_campaigns`

字段建议：

1. `id` bigint pk
2. `title` varchar(120)
3. `content` text
4. `level` varchar(20) default 'info'（info/warning/success）
5. `requires_ack` tinyint(1) default 0
6. `starts_at` datetime nullable
7. `ends_at` datetime nullable
8. `status` varchar(20) default 'draft'（draft/scheduled/sent/stopped）
9. `created_by` bigint
10. `sent_at` datetime nullable
11. `created_at` / `updated_at`

索引：

1. `idx_popup_campaigns_status_time (status, starts_at, ends_at)`
2. `idx_popup_campaigns_created_by (created_by)`

### 5.2 `popup_campaign_user`

字段建议：

1. `id` bigint pk
2. `campaign_id` bigint
3. `user_id` bigint
4. `delivery_status` varchar(20) default 'pending'（pending/sent/failed）
5. `pushed_at` datetime nullable
6. `created_at` / `updated_at`

约束与索引：

1. 唯一：`uk_campaign_user (campaign_id, user_id)`
2. 索引：`idx_popup_target_user_status (user_id, delivery_status)`

### 5.3 `popup_receipts`

字段建议：

1. `id` bigint pk
2. `campaign_id` bigint
3. `user_id` bigint
4. `shown_at` datetime nullable
5. `dismissed_at` datetime nullable
6. `confirmed_at` datetime nullable
7. `created_at` / `updated_at`

约束与索引：

1. 唯一：`uk_popup_receipt (campaign_id, user_id)`
2. 索引：`idx_popup_receipt_user (user_id)`

## 6. 关键业务流程

### 6.1 管理员创建并发送

1. 管理员进入 Filament `用户管理` 列表，勾选目标用户后触发“发送弹窗”批量动作。
2. 在批量动作弹窗中填写内容、是否强制确认。
3. 批量动作将所选用户 ID 作为目标用户集合传给 Service。
4. Action 层仅做校验与调用 Service。
5. Service 开事务：写 `popup_campaigns` + 批量写 `popup_campaign_user`。
6. 活动创建后立即生效并可被用户侧拉取；队列任务仅作异步增强（如批量审计标记、统计聚合），不阻塞展示。

### 6.2 用户端展示

1. 首页沿用现有轮询请求 `GET /home-summary`，响应新增可选字段 `popup`。
2. 服务端筛选：
   - 未登录用户直接返回 `popup = null`，不查询弹窗业务表
   - 用户在 `popup_campaign_user`
   - campaign 在有效窗口且状态可投放
   - receipt 未 confirmed（或按规则允许重复）
3. 命中后在 `popup` 字段返回单条最高优先级弹窗（V1 建议一次只弹一条）。
4. Blade 使用 Flowbite Modal 渲染。

### 6.3 用户回执

1. 弹窗实际出现后立即调用 `POST /popup/{campaign}/shown`（幂等）。
2. 用户关闭调用 `POST /popup/{campaign}/dismiss`。
3. 用户确认调用 `POST /popup/{campaign}/confirm`。
4. Service 使用 `updateOrCreate`，避免重复记录。
5. 多标签页并发情况下，后到标签页提交 `shown` 仍应安全幂等并返回成功。

## 7. 权限与安全

1. 管理员发送弹窗使用 `Policy/Gate`：`create/send/stop/viewAudit`。
2. 用户侧所有回执接口必须 `auth`。
3. 发送与回执请求统一 `FormRequest` 校验（长度、时间窗口合法性、目标用户数量上限）。
4. 弹窗内容默认转义；若允许富文本，必须引入白名单清洗。
5. 关键动作写审计日志（创建、发送、停止、重发）。
6. 时间窗口统一按应用时区（`config('app.timezone')`）解释与比较，避免 `starts_at/ends_at` 边界偏差。

## 8. 接口草案

### 8.1 用户侧

1. `GET /home-summary`（复用现有接口，新增可选 `popup` 字段）
2. `POST /popup/{campaign}/shown`
3. `POST /popup/{campaign}/dismiss`
4. `POST /popup/{campaign}/confirm`

响应示例（`GET /home-summary`）：

```json
{
  "participant_count": "1,234",
  "total_profit": "12,345.67",
  "popup": {
    "campaign_id": 12,
    "title": "系统维护提醒",
    "content": "今晚 23:00-23:30 短时维护",
    "level": "warning",
    "requires_ack": true,
    "ends_at": "2026-04-16 23:30:00"
  }
}
```

约定：

1. 若当前无可展示弹窗，`popup` 返回 `null`。
2. 统计字段保持原有格式与含义，避免影响既有首页逻辑。
3. 未登录请求必须返回 `popup = null`。

### 8.2 管理侧

管理侧入口固定在 Filament `用户管理` 列表批量动作，不额外开放公共管理 API（降低暴露面）。

## 9. 前端展示规则（V1）

1. 单页面会话内同一 `campaign_id` 只弹一次（前端内存防抖）。
2. `requires_ack = true` 时隐藏“仅关闭”按钮，仅允许“我已知晓”。
3. `ends_at` 过期后不展示。
4. 同时命中多条时，按 `created_at desc`（后续可加 `priority`）。

## 9.1 前端轮询规则（MVP）

1. 复用首页现有轮询 `GET /home-summary`（当前实现为 3 秒）。
2. 从响应中的 `popup` 字段决定是否弹窗展示。
3. 若后续首页轮询间隔调整到 15-30 秒，应同步评估统计展示体验与弹窗时效需求。
4. 维护 `displayed_campaign_ids`（会话内内存集合），同一活动只展示一次。
5. 当前弹窗处于展示态时，不重复弹出新弹窗。
6. `popup` 为空时不变更 UI，等待下一次轮询。
7. 通过 `localStorage` + `storage` 事件同步已展示 `campaign_id`，减少多标签页重复弹窗。
8. 首次展示后立即上报 `shown`，将重复弹窗风险收敛到幂等回执层。

## 10. 事务、并发与幂等

1. 创建活动与目标用户列表必须同事务。
2. 回执写入使用唯一键 + `updateOrCreate` 实现幂等。
3. 若存在异步增强任务，任务内按 `chunkById` 执行，避免大批量内存峰值。
4. 对重复点击确认/关闭接口返回成功（幂等语义）。
5. 弹窗查询统一收敛在 Service，并由 `/home-summary` 在响应中聚合返回，保证轮询场景结果一致。

## 11. 测试设计（质量门禁）

至少覆盖以下三类（符合项目规则）：

1. 成功路径：
   - 管理员在用户管理列表勾选用户后，批量发送弹窗成功；
   - 目标用户可拉取到弹窗并确认后不再拉到。
2. 权限拒绝路径：
   - 非管理员不能创建/发送；
   - 非目标用户无法写该活动回执。
3. 非法输入路径：
   - 标题超长、空内容、时间窗口非法、目标用户为空等。

建议补充：

1. 过期活动不展示。
2. 重复回执幂等。
3. 多活动同时命中时的选择逻辑。
4. 未登录请求 `GET /home-summary` 返回 `popup = null`。
5. 队列停机时，已创建活动仍可被目标用户拉取到并展示。
6. 多标签页同时打开时同一 `campaign_id` 不重复弹出。
7. `starts_at/ends_at` 时区边界测试（开始瞬间、结束瞬间）。

## 12. 发布与验收

验收标准：

1. 管理员可在 Filament 用户管理列表勾选用户并完成定向投放。
2. 指定用户进入站点时稳定弹出。
3. 关闭/确认状态可追踪且不重复弹出。
4. 审计信息可查。

交付前命令：

1. `php artisan test`
2. `npm run build`

## 13. 后续扩展（不影响 V1）

1. Broadcast 实时弹窗（Phase 2，与轮询并存，作为低延迟增强）。
2. 受众规则引擎（标签圈选）。
3. 模板化弹窗（多语言、变量替换）。
4. 冷却时间和频控策略。
