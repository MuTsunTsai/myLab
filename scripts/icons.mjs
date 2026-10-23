/**
 * 產生只含實際用到之圖示的 Bootstrap Icons 字型子集與 CSS，輸出到 src/generated/（不納入版本控制）。
 *
 * 完整的 Bootstrap Icons 字型約 130 KB、CSS 列了兩千多個圖示，但佈景主題只用到二十幾個。
 * 這裡掃描原始碼中出現的 bi-* 類別名稱，以及 SCSS 中直接以碼位（例如 "\F282"）引用的圖示，
 * 再用 subset-font（HarfBuzz）裁出只含這些字符的 woff2。
 * 圖示仍然是字型，與文字的垂直對齊方式和原本完全相同。
 *
 * 注意：圖示名稱必須以完整的 bi-xxx 字串出現在原始碼中，動態組出來的名稱（例如 "bi-" + name）掃描不到。
 *
 * 用法：node scripts/icons.mjs（pnpm build／watch 會先自動執行）
 */
import { mkdirSync, readdirSync, readFileSync, statSync, writeFileSync } from "node:fs";
import { createRequire } from "node:module";
import { dirname, extname, join } from "node:path";
import { fileURLToPath } from "node:url";
import subsetFont from "subset-font";

const ROOT = fileURLToPath(new URL("..", import.meta.url));
const OUT = join(ROOT, "src", "generated");
const require = createRequire(import.meta.url);
const FONT_DIR = join(dirname(require.resolve("bootstrap-icons/package.json")), "font");

const map = JSON.parse(readFileSync(join(FONT_DIR, "bootstrap-icons.json"), "utf8"));

/** 要掃描的檔案：src 與 theme 底下的 ts、scss、php（略過產出物） */
function* sourceFiles(dir) {
	for(const name of readdirSync(dir)) {
		const path = join(dir, name);
		if(statSync(path).isDirectory()) {
			if(name === "dist" || name === "generated" || name === "node_modules") continue;
			yield* sourceFiles(path);
		} else if([".ts", ".scss", ".php"].includes(extname(name))) {
			yield path;
		}
	}
}

const names = new Set();
const codepoints = new Set();
for(const file of [...sourceFiles(join(ROOT, "src")), ...sourceFiles(join(ROOT, "theme"))]) {
	const text = readFileSync(file, "utf8");
	for(const [, name] of text.matchAll(/\bbi-([a-z0-9]+(?:-[a-z0-9]+)*)/g)) {
		if(Object.hasOwn(map, name)) names.add(name);
	}
	if(file.endsWith(".scss")) {
		// 例如 content: "\F282"（Bootstrap Icons 的碼位在 U+F100 以後的私用區）
		for(const [, hex] of text.matchAll(/\\(F[0-9A-F]{3})\b/gi)) codepoints.add(parseInt(hex, 16));
	}
}
for(const name of names) codepoints.add(map[name]);

// 字型子集
const font = readFileSync(join(FONT_DIR, "fonts", "bootstrap-icons.woff2"));
const subset = await subsetFont(font, String.fromCodePoint(...codepoints), { targetFormat: "woff2" });

// CSS：沿用原始 CSS 的版權宣告與共用規則，只列出用到的圖示
const css = readFileSync(join(FONT_DIR, "bootstrap-icons.css"), "utf8");
const header = css.match(/^\/\*![\s\S]*?\*\//)[0];
const base = css.match(/\.bi::before,[\s\S]*?\n\}/)[0];
const rules = [...names].sort().map(name => `.bi-${name}::before { content: "\\${map[name].toString(16)}"; }`);
const output = `${header}

/* 由 scripts/icons.mjs 產生，請勿手動修改 */

@font-face {
  font-display: block;
  font-family: "bootstrap-icons";
  src: url("./bootstrap-icons.woff2") format("woff2");
}

${base}

${rules.join("\n")}
`;

mkdirSync(OUT, { recursive: true });
writeFileSync(join(OUT, "bootstrap-icons.woff2"), subset);
writeFileSync(join(OUT, "bootstrap-icons.css"), output);
console.log(`Bootstrap Icons：${codepoints.size} 個圖示，字型 ${(font.length / 1024).toFixed(0)} KB → ${(subset.length / 1024).toFixed(1)} KB`);
