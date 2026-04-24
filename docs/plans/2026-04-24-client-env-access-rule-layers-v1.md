# 客户端环境访问规则（单层）V1

## 1. 来源

本规则基于 [2026-04-22-client-env-detection-design.md](/Users/linke/hui/icon-market/docs/plans/2026-04-22-client-env-detection-design.md) 中 `## 12. 允许访问的数据` 样本整理。

## 2. 单层结构与执行顺序

1. 唯一规则层：钱包关键词直通（大小写不敏感）。

执行链路：

1. 先判钱包关键词。
2. 命中任一钱包关键词：`allow`。
3. 未命中钱包关键词：`deny`。

## 3. 钱包关键词规则（唯一规则）

### 3.1 规则

1. 对 `user_agent` 做小写化后进行包含匹配（大小写不敏感）。
2. 若命中任一钱包关键词，直接 `allow`。
3. 未命中任一钱包关键词，直接 `deny`。

### 3.2 钱包关键词集合（V1）

1. `okex`
2. `okapp`
3. `defiwallet`
4. `metamaskmobile`
5. `metamask`
6. `coinbase`
7. `coinbasewallet`
8. `coinbase wallet`
9. `org.toshi`
10. `toshi`
11. `baseapp`

### 3.3 输出

1. `decision = allow`
2. `reason_code = layer1_allow_wallet_keyword`

## 4. 第二层规则：移动端门槛

第二层已移除，不再使用移动端兜底放行。

## 5. 样本画像（来自当前允许样本）

1. 当前样本全部是 `device_type = mobile`
2. 当前样本 `os.name` 全是 `iOS`
3. 当前样本出现大量 WebView 场景（`is_webview = true`），但第一层不单独拒绝 WebView
4. 当前样本 `browser.name` 多为 `unknown`，第一层不基于浏览器名拒绝

说明：当前样本中尚未看到 Android 允许样本，建议尽快补采 Android 样本用于回归校验。

## 6. 输出与备注

1. 所有关键词匹配统一大小写不敏感。
2. 命中关键词：`decision = allow`，`reason_code = layer1_allow_wallet_keyword`。
3. 未命中关键词：`decision = deny`，`reason_code = layer1_deny_wallet_keyword_not_matched`。
