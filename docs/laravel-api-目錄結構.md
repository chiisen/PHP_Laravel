# Laravel API 開發：目錄結構詳解

## 概述

當你在 Laravel 中新增一支 API 時，請求會依序經過多個目錄。每個目錄都有其明確的職責，掌握這些能幫助你建立結構清晰的應用程式。

---

## 各目錄職責解析

### 1. `routes/api.php` — 入口點

**職責：定義 API 路由（URL + HTTP 方法 + 控制器）**

```php
Route::get('/products', [ProductController::class, 'index']);
Route::post('/products', [ProductController::class, 'store']);
```

**比喻：門牌號碼** — 客戶端根據這裡定義的路徑來呼叫你的 API。

---

### 2. `app/Http/Controllers/` — 請求處理

**職責：接收 HTTP 請求、調用商業邏輯、返回 Response**

```php
class ProductController extends Controller
{
    public function index(ProductService $service)  // 依賴注入
    {
        $products = $service->getAllProducts();
        return ProductResource::collection($products);  // 回傳 JSON
    }
}
```

**比喻：接待員** — 負責接收請求、調用 Service、格式化回應。

---

### 3. `app/Http/Requests/` — 資料驗證

**職責：驗證請求資料（格式、必填、規則）**

```php
class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    
    public function rules(): array
    {
        return [
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ];
    }
}
```

**比喻：安檢門** — 確保進來的資料符合預期格式，否則自動回傳 422 錯誤。

---

### 4. `app/Http/Resources/` — 資料格式化

**職責：將模型轉換為 JSON 格式（控制回應結構）**

```php
class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'price' => $this->price,
            'links' => [
                'self' => "/api/products/{$this->id}",
            ]
        ];
    }
}
```

**比喻：化妝師** — 決定回傳給客戶端的 JSON 長什麼樣子，可隱藏敏感欄位。

---

### 5. `app/Models/` — 資料模型

**職責：代表資料庫資料表，提供 Eloquent ORM 操作方法**

```php
class Product extends Model
{
    protected $fillable = ['name', 'price', 'description'];
    
    // 關聯
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
```

**比喻：資料存取層** — 讓你可以用 `Product::find()`、`$product->category` 這樣的物件導向語法操作資料庫。

---

### 6. `app/Services/` — 商業邏輯

**職責：放置核心業務邏輯（Repository 模式）**

```php
class ProductService
{
    public function __construct(private ProductRepository $repo) {}
    
    public function getAllProducts(): Collection
    {
        $products = $this->repo->all();
        // 額外邏輯：快取、權限檢查、資料轉換
        return $products;
    }
}
```

**比喻：業務部門** — 將商業邏輯從 Controller 抽離出來，保持 Controller 簡潔，Service 可被多個 Controller 共用。

---

### 7. `database/seeders/` — 測試資料

**職責：產生假資料（開發/測試用）**

```php
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::factory()->count(50)->create();
    }
}
```

**比喻：試金石** — 讓你有資料可以測試 API，確保上線前功能正常。

---

### 8. `database/migrations/` — 資料表結構

**職責：定義資料庫資料表的欄位與結構**

```php
public function up(): void
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->decimal('price', 10, 2);
        $table->timestamps();
    });
}
```

**比喻：地基** — 決定資料如何存放，是所有資料操作的基礎。

---

## 完整請求流程圖

```
客戶端 (HTTP 請求)
    ↓
routes/api.php        → 路由分發
    ↓
Http/Requests/        → 資料驗證 (擋掉不合格的請求)
    ↓
Http/Controllers/    → 接收請求，調用 Service
    ↓
app/Services/        → 執行商業邏輯
    ↓
app/Models/          → 與資料庫互動 (Eloquent ORM)
    ↓
database/migrations/ → 資料表結構
    ↓
app/Http/Resources/  → 格式化回應 JSON
    ↓
回傳 Response
```

---

## 目錄對照表

| 目錄 | 比喻 | 新增 API 時的必要程度 |
|------|------|----------------------|
| `routes` | 門牌號碼 | ✅ 必要 |
| `Controllers` | 接待員 | ✅ 必要 |
| `Requests` | 安檢門 | ⚠️ 建議（有驗證時） |
| `Resources` | 化妝師 | ⚠️ 建議（有自訂回傳格式時） |
| `Models` | 資料存取 | ✅ 必要（有資料庫操作時） |
| `Services` | 業務部門 | ⚠️ 建議（邏輯複雜時） |
| `Seeders` | 試金石 | 🔸 測試用 |
| `Migrations` | 地基 | ✅ 必要（有資料表時） |

---

## 新增 API 時的開發順序建議

1. **先建立 Migration** — 確定資料表結構
2. **建立 Model** — 對應資料表
3. **建立 Seeder/Factory** — 產生測試資料
4. **建立 Service** — 撰寫商業邏輯
5. **建立 Request** — 定義驗證規則
6. **建立 Resource** — 定義回傳格式
7. **建立 Controller** — 串接所有元件
8. **註冊路由** — 對外開放 API

---

## 新手必知的開發重點

### 1. HTTP 狀態碼

正確使用狀態碼能讓客戶端知道請求的結果：

