# 客服系统稳定用户 ID 开发执行文档

## 1. 目标

让已登录客户在不同设备、浏览器会话过期或重新连接后，继续使用同一个 Stream Chat 用户身份和客服频道，从而稳定访问聊天记录。未登录访客继续使用现有随机 ID 逻辑。

本次只做三件事：统一聊天与通知 token 的身份生成、修正登录用户的未读检查条件、处理账号切换时的旧连接与未读状态。沿用现有页面生命周期，不新增复杂同步机制，不把手机号、邮箱等个人信息写入 Stream ID。

## 2. 当前实现与问题

客服 token 由 `StreamChatTokenService` 生成。当前访客 ID 来自 Laravel Session 中的 `stream_chat.guest_id`；如果 Session 中没有该值，服务端会生成随机 ID 并保存。客服频道 ID 根据此 ID 拼接。因此 Session 丢失时，访客会获得新的 Stream 用户和新频道，旧消息仍在旧频道中。

客服页面拿到 token 后调用 Stream SDK 的 `connectUser()`，再对返回频道调用 `channel.watch()` 加载消息。客服工作台根据客服账号所属频道查询客服会话。

当前未读检查依赖浏览器本地未读数或 `stream_chat_has_session` 标记，通知 token 也依赖游客 Session。新设备登录后可能因没有这些状态而跳过检查；账号切换后，本地未读状态也可能沿用旧账号的数据。

## 3. 改造规则

### 3.1 已登录客户

- Stream 用户 ID 直接使用现有、不可修改的 `username` 字段。
- 客服频道 ID 使用该用户名生成，例如 `support-customer-<username>`。
- JWT 的 `user_id` 必须等于返回 payload 的 `user.id`；频道 ID 按固定规则生成，频道成员包含该用户和指定客服。
- 显示名称继续走现有用户名逻辑。
- 主聊天 token 和未读通知 token 在现有服务内复用同一套身份生成规则；登录身份只取服务端认证用户，不依赖游客 Session。

### 3.2 未登录访客

- 保留当前 Session 随机 ID 和访客频道生成逻辑。
- 未登录用户仍可访问客服。
- Session 保持期间访客身份稳定；Session 丢失时可能生成新的访客身份，行为与当前相同。

### 3.3 登录/登出和账号切换

登录用户身份以认证用户为准，不依赖匿名 Session ID 定位登录后的频道。若浏览器内 Stream SDK 当前连接用户与服务端返回用户不同，前端必须先断开旧用户再连接新用户，避免同一客户端实例用户冲突。

登录、退出或切换账号时，清理旧连接的事件监听、页面消息及本地未读状态，再按当前身份初始化。沿用现有跳转和页面初始化流程；页面重载已释放的连接与监听无需重复处理，不新增跨标签页实时同步机制。清理旧未读状态后，登录用户仍应正常请求通知 token。

匿名访客频道不会自动并入登录账号频道。旧访客消息仍保留在原频道中，但登录后的账号频道不会自动显示这些历史消息。

### 3.4 未读检查

- 登录用户在现有未读初始化入口检查未读，不依赖本地游客会话标记或缓存未读数；新设备无需先打开聊天页。
- 通知 token 接口对登录用户直接使用稳定身份，即使没有 `stream_chat.guest_id` 也可签发。
- 游客保留现有检查条件；没有已初始化的游客 Session 时，不为通知检查新建游客身份。
- 聊天页继续由自身连接处理未读，避免导航重复建立连接。

## 4. 预计改动文件

