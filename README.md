# myLab

星君研究室（[abstreamace.com/sglab](https://abstreamace.com/sglab/)）的 WordPress 佈景主題，以 [Mynote](https://github.com/terrylinooo/mynote) 及其子主題的客製化為基礎重新打造。

> [!NOTE]
> 這個佈景主題原則上是給我自己的網站使用的，許多地方直接依照該網站的內容與外掛（例如 WP Githuber MD 的輸出格式）設計，介面文字也直接以中文撰寫。歡迎參考或取用其中的程式碼，但不建議直接安裝 Release 使用；日後的更新也不會考慮其他網站的相容性。

- 藍色調的暗色設計，Bootstrap 5（Sass 客製化、只編譯用到的元件）
- 程式碼以 [Shiki](https://shiki.style/) 渲染（dark-plus），附複製按鈕
- 數學式以 [KaTeX](https://katex.org/) 渲染，Mermaid 語法的圖表以 [Pele](https://pele.run/) 渲染（比 Mermaid 小很多、也快很多），取代 WP Githuber MD 內建的舊版本
- 內建目錄（取代 Easy Table of Contents），錨點格式與 Easy TOC 相同，既有連結不會失效
- 透過 GitHub Releases 自動更新

## 開發

```sh
pnpm install
pnpm build      # 建置到 theme/dist/
pnpm watch      # 監看並持續建置
pnpm preview    # 在 http://127.0.0.1:9400 啟動含測試資料的本機 WordPress（WordPress Playground）
pnpm typecheck
```

`pnpm preview` 會把 `theme/` 掛載為佈景主題，搭配 `pnpm watch`，修改後重新整理頁面即可看到結果。PHP 的修改也會立即生效。測試資料定義在 [preview/setup.php](preview/setup.php)，每次啟動都會重建。Windows 上請從 PowerShell 執行（Git Bash 會把 `/wordpress/...` 路徑轉換掉）。

### 專案結構

`theme/` 就是實際安裝到網站上的佈景主題（打包時整個資料夾成為 `mylab.zip` 的內容），其餘是開發用的 Node.js 專案。

| 路徑 | 說明 |
| --- | --- |
| `src/main.ts` | 前端進入點 |
| `src/ts/` | 導覽列、目錄 scrollspy、版面切換；`code.ts`、`math.ts`、`diagram.ts` 為按需動態載入的內容處理器 |
| `src/scss/` | 樣式；`_variables.scss` 為 Bootstrap 變數，`_tokens.scss` 為佈景主題的 CSS 變數 |
| `theme/` | 佈景主題本體：`style.css`、`functions.php` 與各模板 |
| `theme/inc/` | PHP 功能，依職責拆分（見 `functions.php`） |
| `theme/template-parts/` | 模板片段 |
| `theme/dist/` | 建置結果（不納入版本控制），由 `inc/assets.php` 依 `dist/manifest.json` 載入 |
| `preview/` | 本機預覽用的 Playground 設定與測試資料 |
| `scripts/` | 打包與發佈腳本 |

### 更新相依套件

```sh
pnpm update --latest
pnpm build && pnpm preview   # 在本機確認沒問題後再發佈
```

## 發佈

```sh
pnpm release            # patch，例如 1.0.0 → 1.0.1
pnpm release minor      # 或 major，或直接指定版本號
```

會更新 `package.json` 與 `theme/style.css` 的版本號、commit、建立 tag 並推送。GitHub Actions（`.github/workflows/release.yml`）接著會建置並把 `mylab.zip` 發佈為 Release；網站後台的「更新」頁面就會出現新版本（也可以在「外觀 › 佈景主題」為 myLab 啟用自動更新）。

更新機制見 [theme/inc/class-mylab-updater.php](theme/inc/class-mylab-updater.php)：`style.css` 的 `Update URI` 指向本 repo，WordPress 便會向 GitHub Releases 查詢新版本。
