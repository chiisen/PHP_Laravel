# Laravel Breeze 帳號登入系統實作指南

> 最後更新：2026-03-24  
> Laravel 版本：12.x

---

## 目錄

- [概述](#概述)
- [方案比較](#方案比較)
- [安裝 Breeze](#安裝-breeze)
- [架構解析](#架構解析)
- [核心功能說明](#核心功能說明)
- [安全機制](#安全機制)
- [使用方式](#使用方式)
- [路由總覽](#路由總覽)
- [自訂與擴充](#自訂與擴充)

---

## 概述

Laravel Breeze 是 Laravel 官方提供的輕量級認證系統，提供完整的登入、註冊、密碼重置、Email 驗證等功能。適合快速建立標準認證系統，同時保持程式碼清晰易於客製化。

### 特色

- ✅ Laravel 官方維護，最穩健
- ✅ 包含完整登入/註冊/密碼重置/Email 驗證
- ✅ 內建 Blade、React、Vue 模板選擇
- ✅ 已整合 Sanctum（API 認證）
- ✅ 包含測試範例
- ✅ 程式碼清晰，易於學習與客製化

---

## 方案比較

在 Laravel 專案中實作帳號登入功能，主要有以下三種方案：

### 方案一：Laravel Breeze（⭐ 強烈推薦）

**適合場景**：快速建立標準登入/註冊功能，包含完整 UI

```bash
composer require laravel/breeze --dev
php artisan breeze:install
```

| 優點 | 缺點 |
|------|------|
| ✅ Laravel 官方維護，最穩健 | ⚠️ 會覆蓋現有視圖文件 |
| ✅ 包含完整登入/註冊/密碼重置/Email 驗證 | |
| ✅ 內建 Blade 或 React/Vue 模板 | |
| ✅ 已整合 Sanctum | |
| ✅ 包含測試範例 | |

---

### 方案二：手動實作

**適合場景**：需要完全控制登入流程或學習 Auth 原理

```bash
# 可選：安裝基礎套件
composer require laravel/ui
php artisan ui:bootstrap  # 或 vue/react
```

| 優點 | 缺點 |
|------|------|
| ✅ 完全客製化 | ⚠️ 需自行處理安全性（CSRF、密碼雜湊等） |
| ✅ 無多餘程式碼 | ⚠️ 開發時間較長 |

---

### 方案三：Laravel Jetstream

**適合場景**：需要進階功能（雙因素驗證、團隊管理等）

```bash
composer require laravel/jetstream
php artisan jetstream:install livewire  # 或 inertia
```

| 優點 | 缺點 |
|------|------|
| ✅ 包含 Breeze 所有功能 | ⚠️ 較重量級 |
| ✅ 雙因素認證 (2FA) | ⚠️ 學習曲線較陡 |
| ✅ 團隊管理 | |
| ✅ Profile 管理頁面 | |

---

### 選擇建議

| 需求 | 推薦方案 |
|------|----------|
| 快速上線、標準功能 | **Breeze** |
| 學習 Auth 原理 | 手動實作 |
| 企業級功能（2FA、團隊） | Jetstream |

---

## 安裝 Breeze

### 步驟 1：安裝套件

```bash
cd /Users/liao-eli/github/PHP_Laravel/src
composer require laravel/breeze --dev
```

### 步驟 2：安裝 Breeze 模板

選擇模板類型：

```bash
# Blade 版本（推薦，最簡單）
php artisan breeze:install blade

# 或 React 版本
php artisan breeze:install react

# 或 Vue 版本
php artisan breeze:install vue
```

### 步驟 3：安裝前端依賴並編譯

```bash
npm install
npm run build
```

### 步驟 4：執行資料庫遷移

```bash
php artisan migrate
```

這會建立以下資料表：
- `users` - 使用者資料
- `password_reset_tokens` - 密碼重置 Token
- `sessions` - Session 資料
- `personal_access_tokens` - Sanctum API Token（如已安裝）

---

## 架構解析

### 產生的檔案結構

```
src/
├── app/
│   ├── Http/
│   │   ├── Controllers/Auth/
│   │   │   ├── AuthenticatedSessionController.php    # 登入/登出
│   │   │   ├── ConfirmablePasswordController.php     # 密碼確認
│   │   │   ├── EmailVerificationNotificationController.php
│   │   │   ├── EmailVerificationPromptController.php
│   │   │   ├── NewPasswordController.php             # 重置密碼
│   │   │   ├── PasswordController.php                # 更新密碼
│   │   │   ├── PasswordResetLinkController.php       # 發送重置連結
│   │   │   ├── RegisteredUserController.php          # 註冊
│   │   │   └── VerifyEmailController.php             # 驗證 Email
│   │   └── Requests/Auth/
│   │       └── LoginRequest.php                      # 登入表單驗證（含速率限制）
│   └── Models/
│       └── User.php                                  # 使用者模型（已整合）
│
├── resources/views/
│   ├── auth/
│   │   ├── login.blade.php                           # 登入頁面
│   │   ├── register.blade.php                        # 註冊頁面
│   │   ├── forgot-password.blade.php
│   │   ├── reset-password.blade.php
│   │   └── verify-email.blade.php
│   ├── components/                                   # UI 元件
│   │   ├── application-logo.blade.php
│   │   ├── auth-session-status.blade.php
│   │   ├── danger-button.blade.php
│   │   ├── dropdown-link.blade.php
│   │   ├── dropdown.blade.php
│   │   ├── input-error.blade.php
│   │   ├── input-label.blade.php
│   │   ├── modal.blade.php
│   │   ├── nav-link.blade.php
│   │   ├── primary-button.blade.php
│   │   ├── responsive-nav-link.blade.php
│   │   ├── secondary-button.blade.php
│   │   └── text-input.blade.php
│   ├── layouts/
│   │   ├── app.blade.php                             # 已登入使用者佈局
│   │   └── guest.blade.php                           # 訪客佈局
│   ├── profile/
│   │   ├── edit.blade.php
│   │   └── partials/
│   │       ├── delete-user-form.blade.php
│   │       ├── update-password-form.blade.php
│   │       └── update-profile-information-form.blade.php
│   └── dashboard.blade.php                           # 控制台
│
└── routes/
    └── auth.php                                      # 認證路由
```

---

## 核心功能說明

### 1️⃣ 登入流程

**控制器**：`AuthenticatedSessionController.php`

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * 顯示登入表單
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * 處理登入請求
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // 驗證並嘗試登入
        $request->authenticate();

        // 重新生成 Session ID（防止 Session 固定攻擊）
        $request->session()->regenerate();

        // 導向至控制台
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * 處理登出請求
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        // 使 Session 無效
        $request->session()->invalidate();

        // 重新生成 CSRF Token
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
```

**流程圖**：

```
GET  /login  → create() → view('auth.login')
                    ↓
POST /login  → store()
                ├─ LoginRequest::authenticate()  # 驗證帳號密碼
                ├─ 速率限制檢查（5 次/分鐘）
                ├─ Auth::attempt()               # 登入
                ├─ Session::regenerate()         # 防止 Session 固定攻擊
                └─ redirect('dashboard')
                    ↓
POST /logout → destroy()
                ├─ Auth::logout()
                ├─ Session::invalidate()
                └─ redirect('/')
```

---

### 2️⃣ 註冊流程

**控制器**：`RegisteredUserController.php`

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * 顯示註冊表單
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * 處理註冊請求
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // 驗證規則
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 建立使用者
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 觸發註冊事件
        event(new Registered($user));

        // 自動登入
        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
```

**流程圖**：

```
POST /register → store()
                  ↓
                驗證規則：
                  ├─ name: 必填，最大 255 字
                  ├─ email: 必填，唯一
                  └─ password: 必填，需確認，符合複雜度
                  ↓
                Hash::make()                  # 密碼雜湊
                User::create()                # 建立使用者
                event(new Registered())       # 觸發事件
                Auth::login()                 # 自動登入
                redirect('dashboard')
```

---

### 3️⃣ 登入請求驗證（含速率限制）

**Form Request**：`LoginRequest.php`

```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * 確定使用者是否有權限發出此請求
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 獲取適用於請求的驗證規則
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * 嘗試驗證請求的憑證
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * 確保登入請求未受速率限制
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * 獲取請求的速率限制節流密鑰
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
```

**速率限制機制**：
- 允許 5 次失敗嘗試/分鐘
- 超過限制後顯示倒數計時器
- 節流密鑰 = email + IP 地址

---

### 4️⃣ 密碼重置流程

```
忘記密碼：
GET  /forgot-password → PasswordResetLinkController::create() → 顯示表單
POST /forgot-password → PasswordResetLinkController::store()  → 發送重置郵件

重置密碼：
GET  /reset-password/{token} → NewPasswordController::create() → 顯示重置表單
POST /reset-password → NewPasswordController::store() → 更新密碼
```

---

### 5️⃣ Email 驗證流程

```
GET  /verify-email → EmailVerificationPromptController → 驗證提醒頁面
GET  /verify-email/{id}/{hash} → VerifyEmailController → 驗證 Email
POST /email/verification-notification → 重新發送驗證信
```

---

## 安全機制

Breeze 內建多層安全防護：

| 機制 | 說明 | 實作位置 |
|------|------|----------|
| **密碼雜湊** | 使用 Bcrypt 演算法 | `Hash::make()` |
| **速率限制** | 登入失敗 5 次/分鐘 | `LoginRequest::ensureIsNotRateLimited()` |
| **Session 固定攻擊防護** | 登入後重新生成 Session ID | `$request->session()->regenerate()` |
| **CSRF 保護** | 表單自動加入 Token | Blade 中的 `@csrf` |
| **密碼規則** | 最小長度、複雜度要求 | `Rules\Password::defaults()` |
| **Email 驗證** | 可選的 Email 驗證功能 | `VerifyEmailController` |
| **登出後 Token 更新** | 防止登出後 CSRF Token 被濫用 | `$request->session()->regenerateToken()` |

---

## 使用方式

### 執行遷移建立資料庫表

```bash
cd /Users/liao-eli/github/PHP_Laravel/src
php artisan migrate
```

### 啟動開發伺服器

```bash
# 使用 Docker Compose
docker compose up -d

# 或使用 Laravel Sail
./vendor/bin/sail up -d
```

### 訪問頁面

| 功能 | URL |
|------|-----|
| 登入 | http://localhost:8080/login |
| 註冊 | http://localhost:8080/register |
| 忘記密碼 | http://localhost:8080/forgot-password |
| 控制台（需登入） | http://localhost:8080/dashboard |
| 個人資料編輯 | http://localhost:8080/profile/edit |

---

## 路由總覽

### 訪客路由（middleware: guest）

未登入的使用者可訪問：

```php
GET  /register                    → 註冊頁面
POST /register                    → 處理註冊
GET  /login                       → 登入頁面
POST /login                       → 處理登入
GET  /forgot-password             → 忘記密碼頁面
POST /forgot-password             → 發送重置連結
GET  /reset-password/{token}      → 重置密碼頁面
POST /reset-password              → 處理重置
```

### 已登入用戶路由（middleware: auth）

已登入的使用者可訪問：

```php
GET  /verify-email                → Email 驗證提醒
GET  /verify-email/{id}/{hash}    → 執行 Email 驗證
POST /email/verification-notification → 重新發送驗證信
GET  /confirm-password            → 確認密碼（敏感操作前）
POST /confirm-password            → 驗證密碼
PUT  /password                    → 更新密碼
POST /logout                      → 登出
```

### 路由設定檔

所有認證路由定義在 `routes/auth.php`：

```php
<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
```

---

## 自訂與擴充

### 修改登入頁面

編輯 `resources/views/auth/login.blade.php`：

```blade
<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full"
                          type="email" name="email"
                          :value="old('email')" required autofocus
                          autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full"
                          type="password"
                          name="password"
                          required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox"
                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                       name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                   href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
```

### 新增使用者欄位

1. **修改遷移檔案**：

```php
// database/migrations/xxxx_xx_xx_add_xxx_to_users_table.php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('phone')->nullable()->after('email');
        $table->date('birthday')->nullable()->after('phone');
    });
}
```

2. **修改 User Model**：

```php
// app/Models/User.php
protected $fillable = [
    'name',
    'email',
    'password',
    'phone',      // 新增
    'birthday',   // 新增
];
```

3. **修改註冊表單驗證**：

```php
// app/Http/Controllers/Auth/RegisteredUserController.php
$request->validate([
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
    'password' => ['required', 'confirmed', Rules\Password::defaults()],
    'phone' => ['nullable', 'string', 'max:20'],
    'birthday' => ['nullable', 'date'],
]);
```

### 自訂密碼規則

```php
use Illuminate\Validation\Rules\Password;

// 最小長度 8 位
Password::min(8)

// 最小長度 8 位，至少包含字母和數字
Password::min(8)->mixedCaseAndNumbers()

// 最小長度 8 位，包含字母、數字、符號
Password::min(8)
    ->mixedCase()
    ->numbers()
    ->symbols()
    ->uncompromised()  // 檢查是否為洩漏密碼
```

### 修改登入後導向頁面

修改 `AuthenticatedSessionController.php`：

```php
public function store(LoginRequest $request): RedirectResponse
{
    $request->authenticate();
    $request->session()->regenerate();

    // 自訂導向頁面
    return redirect()->intended(route('home', absolute: false));
    // 或根據角色導向
    // return auth()->user()->role === 'admin'
    //     ? redirect('/admin')
    //     : redirect('/dashboard');
}
```

---

## 常見問題

### Q1: 如何關閉 Email 驗證？

Breeze 預設不強制 Email 驗證。如需啟用，在 `app/Models/User.php` 實作 `MustVerifyEmail` 介面：

```php
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    // ...
}
```

如需關閉，確保 User 模型**沒有**實作此介面即可。

---

### Q2: 如何修改 Session 過期時間？

在 `config/session.php` 修改：

```php
'lifetime' => env('SESSION_LIFETIME', 120),  // 分鐘
```

或在 `.env` 中設定：

```env
SESSION_LIFETIME=1440  # 24 小時
```

---

### Q3: 如何實作「記住我」功能？

Breeze 已內建「記住我」功能。在登入表單中加入：

```blade
<input type="checkbox" name="remember" id="remember_me">
<label for="remember_me">Remember me</label>
```

控制器會自動處理：

```php
Auth::attempt($credentials, $request->boolean('remember'));
```

---

### Q4: 如何整合 Socialite（第三方登入）？

1. 安裝 Socialite：

```bash
composer require laravel/socialite
```

2. 設定 `.env`：

```env
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8080/auth/google/callback
```

3. 新增路由：

```php
Route::get('auth/google', [SocialiteController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [SocialiteController::class, 'handleGoogleCallback']);
```

---

## 參考資源

- [Laravel Breeze 官方文件](https://laravel.com/docs/starter-kits#laravel-breeze)
- [Laravel 認證文件](https://laravel.com/docs/authentication)
- [Laravel Sanctum 文件](https://laravel.com/docs/sanctum)
- [Laravel Socialite 文件](https://laravel.com/docs/socialite)

---

## 更新紀錄

| 日期 | 版本 | 說明 |
|------|------|------|
| 2026-03-24 | 1.0.0 | 初始版本，安裝 Laravel Breeze v2.4.1 |
