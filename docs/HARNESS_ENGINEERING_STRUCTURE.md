# Harness Engineering: `src` 結構優化評估

## DRI 與目標
- DRI: `liao-eli`
- 核心意圖: 優化整體專案結構，提升可維護性
- 限制: 不改變既有 API 對外行為

## 平行原型

### 原型 A: 傳統分層優化 (Layer-first)
- 作法: 保持 `app/Http/Controllers/Api`，僅補齊 `FormRequest`、Service 層與回應封裝。
- 優點: 遷移成本低，學習曲線小。
- 風險: 功能邊界仍分散在 `Http/*`，中長期容易出現跨層耦合。

### 原型 B: 功能聚合優化 (Feature-first, 本次採用)
- 作法: 以業務能力拆分 `app/Domain/Auth`、`app/Domain/User`，將 Controller/Request 同域收斂。
- 優點: 功能責任清晰、擴展時更容易定位與測試。
- 風險: 初期需要團隊適應新路徑與命名。

## 評估基準 (Evaluation)
- 可維護性指標
  - 控制器是否依功能域聚合
  - 驗證邏輯是否集中在 `FormRequest`
  - 路由是否僅依賴單一清晰命名空間
- 驗證方式
  - `./scripts/evaluate_structure.sh`
  - `./vendor/bin/sail artisan test`
  - `./vendor/bin/sail php ./vendor/bin/pint --test`

## 決策矩陣

| 指標 | 原型 A (Layer-first) | 原型 B (Feature-first) |
|---|---|---|
| 上手速度 | 高 | 中 |
| 功能內聚 | 中 | 高 |
| 未來擴展 | 中 | 高 |
| 跨模組耦合風險 | 中高 | 中低 |
| 長期可維護性 | 中 | 高 |

## 推薦結論
- 推薦原型 B (Feature-first) 作為主線。
- 理由: 在不改外部 API 的前提下，能最快建立可擴展的結構基線。
