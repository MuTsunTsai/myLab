/**
 * 以 Shiki 渲染程式碼區塊，並加上複製按鈕。
 *
 * 使用 fine-grained 的 shiki/core，搭配 JavaScript regex engine（不需要載入 WASM）；
 * 各語言的 grammar 由打包工具拆成獨立 chunk，只會載入頁面上用到的語言。
 */
import { createHighlighterCore, type HighlighterCore } from "shiki/core";
import { createJavaScriptRegexEngine } from "shiki/engine/javascript";
import { bundledLanguages } from "shiki/langs";
import darkPlus from "shiki/themes/dark-plus.mjs";

const THEME = "dark-plus";

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

function isBundled(lang: string): lang is keyof typeof bundledLanguages {
	return Object.hasOwn(bundledLanguages, lang);
}

export async function highlightCode(blocks: HTMLElement[]): Promise<void> {
	const langs = [...new Set(blocks.map(getLang).filter(isBundled))];
	let highlighter: HighlighterCore | undefined;
	try {
		highlighter = await createHighlighterCore({
			themes: [darkPlus],
			langs: langs.map(lang => bundledLanguages[lang]),
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
		if(highlighter && isBundled(lang)) {
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