| 文件 | 预计改动 |
| --- | --- |
| `app/Modules/Support/Services/StreamChatTokenService.php` | 登录用户使用稳定 Stream ID；访客保留 Session ID。统一 token、用户 payload、频道 ID 和成员 ID。主聊天与未读通知 payload 使用相同身份解析规则。 |
| `resources/views/support/stream-chat.blade.php` | 身份切换时清理旧连接、监听、消息及未读状态，再连接当前用户。频道仍由服务端 payload 指定并调用 `watch()`。 |
| `resources/js/stream-chat-unread.js` | 登录用户跳过本地游客标记限制；游客保留现有条件；账号切换时清理旧未读状态及仍存活的连接、监听。 |
| `resources/js/app.js` | 复用 `resolvePageCacheContext()`，将现有服务端渲染的账号上下文传给未读初始化入口。无需修改导航 Blade。 |
| `tests/Feature/Support/StreamChatTest.php` | 覆盖登录用户重复请求时 Stream ID/频道 ID 稳定、访客原有行为不变、通知 token 身份一致等情形。 |
| `tests/Frontend/stream-chat-unread.test.js` | 覆盖新设备无本地标记仍检查未读、游客条件不变、账号切换清理旧状态；复用现有测试文件。 |

不预计增加数据库迁移、依赖或 Stream 后台配置变更。

## 5. 实施步骤

1. **上线前基线确认**：核对生产 Stream 应用、API Key 对应项目、channel type/prefix、当前客服访问规则及客服台频道查询逻辑；记录当前部署版本。
2. **实现稳定身份**：服务端根据认证状态选择身份。认证用户直接使用不可修改的 `username`，未登录用户保持原 Session 随机 ID。实施前核对用户名全局唯一、字符集兼容 Stream ID。
3. **修正未读检查**：主聊天与通知 token 复用同一身份规则。登录用户不依赖游客 Session 或本地游客标记即可检查未读；游客保留现有检查条件，聊天页避免重复连接。
4. **处理客户端身份切换**：沿用现有页面生命周期，断开仍存活的旧连接、移除旧监听、清空旧消息和未读状态，再连接当前身份；不另建跨标签页同步机制。
5. **验证客服工作台**：确认客服端仍能查询新频道、显示消息、回复消息并正确更新未读状态；验证图片附件流程。
6. **自动化验证**：复用现有测试文件，覆盖同账号跨 Session 身份稳定、不同账号身份不同、登录用户无游客 Session 仍可获取通知 token、新设备未读检查、游客原有行为及账号切换清理。执行现有前端测试命令、`php artisan test` 和 `npm run build`。
7. **生产小范围验收**：用测试客户账号在两台设备或两个浏览器登录，核对 Stream 用户 ID 与频道 ID 相同；再验证未登录访客仍可联系在线客服。
8. **观察后完成上线**：检查 Stream API Logs、客服台新频道、消息送达与 token 错误情况，确认稳定后完成发布。

## 6. 生产数据兼容与风险

- **匿名历史不自动迁移**：改造后登录客户进入新的账号频道。以前随机访客频道及消息继续保留在 Stream 和客服工作台中，但不会自动并入登录频道。
- **ID 格式需长期稳定**：生产上线后如改变 ID 前缀或生成规则，会再次创建另一套用户和频道。
- **多账号切换需正确断连**：同一浏览器登录不同账号时，必须切换 Stream 客户端身份，否则 SDK 可能报用户冲突或沿用旧连接。
- **ID 使用现有用户名**：项目登录字段为 `username`，当前用户名不可修改；实施前仍需确认其全局唯一约束与字符规则符合 Stream ID 要求。不得改用邮箱或手机号等个人信息。
- **消息保留行为不变**：本改造不删除 Stream 消息；是否清理由 Stream 保留策略或显式删除操作决定。

## 7. 回滚方案

应用代码回滚到改造前版本即可恢复原随机访客身份逻辑。改造期间创建的账号频道及消息仍保留在 Stream，但旧代码不会自动回到这些账号频道。回滚时记录上线期间新建的账号频道，供客服继续查阅。

本方案不要求删除任何 Stream 用户、频道或消息；回滚不执行数据清理。

## 8. 验收标准

