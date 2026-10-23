import { readdirSync, readFileSync } from "node:fs";
import { createRequire } from "node:module";
import { dirname, join } from "node:path";
import { defineConfig } from "@rsbuild/core";
import { pluginSass } from "@rsbuild/plugin-sass";

/**
 * Shiki 語法檔套件（@shikijs/langs）的版本與所有可用的語言名稱（含別名）。
 * 未打包進佈景主題的語言會在執行期從 CDN 載入同一版本，見 src/ts/code.ts。
 */
function shikiLangs(): { version: string; names: string[] } {
	const require = createRequire(import.meta.url);
	const shikiRequire = createRequire(require.resolve("shiki"));
	const dist = dirname(shikiRequire.resolve("@shikijs/langs"));
	const { version } = JSON.parse(readFileSync(join(dist, "..", "package.json"), "utf8"));
	const names = readdirSync(dist)
		.filter(f => f.endsWith(".mjs") && f !== "index.mjs")
		.map(f => f.slice(0, -4));
	return { version, names };
}

const shiki = shikiLangs();

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
		define: {
			SHIKI_LANGS_VERSION: JSON.stringify(shiki.version),
			SHIKI_LANGS_NAMES: JSON.stringify(shiki.names),
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
