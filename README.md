
## 生产环境初始化

项目内置一键初始化脚本：

```bash
bash scripts/deploy.sh
```

注意：该脚本不会在 `git pull` 后自动触发。每次部署更新后需要手动执行一次。

### PHP / 目录可配置说明
`bash scripts/deploy.sh` 默认使用：
- `PHP_BIN=/www/server/php/83/bin/php`
- `APP_DIR=仓库根目录（脚本自动推导）`
- `WEB_USER/WEB_GROUP` 自动优先使用 `www`，若不存在则使用 `www-data`

如果新服务器路径不同，可在执行时覆盖：

```bash
PHP_BIN=/usr/bin/php8.3 APP_DIR=/www/wwwroot/your-domain.com bash scripts/deploy.sh
```

如果需要显式指定权限用户组：

```bash
PHP_BIN=/usr/bin/php8.3 APP_DIR=/www/wwwroot/your-domain.com WEB_USER=www-data WEB_GROUP=www-data bash scripts/deploy.sh
```

首次执行前请确认：

1. 复制 `.env.production.example` 为 `.env`（若 `.env` 不存在，脚本也会自动复制）。
2. 在 `.env` 中填写真实生产数据库配置（不要使用占位密码）。
3. Web 站点根目录指向 `public`，并启用 Laravel 伪静态规则。
4. **项目要求 PHP >= 8.3.0**，服务器如有多个 PHP 版本，请确保 `PHP_BIN` 指向 8.3 可执行文件。
5. 由于 Filament / Livewire 运行时需要前端资产，请确保 `public/vendor/livewire` 目录可写，部署脚本会自动发布 Livewire 静态前端资源。

脚本会自动执行：`composer install --no-dev`、`key:generate`、数据库迁移、默认执行 `db:seed --force`、Livewire 资产发布、`storage/bootstrap` 权限修复，以及 Laravel 缓存重建（`optimize:clear` / `optimize`）。

另外，部署脚本会自动幂等写入 Laravel Scheduler 的 crontab（每分钟执行一次 `php artisan schedule:run`），默认开启，可通过环境变量关闭：

```bash
INSTALL_SCHEDULER_CRON=0 bash scripts/deploy.sh
```

建议部署后检查：

```bash
crontab -l
php artisan schedule:list
```

数据库说明：

1. `migrate --force` 只负责表结构迁移（创建/更新表），不会同步开发环境里的业务数据。
2. 如需同步开发环境数据，需手动导入 SQL 备份。
3. 已迁移过表结构时，导入“完整备份（含 CREATE TABLE）”通常会因表已存在报错，不会自动覆盖。
4. 导入“仅数据 SQL（INSERT）”通常是追加或按唯一键冲突报错，也不会自动覆盖旧数据。
5. 若目标是“完全覆盖线上数据”，需要先手动清库/清表，再导入备份。

服务器常用流程（拉取代码后）：

```bash
git pull
cp -n .env.production.example .env
# 编辑 .env，填写真实 DB 配置
PHP_BIN=/usr/bin/php8.3 APP_DIR=/www/wwwroot/your-domain.com bash scripts/deploy.sh
```

**部署故障排除：**
- 如遇到 "PHP Fatal error: Uncaught RuntimeException: Composer detected issues in your platform: Your Composer dependencies require a PHP version ">= 8.3.0""，请将 `PHP_BIN` 指向 PHP 8.3，例如：`PHP_BIN=/usr/bin/php8.3`
- 如遇到 "Your local changes to the following files would be overwritten by merge"，请先备份或清理 `public/build/` 目录下的文件，然后重新执行 `git pull`
- 如果后台打开后出现 `/livewire/livewire.min.js` 404，请检查 Nginx 是否把 `/livewire` 请求转发给 Laravel；也可以直接运行 `PHP_BIN=/usr/bin/php8.3 php artisan livewire:publish --assets` 以生成 `public/vendor/livewire` 静态前端资源。

### 后台空白页修复
- 如果后台打开后显示空白，并且浏览器控制台报 `/livewire/livewire.min.js` 404，说明 Livewire 前端资源未正确发布或 `/livewire` 路径没有被 Laravel 处理。
 解决步骤：
  1. 进入服务器目录：`cd /www/wwwroot/your-domain.com`
  2. 拉取最新代码：`git pull origin main`
  3. 运行部署脚本：`PHP_BIN=/usr/bin/php8.3 APP_DIR=/www/wwwroot/your-domain.com bash scripts/deploy.sh`
  4. 如果需要手动修复，可以执行：`PHP_BIN=/usr/bin/php8.3 php artisan livewire:publish --assets` 和 `PHP_BIN=/usr/bin/php8.3 php artisan optimize:clear`
  5. 确认 Nginx 伪静态规则生效，`public` 目录为站点根，并且 `/livewire` 请求能被 Laravel 路由处理。
- 这个问题通常不是代码业务逻辑出错，而是部署后 Livewire 资源未发布或服务端 rewrite 配置不正确。

### 主题配置
项目支持多风格切换，默认科技风格（tech），可选商务风格（business）。

- 配置文件：`config/themes.php`
  ```php
  return [
      'active' => env('APP_THEME', 'tech'),
      'available' => ['tech', 'business'],
  ];
  ```
- 在 `.env` 中设置 `APP_THEME=business` 可切换到商务风格。
- 前端支持实时切换（首页右下角按钮），并保存到浏览器 localStorage。

### 字体规范（5 档）
当前项目前端字体统一使用 5 档语义类，禁止在 Blade 中新增 `text-sm`、`text-xl`、`text-[...]` 这类直接字号写法。