- 同一登录账号在不同设备和不同 Session 下获得以其 `username` 为基础的相同 Stream 用户 ID 与客服频道 ID。
- 聊天页 token 和未读通知 token 使用一致的登录用户身份。
- 新设备无本地游客标记、无游客 Session 时，登录用户仍能获取通知 token 并检查未读，无需先打开聊天页。
- 游客→账号 A→退出→账号 B 按现有页面跳转流程切换后，不显示上一身份的消息或未读状态；仍存活的旧连接和监听已清理。
- 未登录访客仍可使用客服，且 Session 有效时身份保持稳定。
- 客服工作台可以看到并回复账号频道内的新消息。
- 原访客频道及消息仍保留，没有被误删或自动合并。
- `php artisan test` 与 `npm run build` 均通过。

## 9. 本文范围

本文为实施前方案，不代表已执行代码改造、生产部署或 Stream 数据迁移。本次不新增依赖、数据库映射或身份同步系统，不扩展历史合并、跨标签页实时同步或 token 即时撤销。跨设备恢复仅适用于稳定账号身份下产生的聊天，已签发 token 的有效期沿用现有规则。若后续要求合并匿名访客与登录账号的历史聊天，再另行设计。

## 10. 一次执行的任务目标与边界

后续执行本文件时，应连续完成代码修改、现有测试扩展、验证和交付记录，而不是只返回实施建议。执行起点为仓库根目录 `H:\Projects\icon`。开始前读取当前 `AGENTS.md`、检查工作区，保留已有改动；存在 `.codegraph/` 时先用 CodeGraph 查看下列符号及调用方。本文中的代码是实施约定和关键片段，需整合进当前文件，不可覆盖未涉及的功能。

本次只修改现有文件，不计划新建文件、类、目录、依赖、数据库表或配置项。两个公开 token 路由名称继续保留，避免修改调用方；虽然方法名带 Guest，登录请求同样按认证身份处理。客服端 token、JWT 签名方式和有效期沿用现状。

| 任务 | 目标 | 必须完成的产物 |
| --- | --- | --- |
| T1 | 两个 token 入口使用同一身份与 payload 生成代码 | 服务内消除重复生成逻辑；登录用户不再读取游客 ID |
| T2 | 新设备登录即可检查未读 | 入口传入账号上下文；登录分支绕过本地游客标记 |
| T3 | 账号切换后不残留旧连接和未读状态 | 初始化前比较账号上下文并清缓存；连接前检查 SDK 当前用户 |
| T4 | 用现有测试证明行为 | 后端身份与错误路径测试、前端未读和切换测试通过 |
| T5 | 完成验证和交付 | 记录实际命令、结果、修改文件和未验证事项 |

## 11. 文件级开发清单

以下路径均相对于仓库根目录。

| 操作 | 文件 | 修改位置与内容 |
| --- | --- | --- |
| 修改 | `app/Modules/Support/Services/StreamChatTokenService.php` | 保留两个公开方法；新增一个私有共享 payload 方法，统一身份选择、频道及 token 返回值 |
| 修改 | `resources/js/app.js` | 从已有导航缓存模块导入 `resolvePageCacheContext`，将其结果传给未读初始化 |
| 修改 | `resources/js/stream-chat-unread.js` | 增加账号上下文参数和一个缓存上下文 key；初始化先清旧账号缓存；登录用户主动检查；连接前检查当前 SDK 用户 |
| 修改 | `resources/views/support/stream-chat.blade.php` | `connectChat()` 内连接前检查 SDK 用户；切换时清理仍存活的旧订阅、消息、上传占位和未读状态 |
| 修改 | `tests/Feature/Support/StreamChatTest.php` | 扩展账号、Session、JWT 关联、请求身份不可伪造、已有错误路径测试 |
| 修改 | `tests/Frontend/stream-chat-unread.test.js` | 扩展账号上下文、缓存迁移、新设备检查、连接切换、失败及重复连接测试 |
| 修改 | `tests/Feature/Navigation/GlobalNavigationTest.php` | 验证已存在的服务端账号上下文可供初始化使用，游客与登录账号上下文正确 |
| 修改（已获用户确认） | `app/Filament/Resources/Users/Schemas/UserForm.php` | 已有用户编辑时禁用 username，使用 Filament 原生 `disabledOn('edit')`；新建用户仍可填写 |
| 修改（已获用户确认） | `tests/Feature/Admin/UserManagementPageTest.php` | 验证编辑请求不能改名，其他资料仍可保存，新建用户仍可设置 username |
| 只核对 | `resources/views/components/nav/top.blade.php` | 已有 `data-page-cache-context="guest"` 或 `user:<数据库用户ID>`，无需再加属性 |
| 只核对 | `resources/js/navigation-page-cache.js` | 已导出 `resolvePageCacheContext(root)`；复用，不修改缓存系统 |
| 只核对 | `app/Modules/Support/Http/Controllers/StreamChatNotifyTokenController.php` | 保留现有异常到 HTTP 状态的映射，确认游客未初始化仍返回 404 |
| 只核对 | `app/Modules/Support/Http/Controllers/StreamChatGuestTokenController.php`、`routes/web.php` | 保留 URL、公开访问和 CSRF 行为，不新增请求身份参数 |
| 只核对 | `resources/views/support/stream-chat-agent.blade.php`、`config/stream_chat.php` | 现有客服成员查询仍覆盖新频道，保留 channel type/prefix 和客服 ID 配置 |

