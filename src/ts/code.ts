/**
 * 以 Shiki 渲染程式碼區塊，並加上複製按鈕。
 *
 * 使用 fine-grained 的 shiki/core，搭配 JavaScript regex engine（不需要載入 WASM）。
 * 語法檔的來源分兩種：
 * - 常用語言打包進佈景主題（各自是獨立 chunk，只載入頁面上用到的）
 * - 其它語言在執行期從 CDN 載入同一版本的 @shikijs/langs，避免把數百個語法檔都塞進佈景主題
 */
import { createHighlighterCore, type HighlighterCore, type LanguageRegistration } from "shiki/core";
import { createJavaScriptRegexEngine } from "shiki/engine/javascript";
import darkPlus from "shiki/themes/dark-plus.mjs";

const THEME = "dark-plus";

type Grammar = LanguageRegistration[];
type GrammarLoader = () => Promise<{ default: Grammar }>;

const typescript: GrammarLoader = () => import("shiki/langs/typescript.mjs");
const javascript: GrammarLoader = () => import("shiki/langs/javascript.mjs");
const shellscript: GrammarLoader = () => import("shiki/langs/shellscript.mjs");
const powershell: GrammarLoader = () => import("shiki/langs/powershell.mjs");
const cpp: GrammarLoader = () => import("shiki/langs/cpp.mjs");
const csharp: GrammarLoader = () => import("shiki/langs/csharp.mjs");
const yaml: GrammarLoader = () => import("shiki/langs/yaml.mjs");
const markdown: GrammarLoader = () => import("shiki/langs/markdown.mjs");
const python: GrammarLoader = () => import("shiki/langs/python.mjs");
const makefile: GrammarLoader = () => import("shiki/langs/makefile.mjs");
const kotlin: GrammarLoader = () => import("shiki/langs/kotlin.mjs");

/**
 * 打包進佈景主題的常用語言（鍵為 Githuber MD 輸出的 language-xxx 名稱，含別名）。
 * 依網站文章實際用到的語言挑選（2026/10 透過 REST API 掃過全站 80 篇文章與頁面），再加上幾個可能用到的；要增減時只需修改這裡。
 */
const BUNDLED: Record<string, GrammarLoader> = {
	typescript, ts: typescript,
	tsx: () => import("shiki/langs/tsx.mjs"),
	javascript, js: javascript,
	jsx: () => import("shiki/langs/jsx.mjs"),
	json: () => import("shiki/langs/json.mjs"),
	jsonc: () => import("shiki/langs/jsonc.mjs"),
	html: () => import("shiki/langs/html.mjs"),
	css: () => import("shiki/langs/css.mjs"),
	scss: () => import("shiki/langs/scss.mjs"),
	vue: () => import("shiki/langs/vue.mjs"),
	shellscript, bash: shellscript, sh: shellscript, shell: shellscript, zsh: shellscript,
	powershell, ps1: powershell,
	cpp, "c++": cpp,
	c: () => import("shiki/langs/c.mjs"),
	csharp, cs: csharp, "c#": csharp,
	razor: () => import("shiki/langs/razor.mjs"),
	php: () => import("shiki/langs/php.mjs"),
	python, py: python,
	yaml, yml: yaml,
	markdown, md: markdown,
	xml: () => import("shiki/langs/xml.mjs"),
	sql: () => import("shiki/langs/sql.mjs"),
	diff: () => import("shiki/langs/diff.mjs"),
	makefile, make: makefile,
	kotlin, kt: kotlin, kts: kotlin,
	swift: () => import("shiki/langs/swift.mjs"),
	apache: () => import("shiki/langs/apache.mjs"),
};

const CDN_NAMES = new Set(SHIKI_LANGS_NAMES);
const CDN_BASE = `https://cdn.jsdelivr.net/npm/@shikijs/langs@${SHIKI_LANGS_VERSION}/dist/`;

/** 取得語言的語法檔；Shiki 不支援的語言回傳 undefined */
function loadGrammar(lang: string): Promise<Grammar> | undefined {
	if(Object.hasOwn(BUNDLED, lang)) return BUNDLED[lang]().then(m => m.default);
	if(CDN_NAMES.has(lang)) {
		const url = CDN_BASE + encodeURIComponent(lang) + ".mjs";
		return import(/* webpackIgnore: true */ url).then((m: { default: Grammar }) => m.default);
	}
	return undefined;
}

/** 依語言對 Shiki 輸出的 HTML 做的額外調整 */
const POST_PROCESS: Record<string, (html: string) => string> = {
	// 自訂元件之顏色
	razor: html => html.replace(/#F44747/gi, "#009696"),
};

function getLang(el: HTMLElement): string {
	for(const c of el.classList) {
		if(c.startsWith("language-")) return c.slice(9).toLowerCase();
	}
	return "";
}

export async function highlightCode(blocks: HTMLElement[]): Promise<void> {
	// 先載入頁面上用到的所有語法檔；個別失敗（例如 CDN 連不上）時，該語言就以純文字顯示
	const loaded = new Set<string>();
	const grammars: Grammar[] = [];
	await Promise.all([...new Set(blocks.map(getLang))].map(async lang => {
		try {
			const grammar = await loadGrammar(lang);
			if(!grammar) return;
			grammars.push(grammar);
			loaded.add(lang);
		} catch(e) {
			console.error(`[myLab] 無法載入語言 ${lang} 的語法檔`, e);
		}
	}));

	let highlighter: HighlighterCore | undefined;
	try {
		highlighter = await createHighlighterCore({
			themes: [darkPlus],
			langs: grammars,
			engine: createJavaScriptRegexEngine({ forgiving: true }),
		});
	} catch(e) {
		console.error("[myLab] Shiki 初始化失敗", e);
	}

	for(const code of blocks) {
		const pre = code.parentElement!;
		const lang = getLang(code);
		const text = code.textContent!.replace(/\n$/, "");

		let rendered: HTMLElement = pre;
		if(highlighter && loaded.has(lang)) {
			try {
				let html = highlighter.codeToHtml(text, { lang, theme: THEME });
				html = POST_PROCESS[lang]?.(html) ?? html;
				const template = document.createElement("template");
				template.innerHTML = html;
				rendered = template.content.firstElementChild as HTMLElement;
			} catch(e) {
				console.error(`[myLab] 無法以 Shiki 渲染語言 ${lang}`, e);
			}
		}
		// 先插入容器再放入內容；未高亮時 rendered 就是 pre 本身，不能直接 replaceWith
		const block = createBlock(lang, text);
		pre.before(block);
		block.append(rendered);
		if(rendered !== pre) pre.remove();
	}
}

function createBlock(lang: string, text: string): HTMLElement {
	const block = document.createElement("div");
	block.className = "code-block";
	if(lang) block.dataset.lang = lang;

	const copy = document.createElement("button");
	copy.type = "button";
	copy.className = "code-block__copy";
	copy.title = "複製程式碼";
	copy.setAttribute("aria-label", "複製程式碼");
	copy.innerHTML = '<i class="bi bi-clipboard" aria-hidden="true"></i>';
	copy.addEventListener("click", async () => {
		try {
			await navigator.clipboard.writeText(text);
			copy.classList.add("is-copied");
			copy.innerHTML = '<i class="bi bi-check2" aria-hidden="true"></i>';
			setTimeout(() => {
				copy.classList.remove("is-copied");
				copy.innerHTML = '<i class="bi bi-clipboard" aria-hidden="true"></i>';
			}, 2000);
		} catch(e) {
			console.error("[myLab] 複製失敗", e);
		}
	});

	block.append(copy);
	return block;
}
