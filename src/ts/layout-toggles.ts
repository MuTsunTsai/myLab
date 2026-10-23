/**
 * 文章頁標題下方的版面切換按鈕：
 * - sidebar：隱藏整個側欄，讓內文佔滿寬度
 * - widgets：只隱藏一般側欄，保留固定側欄（目錄）
 *
 * 狀態以 body class 表示，並記在 localStorage 讓下一篇文章沿用。
 */

const TOGGLES = {
	sidebar: "sidebar-hidden",
	widgets: "widgets-hidden",
} as const;

type ToggleName = keyof typeof TOGGLES;

const STORAGE_PREFIX = "mylab:";

function load(name: ToggleName): boolean {
	try {
		return localStorage.getItem(STORAGE_PREFIX + name) === "1";
	} catch {
		return false;
	}
}

function save(name: ToggleName, hidden: boolean): void {
	try {
		localStorage.setItem(STORAGE_PREFIX + name, hidden ? "1" : "0");
	} catch {
		// 無痕模式等情況下無法儲存，忽略即可
	}
}

export function initLayoutToggles(): void {
	const buttons = document.querySelectorAll<HTMLButtonElement>("[data-mylab-toggle]");
	for(const button of buttons) {
		const name = button.dataset.mylabToggle as ToggleName;
		const className = TOGGLES[name];
		if(!className) continue;

		const apply = (hidden: boolean): void => {
			document.body.classList.toggle(className, hidden);
			button.setAttribute("aria-pressed", String(!hidden));
		};
		apply(load(name));

		button.addEventListener("click", () => {
			const hidden = !document.body.classList.contains(className);
			apply(hidden);
			save(name, hidden);
		});
	}
}