导航测试是唯一额外的现有测试接入点；不得为此改造导航系统。若实施时文件已变化，以相同职责的当前符号为准，并在交付记录中说明实际路径。

## 12. T1：统一服务端身份和 payload

### 12.1 固定身份规则

| 请求状态 | Stream 用户 ID | 频道 ID | 是否创建游客 Session ID |
| --- | --- | --- | --- |
| 已登录，任一 token 入口 | 认证用户原始 `username`，保留大小写 | `<配置的channel_prefix>-customer-<username>` | 否 |
| 游客，主聊天入口 | 现有 `stream_chat.guest_id`，缺少时生成 `guest_` + 原有随机串 | 原有 `<prefix>-<将guestId下划线替换为连字符>` | 缺少时创建 |
| 游客，通知入口 | 现有 `stream_chat.guest_id` | 同上 | 否；缺少时维持 404 |

上线前确认 username 非空、唯一、不可修改且符合当前 Stream 标识规则，并确认不会与现有客服、游客身份冲突。不要自行小写化、截断或替换用户名字符，避免不同账号被合成同一身份。若现有数据不满足这些前提，应报告具体冲突，不能静默回退游客身份或自行更换 ID 方案。

实施核对发现后台原先允许改名，用户已确认本次同时锁定已有用户的后台 username。使用 Filament 原生禁用字段能力，使其不参与编辑表单的保存；新建不受影响。不扩展为数据库或模型层的全局禁止改名，维护脚本及手工 SQL 仍须遵守不可改名的约定。

### 12.2 代码修改方式

两个公开方法仅委托同一私有方法：

```php
public function issueGuestTokenPayload(Request $request): array
{
    return $this->issueCustomerTokenPayload($request, true);
}

public function issueExistingGuestTokenPayload(Request $request): array
{
    return $this->issueCustomerTokenPayload($request, false);
}
```

新增 `private function issueCustomerTokenPayload(Request $request, bool $allowGuestCreation): array`，按以下顺序实现：

1. 保留原有 API Key / Secret 检查及异常文本，先检查配置再处理身份。
2. 使用 `$request->user()` 获取认证用户。若为现有 `User`，使用其 username；不读取、不覆盖 `stream_chat.guest_id`。
3. 若为游客，读取原有 Session ID。缺失且不允许创建时，抛出原有 `Stream Chat guest session is not initialized.` 异常；允许创建时沿用现有随机 ID 和 Session 写入。
4. 显示名称调用现有 `resolveDisplayName()`；游客名称仍保存至 `stream_chat.guest_name`，登录分支无需写入游客名称。
5. 使用现有配置生成频道及成员，返回原结构。

核心身份及频道分支如下，配置检查、游客创建和名称处理按上述要求整合：

