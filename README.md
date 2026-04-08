
## 生产环境初始化

项目内置一键初始化脚本：

```bash
bash scripts/deploy.sh
```

注意：该脚本不会在 `git pull` 后自动触发。每次部署更新后需要手动执行一次。

### PHP 版本说明
`bash scripts/deploy.sh` 已经内部固定使用 `/www/server/php/83/bin/php`，所以直接运行脚本即可使用 PHP 8.3，无需额外指定版本。

首次执行前请确认：

1. 复制 `.env.production.example` 为 `.env`（若 `.env` 不存在，脚本也会自动复制）。
2. 在 `.env` 中填写真实生产数据库配置（不要使用占位密码）。
3. Web 站点根目录指向 `public`，并启用 Laravel 伪静态规则。
4. **项目要求 PHP >= 8.3.0**，服务器如有多个PHP版本，请确保使用 `/www/server/php/83/bin/php` 路径。
5. 由于 Filament / Livewire 运行时需要前端资产，请确保 `public/vendor/livewire` 目录可写，部署脚本会自动发布 Livewire 静态前端资源。

脚本会自动执行：`composer install --no-dev`、`key:generate`、数据库迁移、Livewire 资产发布、`storage/bootstrap` 权限修复，以及 Laravel 缓存重建（`optimize:clear` / `optimize`）。

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
bash scripts/deploy.sh
```

**部署故障排除：**
- 如遇到 "PHP Fatal error: Uncaught RuntimeException: Composer detected issues in your platform: Your Composer dependencies require a PHP version ">= 8.3.0""，请使用 `/www/server/php/83/bin/php` 替代 `php` 命令
- 如遇到 "Your local changes to the following files would be overwritten by merge"，请先备份或清理 `public/build/` 目录下的文件，然后重新执行 `git pull`
- 如果后台打开后出现 `/livewire/livewire.min.js` 404，请检查 Nginx 是否把 `/livewire` 请求转发给 Laravel；也可以直接运行 `/www/server/php/83/bin/php artisan livewire:publish --assets` 以生成 `public/vendor/livewire` 静态前端资源。

### 后台空白页修复
- 如果后台打开后显示空白，并且浏览器控制台报 `/livewire/livewire.min.js` 404，说明 Livewire 前端资源未正确发布或 `/livewire` 路径没有被 Laravel 处理。
- 解决步骤：
  1. 进入服务器目录：`cd /www/wwwroot/bitcon.yunqueapp.com`
  2. 拉取最新代码：`git pull origin main`
  3. 运行部署脚本：`bash scripts/deploy.sh`
  4. 如果需要手动修复，可以执行：`/www/server/php/83/bin/php artisan livewire:publish --assets` 和 `/www/server/php/83/bin/php artisan optimize:clear`
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
cd /www/wwwroot/bitcon.yunqueapp.com

# 拉取修复代码
git pull origin main

# 清理缓存
/www/server/php/83/bin/php artisan config:clear
/www/server/php/83/bin/php artisan view:clear
/www/server/php/83/bin/php artisan route:clear
/www/server/php/83/bin/php artisan cache:clear
