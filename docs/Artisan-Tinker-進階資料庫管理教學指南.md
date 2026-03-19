# Artisan Tinker 進階操作教學：資料庫管理與除錯

## 概述

Artisan Tinker 是一個非常強大的工具，不僅可以用於一般 PHP 和 Laravel 功能測試，也是進行資料庫管理和除錯的利器。本文將重點介紹如何在 Tinker 環境中安全地進行資料庫管理操作，特別是外鍵限制處理、資料清空和資料庫維護。

## 如何正確進入 Tinker 環境

```bash
# 推薦做法：使用 Sail 啟動 (如果使用 Laravel Sail)
./vendor/bin/sail artisan tinker

# 或一般環境
php artisan tinker

# 執行一次性指令退出
php artisan tinker --execute="info(); App\Models\User::first();"

# 在 Docker 環境中執行 (您目前的環境)
docker exec -it php-learn php artisan tinker
```

## 資料庫表清空操作（處理外鍵限制）

### 1. 關閉外鍵檢查機制

在有外鍵關聯的資料庫中，有時候需要清空表並重新插入資料。最安全的方式是臨時禁用外鍵檢查：

```php
// 進入 Tinker 後執行第一步
>>> Schema::disableForeignKeyConstraints();
=> true
```

#### 為什麼需要這麼做？
- 在有外鍵約束的關係數據庫中，不能隨意清空父表
- 例如：如果有關係 orders → order_items，必須先清空 order_items 才能清空 orders
- 禁用外鍵檢查可以讓我們更靈活地處理

### 2. 安全清空多個表

假設有複雜的外鍵結構，以下是最佳實踐方法：

```php
// 1. 先禁用外鍵檢查
>>> Schema::disableForeignKeyConstraints();

// 2. 按順序截斷表 (必須從子表開始)
>>> DB::table('menu_permissions')->truncate();
=> 0

>>> DB::table('menus')->truncate();
=> 0

>>> DB::table('sms')->truncate();
=> 0

// 3. 重新啟用外鍵檢查
>>> Schema::enableForeignKeyConstraints();
=> true

// 檢查所有表是否都已清空
>>> DB::table('menus')->count();
=> 0

>>> DB::table('menu_permissions')->count();
=> 0

>>> DB::table('sms')->count();
=> 0
```

### 3. 更安全的表清空腳本

為避免手動逐個執行，可以創建一個安全的清空腳本：

```php
// 建立一個安全的批次清空程式
>>> $tables = ['sms', 'menu_permissions', 'menus'];
>>> foreach($tables as $table) { 
    DB::statement("TRUNCATE TABLE {$table}"); 
}
// 注意：此方法對於有複雜外鍵關係可能仍會出錯，所以最好還是使用 disableForeignKeyConstraints

// 或更具體的安全腳本
>>> $truncateOrder = ['menu_permissions', 'menus', 'sms'];
>>> Schema::disableForeignKeyConstraints();
>>> foreach($truncateOrder as $table) {
    DB::statement("TRUNCATE TABLE `{$table}`");
}
>>> Schema::enableForeignKeyConstraints();
```

## 資料庫管理的高級操作

### 1. 查閱資料庫結構

```php
// 查看所有資料庫表
>>> DB::select('SHOW TABLES');
// 在 MySQL 中顯示結果

// 檢查某張表的結構
>>> DB::connection()->getDoctrineSchemaManager()->listTableColumns('users')
// 顯示 users 表的所有欄位結構

// 檢查表關聯關係
>>> DB::select("SELECT 
    TABLE_NAME,COLUMN_NAME,CONSTRAINT_NAME, REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE REFERENCED_TABLE_SCHEMA = DATABASE()");
// 顯示所有外鍵關係
```

### 2. 資料插入範例

```php
// 手動插入測試資料
>>> DB::table('menus')->insert([
    'name' => 'Dashboard',
    'uri' => '/dashboard',
    'parent_id' => null,
    'sort_order' => 1,
    'created_at' => now(),
    'updated_at' => now()
]);

// 檢查插入結果
>>> DB::table('menus')->where('name', 'Dashboard')->first();
{
  "id": 1,
  "name": "Dashboard",
  "uri": "/dashboard",
  "parent_id": null,
  "sort_order": 1,
  "created_at": "2023-12-01 12:00:00",
  "updated_at": "2023-12-01 12:00:00"
}

// 使用模型插入
>>> App\Models\Menu::create([
    'name' => 'Posts Menu',
    'uri' => '/posts',
    'sort_order' => 2
]);
```

### 3. 資料查詢與修復

```php
// 查詢特定條件資料
>>> App\Models\User::where('email', 'test@example.com')->get();

// 批次更新
>>> App\Models\User::where('status', 'inactive')->update(['status' => 'archived']);

// 刪除不符合條件的資料
>>> App\Models\User::where('created_at', '<', now()->subYear())->delete();

// 複雜的資料查詢與處理
>>> App\Models\User::with('posts')
    ->whereHas('posts', function($q) {
        $q->where('status', 'published');
    })
    ->paginate(10);

// 批次重設序列值 (對於 PostgreSQL 等需要的情況)
>>> DB::select("SELECT setval('menus_id_seq', (SELECT MAX(id) FROM menus));");
// 通常在 MySQL 中會自動設置，但在某些情況下需要用以下方式：
>>> DB::statement("ALTER TABLE menus AUTO_INCREMENT = 1;");
```

## 常見實作場景與腳本

### 1. 資料表重置與資料庫重構

適用於清空並重新種子的情況：

