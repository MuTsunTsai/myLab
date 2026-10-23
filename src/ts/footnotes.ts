/**
 * 註腳預覽：滑鼠移到內文的註腳編號上時，浮出該註腳的內容。
 * 只在有滑鼠的裝置上啟用；觸控裝置維持點擊跳轉到文末。
 *
 * WP Githuber MD（Parsedown Extra）輸出的標記：
 * - 內文：<sup id="fnref1:x"><a href="#fn:x" class="footnote-ref">1</a></sup>
 * - 文末：<div class="footnotes"><hr><ol><li id="fn:x"><p>…<a class="footnote-backref">↩</a></p></li></ol></div>
 */
export function initFootnotes(): void {
	if(!matchMedia("(hover: hover) and (pointer: fine)").matches) return;
	const refs = document.querySelectorAll<HTMLAnchorElement>(".entry-content a.footnote-ref");
	if(!refs.length) return;

	const tip = document.createElement("div");
	tip.className = "footnote-tip";
	tip.id = "footnote-tip";
	tip.setAttribute("role", "tooltip");
	tip.hidden = true;
	document.body.append(tip);

	let hideTimer: number | undefined;
	const hide = (): void => {
		hideTimer = window.setTimeout(() => { tip.hidden = true; }, 150);
	};
	// 讓滑鼠可以移進預覽框（例如點裡面的連結）而不會消失
	tip.addEventListener("mouseenter", () => clearTimeout(hideTimer));
	tip.addEventListener("mouseleave", hide);

	const show = (ref: HTMLAnchorElement): void => {
		const note = document.getElementById(ref.getAttribute("href")!.slice(1));
		if(!note) return;
		clearTimeout(hideTimer);

		tip.replaceChildren(...[...note.childNodes].map(n => n.cloneNode(true)));
		for(const backref of tip.querySelectorAll(".footnote-backref")) backref.remove();
		tip.hidden = false;

		// 預設放在編號下方，空間不夠時改放上方；水平方向限制在視窗內
		const rect = ref.getBoundingClientRect();
		const margin = 8;
		const left = Math.min(Math.max(margin, rect.left + rect.width / 2 - tip.offsetWidth / 2), document.documentElement.clientWidth - tip.offsetWidth - margin);
		const below = rect.bottom + margin + tip.offsetHeight < window.innerHeight;
		const top = below ? rect.bottom + margin : rect.top - margin - tip.offsetHeight;
		tip.style.left = `${left + window.scrollX}px`;
		tip.style.top = `${top + window.scrollY}px`;
	};

	for(const ref of refs) {
		ref.setAttribute("aria-describedby", tip.id);
		ref.addEventListener("mouseenter", () => show(ref));
		ref.addEventListener("focus", () => show(ref));
		ref.addEventListener("mouseleave", hide);
		ref.addEventListener("blur", hide);
	}
}