```php
$user = $request->user();
$isAuthenticated = $user instanceof User;
// $streamUserId：登录时取 $user->username；游客时取现有或新建 guestId。
$channelPrefix = (string) config('stream_chat.channel_prefix', 'support');
$channelId = $isAuthenticated
    ? sprintf('%s-customer-%s', $channelPrefix, $streamUserId)
    : sprintf('%s-%s', $channelPrefix, str_replace('_', '-', $streamUserId));
```

返回协议保持不变，两个入口均由共享方法生成：

```php
return [
    'apiKey' => $apiKey,
    'token' => $this->createUserToken($streamUserId, $apiSecret),
    'user' => ['id' => $streamUserId, 'name' => $displayName],
    'channel' => [
        'type' => (string) config('stream_chat.channel_type', 'messaging'),
        'id' => $channelId,
        'name' => $displayName,
        'members' => [
            $streamUserId,
            (string) config('stream_chat.agent_user_id', 'support_agent_1'),
        ],
    ],
];
```

仅在现有服务增加这个私有方法，是为复用项目特有身份规则；认证、Session、配置及随机数继续使用 Laravel 现有能力，不引入新的身份框架。客户端传入的 `user_id`、username、频道或成员字段均不参与身份选择。

## 13. T2：新设备未读检查与缓存隔离

### 13.1 复用现有认证上下文

`resources/js/app.js` 合并现有 import，修改初始化调用：

```javascript
import { initNavigationPageCache, resolvePageCacheContext } from './navigation-page-cache';

// 保留现有其他初始化调用和顺序。
void initStreamChatUnreadBadge({ accountContext: resolvePageCacheContext() });
```

`accountContext` 是服务端页面标识：`guest` 或 `user:<数据库用户ID>`，只用于本地缓存隔离与登录判断，不是 Stream 用户 ID，也不能作为后端授权依据。导航 Blade 已输出此值，无需增加查询或新接口。

### 13.2 初始化顺序及代码约定

在 `stream-chat-unread.js` 导出常量：

```javascript
export const STREAM_CHAT_ACCOUNT_CONTEXT_KEY = 'stream_chat_account_context';
```

为 `initStreamChatUnreadBadge()` 增加 `accountContext = 'guest'` 参数。找到徽标节点后，在读取并展示缓存、注册监听、判断是否跳过远程检查之前执行：

```javascript
const previousContext = storage?.getItem?.(STREAM_CHAT_ACCOUNT_CONTEXT_KEY);
if (previousContext !== accountContext) {
  storage?.removeItem?.(STREAM_CHAT_UNREAD_COUNT_KEY);
  storage?.removeItem?.(STREAM_CHAT_SESSION_KEY);
}
storage?.setItem?.(STREAM_CHAT_ACCOUNT_CONTEXT_KEY, accountContext);
const isAuthenticated = accountContext.startsWith('user:');
```

首次上线时没有上下文 key，也执行一次清理，避免把历史公共缓存认作当前账号。代价是原有游客未读提示暂时清零，游客再次打开聊天页后恢复标记；不做额外缓存迁移。这项兼容行为要写入测试，不能继续要求“无上下文的旧缓存必须立即展示”。已有匹配上下文时，仍可先展示该身份的缓存未读数。

保留聊天页去重条件，远程检查改为：

```javascript
if (resolvePathname(windowObj) === '/stream-chat'
    || (!isAuthenticated && !shouldCheckRemoteUnread(storage, cachedCount))) {
  return null;
}
```

其余行为沿用当前代码：请求 `/stream-chat/notify-token`，成功连接后取 `me.total_unread_count` 并订阅未读事件；404 清理会话标记并将未读置零；其他失败保留当前身份已验证的缓存。异常处理不得重新显示已清掉的上一账号缓存。

不在此处创建频道或调用 `markRead()`，以免普通页面的未读检查误将客户消息标成已读。聊天页继续由自己的连接、`watch()` 和 `markRead()` 处理消息。

## 14. T3：连接与账号切换

### 14.1 两个连接入口都检查现有用户

