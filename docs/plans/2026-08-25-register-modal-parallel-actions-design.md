# 注册弹窗双按钮并排改造方案

## 目标

只调整现有注册 PIN 面板底部的两个操作：

```text
当前
[ 确认注册 ]
已有账号，去登录

改造后
[ 注册新账户 ] [ 登录账户 ]
```

两个按钮并排、等宽；点击逻辑不变：

- `创建账户`：提交现有 `/register` 表单。
- `登录已有账户`：复用现有 `data-switch-panel="login"`，切换到登录面板。

不新增页面、弹窗、路由、接口或数据库字段。

## 布局与多语言处理

使用双列网格布局，按钮文字允许自动换行并居中；两个按钮保持相同高度。

- 两个按钮的尺寸与排版一致：等宽、相同圆角、内边距、字体粗细和最小高度。
- 注册按钮保留现有主色实心样式，作为主操作。
- 登录入口从下划线文本链接改为同尺寸的描边按钮，作为次操作；不使用主色实心，避免两个提交类按钮难以区分。
- 不强制单行，防止德语、法语等较长文案溢出。
- 同时将两个按钮文案改为短语，降低换行概率。

## 修改文件

| 文件 | 修改内容 |
| --- | --- |
| `resources/views/components/auth/activate-pin-modal.blade.php` | 将现有注册提交按钮和登录切换链接放入双列容器；保留 `data-switch-panel="login"`，无需改动登录切换 JS；添加等宽、居中、可换行的 Tailwind 工具类。 |
| `lang/zh-CN/pages/me.php` | 缩短 `submit`、`login` 文案。 |
| `lang/en/pages/me.php` | 同步英文短文案。 |
| `lang/de/pages/me.php` | 同步德文短文案。 |
| `lang/es/pages/me.php` | 同步西班牙文短文案。 |
| `lang/fr/pages/me.php` | 同步法文短文案。 |
| `lang/ja/pages/me.php` | 同步日文短文案。 |
| `lang/ko/pages/me.php` | 同步韩文短文案。 |
| `lang/pt/pages/me.php` | 同步葡萄牙文短文案。 |
| `tests/Feature/User/MyCenterPageTest.php` | 更新旧文案断言，并确认登录切换属性仍输出。 |
| `tests/Feature/Product/PublicProductCatalogTest.php` | 同步更新访客购买弹窗的文案与登录入口断言。 |

## 多语言文案清单

| 语言 | `submit`（原 → 新） | `login`（原 → 新） |
| --- | --- | --- |
| 简体中文 | 确认注册 → 注册新账户 | 已有账号，去登录 → 登录账户 |
| English | Confirm Registration → Create Account | Already have an account? Log in → Sign In |
| Deutsch | Registrierung bestätigen → Konto erstellen | Bereits ein Konto? Anmelden → Anmelden |
| Español | Confirmar registro → Crear cuenta | ¿Ya tienes una cuenta? Inicia sesión → Iniciar sesión |
| Français | Confirmer l’inscription → Créer un compte | Vous avez déjà un compte ? Connectez-vous → Se connecter |
| 日本語 | 登録を確定 → 新規登録 | すでにアカウントをお持ちですか？ ログイン → ログイン |
| 한국어 | 등록 확인 → 계정 만들기 | 이미 계정이 있으신가요? 로그인 → 로그인 |
| Português | Confirmar registro → Criar conta | Já tem uma conta? Entrar → Entrar |

## 不需要修改

- `routes/web.php`
- `app/Modules/User/Http/Controllers/Auth/RegisteredUserController.php`
- 登录控制器、数据表、迁移、接口

## 验证

实施后执行：

```text
php artisan test
npm run build
```
