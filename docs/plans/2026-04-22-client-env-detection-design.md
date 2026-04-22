# 客户端环境检测 MVP 设计稿（专属 URL + 文档存储）

## 1. 目标

本阶段只做 2 件事：

1. 提供一个专属 URL，用于探测请求方设备和浏览器信息。
2. 将探测结果写入一个文档，便于开发阶段验证链路是否跑通。

明确不做：

1. 不挂网站探针。
2. 不做后端白名单/黑名单访问控制。
3. 不影响现有页面行为。

## 2. 方案

沿用方案 B：复用现有锁文件中的 `shipfastlabs/agent-detector`。

原因：

1. 不新增依赖。
2. 实现最小、可快速验证。

## 3. MVP 范围

纳入：

1. `GET /dev/client-env/detect`：返回识别结果 JSON。
2. `POST /dev/client-env/collect`：接收并记录一次检测结果（服务端也会补充自身检测值）。
3. 文档落地：`storage/app/private/client-env/probe-log.jsonl`（JSON 记录块）。
4. 去重策略：同一用户（未登录时按 IP）+ 同一 User-Agent 仅记录一次，重复刷新不重复写入。

不纳入：

1. DB 表设计与 migration。
2. 任何业务拦截逻辑。
3. 前端页面自动采集。

## 4. 模块与文件

1. `app/Modules/ClientEnv/Http/Controllers/DetectClientEnvController.php`
2. `app/Modules/ClientEnv/Http/Controllers/CollectClientEnvController.php`
3. `app/Modules/ClientEnv/Services/ClientEnvDetectorService.php`
4. `app/Modules/ClientEnv/Services/ClientEnvProbeLogService.php`
5. `app/Modules/ClientEnv/Http/Requests/CollectClientEnvRequest.php`
6. `config/client_env.php`
7. `tests/Feature/ClientEnv/DetectClientEnvTest.php`
8. `tests/Feature/ClientEnv/CollectClientEnvTest.php`
9. `routes/web.php`（仅新增 dev 专属路由）

## 5. 路由设计

1. `GET /dev/client-env/detect`
2. `POST /dev/client-env/collect`

约束：

1. 仅用于开发/测试验证。
2. 与现有业务 URL 完全隔离。

## 6. 数据结构

### 6.1 detect 返回

```json
{
  "ok": true,
  "data": {
    "device_type": "mobile",
    "is_mobile": true,
    "is_tablet": false,
    "is_desktop": false,
    "is_webview": false,
    "browser": { "name": "Chrome", "version": "123.0.0" },
    "os": { "name": "Android", "version": "14" },
    "source": "user_agent"
  }
}
```

### 6.2 collect 入参（最小）

```json
{
  "client": {
    "browser_name": "Chrome",
    "browser_version": "123.0.0",
    "platform": "Android"
  }
}
```

### 6.3 文档记录格式（JSONL）

每条为一个 JSON 记录块（记录之间空一行），字段建议：

1. `timestamp`
2. `request_id`
3. `ip`
4. `user_agent`
5. `server_detect`（服务端识别结果）
6. `client_reported`（客户端上报，可空）

## 7. 存储策略

1. 文件路径：`storage/app/private/client-env/probe-log.jsonl`
2. 追加写入，不覆盖历史；记录之间增加空行，便于人工阅读。
3. 单条写入失败返回错误，但不影响其它业务（因为是专属调试接口）。
4. 不写入账号敏感信息。

## 8. 测试范围（最小）

1. `GET /dev/client-env/detect` 成功返回结构。
2. 空 UA 请求返回 `unknown` 降级结果。
3. `POST /dev/client-env/collect` 成功写入一条日志。
4. `POST /dev/client-env/collect` 非法入参返回校验错误。

## 9. 验证命令

1. `php artisan test`
2. `npm run build`（本次无前端改动，可按项目门禁执行）

## 10. 交付说明（本阶段）

交付后你可以直接做两步验证：

1. 访问 `GET /dev/client-env/detect`，确认识别结果。
2. 调用 `POST /dev/client-env/collect`，确认 `storage/app/private/client-env/probe-log.jsonl` 产生记录。
