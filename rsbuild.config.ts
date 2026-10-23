import { defineConfig } from "@rsbuild/core";
import { pluginSass } from "@rsbuild/plugin-sass";

/**
 * 輸出到 theme/dist/，並產生 manifest.json 讓 PHP 端（theme/inc/assets.php）依此 enqueue。
 * 檔名一律帶 hash，所以 PHP 端不需要另外加版本參數。
 */
export default defineConfig({
	plugins: [
		pluginSass({
			sassLoaderOptions: {
				sassOptions: {
					// Bootstrap 5.3 仍使用 @import 與舊式全域函式，這些警告不是我們能處理的
					quietDeps: true,
					silenceDeprecations: ["import", "global-builtin", "color-functions", "if-function"],
				},
			},
		}),
	],
	source: {
		entry: {
			main: "./src/main.ts",
		},
	},
	output: {
		target: "web",
		distPath: {
			root: "theme/dist",
		},
		// 執行期依 document.currentScript 決定 chunk 的路徑，因此不需要知道佈景主題的 URL
		assetPrefix: "auto",
		manifest: "manifest.json",
		cleanDistPath: true,
		legalComments: "none",
		injectStyles: false,
	},
	tools: {
		htmlPlugin: false,
	},
	performance: {
		chunkSplit: {
			strategy: "split-by-experience",
		},
	},
});
