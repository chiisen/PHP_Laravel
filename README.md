# PHP Laravel 學習專案

本專案提供一個基於 Docker 的完整 Laravel 開發環境，包含 Nginx、PHP-FPM (PHP 8.3) 以及預載的 Xdebug 偵錯工具。

---

## 🚀 Quick Start (快速開始)

> **第一次使用？跟著這 4 步驟就能啟動專案！**

### 步驟 1：啟動 Docker 容器

```bash
# 輕量模式（推薦新手）
docker compose up -d

# 完整模式（含 MySQL + Adminer）
docker compose --profile mysql up -d
```

### 步驟 2：設定 APP_KEY（重要！）

```bash
# 複製環境設定檔
docker exec -it php-learn cp .env.example .env

# 生成加密金鑰
docker exec -it php-learn php artisan key:generate
```

### 步驟 3：訪問網站

打開瀏覽器訪問：**[http://localhost:8080](http://localhost:8080)**

### 步驟 4：停止服務

```bash
# 完整關閉（如果啟動時有加 --profile mysql）
docker compose --profile mysql down
```

---

### 📋 常用指令速查

| 功能 | 指令 |
|------|------|
| 進入 Artisan | `docker exec -it php-learn php artisan [command]` |
| 進入 Tinker | `docker exec -it php-learn php artisan tinker` |
| 查看日誌 | `docker compose logs -f php` |
| 重啟服務 | `docker compose restart` |

### ⚠️ 常見問題

**Q: 頁面顯示 500 錯誤？**  
→ 執行步驟 2 設定 APP_KEY（見 [完整診斷流程](#-app_key-設定重要)）

**Q: Network Resource is still in use？**  
→ 使用 `docker compose --profile mysql down` 完整關閉（見 [詳細說明](#-啟動與關閉服務)）

---

## 詳細說明

## 環境
Windows 11 + WSL2 + Docker Desktop

## 專案結構

- `docker-compose.yml`: 環境定義檔
- `docker/php/Dockerfile`: PHP 映像檔定義（含 Composer, Xdebug）
- `nginx/default.conf`: Nginx 設定檔
- `php-conf/xdebug.ini`: Xdebug 設定
- `src/`: Laravel 原始碼目錄
- **MySQL**: 8.0 資料庫
- **Adminer**: 網頁版資料庫管理工具 (Port 8081)

---

## 🏗️ 啟動與關閉服務

### 啟動模式

你可以根據練習需要決定是否啟動 MySQL（以節省電腦資源）：

- **輕量模式 (不帶 MySQL)**：
  ```bash
  docker compose up -d
  ```

- **完整模式 (啟動 MySQL + Adminer)**：
  ```bash
  docker compose --profile mysql up -d
  ```

### 停止服務

```bash
# 如果你有啟動 mysql profile，必須帶上 profile 才能完全關閉
docker compose --profile mysql down
```

> ⚠️ **注意：為什麼只打 `docker compose down` 會殘留 MySQL？**
> 因為 MySQL 目錄被歸類在 `mysql` profile 中。如果你在啟動時使用了 profile，但關閉時沒加，Docker 會認定你「只想關閉預設服務 (PHP/Nginx)」，從而導致 MySQL 容器繼續在背景執行。

### 🔸 常見警告：Network Resource is still in use

執行 `docker compose down` 後若看到：
```
[+] down 1/1
 ! Network php_laravel_default Resource is still in use
```

**原因**：
- 還有容器（通常是 MySQL）在使用該網路
- 通常發生在「啟動時用了 `--profile mysql`，但關閉時沒加」的情況

**檢查是否有殘留容器**：
```bash
# 查看所有執行中的容器
docker ps

# 查看所有容器（含已停止）
docker ps -a --filter "name=php_laravel"
```

**解決方案**：

1. **完整關閉（推薦）**：
   ```bash
   # 一定要加上 --profile mysql
   docker compose --profile mysql down
   ```

2. **強制移除懸掛網路**（如警告持續出現）：
   ```bash
   docker network rm php_laravel_default
   ```

**最佳實踐**：
```bash
# 啟動完整環境（含 MySQL）
docker compose --profile mysql up -d

# 關閉完整環境（必須加 profile）
docker compose --profile mysql down
```

> 💡 **記住**：**啟動時用了什麼 profile，關閉時就要加上相同的 profile**，這樣才能確保所有資源都被正確清理。

### 使用 Artisan 與 Tinker

```bash
# 進入 Tinker 練習語法
docker exec -it php-learn php artisan tinker

# 執行 Artisan 指令
docker exec -it php-learn php artisan [command]
```

---

## 🔑 APP_KEY 設定 (重要)

### ⚠️ 常見問題：500 Internal Server Error

啟動專案後若遇到頁面顯示 **500 錯誤**，可能是缺少 `APP_KEY`。

### 🔍 診斷步驟

**步驟 1：查看 Laravel 日誌**

```bash
# 查看最後 100 行日誌
docker exec php-learn tail -100 /var/www/html/storage/logs/laravel.log

# 或持續監控日誌
docker exec php-learn tail -f storage/logs/laravel.log
```

**步驟 2：確認錯誤訊息**

若看到以下錯誤：
```
production.ERROR: No application encryption key has been specified.
{"exception":"[object] (Illuminate\\Encryption\\MissingAppKeyException(code: 0): 
No application encryption key has been specified.
```

這表示 `.env` 檔案中的 `APP_KEY` 為空或未設定。

### 🔧 解決方案

**步驟 1：確認 `.env` 檔案存在**

```bash
# 如果 src/.env 不存在，從範例檔複製
docker exec -it php-learn cp .env.example .env
```

**步驟 2：生成 APP_KEY**

```bash
# 自動生成並寫入 .env 檔案
docker exec -it php-learn php artisan key:generate
```

執行後會看到：
```
Application key [base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx] set successfully.
```

**步驟 3：驗證設定**

```bash
# 檢查 .env 中的 APP_KEY 是否已設定
docker exec php-learn grep APP_KEY .env
```

應看到類似：
```
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=
```

**步驟 4：重新整理頁面**

完成後重新整理瀏覽器，網站應可正常運作。

### 📖 為什麼需要 APP_KEY？

| 用途 | 說明 |
|------|------|
| **Session 加密** | 保護使用者工作階段資料 |
| **Cookie 加密** | 加密透過 Cookie 傳輸的敏感資訊 |
| **CSRF 保護** | 生成表單防護令牌 |
| **資料加密** | Laravel 的 `Crypt` facade 需要此金鑰 |

> 💡 **注意**：`APP_KEY` 是專案級密鑰，不應在團隊間共享。每個開發環境應有自己的 key。生產環境務必使用獨一無二的 key。

---

## 🗄️ 資料庫管理與切換

本環境支援 **MySQL** 與 **SQLite** 兩種模式，你可以隨時切換。

### 1. 切換資料庫 (在 `src/.env` 修改)

要切換資料庫，請編輯 `src/.env` 檔案中的 `DB_CONNECTION` 區段：

#### 🔹 模式 A: MySQL (建議，模擬公司環境)
```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret
```

#### 🔹 模式 B: SQLite (輕量，不需啟動 MySQL 容器)
```env
DB_CONNECTION=sqlite
# 下面四行在 SQLite 模式下可省略或註解掉
# DB_HOST=db
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=laravel
# DB_PASSWORD=secret
```

---

### 2. 重置資料庫與重建資料 (重要)

當你修改了 `.env` 設定、或想要清空所有資料並重新產生測試資料時，請執行：

```bash
# 此指令會刪除所有資料表 -> 重新建立 -> 跑初始 Seed (含 10 筆測試 User)
docker exec -it php-learn php artisan migrate:fresh --seed
```

---

## 🛠️ Adminer (資料庫管理工具)

不需安裝額外軟體，直接透過瀏覽器管理資料庫：
- **存取網址**：[http://localhost:8081](http://localhost:8081)
- **登入資訊**：
  - 系　統：`MySQL`
  - 伺服器：`db`
  - 使用者：`laravel`
  - 密　碼：`secret`
  - 資料庫：`laravel`

---

## 🏗️ Laravel 資料庫開發流程 (Workflow)

在 Laravel 中，我們不建議直接在 MySQL 裡寫 SQL 建立資料表，而是使用 **Migration (遷移)** 機制。

### 步驟 1：建立遷移檔
在終端機執行，這會產生一個新的檔案在 `src/database/migrations/` 下：
```bash
docker exec -it php-learn php artisan make:migration create_posts_table
```

### 步驟 2：定義欄位
打開剛產生的檔案，在 `up()` 方法中定義欄位：
```php
public function up(): void {
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->string('title'); // 建立一個字串欄位
        $table->text('content');  // 建立一個長文字欄位
        $table->timestamps();
    });
}
```

### 步驟 3：執行遷移
將定義好的內容同步到 MySQL 中：
```bash
docker exec -it php-learn php artisan migrate
```

### 步驟 4：建立模型 (Model)
為了能用 PHP 操作這個表，建議建立對應的 Model：
```bash
docker exec -it php-learn php artisan make:model Post
```
之後你就可以在程式碼中使用 `Post::all()` 來讀取資料了。

### 步驟 5：建立種子資料 (Seeder)
當資料表建好後，為了方便測試，我們會使用 Seeder 來產生假資料。

- **用途**：自動填充測試資料、建立預設的管理員帳號或系統設定值。
- **主要的檔案**：`src/database/seeders/DatabaseSeeder.php` (這是所有 Seeder 的入口點)。
- **操作流程**：
  1. **建立 Seeder**：`php artisan make:seeder PostSeeder` (選用，直接寫在 DatabaseSeeder 也可以)。
  2. **執行 Seed**：
     ```bash
     # 執行所有在 DatabaseSeeder 定義的種子
     docker exec -it php-learn php artisan db:seed
     
     # 或者在 migrate 時順便執行 (最常用)
     docker exec -it php-learn php artisan migrate:fresh --seed
     ```
- **結合 Factory**：通常我們會搭配 Factory 來產生 10 筆或更多資料，例如：
  `User::factory(10)->create();`

---

## 🚀 偵錯方法 (Xdebug 詳解)

本環境已經針對 Laravel 優化了偵錯設定，支援中斷點 (Breakpoint) 與變數監看。

### 1. 啟動監聽 (VS Code)
- 切換到 VS Code 的「執行與偵錯」(Ctrl+Shift+D)。
- 下拉選單選擇 **"Listen for Xdebug (Docker)"**。
- 按下綠色播放鍵（或 `F5`），底部狀態列變為橘色/藍色即代表監聽中。
- .vscode/launch.json
```json
{
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Listen for Xdebug (Docker)",
            "type": "php",
            "request": "launch",
            "port": 9003,
            "pathMappings": {
                "/var/www/html": "${workspaceFolder}/src"
            },
            "log": false
        }
    ]
}
```

### 2. 設定進入點 (以 Route 為例)
- 打開 `src/routes/web.php`。
- 在第 6 行 `return view('welcome');` 左側點擊一下，出現 **紅點**。

### 3. 觸發偵錯
- 打開瀏覽器存取 [http://localhost:8080](http://localhost:8080)。
- 瀏覽器會進入加載狀態，此時 VS Code 會自動跳出並黃色高亮該行，你可以在左側看到當前的全域變數與物件狀態。

### 📌 常見 Q&A
- **斷不住？** 請檢查 `docker-compose.yml` 中的 `extra_hosts` 是否有 `host.docker.internal:host-gateway` (WSL2 必要)。
- **路徑不正確？** 偵錯設定已鎖定 `pathMappings` 為 `/var/www/html` 映射到本地的 `${workspaceFolder}/src`。

---

## 🤖 GitHub Actions 學習歷程

本專案包含一系列 GitHub Actions 練習，用於學習自動化工作流 (CI/CD)。

### 練習 1：Hello World (基礎語法與觸發)
*   **檔案路徑**：`.github/workflows/hello-world.yml`
*   **學習重點**：
    *   `on: [push]`：設定觸發條件。
    *   `jobs` 與 `steps`：定義工作流結構。
    *   `run`：執行 Shell 指令。
    *   `${{ github.actor }}`：使用 GitHub Actions 內建變數。
*   **驗證方式**：推送代碼至 GitHub 後，在 Repo 的 **Actions** 標籤頁查看執行結果。

### 練習 2：Laravel Pint (自動化代碼排版檢查)
*   **檔案路徑**：`.github/workflows/laravel-pint.yml`
*   **學習重點**：
    *   `uses: actions/checkout@v4`：學習如何引用現成的 Action 插件。
    *   `shivammathur/setup-php@v2`：設定特定的 PHP 版本。
    *   `on: workflow_dispatch`：改為手動觸發，避免每次 Push 都執行檢查（雖然這在正式專案中通常是自動的）。
    *   **CI 概念**：讓機器幫你檢查代碼規範，不符規範的工作流會變為紅色（失敗）。
*   **驗證方式**：推送後查看 Actions，試著故意寫出排版醜陋的 PHP 代碼（如亂點空格），看看 Action 是否會報錯。

### 練習 3：Laravel Tests (自動化測試與 Service 容器)
*   **檔案路徑**：`.github/workflows/laravel-tests.yml`
*   **學習重點**：
    *   `services`：學習如何在 Action 中啟動 MySQL 虛擬容器。
    *   `health-cmd`：確保資料庫啟動完成後才開始執行步驟。
    *   `env` (環境變數)：覆蓋 Laravel 的 `.env` 設定，讓它連結到 Action 的虛擬資料庫。
    *   **CI 流程整合**：Checkout -> Setup PHP -> Install -> Migrate -> Test。
*   **驗證方式**：手動執行 Action，觀察它是否成功啟動 MySQL、跑完 Migration 並通過測試。

### 🚀 專業級整合：CI + Telegram 通知
*   **檔案路徑**：`.github/workflows/main-ci.yml`
*   **學習重點**：
    *   **工作流整合**：將 Lint 與 Test 串聯，確保「先正確格式化，再通過測試」。
    *   `on: workflow_dispatch`：改為手動觸發，方便學習階段自主控制執行時機。
    *   **條件執行 (`if`)**：使用 `if: success()` 或 `if: failure()` 根據結果執行不同步驟。
    *   **Secrets 管理**：學習透過 GitHub Secrets 保護私鑰（如 Telegram Bot Token）。
    *   **第三方 Action**：使用 `appleboy/telegram-action` 快速達成通知功能。
*   **驗證方式**：設定好 GitHub Secrets 後，推送代碼。成功或失敗時，你的手機 Telegram 應該會立即收到推播訊息！

#### 🔑 如何設定 GitHub Secrets (Telegram 通知)

為了讓 GitHub Actions 能安全地發送訊息到你的 Telegram 而不外洩金鑰，請按照以下步驟設定：

1.  **取得必要資訊**：
    *   **Bot Token**：在 Telegram 搜尋 `@BotFather`，建立新機器人 (`/newbot`) 後取得 `HTTP API token`。
    *   **Chat ID**：在 Telegram 搜尋 `@userinfobot` 並傳送訊息，取得回傳的 `Id` 數字。
2.  **進入 GitHub 設定**：
    *   打開你的 GitHub 儲存庫 (Repository) 網頁。
    *   點擊上方導覽列最右邊的 **Settings**。
3.  **新增 Secret**：
    *   在左側選單找到 **Security** 區段，點擊 **Secrets and variables** > **Actions**。
    *   點擊右上角的綠色按鈕 **New repository secret**。
4.  **填入金鑰**：
    *   **第一個**：Name 輸入 `TELEGRAM_TO`，Value 輸入你的 Chat ID。點擊 **Add secret**。
    *   **第二個**：再次點擊 **New repository secret**，Name 輸入 `TELEGRAM_TOKEN`，Value 輸入你的 Bot Token。點擊 **Add secret**。
5.  **完成**：現在 `main-ci.yml` 就能讀取到這些加密資訊並發送通知了。

---

## 學習建議

- **路由練習**：修改 `src/routes/web.php` 練習定義 API 與網頁。
- **語法練習**：頻繁使用 `php artisan tinker` 驗證小段程式碼。
- **資料庫**：目前已安裝 `pdo_mysql` 擴充，如需資料庫容器可進一步擴充此環境。

---

## 🌐 API 練習範例 (含認證機制)

本專案展示了從基礎資料讀取到具備安全性（Token 認證）的完整 API 流程。

### 1. 基礎 User 列表 (無須認證)
- **URL**: [http://localhost:8080/api/users](http://localhost:8080/api/users)
- **功能**: 展示如何從資料庫讀取所有使用者資料。
- **檔案**:
  - **控制器**: `src/app/Http/Controllers/Api/UserController.php`
  - **路由**: `src/routes/api.php` (`Route::get('/users', ...)`)

### 2. 進階：API 認證流程 (Sanctum)
為了實作安全性驗證，本專案新增了基於 **Laravel Sanctum** 的登入機制。

#### 🔹 實作流程與詳細說明：
1. **模型升級 (`User.php`)**：
   在 `App\Models\User` 中引入 `Laravel\Sanctum\HasApiTokens` 擴充。這讓 `User` 物件具備 `createToken()` 方法來產出 API 金鑰。
2. **新增控制器 (`AuthController.php`)**：
   - **資料驗證 (Validation)**：在處理請求前，使用 Laravel 的 `validate()` 機制優化資料品質。
     - **Email 格式檢查**：採用 `email:rfc,dns` 規則。
       - `rfc`：確保符合官方 RFC 規範。
       - `dns`：檢查該網域是否真的存在 A 或 MX 紀錄，能有效防止虛假信箱。
     - **註冊保護**：加入 `unique:users` 與 `confirmed` (確認密碼) 規則。
   - **`register`**: 接收並驗證使用者資料，建立新帳號並產放 Token。
   - **`login`**: 接收 `email`, `password` 與 `device_name`。驗證成功後會發放一個 `plainTextToken`。
   - **`logout`**: 透過 `auth:sanctum` 中間層識別使用者，並移除當前導用的 Token。
3. **路由保護 (`api.php`)**：
   - 使用 `Route::middleware('auth:sanctum')` 群組保護需要登入才能存取的路徑。

#### 🔹 網頁版測試介面：
除了使用 REST Client，本專案也提供了一個現代化的網頁介面供您直接測試：
- **URL**: [http://localhost:8080/login](http://localhost:8080/login)
- **功能**: 直接在瀏覽器進行註冊、登入、查看 Token 與登出演算。

#### 🔹 前端實作流程與原理說明：
認證頁面採用了 **前後端分離 (Decoupled Architecture)** 的設計思維，其核心運作邏輯如下：

1.  **非同步請求 (AJAX/Fetch API)**：
    - 前端不透過傳統的 HTML Form 表單直接跳轉，而是使用 JavaScript 的 `fetch()` 函式發送非同步請求。
    - **優點**：使用者不需要重新整理頁面即可獲得回饋（例如顯示錯誤訊息或動態顯示 Token）。
2.  **狀態儲存 (localStorage)**：
    - 當後端 API 驗證成功並回傳 `token` 之後，前端會使用 `localStorage.setItem('sanctum_token', data.token)` 將金鑰永久存儲在瀏覽器中。
    - 這樣即使關閉網頁，下次開啟時 JavaScript 也能自動讀取 Token 進行自動登入。
3.  **認證標頭 (Authorization Header)**：
    - 當前端需要存取受保護的 API（如 `/api/user`）時，會在請求的標頭（Header）中加入：
      `Authorization: Bearer <儲存的 Token>`
    - 後端的 `auth:sanctum` 中間層會解析此標頭，從資料庫中對比是否存在有效的 Token，進而識別使用者身份。
4.  **安全登出**：
    - 登出時，前端會同時執行兩件事：
        1.  發送請求給後端 `/api/logout`，讓資料庫中的該組 Token 失效。
        2.  使用 `localStorage.removeItem()` 清除瀏覽器的本地存儲，確保本地不再持有過期的金鑰。

#### 🔹 測試指引：
使用 **VS Code REST Client** 開啟 `local.http` 進行以下測試：

| 功能 | 請求方法 | 路徑 | 說明 |
| :--- | :--- | :--- | :--- |
| **登入** | `POST` | `/api/login` | 需帶 JSON: `email`, `password`, `device_name` |
| **獲取個資**| `GET` | `/api/user` | **需帶 Header**: `Authorization: Bearer <TOKEN>` |
| **登出** | `POST` | `/api/logout` | **需帶 Header**: `Authorization: Bearer <TOKEN>` |

> 💡 **開發小撇步**：
> 當你執行 `/api/login` 拿到 Token 後，請將其複製並填入 `local.http` 的變數中，即可快速測試受保護的路由。

---

### 3. 如何增加測試資料
如果你想增加更多隨機使用者資料，可以執行：
```bash
docker exec -it php-learn php artisan tinker --execute="App\Models\User::factory()->count(5)->create()"
```

## 🪵 日誌查詢與管理 (Log Management)

當程式發生錯誤（如 500 Error）時，查詢日誌是排查問題最快的方法。

### 1. 即時監控系統日誌 (Docker Logs)
這可以看到 Nginx、PHP 啟動錯誤或致命錯誤（Fatal Error）：
```bash
# 即時監控所有服務的日誌
docker compose logs -f

# 僅監控 PHP 容器的日誌 (最常用)
docker compose logs -f php
```

### 2. 查看 Laravel 應用程式日誌
Laravel 會將應用程式內部的錯誤（如資料庫連線失敗、程式邏輯報錯）紀錄在 `laravel.log`。

- **檔案位置**：
    - **主機 (Host)**: `src/storage/logs/laravel.log`
    - **容器內 (Inside)**: `/var/www/html/storage/logs/laravel.log`
- **查詢指令**：
    ```bash
    # 使用 tail 指令查看最後 100 行並持續監控
    docker exec php-learn tail -f storage/logs/laravel.log
    ```

### 3. 日誌權限疑難排解
若遇到 `The stream or file "...laravel.log" could not be opened: failed to open stream: Permission denied`：
請在終端機執行：
```bash
docker exec php-learn chmod -R 777 storage bootstrap/cache
```

---

## 常用指令備忘錄

- 重啟服務：`docker compose restart`
- 查看日誌：`docker compose logs -f`
- 重新建立環境：`docker compose up -d --build --force-recreate`

## ✅ 單元測試與功能測試

本專案使用 PHPUnit 進行測試。測試分為 **Feature (功能測試)** 與 **Unit (單元測試)**。

### 1. 測試環境配置
為了保證測試的速度與獨立性，本專案已在 `src/phpunit.xml` 中進行了以下配置：
- **資料庫**：使用 `sqlite` 的 `:memory:` 模式。這意味著測試會在記憶體中執行，速度極快且不會影響你實際的開發資料庫。
- **環境變數**：`APP_ENV` 被設為 `testing`。

### 2. 重要測試工具 (Traits)
- **`RefreshDatabase`**：在測試類別中使用此 Trait，Laravel 會在每個測試案例執行前自動跑 Migration，執行後自動回滾，確保測試環境始終純淨。

### 3. 實戰案例：API 認證測試 (`UserAuthTest.php`)
我們針對 `/api/login` 實作了完整的測試，檔案位於 `src/tests/Feature/UserAuthTest.php`。

**涵蓋的情境：**
- **登入成功**：檢查 Token 是否正確回傳。
- **密碼錯誤**：檢查是否回傳 422 驗證錯誤。
- **格式錯誤**：檢查不合法的 Email 格式。
- **欄位缺失**：檢查漏填資料時的反應。

### 4. 執行測試指令
在 **Docker 環境外** (Windows Terminal) 執行：
```bash
# 執行所有測試
docker exec php-learn ./vendor/bin/phpunit

# 執行特定測試類別 (最常用)
docker exec php-learn ./vendor/bin/phpunit --filter UserAuthTest
```

### 📊 程式碼覆蓋率 (Code Coverage)
本專案支援產生視覺化的程式碼覆蓋率報告，幫助你了解測試涵蓋了哪些程式碼路徑。

#### 1. 前置設定 (已預設完成)
覆蓋率分析依賴於 **Xdebug** 的 `coverage` 模式。
需確認 `php-conf/xdebug.ini` 中的 `xdebug.mode` 包含 `coverage`：
```ini
xdebug.mode=debug,coverage
```
*註：修改此設定後需執行 `docker compose restart php` 才會生效。*

#### 2. 產生報告指令
執行以下指令後，系統會分析所有測試並產出精美的網頁版報告：
```bash
# 產生 HTML 格式的覆蓋率報告
docker exec php-learn ./vendor/bin/phpunit --coverage-html coverage-report
```

#### 3. 查看報告
產生的報告位於 `src/coverage-report/` 目錄中。
- 請直接在瀏覽器打開以下檔案：`src/coverage-report/index.html`。
- 你可以逐行點擊查看哪些程式碼被執行過（綠色），哪些漏掉了（紅色）。

#### 4. 注意事項
- **不提交 Git**：根據最佳實踐，`/coverage-report` 已被加入 `.gitignore`，請勿將報告檔案提交至 Git 儲存庫。
- **效能影響**：開啟 `coverage` 模式會使測試執行速度稍微變慢，建議僅在需要分析覆蓋率時使用。

---

### ⚠️ 測試注意事項與常見坑位
- **外部依賴問題 (DNS Check)**：
  在 API 驗證中若使用了 `email:rfc,dns` 規則，在測試環境中可能會因為網路延遲或無法訪問外部 DNS 導致測試失敗或極慢。
  - **解決建議**：在測試環境中，可考慮暫時將規則降級為 `email:rfc`。
- **JSON 斷言**：
  建議使用 `$this->postJson()` 代替 `$this->post()`，這會自動設定 Header，讓 Laravel 以 API 的方式處理請求與錯誤回傳。
- **資料庫 Seed**：
  如果測試需要特定的預設資料（如權限表），可以在測試的 `setUp()` 方法中呼叫 `$this->seed()`。

---

### 原有的測試基礎說明
(以下為基礎建立指令參考)
1. 單元測試 (Unit Tests)
- 位置: src/tests/Unit
- 用途: 測試單一函式或類別的邏輯，不依賴資料庫或 HTTP 請求。適合測試純邏輯運算。
- 建立指令:
```bash
docker exec php-learn php artisan make:test UserTest --unit
```
2. 功能測試 (Feature Tests)
- 位置: src/tests/Feature
- 用途: 測試完整的功能流程，例如 API 請求、資料庫存取、頁面渲染等。這是最常用的測試類型。
- 建立指令:
```bash
docker exec php-learn php artisan make:test UserAuthTest
```
4. 實戰範例
假設我們要測試一個簡單的 API 端點。您可以建立一個新的測試檔案：

1. 建立測試檔案: 在終端機執行：
```bash
docker-compose exec php php artisan make:test HealthCheckTest
```
編寫測試內容: 編輯新產生的 src/tests/Feature/HealthCheckTest.php：
```php
<?php
namespace Tests\Feature;
// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class HealthCheckTest extends TestCase
{
    /**
     * 測試首頁是否能正常存取
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }
}
```
3. 執行該特定測試:
```bash
docker-compose exec php php artisan test --filter HealthCheckTest
```
4. 常見問題與技巧
- 測試資料庫: 執行測試時，Laravel 通常會重置資料庫。請確保您的 phpunit.xml 或是 .env.testing 有正確配置資料庫連線，通常建議使用 SQLite 記憶體資料庫來加速測試（在 phpunit.xml 中將 DB_CONNECTION 設為 sqlite 並使用 :memory:）。
- 覆蓋率報告: 如果您想看測試覆蓋率（需 Xdebug 支援），可以加 --coverage 參數：  
Xdebug 的設定檔中要開啟 coverage 模式。  
php-conf/xdebug.ini 要啟用它。
1. 重啟 PHP 容器： 設定檔修改後，必須重啟容器才會重新載入 PHP 設定。
```bash
docker-compose restart php
```
2. 再次執行測試覆蓋率指令：
```bash
docker-compose exec php php artisan test --coverage
```

## 本地同步 GitHub
[git同步備份branch](docs/git同步備份branch.md)

## 📚 Laravel 學習筆記
[API 開發目錄結構詳解](docs/laravel-api-目錄結構.md) — 說明新增 API 時各目錄（Routes、Controllers、Requests、Resources、Models、Services、Seeders、Migrations）的職責與請求流程，並包含新手必知的開發重點（HTTP 狀態碼、Mass Assignment、Middleware、軟刪除、除錯技巧等）。

## 🔧 Artisan Tinker 工具指南
[Artisan Tinker 完整教學](docs/Artisan-Tinker-教學指南.md) — 詳細介紹 Laravel 互動式命令列工具的使用方法、實用範例與進階功能，包含 Eloquent 查詢、診斷技巧和其他實用的開發除錯方式。

[Artisan Tinker 進階資料庫管理教學](docs/Artisan-Tinker-進階資料庫管理教學指南.md) — 深入探討如何使用 Tinker 進行資料庫管理，包含外鍵限制處理、批量資料清空、資料表重置和安全操作等高級技巧，適合需要進行複雜資料庫維護的開發者。

## 🖥️ Inertia 頁面開發指南
[Inertia 頁面開發完整教學](docs/Inertia-頁面開發教學指南.md) — 詳細介紹 Laravel 與前端框架(Vue/React)整合的 Inertia 開發模式，包含安裝設定、實作範例、資料共享、表單處理和效能優化等完整指南。

