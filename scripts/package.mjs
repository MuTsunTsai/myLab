/**
 * 將 theme/ 打包為 mylab.zip（根目錄為 mylab/，WordPress 更新時會依此覆蓋原資料夾）。
 *
 * 用法：
 *   node scripts/package.mjs                 打包
 *   node scripts/package.mjs --check 1.2.3   檢查 style.css 的版本號是否為 1.2.3（CI 用）
 */
import { existsSync, readdirSync, readFileSync, statSync, writeFileSync } from "node:fs";
import { join, relative, sep } from "node:path";
import { fileURLToPath } from "node:url";
import { zipSync } from "fflate";

const ROOT = fileURLToPath(new URL("..", import.meta.url));
const THEME = join(ROOT, "theme");
const SLUG = "mylab";

const styleVersion = readFileSync(join(THEME, "style.css"), "utf8").match(/^Version:\s*(.+)$/m)?.[1].trim();

if(process.argv[2] === "--check") {
	const expected = process.argv[3];
	if(styleVersion !== expected) {
		console.error(`style.css 的版本號（${styleVersion}）與 tag（${expected}）不一致`);
		process.exit(1);
	}
	console.log(`版本號一致：${expected}`);
	process.exit(0);
}

if(!existsSync(join(THEME, "dist", "manifest.json"))) {
	console.error("找不到 theme/dist/manifest.json，請先執行 pnpm build");
	process.exit(1);
}

/** @type {Record<string, Uint8Array>} */
const files = {};
function add(path) {
	if(statSync(path).isDirectory()) {
		for(const name of readdirSync(path)) add(join(path, name));
	} else {
		files[`${SLUG}/${relative(THEME, path).split(sep).join("/")}`] = readFileSync(path);
	}
}
add(THEME);

const zip = zipSync(files, { level: 9 });
writeFileSync(join(ROOT, `${SLUG}.zip`), zip);
console.log(`${SLUG}.zip：v${styleVersion}，${Object.keys(files).length} 個檔案，${(zip.length / 1024).toFixed(0)} KB`);
