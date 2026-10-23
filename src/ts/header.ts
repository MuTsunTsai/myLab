/**
 * 頂部導覽列的捲動行為：
 * - 捲動後加上陰影（body.is-scrolled）
 * - 文章頁在標題捲出畫面後，於導覽列顯示文章標題（body.show-post-title），手機版展開選單時除外（.menu-open）
 * - 文章頁的閱讀進度條
 * - 右下角的浮動按鈕（回到頂端、手機版的目錄按鈕）
 */
export function initHeader(): void {
	const body = document.body;
	const bar = document.querySelector<HTMLElement>(".reading-progress__bar");
	const goTop = document.querySelector<HTMLElement>(".go-top");
	// 右下角的浮動按鈕（回到頂端、手機版的目錄按鈕）都在捲動一段距離後才出現
	const floating = document.querySelectorAll<HTMLElement>(".go-top, .drawer-toggle");
	const title = document.getElementById("post-title");

	let ticking = false;
	const update = (): void => {
		ticking = false;
		const y = window.scrollY;
		body.classList.toggle("is-scrolled", y > 8);
		for(const el of floating) el.classList.toggle("is-visible", y > 400);
		if(bar) {
			const max = document.documentElement.scrollHeight - window.innerHeight;
			bar.style.transform = `scaleX(${max > 0 ? Math.min(y / max, 1) : 0})`;
		}
	};
	const request = (): void => {
		if(ticking) return;
		ticking = true;
		requestAnimationFrame(update);
	};
	window.addEventListener("scroll", request, { passive: true });
	window.addEventListener("resize", request, { passive: true });
	update();

	if(title) {
		new IntersectionObserver(([entry]) => {
			// 只有標題往上捲出畫面才顯示，捲到標題下方以外的情況不算
			body.classList.toggle("show-post-title", !entry.isIntersecting && entry.boundingClientRect.top < 0);
		}).observe(title);
	}

	// 手機版選單展開期間（含收合動畫），導覽列改回顯示網站名稱，避免與選單重疊
	const header = document.getElementById("site-header");
	const nav = document.getElementById("primary-nav");
	nav?.addEventListener("show.bs.collapse", () => header?.classList.add("menu-open"));
	nav?.addEventListener("hidden.bs.collapse", () => header?.classList.remove("menu-open"));

	goTop?.addEventListener("click", e => {
		e.preventDefault();
		window.scrollTo({ top: 0, behavior: "smooth" });
	});
}
