# 爬虫与搜索引擎收录拦截方案

## 1. 背景

站点需要尽量减少搜索引擎收录、AI/SEO 爬虫读取，以及低质量机器人请求。该需求不能只依赖 `robots.txt`，因为 `robots.txt` 属于自愿遵守协议，恶意爬虫和部分采集器可以忽略。

本方案先以 Laravel 应用层为主，服务器层为辅，保证规则可以随代码一起部署到不同站点。

## 2. 目标

1. 阻止主流搜索引擎索引页面内容。
2. 阻止常见 AI 训练爬虫、SEO 工具爬虫和搜索引擎爬虫读取页面。
3. 保留正常用户浏览、后台管理、支付回调、系统任务和健康检查能力。
4. 对 AI/SEO/搜索引擎爬虫不设置业务豁免；只要 User-Agent 命中拦截列表，即使访问公开页、分享页或静态页也返回拒绝。
5. 规则可配置、可测试、可灰度，不把关键策略散落在宝塔或 Nginx 手工配置中。

## 3. 非目标

1. 不承诺彻底阻止所有伪装成人类浏览器的爬虫。
2. 不替代登录鉴权、权限控制和敏感页面访问控制。
3. 不在首版引入 Cloudflare、商业 WAF、验证码或新依赖。
4. 不直接修改服务器配置；服务器层规则作为后续增强项。

## 4. 分层策略

### 4.1 禁止收录

对所有普通 Web HTML 响应增加：

```http
X-Robots-Tag: noindex, nofollow, noarchive, nosnippet
```

Blade 页面同时增加：

```html
<meta name="robots" content="noindex,nofollow,noarchive,nosnippet">
```

`public/robots.txt` 增加：

```txt
User-agent: *
Disallow: /
```

说明：这层主要面向正规搜索引擎，目标是不进入索引、不展示快照、不展示摘要。

### 4.2 User-Agent 拦截

新增 Laravel 中间件，读取配置中的关键词列表，对明显爬虫返回 `403 Forbidden`。

建议首批拦截类别：

1. 搜索引擎：`Googlebot`、`Bingbot`、`Baiduspider`、`YandexBot`、`DuckDuckBot`、`Sogou`、`360Spider`
2. SEO 工具：`AhrefsBot`、`SemrushBot`、`MJ12bot`、`DotBot`、`PetalBot`
3. AI/训练爬虫：`GPTBot`、`ChatGPT-User`、`CCBot`、`ClaudeBot`、`anthropic-ai`、`PerplexityBot`、`Bytespider`、`Applebot`、`Amazonbot`
4. 通用脚本采集器：`curl`、`wget`、`python-requests`、`Go-http-client`、`Scrapy`

默认返回：

```http
403 Forbidden
```

如后续想降低探测信号，可配置为 `404` 或 `410`。

### 4.3 技术路径豁免

豁免只用于保护正常业务入口和框架资源，不用于放行 AI/SEO/搜索爬虫。实现时应先判断 User-Agent 是否属于强拦截类别；如果属于搜索引擎、SEO 工具或 AI 爬虫，直接拒绝，不进入路径豁免。

以下路径可用于非强拦截请求的技术豁免：

1. 支付、充值、提现等外部回调接口。
2. Laravel Livewire、Filament 必需资源。
3. 静态资源：`build/*`、`vendor/*`、图片、CSS、JS。
4. 健康检查路径，如未来新增 `/health`。

注意：豁免不代表公开访问，敏感接口仍必须走鉴权、签名或回调校验。

### 4.4 限流

首版可以复用 Laravel Rate Limiter：

1. 对匿名 Web 请求设置合理频率限制。
2. 对登录、注册、助记词登录、充值提交等敏感入口设置更严格限制。
3. 对被识别为脚本 UA 的请求可直接拒绝，不进入业务控制器。

### 4.5 服务器层增强

后续如机器人流量仍高，再考虑：

1. Nginx 根据 User-Agent 提前返回 `403`。
2. 宝塔防火墙或系统防火墙按 IP/国家/ASN 拦截。
3. Cloudflare WAF、Bot Fight Mode、Turnstile。

服务器层规则应优先作为性能优化，不作为唯一安全边界。

## 5. 推荐实现

### 5.1 配置文件

新增：

```text
config/crawlers.php
```

建议配置项：

