# 余额储蓄收益功能设计方案（精简高效版）v0.2

## 1. 目标与边界
1. 新增第三类收益：余额储蓄收益。
2. 与产品收益同日结算节点执行。
3. 后台提供储蓄收益率设置页（可启停、可改比例）。
4. 方案强调低改动、强幂等、可追溯，不做过度建模。

## 2. 精简方案总览
1. 新增一个配置表：`savings_yield_settings`。
2. 不新增“储蓄结算明细表”，收益明细直接复用 `balance_ledgers`。
3. 用账本唯一键实现天然幂等：`(user_id, type, biz_ref_type, biz_ref_id)`。
4. 通过服务层统一口径，把储蓄收益纳入首页收入记录和我的收益汇总。

## 3. 为什么这样最省且稳
1. 你现在已有完整账务主链路（余额变更都走 `balance_ledgers`），复用成本最低。
2. 结算明细若再建新表，开发、联调、统计、测试面都会扩大。
3. 仅新增配置表即可满足“后台可配收益率”诉求。
4. 对现有代码侵入小，回归风险可控，交付速度快。

## 4. 数据结构

### 4.1 新增表：`savings_yield_settings`
1. `id`（主键，固定使用 `1`）。
2. `daily_rate` decimal(8,4) 默认 `0.0000`。
3. `is_active` boolean 默认 `false`。
4. `created_at` / `updated_at`。

### 4.2 复用表：`balance_ledgers`
1. `type`：新增值 `savings_interest_credit`。
2. `biz_ref_type`：`savings_interest`。
3. `biz_ref_id`：`{settlement_date}:{user_id}`，例如 `2026-04-24:1001`。
4. 幂等：依赖现有唯一键防重复发放。

## 5. 结算规则（MVP）
1. 触发时机：`settlement:daily` 同日执行。
2. 生效条件：
   - 配置启用；
   - `daily_rate > 0`；
   - 用户当前余额 `> 0`。
3. 收益公式：`profit = round(current_balance * daily_rate, 2)`。
4. 入账方式：
   - 锁用户余额（`lockForUpdate`）；
   - 更新用户余额；
   - 写入 `balance_ledgers`（`savings_interest_credit`）。
5. 幂等检查：先查同 `biz_ref_id` 账本是否已存在，存在则跳过。

## 6. 后台设计（Filament）
1. 新建 `SavingsYieldSettingResource`，单例编辑页。
2. 字段：
   - `daily_rate`：`>= 0` 且 `< 1`；
   - `is_active`：开关。
3. 首次访问自动 `firstOrCreate(id=1)`。
4. 不做版本管理、不做审核流。

## 7. 前台与统计改造
1. 首页收入记录：在现有“产品收益 + 推荐收益”基础上 union 一段储蓄收益账本。
2. 我的页面：
   - `today_profit` 增加当天 `savings_interest_credit` 汇总；
   - `total_profit` 增加历史 `savings_interest_credit` 汇总。
3. 多语言文案新增：`income_type.savings_interest`。

## 8. 文件罗盘（精简版）

### 8.1 新增文件
1. `database/migrations/2026_04_24_000000_create_savings_yield_settings_table.php`
2. `app/Modules/Savings/Models/SavingsYieldSetting.php`
3. `app/Modules/Savings/Services/GetSavingsYieldSettingService.php`
4. `app/Modules/Savings/Services/ProcessSavingsYieldBatchService.php`
5. `app/Filament/Resources/SavingsYieldSettings/SavingsYieldSettingResource.php`
6. `app/Filament/Resources/SavingsYieldSettings/Schemas/SavingsYieldSettingForm.php`
7. `app/Filament/Resources/SavingsYieldSettings/Pages/EditSavingsYieldSetting.php`
8. `app/Filament/Resources/SavingsYieldSettings/Pages/ListSavingsYieldSettings.php`
9. `tests/Feature/Savings/ProcessSavingsYieldBatchServiceTest.php`
10. `tests/Feature/Savings/SavingsYieldSettingManagementPageTest.php`

### 8.2 修改文件
1. `app/Modules/Settlement/Console/Commands/ProcessDailySettlementCommand.php`
2. `routes/console.php`
3. `app/Modules/Home/Services/HomeHeroPanelService.php`
4. `app/Modules/User/Http/Controllers/MyCenterController.php`
5. `resources/views/home/hero-panel-income-records.blade.php`
6. `lang/zh-CN/pages/income-records.php`
7. `lang/en/pages/income-records.php`（其余语言包补同键）
8. `database/sql/mvp_schema.sql`
9. `tests/Feature/Home/HomeHeroPanelFeedTest.php`
10. `tests/Feature/Home/HeroPanelRecordPagesTest.php`

## 9. 测试清单（够用版）
1. 成功：启用配置后可发放储蓄收益并入账。
2. 权限：非管理员不可改储蓄收益率。
3. 校验：负数或 `>=1` 的利率保存失败。
4. 幂等：同日期重复执行不重复发放。
5. 汇总：首页收入记录、我的收益统计均包含储蓄收益。
6. 质量门：
   - `php artisan test`
   - `npm run build`

## 10. 风险与控制
1. 结算基数口径：本期明确“结算执行瞬时余额”，避免歧义。
2. 大用户量性能：先用 `chunkById`，后续再按需要异步化。
3. 可追溯性：本期以账本为主追溯来源，不单独建储蓄明细表。

## 11. 本期不做
1. 阶梯利率、VIP 利率。
2. 利率版本历史回放。
3. 手工补发/冲正后台工具。
