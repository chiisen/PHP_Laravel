# TODO List — PHP_Laravel 優化計畫

> 自訂 Nginx + PHP-FPM + Laravel 的乾淨模板

---

## 環境建置 (Setup)

- [ ] 補完 `src/` 的標準 Laravel 結構
  - app/、config/、database/、routes/、tests/ 等
  - 確認 bootstrap/app.php 正確引用
- [ ] 建立並執行 `composer install`（目前無 vendor/）
- [ ] 建立並執行 `npm install`（目前無 node_modules/）
- [ ] 補完 `.env.example` 與 `APP_KEY` 生成流程

---

## Docker 環境 (Docker)

- [ ] 確認 docker-compose.yml 完整運作（MySQL + Adminer）
- [ ] 將 php-conf/ xdebug.ini 整合進 Dockerfile（而非 volume mount）
- [ ] 加入 Redis 容器（未來快取用）
- [ ] 調整 nginx/default.conf 支援 Laravel routing（index.php rewrite）
- [ ] 加入 `docker compose exec php php artisan migrate` 範例指令文件化

---

## 功能面 (Features)

- [ ] 建立基礎 Laravel 專案（空白乾淨）
- [ ] 展示自訂 PHP-FPM + Nginx 的優勢（對比 Sail）
  - 可自訂 php-conf（效能、額外擴充）
  - 可自訂 Dockerfile 擴充相依性

---

## 文件 (Docs)

- [ ] 補完「從零開始架設 Laravel + Nginx + PHP-FPM」的步驟文件
- [ ] 建立本地端開發 vs Sail 的情境比較（何時用哪個）
- [ ] local.http 加入 REST API 範例

---

## 測試 (Testing)

- [ ] 建立 PHPUnit 基礎設定
- [ ] 建立 GitHub Actions CI 流程

---

## 維運 (Ops)

- [ ] 確認 .gitignore 正確排除 vendor/、node_modules/
- [ ] 設定 Docker 健康檢查（healthcheck）
- [ ] 建立正式環境部署文件（針對 Nginx + PHP-FPM）