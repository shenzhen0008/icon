# 全站弹窗开发计划

## 1. 目标与执行约定

依据：`docs/plans/2026-10-02-sitewide-popup-delivery-plan.md`。

面向 AI 编码代理及开发人员，按下列四个阶段顺序实施。每阶段开始前确认实际调用链和受影响文件；完成后记录代码变更与验证结果，再进入下一阶段。本文件只规划开发，不代表已实施。

本计划优先采用新的明确契约，不提供旧接口、旧前端逻辑或旧数据兼容层。具体包括：删除首页统计响应中的 `popup` 字段；不读取、迁移或清理旧防重键；删除无调用的 `dismiss` 路由和专用代码。与依据文档中“保留 dismiss 兼容”的描述不同，以本计划为准。

不兼容旧数据不等于清空数据库。本次复用活动、目标用户及回执模型，不做历史数据转换或破坏性清理，不改数据库结构，不修改 `database/sql/mvp_schema.sql`。只有发现本次目标确实需要结构变更时，另行提出范围并同步规划 SQL。

### 最终行为

- 登录用户首次加载使用 `x-nav.top` 的页面，或收到 `page-cache:restored` 时，检查最新一条有效、未确认且属于自己的活动。
- 同一账号、同一浏览器存储中已展示的活动不重复展示；无活动时不轮询。
- 弹窗仅允许确认；确认成功后关闭，失败时保留并允许重试。
- 首页统计刷新独立运行，不再参与弹窗投递。

### 范围限制

- 只取最新一条；最新未确认活动可以遮挡更早活动，确认后等下次页面检查再取下一条。
- 不增加实时推送、查询或展示队列、补查、BFCache 监听、跨标签页互斥、局部账号切换处理、全局初始化管理层。
- 不引入依赖或调整目录体系；新文件放在 PopupPush 对应模块目录。复用 Laravel、现有页面缓存、导航和测试工具。
- 不编写历史活动转换、旧键检测或旧响应兼容分支。

## 2. 阶段一：建立独立读取接口

**目标：** 弹窗查询通过独立认证接口完成，保持服务端身份隔离。

### 文件与任务

| 文件 | 操作 | 任务 |
| --- | --- | --- |
| `app/Modules/PopupPush/Http/Controllers/PendingPopupController.php` | 新增 | invokable 控制器，构造注入 `PopupFeedService`，从当前认证会话取用户 ID |
| `app/Modules/PopupPush/Services/PopupFeedService.php` | 复用 | 核对目标用户、状态、有效期、确认记录和最新优先筛选，不新增第二套查询 |
| `routes/web.php` | 修改 | 在现有 `auth` 组增加 `GET /popup/pending` |
| `config/client_env.php` | 修改 | 按现有配置格式将 `popup/pending` 加入 `persist_excluded_paths` |
| `tests/Feature/PopupPush/PopupPushFeedTest.php` | 修改 | 将弹窗读取测试由 `/home-summary` 切到新接口 |

### 实施步骤

1. 仓库存在 `.codegraph/` 时先用 CodeGraph 定位上述符号与调用链；确认认证 guard、路由顺序和现有测试数据构造方式。
2. 实现控制器：只调用 `resolveForUser(当前认证用户 ID)`，返回 JSON，不接收目标用户或活动 ID。
3. 定义响应为 `{ "popup": null }` 或 `{ "popup": { ... } }`。活动字段沿用服务现有输出：`campaign_id`、`username`、`content`、`level`、`requires_ack`、`ends_at`；前端不据 `requires_ack` 增加关闭分支。
4. 设置 `Cache-Control: private, no-store`。路由放在可能匹配 `/popup/{campaign}` 的通配路由之前，避免冲突。
5. 更新测试，直接断言新接口的身份隔离与返回数据，不保留旧响应断言。

### 完成条件

- 目标用户成功读取；非目标用户返回 null。
- 匿名 HTML 请求返回 302，匿名 JSON 请求返回 401。
- 查询参数中的其他用户 ID 不影响认证身份。
- 已确认、无效状态及有效期外活动不返回；有效期边界有明确测试。
- 多活动按 `created_at`、`id` 倒序返回，确认最新活动后下一次查询返回较早活动。
- 响应禁用缓存；执行 `php artisan test --filter=PopupPushFeedTest`。

