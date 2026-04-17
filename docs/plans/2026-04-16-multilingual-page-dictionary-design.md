# 多语言方案设计稿（页面级字典 + 数据库翻译）

## 1. 目标与边界

本方案用于当前 Laravel 13 项目的多语言落地，目标是：

1. 固定 UI 文案可按页面独立维护；
2. 商品介绍支持后台按语言录入；
3. 帮助页 FAQ 支持后台按语言录入；
4. 不引入 SEO 多语言字段（本项目当前不做 SEO）。

## 2. 最终方案（已定稿）

### 2.1 UI 固定文案：页面级语言包

采用 Laravel 语言包，按页面拆文件，每个页面一个字典文件。

示例目录：

```text
lang/
  zh-CN/
    pages/
      home.php
      product-list.php
      product-detail.php
      help-center.php
      faq.php
    common.php
  en/
    pages/
      home.php
      product-list.php
      product-detail.php
      help-center.php
      faq.php
    common.php
```

约定：

1. 页面文案使用 `__('pages/product-detail.buy_now')`；
2. 公共按钮与短语放 `common.php`，避免重复；
3. key 稳定不随文案变更而变更；
4. key 命名统一小写下划线。

### 2.2 商品介绍：数据库翻译表

商品为业务实体，采用主表 + 翻译表模式：

1. `products`：语言无关字段（当前项目已存在，无需新建）；
2. `product_translations`：语言相关字段（本次新增）。

当前最小落地口径：商品只翻译简介字段。

`product_translations` 字段：

1. `id`
2. `product_id`
3. `locale`
4. `description`
5. `created_at`
6. `updated_at`

约束：

1. `unique(product_id, locale)`
2. `index(locale)`
3. `foreign key(product_id) -> products.id`

语言规模：

1. 目标支持 8 种语言；
2. 单个商品最多对应 8 条翻译记录（每种语言一条）。

### 2.3 FAQ：数据库翻译表

FAQ 数量多、更新频率高，采用数据库管理。

本阶段固定采用（无分类版本）：

1. `help_items`
2. `help_item_translations`

## 3. 本阶段新增三张表（明确结构）

### 3.1 `product_translations`

用途：商品多语言简介（仅 `description`）。

字段：

1. `id`（主键）
2. `product_id`（关联 `products.id`）
3. `locale`（如 `zh-CN`、`en`）
4. `description`（TEXT）
5. `created_at`
6. `updated_at`

约束与索引：

1. `unique(product_id, locale)`
2. `index(locale)`
3. `foreign key(product_id) -> products.id on delete cascade`

### 3.2 `help_items`

用途：FAQ 主体（语言无关字段）。

字段：

1. `id`（主键）
2. `sort`（排序，默认 0）
3. `is_active`（是否启用）
4. `created_at`
5. `updated_at`

约束与索引：

1. `index(is_active, sort)`

### 3.3 `help_item_translations`

用途：FAQ 问题与答案多语言内容。

字段：

1. `id`（主键）
2. `help_item_id`（关联 `help_items.id`）
3. `locale`（如 `zh-CN`、`en`）
4. `question`（VARCHAR）
5. `answer`（TEXT）
6. `created_at`
7. `updated_at`

约束与索引：

1. `unique(help_item_id, locale)`
2. `index(locale)`
3. `foreign key(help_item_id) -> help_items.id on delete cascade`

## 4. 语言与回退策略

统一读取规则：

1. 优先当前 `locale`；
2. 缺失时回退默认语言（建议 `zh-CN`）；
3. Blade 仅展示最终字符串，不做业务判断。

统一配置建议：

1. 在 `config/app.php` 或独立 `config/i18n.php` 维护 `supported_locales`；
2. 通过中间件设置 `App::setLocale()`；
3. 语言切换来源可用 URL 前缀、session 或 cookie（三选一并全站统一）。

## 5. 后台录入规则（管理员）

### 5.1 商品

后台商品创建/编辑页面按语言 Tab 填写：

