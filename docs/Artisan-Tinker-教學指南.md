# Laravel Artisan Tinker 完整教學指南

## 簡介

Laravel Artisan Tinker 是一個非常強大的互動式命令列工具，它基於 PsySH（PHP REPL）。Tinker 讓開發者能夠與 Laravel 應用程式進行互動，在終端機中測試程式碼片段、驗證邏輯、探索物件或與資料庫進行交互，無需撰寫完整的測試或執行整個應用程式。

## 基本概念

REPL (Read-Eval-Print Loop，讀取-求值-輸出循環) 是一種程式設計環境，可以：
- Read：讀取程式語言的程式碼並解析
- Eval：評估(eval)程式碼
- Print：回傳並顯示評估後的結果
- Loop：重複上述行為直到使用者離開

Tinker 是 Laravel 定制化的 REPL 環境，針對 Laravel 專案做了優化。

## 使用方法

### 開始使用 Tinker

```bash
# 進入 Tinker 環境
docker exec -it php-learn php artisan tinker

# 在 Tinker 中執行一次性指令後退出
docker exec -it php-learn php artisan tinker --execute="App\Models\User::first()"

# 執行多行程式碼
docker exec -it php-learn php artisan tinker --execute="info(); App\Models\User::count();"
```

### 在 Tinker 中基本操作

```php
# 退出 Tinker
exit
# 或
quit
# 或按下 Ctrl+C

# 列出當前命名空間可用的類別
ls

# 獲取幫助
?          # 顯示幫助
? info     # 顯示 info 指令的幫助
? exit     # 顯示 exit 指令的幫助

# 清除螢幕
clear

# 歷史指令
向上/向下箭頭  # 瀏覽歷史指令
```

## 實際範例演示

### 1. 基本 PHP 操作

```php
# 基本運算
>>> 2 + 3
=> 5

# 字串操作
>>> strtoupper('hello world')
=> "HELLO WORLD"

# 陣列操作
>>> array_map(function($item) { return $item * 2; }, [1, 2, 3])
=> [2, 4, 6]

# 複雜運算
>>> range(1, 10)
=> [
  0 => 1,
  1 => 2,
  2 => 3,
  3 => 4,
  4 => 5,
  5 => 6,
  6 => 7,
  7 => 8,
  8 => 9,
  9 => 10,
]
```

### 2. Eloquent ORM 操作

```php
# 查詢第一筆 User 資料
>>> App\Models\User::first()
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "created_at": "2023-01-01 10:00:00",
  "updated_at": "2023-01-01 10:00:00"
}

# 計算 Users 表總筆數
>>> App\Models\User::count()
=> 15

# 使用 where 查詢
>>> App\Models\User::where('active', true)->get()
=> Illuminate\Database\Eloquent\Collection {
  #items: [
    // ...
  ]
}

# 建立新 User (測試用，記得用 factory)
>>> App\Models\User::factory()->create(['name' => 'Tinker Test'])
{
  "id": 25,
  "name": "Tinker Test",
  "email": "test@example.com",
  "email_verified_at": null,
  "created_at": "2023-12-01 10:00:00",
  "updated_at": "2023-12-01 10:00:00"
}

# 使用 DB Facade
>>> DB::table('users')->count()
=> 15

>>> DB::table('users')->select('name', 'email')->limit(5)->get()
=> [
  {
    "name": "John Doe",
    "email": "john@example.com"
  },
  // ...
]
```

### 3. 檔案與目錄操作

```php
# 檢查檔案是否存在
>>> File::exists(storage_path('app/public'))
=> true

# 讀取檔案
>>> File::size(base_path('.env'))
=> 1245

# 獲取應用程式路徑資訊
>>> app_path()
=> "/var/www/html/app"

>>> public_path()
=> "/var/www/html/public"

# 日期時間操作
>>> now()
=> Illuminate\Support\Carbon {#3421
  date: "2023-12-01 10:30:45.123456",
  timezone: "UTC",
}

>>> now()->addDays(7)
=> Illuminate\Support\Carbon {#3423
  date: "2023-12-08 10:30:45.123456",
  timezone: "UTC",
}

>>> now()->diffInDays(now()->addMonth())
=> 30
```

### 4. 配置與環境資訊

```php
# 獲取應用程式資訊
>>> app()->version()
=> "Laravel v10.x.x"

>>> config('app.name')
=> "Laravel"

# 檢查環境
>>> app()->environment()
=> "local"

# 動態修改配置
>>> config(['app.debug' => true])

# 檢查是否安裝了某些功能
>>> Auth::check()
=> false
```

### 5. 事件與緩存操作

```php
# 清除緩存
>>> Cache::flush()
=> true

# 暫時停用查詢記錄（提高效能）
>>> DB::disableQueryLog()
=> null

# 查詢被記錄的 SQL 陳述
>>> DB::enableQueryLog(); DB::select('SELECT * FROM users LIMIT 1'); DB::getQueryLog()
=> [
  [
    "query" => "SELECT * FROM users LIMIT 1",
    "bindings" => [],
    "time" => 1.23
  ]
]
```

## 高級功能