修改聊天页 `connectChat()` 和未读模块的连接位置。使用当前 SDK 实例的用户 ID 判断，顺序为：

```javascript
const client = StreamChat.getInstance(payload.apiKey);
if (client.userID && client.userID !== payload.user.id) {
  // 先取消由当前入口持有的旧订阅，并清理其消息/未读状态。
  await client.disconnectUser();
}
```

- 当前没有连接用户：调用现有 `connectUser(payload.user, payload.token)`。
- 当前为同一用户：复用连接，不重复调用 `connectUser()`；未读初值从当前 client 用户状态读取。首次连接仍优先使用 `connectUser()` 返回的 `me.total_unread_count`，缺失时按现有解析规则处理为零。
- 当前为不同用户：取消当前入口持有的旧订阅、断开旧连接，清理旧状态后连接新身份。断开失败时走原有错误路径，不能继续连接并显示旧消息。
- 频道仍严格使用服务端 payload；不根据前端 Session 或缓存重算频道。

实施时核对项目实际加载的 SDK 方法与订阅返回值，不添加新的 SDK 依赖；这些片段用于指定顺序，不要求复制一个新的通用连接封装。

### 14.2 状态清理范围

聊天页在身份不同时清空消息容器、`pendingUploads` 和本地未读数，旧频道监听使用 SDK 返回的订阅对象取消。更新 `currentUserId` 后再渲染新频道历史。`stream_chat_has_session` 应在本次连接成功后写入，避免失败连接留下成功标记。

聊天页内联模块也在初始化时写入同一账号上下文并清理不匹配的缓存，避免 CDN 模块与 app.js 加载顺序不同导致刚建立的游客会话标记被另一入口误清理。两处使用相同的 key 和服务端导航上下文，不额外创建同步系统。

未读模块用局部变量持有当前订阅；在确需替换连接时取消它，不使用全局 `removeAllListeners()`，避免误删 SDK 或其他模块的监听。未读缓存清理由第 13 节的账号上下文比较负责。声音开关为用户设备偏好，不需要随身份清理。

登录、退出和换号沿用现有完整页面跳转；重载已销毁的页面监听不额外管理，不修改登录控制器。聊天页不在现有局部导航缓存白名单内，不将其改成局部导航。已有同账号局部页面切换不重复初始化未读连接。无需增加 MutationObserver、轮询、BroadcastChannel 或新的连接管理类。

验收范围是用户完成当前页面的登录、退出、换号流程后的状态；其他已打开标签页不承诺即时同步，旧聊天 token 不承诺退出时立即失效。

## 15. T4：测试任务与预期结果

### 15.1 后端测试

在 `tests/Feature/Support/StreamChatTest.php` 使用现有 User factory 和数据库测试惯例；新增持久化用户用例时启用项目现有数据库隔离方式（通常为 `RefreshDatabase`）。不要连接真实 Stream 服务。

| 用例 | 必须断言 |
| --- | --- |
| 同账号不同 Session | 清理前次 Session 后重新以同一 User 请求；两个 token 入口的 `user.id`、频道 ID、成员完全相同 |
| 登录用户无游客 Session | 先请求通知入口，无需先请求聊天入口，返回 200 且不创建 `stream_chat.guest_id` |
| 登录前已有游客 ID | 登录后两个入口使用 username，游客 ID 不覆盖账号身份 |
| 不同账号 | A、B 获得不同用户和频道；B 不能因传入 A 的 username、user_id、频道或成员而拿到 A 的身份 |
| JWT 与 payload | 解码 JWT payload，`user_id === response.user.id`；测试中校验原签名仍匹配测试 secret，不断言两个 token 字符串相等 |
| 配置化频道前缀 | 修改测试配置前缀后账号与游客频道均遵循规则 |
| 游客回归 | 同 Session 身份稳定，游客频道格式与显示名称不变；未初始化通知返回原有 404 且不创建 ID |
| 错误路径 | 未配置时两个入口维持现有错误映射，不能返回成功 token |
| 导航上下文 | 在 `GlobalNavigationTest.php` 断言游客为 `guest`，账号 A/B 分别输出各自的 `user:<id>` |