| 狀態碼 | 意義 | 使用時機 |
|--------|------|----------|
| `200` | OK | 請求成功 |
| `201` | Created | 新建資源成功 |
| `400` | Bad Request | 請求格式錯誤 |
| `401` | Unauthorized | 未認證（未登入） |
| `403` | Forbidden | 無權限（已登入但沒權限） |
| `404` | Not Found | 資源不存在 |
| `422` | Unprocessable Entity | 驗證失敗 |
| `500` | Server Error | 伺服器錯誤 |

```php
return response()->json(['error' => 'Not Found'], 404);
return response()->json(['error' => 'Unauthorized'], 401);
return response()->json(['errors' => $validator->errors()], 422);
```

---

### 2. Mass Assignment 防護

Laravel 預設禁止批量賦值，必須明確指定哪些欄位可以寫入：

```php
class User extends Model
{
    // 允許批量賦值的欄位
    protected $fillable = ['name', 'email', 'password'];
    
    // 禁止批量賦值的欄位（與 $fillable 互斥）
    protected $guarded = ['id', 'is_admin'];
}
```

**否則會拋出 `MassAssignmentException`。**

---

### 3. 環境變數 (.env)

- **永遠不要**將 `.env` 提交到 Git（已加入 `.gitignore`）
- 資料庫密碼、第三方 API Key 都在這裡設定
- 修改後記得清除快取：

```bash
php artisan config:clear
php artisan cache:clear
```

---

### 4. 依賴注入 (Dependency Injection)

Laravel 的服務容器會自動注入依賴，無需手動 `new`：

```php
// ✅ 自動注入
public function store(StoreProductRequest $request, ProductService $service)
{
    return $service->create($request->validated());
}

// ❌ 不好 — 手動 new
public function store(Request $request)
{
    $service = new ProductService();
    return $service->create($request->all());
}
```

---

### 5. Middleware（中介層）

用於過濾請求，常見用途包括：
- 登入檢查
- CORS 跨域
- 速率限制
- 日誌記錄

```php
// 單一路由使用
Route::get('/profile', [UserController::class, 'show'])
    ->middleware('auth:sanctum');

// 路由群組使用
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserController::class, 'show']);
    Route::put('/user', [UserController::class, 'update']);
});
```

---

### 6. 軟刪除 (Soft Deletes)

避免資料真的被刪除，適合需要「回收機制」的功能：

```php
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;
}
```

- 刪除時不會真的刪除資料，而是設定 `deleted_at` 時間戳
- 查詢時自動排除已刪除的資料
- 可用 `withTrashed()` 找回已刪除的資料

---

### 7. API 回傳格式統一

建立一致的回傳格式，讓前端更容易處理：

```php
// 成功回應
return response()->json([
    'success' => true,
    'data'    => $product,
]);

// 失敗回應
return response()->json([
    'success' => false,
    'message' => '錯誤訊息',
], 400);
```

---

### 8. CORS 跨域問題

瀏覽器 AJAX 請求被擋？安裝 CORS 套件：

```bash
composer require fruitcake/laravel-cors
```

然後在 `bootstrap/app.php` 設定。

---

### 9. 不要把商業邏輯寫在 Controller

```php
// ❌ 不好 — Controller 塞滿邏輯，難以測試與維護
public function store(Request $request)
{
    $product = new Product();
    $product->name = $request->name;
    $product->price = $request->price * 1.1; // 漲價邏輯
    $product->save();
    // ...更多邏輯
}

// ✅ 較好 — 交給 Service 處理
public function store(StoreProductRequest $request, ProductService $service)
{
    return $service->createProduct($request->validated());
}
```

**原則：Controller 只負責「接收請求」和「回應」，商業邏輯交给 Service。**

---

### 10. 除錯技巧

```php
// 印出變數並終止程式（最常用）
dd($variable);

// 印出變數但不終止
dump($variable);

// 記錄日誌
Log::info('User logged in', ['user_id' => $user->id]);
Log::error('Failed to process', ['error' => $e->getMessage()]);
```

---

### 11. Artisan 常用指令

```bash
# 快速建立完整 API 結構
php artisan make:model Product -mrcs

# 查看所有路由
php artisan route:list

# 執行遷移
php artisan migrate

# 回滾上一次遷移
php artisan migrate:rollback

# 重置所有遷移並重新執行
php artisan migrate:fresh --seed

# 互動式 PHP 練習
php artisan tinker
```

---

### 12. 時區與本地化設定

在 `.env` 中設定：

```env
APP_TIMEZONE=Asia/Taipei
APP_LOCALE=zh_TW
APP_FALLBACK_LOCALE=en
```

---

### 13. 測試

- **功能測試** (`tests/Feature/`) — 測試 API 端點
- **單元測試** (`tests/Unit/`) — 測試 Service 邏輯

```bash
# 執行所有測試
php artisan test

# 執行特定測試
php artisan test --filter UserAuthTest

# 產生覆蓋率報告
php artisan test --coverage-html coverage-report
```

---

### 14. 常見錯誤排查

| 錯誤訊息 | 解決方式 |
|----------|----------|
| `MassAssignmentException` | 檢查 Model 的 `$fillable` |
| `Target class [xxx] not found` | 執行 `php artisan cache:clear` |
| `Class not found` | 執行 `composer dump-autoload` |
| `Table not found` | 執行 `php artisan migrate` |
| `CORS 錯誤` | 安裝並設定 `fruitcake/laravel-cors` |
