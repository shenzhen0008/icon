<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## 生产环境初始化

项目内置一键初始化脚本：

```bash
bash scripts/deploy.sh
```

注意：该脚本不会在 `git pull` 后自动触发。每次部署更新后需要手动执行一次。

首次执行前请确认：

1. 复制 `.env.production.example` 为 `.env`（若 `.env` 不存在，脚本也会自动复制）。
2. 在 `.env` 中填写真实生产数据库配置（不要使用占位密码）。
3. Web 站点根目录指向 `public`，并启用 Laravel 伪静态规则。
4. **项目要求 PHP >= 8.3.0**，服务器如有多个PHP版本，请确保使用 `/www/server/php/83/bin/php` 路径。

脚本会自动执行：`composer install --no-dev`、`key:generate`、数据库迁移、`storage/bootstrap` 权限修复，以及 Laravel 缓存重建（`optimize:clear` / `optimize`）。

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

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