本次接口没有新增必填请求参数，因此无需人为增加 422 分支。无效或伪造身份输入按现有协议忽略，测试证明不能借此切换身份。真实频道读写的权限拒绝需在 Stream 联调中验证，不能用 Laravel payload 测试冒充。

### 15.2 前端测试

在现有 `tests/Frontend/stream-chat-unread.test.js` 中扩展 mock，模拟 `client.userID`、当前用户未读数、`disconnectUser()`、订阅 `unsubscribe()` 和调用顺序。不要增加测试框架。

- 为原有“缓存立即展示”“已有游客会话检查”用例补上匹配的 `stream_chat_account_context: 'guest'`，保持原场景含义。
- 登录账号 `user:1` 在空 storage 下请求通知 token 并显示远程未读数。
- 游客在匹配上下文、无标记且无未读时跳过请求；有标记时正常检查。
- 缺少上下文 key 的历史缓存清零；不显示旧红点。
- `user:1 → user:2`、`user:1 → guest`、`guest → user:1` 在显示缓存前清理旧未读数和会话标记；登录新身份即使缓存被清仍执行远程检查。
- 同账号上下文不清理缓存；请求失败可保持该账号缓存。
- SDK 为其他用户时先断开再连接；同用户不重复连接；未连接时正常连接。
- 404 清理缓存；断开或连接失败时不显示前一账号未读；聊天页不请求导航通知 token。
- 保留已有未读事件更新、ARIA 状态及不触发声音的测试。

聊天页切换逻辑由浏览器联调验证，不为测试而拆分整页脚本或新增连接抽象。测试与 mock 必须断言实际状态和调用顺序，不只匹配源代码字符串。

## 16. T5：执行顺序、命令与完成条件

### 16.1 执行顺序

1. 检查工作区与用户名约束，确认现有路由、SDK 及文件未发生影响方案的变化。
2. 按 T1 修改服务和后端测试；运行客服定向测试。
3. 按 T2 修改 `app.js` 与未读模块，按 T3 修改聊天页；同步更新现有前端和导航测试。
4. 运行前端定向测试，再运行完整 PHP 测试和生产构建。失败则修复本次引入的问题后重跑；不得以构建通过替代行为验证。
5. 检查 diff，只保留本主题改动，记录验证结果。具备浏览器和 Stream 测试环境时执行联调矩阵；不具备则明确列为未验证，不声称已经上线完成。

### 16.2 必跑命令

在仓库根目录逐条执行并记录结果：

```powershell
php artisan test --filter=StreamChatTest
php artisan test --filter=GlobalNavigationTest
node --test tests/Frontend/stream-chat-unread.test.js
php artisan test
npm run build
git diff --check
git status --short
```

当前 `package.json` 没有 `test` script，因此不使用 `npm test`，也不为本次新增 script。数据库测试只能连接已确认的测试数据库，不能针对生产数据库运行迁移或 `RefreshDatabase`。

### 16.3 最小联调矩阵

| 场景 | 通过条件 |
| --- | --- |
| 设备一登录 A 聊天，客服回复；设备二新浏览器登录 A | 不先打开聊天页即可检查未读；进入后看到同一账号频道和新记录 |
| 游客聊天后登录 A | 进入 A 的稳定频道，不自动合并游客记录 |
| 同浏览器 A 退出后登录 B | 不显示 A 的消息与未读，B 连接自己的频道 |
| 游客刷新及 Session 失效 | 有效 Session 身份不变；失效后允许新建游客身份 |
| 客服查询、回复、图片附件 | 新频道可见，文本和图片流程正常 |
| 客户 A 尝试访问 B 频道 | Stream 实际拒绝未授权的读取及发送；如失败，报告权限配置阻塞，不扩大本次代码范围自行重构 |

### 16.4 完成定义与交付格式

