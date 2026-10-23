/**
 * 目錄（.toc）的 scrollspy：依捲動位置標示目前閱讀到的章節。
 * 目錄本身由 PHP 端（inc/class-mylab-toc.php）產生。
 */
import Offcanvas from "bootstrap/js/dist/offcanvas";

export function initToc(): void {
	initDrawer();
	const links = [...document.querySelectorAll<HTMLAnchorElement>(".toc a[href^='#']")];
	if(!links.length) return;

	// 錨點沿用 Easy TOC 的格式，id 本身就含有百分比編碼，所以直接以字面值查找
	const ids = [...new Set(links.map(a => a.getAttribute("href")!.slice(1)))];
	const headings = ids
		.map(id => document.getElementById(id))
		.filter((h): h is HTMLElement => h !== null);
	if(!headings.length) return;

	let current: string | null = null;
	let ticking = false;

	const update = (): void => {
		ticking = false;
		const offset = parseFloat(getComputedStyle(document.documentElement).scrollPaddingTop) || 0;
		let active: HTMLElement | null = null;
		for(const heading of headings) {
			if(heading.getBoundingClientRect().top - offset - 8 > 0) break;
			active = heading;
		}
		const id = active?.id ?? null;
		if(id === current) return;
		current = id;

		for(const link of links) {
			const isActive = link.getAttribute("href")!.slice(1) === id;
			link.classList.toggle("is-active", isActive);
			if(isActive) link.setAttribute("aria-current", "location");
			else link.removeAttribute("aria-current");
		}
		for(const nav of document.querySelectorAll<HTMLElement>(".toc--widget")) {
			const link = nav.querySelector<HTMLElement>("a.is-active");
			if(link) keepVisible(nav, link);
		}
	};

	window.addEventListener("scroll", () => {
		if(ticking) return;
		ticking = true;
		requestAnimationFrame(update);
	}, { passive: true });
	update();
}

/**
 * 手機版的固定側欄抽屜（見 sidebar.php）：
 * - 點選錨點時先關閉抽屜再跳轉；抽屜開啟期間頁面捲動被鎖住，直接跳轉會失敗
 * - 開啟時讓目前章節捲到抽屜中央
 */
function initDrawer(): void {
	const drawer = document.getElementById("sidebar-drawer");
	if(!drawer) return;

	drawer.addEventListener("click", e => {
		const link = (e.target as Element).closest<HTMLAnchorElement>("a[href^='#']");
		// 桌面版的 offcanvas-lg 不會有 .show，維持一般的錨點行為
		if(!link || !drawer.classList.contains("show")) return;
		e.preventDefault();
		const href = link.getAttribute("href")!;
		drawer.addEventListener("hidden.bs.offcanvas", () => {
			if(location.hash === href) document.getElementById(href.slice(1))?.scrollIntoView();
			else location.hash = href;
		}, { once: true });
		Offcanvas.getOrCreateInstance(drawer).hide();
	});

	drawer.addEventListener("shown.bs.offcanvas", () => {
		const body = drawer.querySelector<HTMLElement>(".offcanvas-body");
		const link = drawer.querySelector<HTMLElement>("a.is-active");
		if(!body || !link) return;
		const offset = link.getBoundingClientRect().top - body.getBoundingClientRect().top;
		body.scrollTop += offset - body.clientHeight / 2;
	});
}

/** 在可捲動的側欄目錄中，讓目前章節保持可見；不能用 scrollIntoView，否則會連帶捲動整個頁面。 */
function keepVisible(container: HTMLElement, el: HTMLElement): void {
	const box = container.getBoundingClientRect();
	const rect = el.getBoundingClientRect();
	const margin = box.height / 4;
	if(rect.top < box.top + margin) {
		container.scrollTop -= box.top + margin - rect.top;
	} else if(rect.bottom > box.bottom - margin) {
		container.scrollTop += rect.bottom - (box.bottom - margin);
	}
}