### 1. 自訂 Helper 函式 (Aliases)

在 `config/app.php` 中可以自訂 Tinker 的別名：

```php
'tinker' => [
    'aliases' => [
        'UserModel' => 'App\Models\User',
        'PostModel' => 'App\Models\Post',
        'OrderService' => 'App\Services\OrderService',
    ],
    'commands' => [
        'FooCommand',
        'BarCommand',
    ],
],
```

然後在 Tinker 中可以這樣使用：

```php
# 使用自訂別名
>>> UserModel::all()
# 等同於 App\Models\User::all()

>>> PostModel::whereActive(true)->get()
# 等同於 App\Models\Post::whereActive(true)->get()
```

### 2. 指令與技巧

```php
# 詳細顯示物件資訊
>>> $user = App\Models\User::first();
>>> $user->toArray()              # 輸出為陣列形式
>>> dd($user)                     # 輸出詳細除錯資訊
>>> dump($user)                   # 簡化版輸出(不中斷程式)
>>> tap($user)->toArray()         # 暫存物件方便操作

# 查詢效能分析
>>> DB::enableQueryLog();
>>> App\Models\User::with('posts')->take(10)->get();
>>> collect(DB::getQueryLog())->pluck('time');
# 顯示執行時間列表

# 產生測試資料
>>> App\Models\User::factory(5)->create()  # 一次建立 5 筆
>>> \App\Models\User::factory()->count(3)->make()  # 在記憶體中建立(不儲存到 DB)
```

### 3. 常用診斷函式

PsySH 提供了一些便利的輔助函式：

```php
# 系統資訊
>>> w()              # 顯示目前的工作目錄
>>> pwd()            # 同樣是顯示目前的工作目錄
>>> whereami()       # 顯示目前的位置與上下文

# 物件分析
>>> s($user)         # 顯示物件/陣列的類型與大小
>>> whoopsie()       # 顯示最近的錯誤

# 程式碼執行時間測量
>>> timer()          # 開始計時
>>> App\Models\User::all()->count()
>>> timer()          # 顯示花費時間
```

## 實用範例情境

### 1. 快速測試商業邏輯

```php
# 測試某個服務類別的函式
>>> $order = App\Models\Order::first();
>>> $calculator = new App\Services\OrderCalculator();
>>> $calculator->calculateTax($order->total)
=> 150.75

# 驗證條件邏輯
>>> $statusMap = ['pending' => '待處理', 'completed' => '已完成', 'cancelled' => '已取消'];
>>> $statusMap['pending']
=> "待處理"
```

### 2. 資料遷移準備工作

```php
# 檢查將要更新的筆數
>>> App\Models\User::whereNull('email_verified_at')->count()
=> 245

# 預覽將要執行的操作
>>> App\Models\User::whereNull('email_verified_at')->limit(5)->get()->each->toArray()
# ...
```

### 3. 快速驗證表單請求驗證規則

```php
# 測試 Request 驗證
>>> $request = App\Http\Requests\UserRequest::createFrom(request()->merge(['email' => 'invalid-email'])->all());
>>> $validator = Validator::make($request->all(), $request->rules());
>>> $validator->fails()
=> true
>>> $validator->errors()
# 顯示驗證錯誤
```

## 常見問題與提示

### 1. 安全性考量

```php
# 避免在生產環境使用 Tinker
# 在 production 環境通常會禁用 (app.tinker.enabled = false)

# 生產環境執行後務必清理資料
# 使用後退出前可以清除變數
>>> unset($sensitiveData);       # 清除變數
```

### 2. 效能優化

```php
# 避免取得大量資料
# 建議使用 chunk 或 limit
>>> App\Models\User::limit(10)->get()    # 好的做法

# 不要使用這種方式
# App\Models\User::all()                  # 可能影響效能
```

### 3. 記錄與分享代碼

```bash
# 可以將常用的 Tinker 指令保存在檔案中
cat << 'EOF' > /tmp/tinker_commands.php
<?php
// 顯示重要的應用程式狀態
info('=== 應用程式狀態檢查 ===');
dump(config('app.env'));
dump(config('database.default'));

// 計算關鍵模型數量
$usersCount = App\Models\User::count();
$orderCount = App\Models\Order::count();

info("Users 數量: $usersCount");
info("Orders 數量: $orderCount");

// 隨機取得一個使用者進行詳細檢查
$randomUser = App\Models\User::inRandomOrder()->first();
dd($randomUser->toArray());
EOF

# 執行預製的腳本
docker exec -it php-learn php artisan tinker --execute-file /tmp/tinker_commands.php
```

## 學習與除錯最佳實踐

1. **逐步測試**：使用 Tinker 一步步測試複雜的邏輯
2. **即時反饋**：利用 Tinker 得到立即的結果反饋
3. **探索 Laravel API**：測試不熟悉的 Laravel 功能
4. **數據查詢測試**：在正式應用前先測試 Eloquent 查詢效率
5. **假資料產生**：快速驗證應用程式的資料處理邏輯

Tinker 是提升 Laravel 開發效率的重要工具，熟練掌握它可以顯著提高程式碼質量和開發速度。