- 第 11 节必须修改的文件已落实，不保留“以后实现”的核心逻辑或占位代码。
- 第 15 节自动化用例已实现，必跑命令通过；任何环境阻塞应记录具体命令和原因，不标记为全部完成。
- 第 8 节验收标准满足；联调结果与自动化结果分别记录。
- 交付说明列出实际修改/新建文件（按本方案应无新建文件）、关键行为、执行命令、验证结果和已知限制。
- 不自动提交、部署、清理 Stream 数据或修改生产配置；后续部署沿用现有发布流程。

可用于后续执行的任务描述：

> 按 `docs/support-chat-stable-user-id-migration-plan.md` 完成 T1–T5 的代码改造与验证，遵循现有项目规则，连续完成到可交付状态。保持 username 身份规则和游客兼容，不新增依赖或复杂同步机制。测试或构建失败需修复后再交付；真实 Stream 联调缺少环境时明确列出未验证事项，不把它当成已完成的验证。

## 17. 本次执行记录（2026-11-05）

### 17.1 已实施

- T1：两个 token 入口复用 `issueCustomerTokenPayload()`；登录使用原始 username，游客保留 Session ID；频道前缀继续使用现有配置。
- T2：`app.js` 传入现有账号上下文；登录用户在没有游客本地标记时仍检查未读；首次上线及换号先清理旧缓存。
- T3：聊天页和未读入口检查 SDK 用户并按需断开；清理旧订阅、消息和未读状态，同身份复用连接。未读模块仅持有一个清理回调，防止重复初始化残留监听；没有新增同步系统。
- 用户确认的补充：后台已有用户的 username 禁止编辑，且不参与编辑表单保存；新建用户仍可填写。
- 修改文件：第 11 节全部“修改”行对应的 9 个现有代码/测试文件，以及本执行文档；没有新增业务文件。`public/build/manifest.json` 和哈希资源由构建同步更新。

### 17.2 自动化验证

| 命令或检查 | 实际结果 |
| --- | --- |
| `php artisan test --filter=StreamChatTest` | 11 项通过、101 条断言 |
| `php artisan test --filter=GlobalNavigationTest` | 10 项通过、442 条断言 |
| `php artisan test --filter=UserManagementPageTest` | 5 项通过、43 条断言，包含篡改编辑字段仍无法改名的用例 |
| `node --test tests/Frontend/stream-chat-unread.test.js` | 18 项通过 |
| `php artisan test`（全部修改后的最终运行） | 321 项通过、2222 条断言 |
| `npm run build` | 通过；Vite 仅提示构建插件耗时，无构建错误 |
| `php vendor/bin/pint --test`（本次 5 个 PHP 文件） | 通过 |
| `git diff --check` | 通过 |
| 构建 manifest 引用检查 | 74 个条目引用的资源均存在 |

当前执行环境为 PHP 8.5.12、Node 24.18.0；未另行在 PHP 8.3 环境运行。PHP 测试使用项目已配置的专用 `icon_market_test` 数据库，不对业务库执行迁移。业务库只做了用户名聚合查询：6 个用户，空值/字符检查异常 0 个，与游客前缀及配置客服 ID 冲突 0 个；未读取或输出聊天 token、密钥、用户明细。

### 17.3 未验证与发布限制

本机配置中 Stream API Key / Secret 未就绪，所以未执行真实 Stream 浏览器联调。第 16.3 节的跨设备消息恢复、图片附件、客服回包及跨客户频道权限拒绝，须在配置好的测试环境按矩阵验证；自动化 mock 测试不能替代这些结果。本次没有部署或更改生产配置。

旧游客记录不合并，首次升级无法识别归属的旧未读缓存会清零；跨标签页实时同步和退出后的 token 即时撤销不在范围内。后台已锁定改名，但维护脚本或手工 SQL 仍不得修改 username，否则会产生新聊天身份。

工作区原有 `.env.testing`、`phpunit.xml`、后台用户列表/持仓页面等未提交改动已保留；未自动提交。本文件本身仍为 Git 未跟踪文件，后续提交改造时需要一并纳入。
