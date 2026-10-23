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
 * PostCSS 外掛：@font-face 只保留 woff2 的來源。
 * KaTeX、Bootstrap Icons 等套件同時附了 woff、ttf，但現代瀏覽器只會用 woff2；
 * 在 css-loader 解析 url() 之前把其它格式的引用刪掉，那些字型檔就不會被輸出。
 */
const woff2Only = {
	postcssPlugin: "mylab-woff2-only",
	AtRule: {
		"font-face"(rule: { walkDecls(prop: string, cb: (decl: { value: string }) => void): void }) {
			rule.walkDecls("src", decl => {
				// 以不在括號內的逗號切開各個來源
				const sources = decl.value.split(/,(?![^(]*\))/);
				const woff2 = sources.filter(s => /\.woff2(?:[?#"')]|$)/i.test(s));
				if(woff2.length) decl.value = woff2.join(",");
			});
		},
	},
};

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
		postcss: (_options, { addPlugins }) => {
			addPlugins(woff2Only);
		},
	},
	performance: {
		chunkSplit: {
			strategy: "split-by-experience",
		},
	},
});
