# 产品与帮助多语言方案设计稿

## 1. 目标

为当前项目确定一套足够轻、可长期维护的多语言方案。

当前结论：

- 产品走数据库多语言
- 帮助 FAQ 走语言包
- 固定界面文案走语言包

## 2. 已定方案

### 2.1 产品

产品是业务实体，管理员希望在后台新建产品时同时填写多语言标题和简介，因此产品采用：

- `products`
- `product_translations`

其中：

- `products` 存语言无关的业务字段
- `product_translations` 存各语言标题和简介

### 2.2 帮助 FAQ

帮助内容数量少、结构固定、变更频率低，因此当前阶段不建表，直接走语言包：

- `lang/zh-CN/help.php`
- `lang/en/help.php`
- 其他 locale 对应的 `help.php`

FAQ 的问题与答案都放在语言包中维护。

### 2.3 固定界面文案

所有固定界面文案统一走 Laravel 语言包，例如：

- 导航文字
- 按钮文字
- 表单标签
- 页面固定标题
- 通用提示语
- 校验错误信息

## 3. 产品数据结构

### 3.1 主表：`products`

继续保留现有主表，用来存放不随语言变化的业务字段，例如：

- `id`
- `code`
- `unit_price`
- `purchase_limit`
- `limit_min_usdt`
- `limit_max_usdt`
- `rate_min_percent`
- `rate_max_percent`
- `cycle_days`
- `product_icon_path`
- `symbol_icon_paths`
- `is_active`
- `sort`
- `created_at`
- `updated_at`

当前主表中的：

- `name`
- `description`

后续迁移到翻译表承载。

### 3.2 翻译表：`product_translations`

建议字段：

- `id`
- `product_id`
- `locale`
- `name`
- `description`
- `created_at`
- `updated_at`

建议约束：

- `unique(product_id, locale)`
- `index(locale)`
- `foreign key(product_id) -> products.id`

## 4. 对现有 products 表的判断

现有 `products` 表适合作为产品主表继续保留，但不适合直接扩展成多语言内容表。

当前表里的大部分字段都是语言无关业务字段，真正需要翻译的主要是：

- `name`
- `description`

因此当前确定的方向是：

- 保留 `products`
- 新增 `product_translations`
- 不在 `products` 原表继续增加多组语言字段

## 5. Laravel 层约定

### 5.1 产品翻译读取

产品内容按以下规则读取：

1. 优先取当前 locale 的翻译
2. 当前 locale 缺失时回退 `zh-CN`
3. 控制器 / Query / Service 层负责解析翻译
4. Blade 只展示最终结果

### 5.2 帮助页读取

帮助页直接读取当前 locale 的语言文件，不走数据库查询。

## 6. 语言包结构建议

当前页面很少，建议直接按页面组织语言包，一个页面一个语言文件。

例如：

- `lang/zh-CN/home.php`
- `lang/zh-CN/products.php`
- `lang/zh-CN/help.php`
- `lang/zh-CN/me.php`
- `lang/zh-CN/support.php`

其他语言对应同名文件：

- `lang/en/home.php`
- `lang/en/products.php`
- `lang/en/help.php`
- `lang/en/me.php`
- `lang/en/support.php`

页面内的固定文案都放在对应页面语言包里维护。

例如：

- `products.php` 存产品列表页、产品详情页的固定 UI 文案
- `help.php` 存帮助页标题、说明、FAQ 内容
- `me.php` 存我的页面固定文案

这样结构最直观，后续你要改某个页面的文案，直接打开对应页面语言包即可。

## 7. 后台维护方式

当前只对产品提供后台多语言维护。

产品后台建议分为两部分：

1. 基础信息
   - 价格
   - 限额
   - 状态
   - 图标

2. 多语言内容
   - `zh-CN` 标题、简介
   - `en` 标题、简介
   - 其他固定语言的标题、简介

管理员在新建产品时，一次填写各语言标题和简介。

帮助 FAQ 暂不提供后台编辑，继续通过语言包维护。

## 8. 迁移建议

### 8.1 产品

分两阶段处理：

第一阶段：

- 新增 `product_translations`
- 把现有 `products.name`、`products.description` 迁移到默认语言 `zh-CN`
- 读取逻辑优先翻译表，缺失时暂时回退主表字段

第二阶段：

- 数据确认完成后，移除 `products.name`、`products.description`

### 8.2 帮助

帮助页当前不做数据库迁移，后续只需把现有 FAQ 内容整理进 `lang/*/help.php`。

## 9. 最终结论

当前项目的多语言方案确定为：

1. 产品使用 `products + product_translations`
2. 帮助使用 `lang/*/help.php`
3. 固定界面文案全部进入 Laravel `lang/*`
4. 产品多语言由后台在创建/编辑产品时一次性维护
5. 帮助 FAQ 继续通过代码语言包维护