```php
return [
    'enabled' => env('CRAWLER_BLOCK_ENABLED', true),
    'block_empty_user_agent' => env('CRAWLER_BLOCK_EMPTY_USER_AGENT', false),
    'response_status' => (int) env('CRAWLER_BLOCK_STATUS', 403),
    'always_block_user_agents' => [
        // Search engines, SEO crawlers, AI crawlers.
    ],
    'blocked_user_agents' => [
        // Generic scripts and optional lower-confidence bots.
    ],
    'except_paths' => [
        'build/*',
        'vendor/*',
        'livewire/*',
    ],
];
```

`.env.production.example` 增加：

```env
CRAWLER_BLOCK_ENABLED=true
CRAWLER_BLOCK_EMPTY_USER_AGENT=false
CRAWLER_BLOCK_STATUS=403
```

### 5.2 中间件

新增模块化中间件：

```text
app/Modules/Security/Http/Middleware/BlockCrawlerBots.php
```

职责：

1. 读取 `User-Agent`。
2. 先匹配 `always_block_user_agents`；命中搜索引擎、SEO 工具或 AI 爬虫时直接返回配置状态码，不应用路径豁免。
3. 再判断配置中的技术豁免路径，避免误伤回调、框架资源和静态资源。
4. 空 UA 是否拦截由配置控制。
5. 对普通拦截 UA 做大小写不敏感关键词匹配。
6. 命中后返回配置状态码。
7. 未命中则继续请求。

### 5.3 响应头

新增：

```text
app/Modules/Security/Http/Middleware/PreventIndexing.php
```

职责：

1. 对普通 Web 响应追加 `X-Robots-Tag`。
2. 避免影响文件下载、JSON API、回调接口。
3. 可通过配置开关关闭。

### 5.4 robots.txt

新增或更新：

```text
public/robots.txt
```

内容：

```txt
User-agent: *
Disallow: /
```

### 5.5 Blade meta

在主布局 `<head>` 中加入：

```html
<meta name="robots" content="noindex,nofollow,noarchive,nosnippet">
```

如项目存在多个布局，需要只改公共布局，避免散落。

## 6. 测试要求

新增 Feature 测试：

1. 普通浏览器 UA 可以访问公开页面。
2. `Googlebot` 访问公开页面返回 `403`。
3. `GPTBot` 访问公开页面返回 `403`。
4. AI/SEO/搜索爬虫访问技术豁免路径仍返回 `403`。
5. 普通浏览器访问技术豁免路径不被爬虫中间件拦截。
6. HTML 响应包含 `X-Robots-Tag`。
7. `robots.txt` 返回 `Disallow: /`。

执行：

```bash
php artisan test
npm run build
```

## 7. 风险与注意事项

1. User-Agent 可以伪造，因此该方案只能减少常规爬取，不能阻止高强度定向采集。
2. 拦截 `curl`、`python-requests` 可能影响第三方监控或接口联调，建议先确认是否有外部服务依赖。
3. `ChatGPT-User` 有时代表用户主动请求网页摘要，拦截后用户在 AI 工具中打开站点可能失败。
4. `facebookexternalhit`、`Twitterbot` 等社交预览爬虫如果纳入强拦截列表，外部平台分享卡片可能无法生成；本项目策略仍优先保护内容不被机器人读取。
5. 如果未来启用 CDN/WAF，需要保持应用层和服务器层规则一致，避免误伤支付回调。

## 8. 分阶段落地

### 阶段一：应用层软落地

1. 增加配置文件。
2. 增加两个中间件。
3. 增加 `robots.txt`。
4. 增加主布局 robots meta。
5. 增加测试。

### 阶段二：生产观察

1. 查看 `laravel.log` 是否有误伤。
2. 查看 Nginx access log 中机器人 UA 是否减少。
3. 确认支付回调、登录、后台、Livewire 正常。
4. 确认 AI/SEO/搜索爬虫即使访问静态路径或分享路径也被拒绝。

### 阶段三：服务器层增强

1. 对高频恶意 UA 在 Nginx 层提前拒绝。
2. 对异常 IP 进行限速或拉黑。
3. 需要时接入 WAF 或 Cloudflare。

## 9. 建议默认决策

首版建议：

1. 启用 `robots.txt` 全站禁止。
2. 启用 `X-Robots-Tag` 和 HTML robots meta。
3. 强制拦截明确搜索引擎、SEO 工具、AI 爬虫，不设置爬虫类别豁免。
4. 暂不拦截空 User-Agent，避免误伤少量合法服务。
5. 社交预览爬虫可按“机器人优先拦截”策略纳入强拦截列表。
6. 不动服务器配置，先通过代码和测试落地。