## 3. 阶段二：实现公共弹窗组件并接入导航

**目标：** 弹窗在业务页共享一个入口，脱离首页 DOM 和轮询。

### 文件与任务

| 文件 | 操作 | 任务 |
| --- | --- | --- |
| `resources/views/components/popup-push/pending.blade.php` | 新增 | 弹窗 DOM、读取、防重、展示与确认逻辑 |
| `resources/views/components/nav/top.blade.php` | 修改 | 在 main 外挂载一次 `x-popup-push.pending` |
| `resources/views/components/home/stats.blade.php` | 修改 | 同时删除原弹窗 DOM、状态、监听器和回执逻辑，保留统计刷新 |
| `tests/Frontend/PopupPush/pending-popup.test.js` | 新增 | 用现有工具验证核心前端行为，不新增测试依赖 |
| 现有语言字典中弹窗文案所在文件 | 必要时修改 | 复用确认文案，补充可重试的确认失败提示 |

`resources/js/navigation-page-cache.js` 为阅读和事件核对对象，本阶段不修改其缓存算法。实施前确认语言字典的实际路径，并列入变更清单。

### 实施步骤

1. 复用现有弹窗样式与原生 `dialog`，提供确认按钮、纯文本内容和可访问的错误提示区域。无关闭按钮；阻止 Escape 和遮罩关闭。
2. 从 `#top-nav` 读取 `data-page-cache-context`。guest 不请求；用户上下文用于构造新键，例如 `popup_push_shown_campaign_ids:user:{id}`。
3. 组件初始化时读取 localStorage 到内存 `Set`；解析或写入失败只捕获异常，继续使用内存记录。`storage` 事件只同步当前用户键。
4. 检查函数先判断 guest、in-flight 和弹窗是否打开，任一成立即返回；发请求前设置布尔锁，在 `finally` 中释放。
5. GET 请求发送 `Accept: application/json`；检查成功状态与响应形状。读取失败静默返回，不记录已展示。
6. 检查活动 ID 是否已展示；正文和称呼通过 `textContent` 构造。`showModal()` 成功后才写入防重记录并发送 `shown` 回执；展示回执失败不阻断 UI。
7. 确认请求携带 CSRF token；提交时禁用按钮。仅 HTTP 成功且 `ok === true` 时关闭；失败显示错误，恢复按钮并保留弹窗。
8. 首次 DOM ready 检查一次；脚本执行时 DOM 已就绪则直接检查。监听 `page-cache:restored`，忙碌时直接跳过，不补查；确认成功后不自动查询下一条。
9. 挂载新组件与删除首页旧弹窗代码在同一阶段完成，防止双入口、双请求。组件只挂一次，由结构保证初始化一次。

### 完成条件

- 首次加载和自定义缓存恢复能检查；并发事件最多保留一个请求，已打开弹窗不被覆盖。
- 相同账号活动防重，不同账号键隔离；不访问旧键。
- 确认的 HTTP 错误、网络错误和非预期响应均可重试，成功后关闭。
- 管理员正文中的 HTML 作为文本显示；确认按钮和错误提示具备基本可访问性。
- 首页统计不再依赖弹窗 DOM 存在；只剩一套弹窗展示入口。
- 前端测试实际执行组件行为；不抽象存储适配器、事件管理器或请求队列只为方便测试。若现有工具无法覆盖 DOM 行为，用一个浏览器执行检查覆盖该部分并记录结果，不以纯字符串断言代替。
- 执行相关前端检查、`php artisan test` 和 `npm run build`。

## 4. 阶段三：移除旧投递契约和无用代码

**目标：** 完成调用方切换，删除旧路径，不保留兼容层。

### 文件与任务

