# PHP_Laravel
學習 PHP / Laravel 的專案。  
下面是一個「適合學習 PHP 語法與基礎 Web 開發」用的極簡 docker-compose 範例：一個 Nginx（反向代理 / Web Server）＋一個 PHP-FPM（跑 PHP）容器，程式碼掛載在 `./src`。  

***

## 專案結構建議

在你要練習的資料夾裡，先預期會長這樣：  

- `docker-compose.yml`  
- `nginx/`  
  - `default.conf`（Nginx 設定）  
- `src/`  
  - `index.php`（你的 PHP 練習檔）

***

## Dockerfile
建立 Dockerfile 以安裝 Xdebug  
建立了 [docker/php/Dockerfile](./docker/php/Dockerfile)，在啟動時自動安裝並啟用 Xdebug。  

## docker-compose.yml（Nginx + PHP-FPM）
將 php 服務改為使用自定義 Build，並將 xdebug.ini 掛載到正確的 php 容器路徑。  

[docker-compose.yml](./docker-compose.yml)

說明（關鍵點）：  
- `php` 使用自定義 Build，並將 
xdebug.ini
 掛載到正確的 
php
 容器路徑。

[docker-compose.yml](./docker-compose.yml)

說明（關鍵點）：  
- `php` 使用官方 `php:8.3-fpm`，適合學新語法與 Laravel 新版。  
- `nginx` 用輕量的 `nginx:alpine`，port 對外開 8080，避免佔用 80。  
- `./src` 掛到 `/var/www/html`，你在主機編輯檔案後，容器會即時看到。  

***

## Nginx 設定：nginx/default.conf

在專案裡建立 `nginx/default.conf`：  

[nginx/default.conf](./nginx/default.conf)  

- `fastcgi_pass php:9000;` 裡的 `php` 就是上面 compose 裡 `service` 的名字（容器 DNS 名稱）。  
- 這樣所有 `.php` 會丟給 PHP-FPM 容器去執行。  

***

## 測試檔：src/index.php

在 `src/index.php` 放一個超簡單測試：  

```php
<?php
phpinfo();
```

或之後改成你要練習的 PHP 語法、函式、物件導向、Composer 等。  

***

## 啟動方式

在專案根目錄（有 docker-compose.yml 的那層）執行：  

```bash
# docker compose down
docker compose up -d
# 或舊版 Docker：docker-compose up -d
```

然後在瀏覽器開：  

```text
http://localhost:8080
```

應該就會看到 `phpinfo()` 內容，代表 PHP + Nginx + volume 都正常。  

***

如果你接下來想要：  
- 加上 **Xdebug（除錯 breakpoint 用）**  
- 或者把這個環境調成 **Laravel 學習版**（加 composer / mysql）  

---

## 啟動偵錯：

1. 在 VS Code 按下 F5（或點選偵錯面板的 "Listen for Xdebug (Docker)"）。
2. src/index.php 的第 2 行 phpinfo(); 設定中斷點。
3. 開啟瀏覽器存取 http://localhost:8080。
4. 現在中斷點應該可以正常觸發了。