```php
>>> echo "開始資料表重置...";
// 關閉外鍵限制
>>> Schema::disableForeignKeyConstraints();

// 清空指定表
>>> $tables = ['sms', 'menu_permissions', 'menus', 'orders', 'order_items', 'products'];
>>> foreach($tables as $table) { 
    DB::table($table)->truncate(); 
    echo "{$table}: 清空完成\n";
}

// 重新啟用外鍵限制
>>> Schema::enableForeignKeyConstraints();
>>> echo "外鍵限制恢復完成";
```

### 2. 資料驗證與確認

```php
// 驗證資料一致性
>>> $menuWithPermissionCount = DB::table('menus')
    ->join('menu_permissions', 'menus.id', '=', 'menu_permissions.menu_id')
    ->count();
    
>>> $totalCount = DB::table('menus')->count();
    
>>> echo "有權限的選單：{$menuWithPermissionCount} / 總計：{$totalCount}";
    
// 驗證資料完整性
>>> $orphanedPermissions = DB::table('menu_permissions')
    ->leftJoin('menus', 'menu_permissions.menu_id', '=', 'menus.id')
    ->whereNull('menus.id')
    ->select('menu_permissions.*')
    ->get();

>>> if($orphanedPermissions->isEmpty()) {
    echo "✓ 沒有孤立的權限資料";
} else {
    echo "⚠ 檢測到 {$orphanedPermissions->count()} 筆孤立權限資料";
    dd($orphanedPermissions);  // 顯示孤立資料以供處理
}
```

### 3. 效能查詢測試

```php
// 清除先前的查詢記錄
>>> DB::enableQueryLog();
>>> // 執行要測試的查詢
>>> App\Models\User::with('posts')->get()->count();
=> 1500

// 查看實際執行的 SQL
>>> DB::getQueryLog();
// 顯示所有執行的查詢與執行時間
// 用於效能除錯

// 取得查詢時間總和
>>> collect(DB::getQueryLog())->sum('time');

// 清理記錄
>>> DB::flushQueryLog();
```

## 安全操作注意事項

### 1. 生產環境操作禁忌

```php
// ❌ 絕對不要在生產環境執行
>>> DB::table('users')->truncate();  // 資料將全部消失！

// ❌ 也不要在生產環境隨便執行
>>> DB::statement("DELETE FROM orders WHERE customer_id IN (...)");
```

### 2. 開發環境安全措施

```php
// 檢查當前環境
>>> App::environment()
=> "local"  // 應該是 local 或 testing 才能執行敏感操作

// 如果是開發環境，執行某些操作前做確認
>>> if(app()->environment(['local', 'staging'])) {
    echo "環境確認：此為 " . app()->environment() . " 環境，可以執行操作";
} else {
    echo "警告：目前處於生產環境，中止敏感操作！"; 
    return;
}
```

### 3. 還原備份概念

```php
// 在執行大規模操作前，檢查資料狀態
>>> $backupCounts = [
    'menus' => DB::table('menus')->count(),
    'permissions' => DB::table('menu_permissions')->count(),
];

>>> echo "操作前狀態：" . json_encode($backupCounts, JSON_PRETTY_PRINT);
// 執行您的操作...
// 操作後比對狀態
```

## 完整的清空與重種範例

根據您提供的步驟，以下是完整的腳本：

```php
// 在 Tinker 環境下（通過 Sail 連線）
./vendor/bin/sail artisan tinker

// 1. 關閉外鍵檢查
>>> Schema::disableForeignKeyConstraints();
=> true

// 2. 清空選單相關表 (順序建議：從子表到父表)
>>> DB::table('menu_permissions')->truncate();
>>> DB::table('menus')->truncate();

// 3. 清空簡訊日誌表
>>> DB::table('sms')->truncate();

// 4. 重啟外鍵檢查
>>> Schema::enableForeignKeyConstraints();
=> true

// 退出 Tinker 環境
>>> exit

// 執行 Seeder（從命令列）
./vendor/bin/sail artisan db:seed --class=MenuSeeder
./vendor/bin/sail artisan db:seed --class=SmsSeeder
```

## 實用指令與快捷方式

```php
// 显示当前工作环境信息
>>> info("Environment: ".app()->environment());

// 一次执行多个指令
>>> app()->environment(); DB::table('users')->count(); now();

// 创建临时工厂数据用于测试
>>> $testUsers = App\Models\User::factory()->count(5)->create();

// 快速测试数据库连接
>>> DB::select('SELECT 1');
=> [{"1": 1}]  // 成功連接

// 重置数据后重新运行所有迁移（完整重建）
>>> Artisan::call('migrate:fresh', ['--seed' => true]);

// 测试复杂查询而不影响现有表
>>> DB::table('users')->whereExists(function($q) {
    $q->select(DB::raw(1))
      ->from('posts')
      ->whereColumn('posts.user_id', 'users.id');
})->count();
```

## 常見問題與故障排除

1. **外鍵檢查錯誤仍然發生**
   - 解決辦法：確認是在整個事物中的同一連線上下文中執行

2. **Truncate 失敗或表格被鎖定**
   - 解決辦法：使用 DELETE FROM 指令替代，但 TRUNCATE 通常比較快
   ```php
   // 替代方案
   >>> DB::table('table_name')->delete();
   ```

3. **執行完大批量操作後要做的事**
   - 檢查索引是否需要重建 (`optimize table`)
   - 驗證資料完整性
   - 檢查自動遞增 ID 設定正確與否

Artisan Tinker 是一個極其強大的資料庫管理和除錯工具，只要遵守安全操作原則，就能大幅提高開發和維護效率。