| 文件 | 操作 | 任务 |
| --- | --- | --- |
| `app/Modules/Home/Services/HomeSummaryService.php` | 修改 | 删除 PopupFeedService 注入、popup 返回字段及无用用户参数 |
| `app/Modules/Home/Http/Controllers/HomeSummaryFeedController.php` | 必要时修改 | 按新的统计服务签名更新调用 |
| `app/Modules/User/Http/Controllers/HomeController.php` | 必要时修改 | 删除仅用于旧弹窗解析的传参和视图数据 |
| `routes/web.php` | 修改 | 删除 `dismiss` 路由，保留 `pending`、`shown`、`confirm` |
| `app/Modules/PopupPush/Http/Controllers/MarkPopupDismissedController.php` | 删除 | 移除不再使用的关闭控制器 |
| `app/Modules/PopupPush/Services/PopupReceiptService.php` | 修改 | 核对全部调用后删除无用 `markDismissed()`；保留展示、确认及目标用户校验 |
| 现有 Home / PopupPush 相关测试 | 修改 | 删除旧契约断言，补充统计接口无 popup 的断言 |

### 实施步骤

1. 定位 `HomeSummaryService::resolve()` 的全部调用方，逐一更新；保留仍服务于其他业务的用户身份逻辑。
2. 删除统计服务中的弹窗依赖；删除首页残余 `payload.popup` 判断和旧防重键引用。
3. 定位 `dismiss` 路由、控制器、服务方法的全部调用方；删除专用调用、实现与测试，不以空方法或重定向保留旧契约。
4. 数据库现有字段不因代码清理顺带删除；不增加历史数据适配、不读取旧展示记录。
5. 搜索旧 DOM ID、旧存储键及旧接口消费位置，确认运行时代码不再引用；历史文档中的描述无需批量修改。

### 完成条件

- `/home-summary` 仅返回统计数据，首页及其他调用方正常运行。
- 运行时代码无旧首页弹窗入口、旧防重键读写或 dismiss 调用。
- `shown`、`confirm` 保留认证、目标用户限制与重复回执的既有语义。
- 使用现有服务和测试，不增加兼容开关、双写或数据迁移。
- 执行 `php artisan test`、相关前端检查和 `npm run build`。

## 5. 阶段四：完成集成验收和交付

**目标：** 验证真实页面行为及整体质量，提供可复核交付记录。

### 文件与任务

- 更新前述模块测试中发现的缺口，不新建全套浏览器测试基础设施。
- 在本开发计划末尾追加实际执行记录：变更文件、命令、结果、手工验收结果与已知限制。

### 实施步骤

1. 跑完整测试与构建，记录结果；有失败先定位修复，禁止按通过交付。
2. 用目标用户在首页、个人中心、产品、充值、订单页实际触发展示，确认组件在 main 外且仅挂一次。
3. 通过自定义缓存切换页面，验证检查、防重、弹窗不被覆盖及首页统计刷新。
4. 用非目标用户和匿名用户验证隔离；退出后重新登录另一账号，验证防重键独立。
5. 模拟确认失败并重试；确认成功后下一次检查不再返回该活动。
6. 两个标签页检查 storage 同步；禁用存储检查当前文档内存防重。接受同时首次查询可能重复的边界，不搭建原子锁。
7. 确认新、旧活动均按当前统一规则处理，不存在为历史数据添加的分支。

### 最终质量门禁

```text
php artisan test
npm run build
```

另按仓库现有命令执行本次前端检查，命令须在交付记录中写出。测试和构建通过且关键手工场景验证完成后，才标记四个阶段完成。阶段之间存在依赖：1 → 2 → 3 → 4，不以临时兼容逻辑跳过集成工作。

### 必须写明的交付限制

- 单页面停留期间不接收新活动；原生 BFCache 恢复不保证重新检查。
- 最新未确认活动可能遮挡旧活动；不保证全部活动逐条投递。
- 存储不可用时仅当前文档防重；跨标签页无原子互斥。
- 确认失败后离开页面，已展示记录可能阻止再次展示；没有持久化确认重试。
- `shown` 回执失败可能缺失展示统计；不提供旧防重数据或旧接口兼容。

## 6. 执行记录（开发时填写）

| 阶段 | 状态 | 变更文件 | 执行命令与结果 | 手工验证 / 限制 |
| --- | --- | --- | --- | --- |
| 一：读取接口 | 未开始 | — | — | — |
| 二：公共组件 | 未开始 | — | — | — |
| 三：旧逻辑清理 | 未开始 | — | — | — |
| 四：集成验收 | 未开始 | — | — | — |
