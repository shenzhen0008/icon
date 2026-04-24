# 客户端环境检测与访问决策设计稿（两层流程）

## 1. 目标

本阶段采用两层方案，目标如下：

1. 第一层探针：稳定提取客户端环境信息（服务端识别 + 客户端上报）。
2. 第二层决策：根据规则进行 allow/deny 判定，并在需要时拦截请求。
3. 审计可追溯：保留最小审计记录，支持误杀排查和规则优化。

明确不做：

1. 不保存全量原始探针明文数据。
2. 不一次性对所有页面开启硬拦截。
3. 不引入新第三方依赖（继续复用现有能力）。

## 2. 方案

沿用方案 B：复用现有锁文件中的 `shipfastlabs/agent-detector`，并扩展为中间件 + 决策服务。

原因：

1. 不新增依赖。
2. 与当前模块兼容，可平滑从 dev 探针演进到生产流程。
3. 满足“先采集、后决策、可审计”的落地路径。

## 3. MVP 范围

纳入：

1. 全局中间件提取探针数据，生成统一 `ProbeContext`。
2. 第二层 `DecisionService` 执行规则判定，输出 `decision/reason_code/risk_score`。
3. 支持两种模式：
   - `shadow`：只判定不拦截。
   - `enforce`：判定失败直接拒绝访问。
4. 审计入库策略：
   - `deny` 全量记录。
   - `allow` 采样或按 TTL 去重记录。
5. 现有 dev 路由保留用于联调和回归验证。

不纳入：

1. 复杂设备指纹 SDK 引入。
2. 机器学习风控模型。
3. 跨系统风控联动（仅保留扩展点）。

## 4. 模块与文件

现有文件（保留）：

1. `app/Modules/ClientEnv/Http/Controllers/DetectClientEnvController.php`
2. `app/Modules/ClientEnv/Http/Controllers/CollectClientEnvController.php`
3. `app/Modules/ClientEnv/Services/ClientEnvDetectorService.php`
4. `app/Modules/ClientEnv/Services/ClientEnvProbeLogService.php`
5. `app/Modules/ClientEnv/Http/Requests/CollectClientEnvRequest.php`
6. `config/client_env.php`
7. `tests/Feature/ClientEnv/DetectClientEnvTest.php`
8. `tests/Feature/ClientEnv/CollectClientEnvTest.php`

新增建议文件：

1. `app/Modules/ClientEnv/Http/Middleware/ClientEnvProbeMiddleware.php`
2. `app/Modules/ClientEnv/DTO/ProbeContext.php`
3. `app/Modules/ClientEnv/DTO/DecisionResult.php`
4. `app/Modules/ClientEnv/Services/ClientEnvDecisionService.php`
5. `app/Modules/ClientEnv/Services/ClientEnvAuditService.php`
6. `database/migrations/*_create_client_env_decision_logs_table.php`
7. `tests/Feature/ClientEnv/ClientEnvProbeMiddlewareTest.php`
8. `tests/Feature/ClientEnv/ClientEnvDecisionServiceTest.php`

## 5. 请求流程设计

全局请求链路：

1. 请求进入 `ClientEnvProbeMiddleware`。
2. 中间件提取：
   - UA/IP/Accept-Language/关键 Header。
   - 客户端上报（如 `X-Client-Env` 或请求体中的探针字段）。
3. 组装 `ProbeContext`，调用 `ClientEnvDecisionService::decide()`。
4. 得到 `DecisionResult`：
   - `allow`：继续后续中间件与控制器。
   - `deny`：返回 `403` 或 `429`（按策略）。
5. `ClientEnvAuditService` 按策略记录审计事件（建议异步队列）。

dev 调试链路（保留）：

1. `GET /dev/client-env/detect`
2. `GET|POST /dev/client-env/collect`

说明：

1. dev 路由用于验证探针与解析能力，不承担生产拦截职责。

## 6. 数据结构

### 6.1 第一层清洗后快照（当前 `probe-log.jsonl` 实际格式）

```json
{
  "timestamp": "2026-04-22T10:47:20+00:00",
  "unique_key": "3ed79ce62c8b712566f6fad2a70ff54b0204a8b2",
  "request_id": "e7f1d7ca-6dc4-44f5-a428-c7a947c14e24",
  "user_key": "ip:221.127.171.34",
  "ip": "203.0.113.10",
  "user_agent": "Mozilla/5.0 ...",
  "server_detect": {
    "device_type": "mobile",
    "is_mobile": true,
    "is_tablet": false,
    "is_desktop": false,
    "is_webview": false,
    "browser": { "name": "unknown", "version": "unknown" },
    "os": { "name": "iOS", "version": "18.7" },
    "source": "user_agent"
  },
  "client_reported": null
}
```

字段说明：

1. `ip`、`user_agent`、`client_reported` 属于原始输入（可做轻清洗）。
2. `server_detect` 属于第一层标准化/派生结果（基于 UA 解析）。
3. `user_key` 为当前实现的用户标识：登录态为 `user:{id}`，未登录为 `ip:{ip}`。
4. 第二层决策可继续基于该快照构建内部 `ProbeContext/DecisionInput`，无需要求第一层直接产出最终决策字段。

### 6.2 DecisionResult（第二层输出）

```json
{
  "decision": "allow",
  "reason_code": "ok",
  "risk_score": 0,
  "rule_version": "v1"
}
```

### 6.3 审计表结构（最小）

建议表：`client_env_decision_logs`

