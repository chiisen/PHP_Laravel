# TODO List — PHP_Laravel 優化計畫

> 自訂 Nginx + PHP-FPM + Laravel 的 Docker 開發模板

---

## 環境建置 (Setup)

- [x] Laravel 完整結構已存在（src/ 下有 vendor/、node_modules/、.env）
- [ ] 建立 Makefile 整合常用指令
  - `make up`, `make down`, `make logs`, `make shell`
  - `make migrate`, `make seed`, `make test`
- [ ] 補完「從零開始架設 Laravel + Nginx + PHP-FPM」的步驟文件
  - 對比 Sail：何時用這個模板而非 Sail

---

## Docker 環境 (Docker)

- [ ] 加入 Redis 容器（目前只有 php/nginx/mysql/adminer）
- [ ] 將 php-conf/xdebug.ini 整合進 Dockerfile（目前是 volume mount）
- [ ] 確認 docker-compose.yml 完整運作（無 Redis、MySQL 需 --profile mysql 啟動）
- [ ] 設定 Docker healthcheck（php、nginx、db、redis）

---

## 功能面 (Features)

- [ ] 建立 Post CRUD 範例（對比 Sail 版本）
  - 教學重點：展示「自己架 Nginx + PHP-FPM」與「Sail」的差異
- [ ] 展示自訂 PHP-FPM + Nginx 的優勢
  - 可自訂 php-conf（效能、額外擴充）
  - 可自訂 Dockerfile 擴充相依性
- [ ] 建立 REST API 路由範例（api.php）

---

## 文件 (Docs)

- [ ] 建立本地端開發 vs Sail 的情境比較（何時用哪個）
- [ ] local.http 加入 REST API 範例
- [ ] 補完「如何更換 PHP 版本」說明（修改 Dockerfile FROM）

---

## 測試 (Testing)

- [ ] 建立 GitHub Actions CI 流程（測試 + lint）
- [ ] 確認 phpunit.xml 正常運作

---

## 維運 (Ops)

- [x] .gitignore 已正確排除 vendor/、node_modules/（實際存在 src/ 內）
- [ ] 建立正式環境部署文件（Nginx + PHP-FPM + MySQL）

---

## 🚧 測試流程

### 啟動環境（不含 MySQL）
```bash
cd /Users/liao-eli/github/PHP_Laravel
docker compose up -d
```

### 啟動環境（含 MySQL + Adminer）
```bash
docker compose --profile mysql up -d
```

### 設定 APP_KEY
```bash
docker exec -it php-learn php artisan key:generate
```

### 訪問
- 網站：http://localhost:8080
- Adminer：http://localhost:8081（需 --profile mysql）

### 常用指令
```bash
docker exec -it php-learn php artisan [command]
docker compose logs -f php
docker compose restart
docker compose --profile mysql down
```

---

## 與 laravel_sail 的定位差異

| | PHP_Laravel（此專案） | laravel_sail |
|---|---|---|
| Docker 堆疊 | Nginx + PHP-FPM 分離 | Sail（全包） |
| 學習重點 | 了解環境如何架設 | 快速開發 |
| 適用情境 | 需要自訂 PHP/Nginx 擴充 | 多專案、快速啟動 |
| 複雜度 | 較高（需理解 Nginx 配置） | 較低（Docker 隔離） |