1. `description`

发布前校验：

1. 默认语言必须完整；
2. 非默认语言允许为空但应有缺失提示。

### 5.2 FAQ

后台 FAQ 创建/编辑同样按语言 Tab 填写：

1. `question`
2. `answer`

运营能力建议：

1. 提供“仅看缺失翻译”筛选；
2. 提供每条 FAQ 的翻译完成度标记。

## 6. 分层与职责

遵循当前项目分层约束：

1. Controller：参数校验、调用 Service、返回响应；
2. Service：多语言回退与聚合逻辑；
3. Model（Eloquent Scope/Relation）：翻译关联与查询约束；
4. Blade：只展示，不查库，不做复杂分支。

## 7. 工程基线（必做）

1. locale 来源必须全站统一，且只保留一套策略（URL 前缀或 session/cookie，三选一）。
2. locale 决策统一在中间件完成，Controller/Blade 不自行决定 locale。
3. 翻译读取与 fallback 必须封装在 Service + Eloquent Scope，禁止在 Controller/Blade 手写分支。
4. 商品与 FAQ 共用同一套翻译读取模式，避免重复实现。

## 8. 数据迁移建议

### 8.1 商品

1. 新建 `product_translations`；
2. 将 `products` 现有中文内容迁移为 `zh-CN`；
3. 读取逻辑切换到翻译表 + 回退；
4. 本阶段只迁移/维护 `description` 的多语言。

### 8.2 FAQ

1. 新建 `help_items` 与 `help_item_translations`；
2. 将现有 FAQ 文案导入默认语言；
3. 后续翻译由后台逐步补齐。

## 9. 性能与质量要求

1. 列表页避免 N+1，统一 eager load 翻译关系；
2. 热点内容可按 `locale` 维度缓存；
3. 新功能测试至少覆盖：
   - 成功路径
   - 无权限路径
   - 非法输入路径

## 10. 最小验收清单

1. locale 切换有效，且全站统一按同一策略生效。
2. 页面 UI 字典可随 locale 正常切换。
3. 商品 `description` 可按 locale 读取，并在缺失时回退默认语言。
4. FAQ `question/answer` 可按 locale 读取，并在缺失时回退默认语言。
5. 后台可按语言录入商品简介与 FAQ 问答。

## 11. 本次结论

最终采用：

1. UI：页面级语言包（每页一个字典文件）；
2. 商品：复用现有 `products`，新增 `product_translations`（当前仅 `description`）；
3. FAQ：无分类，采用 `help_items + help_item_translations`；
4. 全站统一 locale 与 fallback 机制；
5. 不引入 SEO 多语言字段；
6. 本阶段数据库新增 3 张表：`product_translations`、`help_items`、`help_item_translations`；
7. 语言规模维持 8 种，按统一机制逐步补齐翻译内容。

## 12. 推进顺序（实施路线）

### 12.1 先打通前台读取链路

1. 帮助页改为优先数据库翻译读取（`help_items + help_item_translations`）；
2. 商品详情改为走 `product_translations + fallback`；
3. 保持 Blade 只展示最终字符串，不在视图层写 locale 分支。

### 12.2 再补后台录入链路

1. 产品管理改为按语言维护翻译（至少覆盖 `description`）；
2. 新增 HelpItem 的 Filament 资源，并包含 translations 录入能力；
3. 后台录入遵循默认语言必填、非默认语言可缺失但需可识别。

### 12.3 最后做数据迁移与退场

1. 写一次性迁移脚本：`products.description -> product_translations(zh-CN)`；
2. 写一次性迁移脚本：`config/help.php -> help_items/help_item_translations`；
3. 稳定期内保留旧数据源兜底，验证完成后逐步移除 `config/help.php` 依赖。

### 12.4 测试一次补齐

1. 至少覆盖：成功路径、无权限路径、非法输入路径；
2. 增加 locale/fallback 的 Feature Test；
3. 发布前执行并通过：
   - `php artisan test`
   - `npm run build`
