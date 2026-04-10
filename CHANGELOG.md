# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- 新增關於 Laravel Validate、Seeder 以及程式碼覆蓋率 (Code Coverage) 測試流程的詳細說明於 README.md。
- 新增 API 註冊功能 (`/api/register`)。
- 新增 API 登入功能，使用 Laravel Sanctum 進行代幣驗證。
- 新增 `AuthController` 處理 `login` 與 `logout` 請求。
- 在 `User` 模型中啟用 `HasApiTokens` 以支援 API 認證。
- 更新 `routes/api.php` 加入認證相關路由。
- 新增 `src/scripts/evaluate_structure.sh`，可量測結構內聚與命名空間一致性。
- 新增 `docs/HARNESS_ENGINEERING_STRUCTURE.md`，記錄平行原型與決策矩陣。

### Changed
- **README.md 結構重組**：
  - 新增 **🚀 Quick Start (快速開始)** 區段於最前面，提供 4 步驟快速上手指南。
  - 新增 **常用指令速查表** 與 **常見問題 Q&A**。
  - 將詳細說明移至 **🏗️ 啟動與關閉服務** 等後續章節。
  - 新增 **Network Resource is still in use** 警告處理說明。
  - 新增 **APP_KEY 診斷流程**：包含查看日誌指令、錯誤訊息識別與修復步驟。
- API 結構改為 Feature-first：`Auth` 與 `User` 控制器搬移至 `app/Domain/*`。
- 將 API 登入與註冊驗證規則改由 Domain 內 `FormRequest` 管理，降低控制器耦合。