1. `id` bigint
2. `request_id` char(36)
3. `user_id` bigint nullable
4. `ip_hash` char(64)
5. `fingerprint_hash` char(64)
6. `decision` enum(`allow`,`deny`)
7. `reason_code` varchar(64)
8. `risk_score` unsigned tinyint
9. `route_key` varchar(128)
10. `rule_version` varchar(32)
11. `created_at` datetime

索引建议：

1. `idx_decision_created_at (decision, created_at)`
2. `uniq_request_id (request_id)` 唯一索引
3. `idx_fingerprint_created_at (fingerprint_hash, created_at)`

## 7. 存储策略

1. `deny`：全量记录到审计表。
2. `allow`：按业务路径采样或按 `fingerprint_hash + route_key + 24h` 去重。
3. 保留周期：默认 7~30 天（根据容量调节），通过计划任务清理。
4. 原始 UA/IP 不落库存明文，优先存 hash/脱敏值。
5. 文件 `probe-log.jsonl` 保留为开发联调日志，不作为生产审计主存储。

## 8. 上线与灰度策略

1. 阶段 1：`shadow` 模式全站启用，只记录判定，不拦截。
2. 阶段 2：仅高风险写操作开启 `enforce`（登录、充值、提现、下单）。
3. 阶段 3：根据误杀率与漏判率逐步扩大 `enforce` 范围。
4. 始终保留人工兜底（白名单/临时放行开关）。

## 9. 测试范围（最小）

1. 中间件能构建 `ProbeContext` 并注入请求上下文。
2. 决策服务在典型输入下返回稳定 `decision/reason_code`。
3. `shadow` 模式不拦截，`enforce` 模式按规则拦截。
4. `deny` 全量入库；`allow` 采样/去重逻辑生效。
5. 保留原有 dev 探针测试：detect/collect 正常。

## 10. 验证命令

1. `php artisan test`
2. `npm run build`

## 11. 交付说明（本阶段）

交付后按以下步骤验证：

1. 访问 `GET /dev/client-env/detect`，确认第一层探针识别结果。
2. 在 `shadow` 模式发起关键请求，确认产生决策日志且不拦截。
3. 切换单一路由到 `enforce`，确认命中拒绝规则时返回拒绝响应并落审计记录。




## 12. 允许访问的数据
{
    "timestamp": "2026-04-22T10:47:20+00:00",
    "unique_key": "3ed79ce62c8b712566f6fad2a70ff54b0204a8b2",
    "request_id": "e7f1d7ca-6dc4-44f5-a428-c7a947c14e24",
    "user_key": "ip:221.127.171.34",
    "ip": "221.127.171.34",
    "user_agent": "Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 OKEx/6.167.0 (iPhone;U;iOS 26.3.1;zh-Hans-US/zh-CN) locale=zh-CN statusBarHeight/141 OKApp/(OKEx/6.167.0) brokerDomain/www.okx.com brokerId/0 jsbridge/1.1.0 theme/light",
    "server_detect": {
        "device_type": "mobile",
        "is_mobile": true,
        "is_tablet": false,
        "is_desktop": false,
        "is_webview": true,
        "browser": {
            "name": "unknown",
            "version": "unknown"
        },
        "os": {
            "name": "iOS",
            "version": "18.7"
        },
        "source": "user_agent"
    },
    "client_reported": null
}

{
    "timestamp": "2026-04-22T10:47:35+00:00",
    "unique_key": "c4a59938e8cdb3b8ac61629061172ad0b44b6531",
    "request_id": "8b9e4d52-135a-4e8a-b7e1-db67c0e40aa6",
    "user_key": "ip:221.127.171.34",
    "ip": "221.127.171.34",
    "user_agent": "Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148",
    "server_detect": {
        "device_type": "mobile",
        "is_mobile": true,
        "is_tablet": false,
        "is_desktop": false,
        "is_webview": true,
        "browser": {
            "name": "unknown",
            "version": "unknown"
        },
        "os": {
            "name": "iOS",
            "version": "18.7"
        },
        "source": "user_agent"
    },
    "client_reported": null
}

{
    "timestamp": "2026-04-22T10:47:49+00:00",
    "unique_key": "ee3241cc20ca47d6baa63bc51e921f2f07177b22",
    "request_id": "a69c09b6-4146-489a-8ad8-90ed3115b0fa",
    "user_key": "user:6",
    "ip": "221.127.171.34",
    "user_agent": "Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 DeFiWallet/2.50.3",
    "server_detect": {
        "device_type": "mobile",
        "is_mobile": true,
        "is_tablet": false,
        "is_desktop": false,
        "is_webview": true,
        "browser": {
            "name": "unknown",
            "version": "unknown"
        },
        "os": {
            "name": "iOS",
            "version": "18.7"
        },
        "source": "user_agent"
    },
    "client_reported": null
}


{
    "timestamp": "2026-04-22T10:48:09+00:00",
    "unique_key": "9bf20ca941eeff2b15d6b4a1bdcd9bf06b61f1c6",
    "request_id": "e0cdef2c-4f1a-48aa-bae8-ebe963a0da0c",
    "user_key": "ip:221.127.171.34",
    "ip": "221.127.171.34",
    "user_agent": "Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 WebView MetaMaskMobile",
    "server_detect": {
        "device_type": "mobile",
        "is_mobile": true,
        "is_tablet": false,
        "is_desktop": false,
        "is_webview": true,
        "browser": {
            "name": "unknown",
            "version": "unknown"
        },
        "os": {
            "name": "iOS",
            "version": "18.7"
        },
        "source": "user_agent"
    },
    "client_reported": null
}