统一入口：
- 根字号：`resources/css/app.css` 中 `html { font-size: clamp(...) }`
- 5 档类：`text-scale-micro` / `text-scale-body` / `text-scale-ui` / `text-scale-title` / `text-scale-display`

使用规范：

| 类名 | 用途 | 推荐场景 | 不要用于 |
|---|---|---|---|
| `text-scale-micro` | 微字 | 标签、角标、状态提示、次要辅助说明 | 正文段落、按钮主文案 |
| `text-scale-body` | 正文 | 页面段落、表单标签、列表说明、默认内容文本 | 大标题、金额主数值 |
| `text-scale-ui` | 交互文案 | 按钮、导航、Tab、可点击入口文案 | 长段正文、主标题 |
| `text-scale-title` | 小标题 | 卡片标题、模块标题、关键数值标题 | 页面主标题（H1） |
| `text-scale-display` | 大标题 | 页面主标题、首页关键数据大数字 | 次要信息、表单说明 |

落地示例：
```blade
<h1 class="text-scale-display font-semibold">产品市场</h1>
<h2 class="text-scale-title font-semibold">可购买产品</h2>
<p class="text-scale-body text-theme-secondary">请选择产品后继续操作。</p>
<button class="text-scale-ui font-semibold">立即购买</button>
<span class="text-scale-micro text-theme-secondary">更新于 2 分钟前</span>
```

PR 自检（字体相关）：
1. 页面中不新增 `text-sm`/`text-xl`/`text-[...]` 直接字号类。
2. 仅从 5 档语义类中选字号。
3. 大标题只用 `text-scale-display`，按钮只用 `text-scale-ui`，正文默认 `text-scale-body`。

### 推荐返利 MVP
推荐返利功能支持两级提成，邀请码通过 `invite_code` 参数进入站点后，会使用 session、signed cookie 和注册表单隐藏字段兜底保存，适配钱包内置浏览器。

后台配置：
- 入口：`/admin/referral-commission-settings/1/edit`
- 比例存储为小数，例如 `0.05` 表示 5%，`0.02` 表示 2%
- 校验规则：`0 <= 二级比例 <= 一级比例 < 1`

环境变量：
```env
REFERRAL_ENABLED=true
REFERRAL_GO_LIVE_DATE=2026-04-15
REFERRAL_BUSINESS_TIMEZONE=Asia/Shanghai
SETTLEMENT_ENABLED=true
SETTLEMENT_RUN_AT=00:05
SETTLEMENT_TIMEZONE=Asia/Shanghai
```

触发口径：
- 主链路：产品收益结算完成后，系统即时触发推荐提成发放。
- 兜底链路：可手动执行批处理命令补跑历史遗漏提成。

兜底补跑命令：
```bash
php artisan referral:commission-process
```

结算命令（手动补跑某日）：
```bash
php artisan settlement:daily --date=2026-04-16
```

部署后如果启用该功能，需要先执行数据库迁移，再在后台确认提成比例配置与结算调度配置。


## 推送到 GitHub

cd /Users/linke/hui/icon-market

# 已经 build/test 过可跳过；不放心就再跑一遍
npm run build

# 添加所有改动，但排除本地日志文件
git add -A
git restore --staged "public/日志.txt"
git restore "public/日志.txt"

git commit -m "update navigation and product card sizing"
git push origin main

## 服务器端更新：
cd /www/wwwroot/your-domain.com

# 拉取修复代码
git pull origin main

# 推荐：走统一部署脚本（默认执行 composer + migrate + seed + optimize）
PHP_BIN=/usr/bin/php8.3 APP_DIR=/www/wwwroot/your-domain.com bash scripts/deploy.sh

# 首次初始化数据库并校验编码时（可选）
# DB_ROOT_PASSWORD 请替换为服务器 root 密码
DB_INIT_ENABLED=1 DB_ROOT_PASSWORD='你的数据库root密码' PHP_BIN=/usr/bin/php8.3 APP_DIR=/www/wwwroot/your-domain.com bash scripts/deploy.sh

# 如需跳过种子导入（例如后续增量发布）
RUN_SEEDER=0 PHP_BIN=/usr/bin/php8.3 APP_DIR=/www/wwwroot/your-domain.com bash scripts/deploy.sh

# 管理员账号会随 seeder 写入（来自 database/seeders/data/admin_user.json）
# 如需临时覆盖管理员密码，可额外传 ADMIN_SEED_PASSWORD

# 仅手动执行关键步骤（备用）
PHP_BIN=/usr/bin/php8.3 php artisan migrate --force
PHP_BIN=/usr/bin/php8.3 php artisan optimize:clear

# 管理后台
https://xxxxxx.com/admin
# 客服后台
https://xxxxxx.com/stream-chat-agent


mkdir -p /www/wwwroot/zorai.sbs
cd /www/wwwroot/zorai.sbs

# 首次拉取
git clone https://github.com/shenzhen0008/icon.git .

# 配置生产环境文件
cp .env.production.example .env
# 编辑 .env（APP_URL / DB_* / STREAM_CHAT_* / WEB3_WALLETCONNECT_PROJECT_ID）

# 首发部署
PHP_BIN=/usr/bin/php APP_DIR=/www/wwwroot/zorai.sbs WEB_USER=www-data WEB_GROUP=www-data bash scripts/deploy.sh

打开宝塔 软件商店 -> PHP 8.3 -> 设置 -> 禁用函数（disable_functions）
把 putenv 从禁用列表删除。
在宝塔 PHP 8.3 的 disable_functions 里移除 exec

同时检查 proc_open、proc_close 不在禁用列表里
宝塔 PHP 8.3 扩展里启用 fileinfo，然后重启 PHP
安装/启用 mbstring（宝塔里给 PHP 8.3 安装扩展

重启php8.3
/etc/init.d/php-fpm-83 restart
