# 用户等级手动授予方案

## 目标

管理员可为用户手动设置 0–5 共六档等级。等级只用于 `/me` 页面展示，不参与业务规则或自动升级判断。

## 范围

- 档位编码为 `0` 至 `5`，`NULL` 表示未授予等级。等级 0 是有效档位，与未授予区分。
- 管理员在现有 Filament「用户管理」新建或编辑表单中选择等级。
- 等级图标放在 `/me` 账号信息卡片中 `$profile['label']` 右侧，与标签同一行，因此位于账号 ID 和复制按钮上方。
- 六档使用统一且可区分的图标体系，并提供等级名称或可访问文本，避免只依赖颜色传达等级。

## 不在本次范围

- 自动升级/降级、资格判断、积分或交易量门槛。
- 用户自行修改等级。
- 等级权益、权限或价格差异。
- 独立等级管理模块、可配置名称/图标、授予历史、审批流或通知。

## 数据设计

在 `users` 表增加可空整数字段 `user_level`，合法值为 `0` 至 `5`，`NULL` 表示未授予。默认 `NULL`，不改变现有用户行为。该字段只用于单用户展示，不增加索引。


实施时新增可回滚的 Laravel migration，并在同一变更中同步更新 `database/sql/mvp_schema.sql`。`User` 模型允许后台表单写入等级并将其 cast 为整数（空值保留为 `null`）。表单验证只接受空值或 `0` 至 `5`。

## 后台交互

- 在现有 `app/Filament/Resources/Users/Schemas/UserForm.php` 用户信息区增加「用户等级」选择框，选项为 0–5，并允许清空为未授予。
- 新建用户可不选等级；编辑时可授予、更改或清除等级。
- 在现有用户列表增加等级列，显示档位名称；未授予时显示空值占位。
- 继续使用现有用户管理及其授权，不引入后台包或独立资源。

后台使用 0 至 5 作为档位名称。前台使用已确认的双主题徽章，并为每个徽章提供 Level 0 至 Level 5 的可访问名称。

## `/me` 展示

账号卡片位于 `resources/views/components/me/account-panel.blade.php`。在 `$profile['label']` 所在行增加靠右的等级图标。等级为 `NULL` 时不显示；等级为 0–5 时显示对应图标及可访问文本。

`MyCenterController` 将已登录用户的 `user_level` 放入现有 `$profile` 数据。访客没有等级。Blade 只按传入的等级展示，不查询数据库，也不实现等级业务判断。

## 预计变更文件

| 文件 | 变更 |
| --- | --- |
| `database/migrations/<timestamp>_add_user_level_to_users_table.php` | 新增可回滚的 `user_level` 字段迁移 |
| `database/sql/mvp_schema.sql` | 同步增加字段定义 |
| `app/Models/User.php` | 允许赋值并 cast 等级字段 |
| `app/Filament/Resources/Users/Schemas/UserForm.php` | 增加 0–5 档手动选择 |
| `app/Filament/Resources/Users/Tables/UsersTable.php` | 在后台列表显示等级 |
| `app/Modules/User/Http/Controllers/MyCenterController.php` | 向页面资料传递等级 |
| `resources/views/components/me/account-panel.blade.php` | 在账号标签行右侧展示等级图标 |
| `tests/Feature/Admin/...` | 覆盖管理员授予、更改、清除等级和非法值拒绝，并覆盖非管理员提交被拒绝 |
| `tests/Feature/User/MyCenterPageTest.php` | 覆盖访客/未授予不显示、0–5 档展示、清空后不显示 |

除现有模块的必要集成点外，不新增独立业务服务或模块。徽章位于 `public/images/user-level/`，每档两张 192×50 的透明 WebP。深色主题用白底徽章（on-dark），浅色主题用深底徽章（on-light）。

## 验收标准

1. 管理员可为用户设置 0–5 任一档，修改档位或清除等级；非法档位被拒绝。
2. 用户列表显示档位，未授予时显示空值状态。
3. 已授予等级的 `/me` 卡片在账号标签右侧显示匹配图标；未授予用户和访客不显示。
4. 等级只影响管理和展示，不影响登录、交易、充值、权限或其他业务行为。
5. Migration 可回滚，`database/sql/mvp_schema.sql` 与最终数据库结构同步。
6. 测试覆盖成功路径、权限拒绝、非法输入及清空后的展示行为；交付前运行 `php artisan test` 和 `npm run build`。

## 风险与边界

- 若未来等级要控制权益或权限，需另行定义业务规则和授权测试，不可将展示字段直接当作安全授权依据。
