/**
 * 以 Pele 渲染 Mermaid 語法的圖表。Pele 比 Mermaid 小很多、也快很多；
 * 各圖表類型的程式由 pele/lazy 按需載入。配色由 CSS 變數 --pele-* 控制（見 _code.scss）。
 *
 * 為了與 Mermaid 的行為一致，交給 Pele 之前先做這些處理：
 * - Mermaid 的字元實體（#emsp;、#9829; 等）先轉成實際字元；Pele 會把它們原樣顯示出來
 * - 關閉節點文字的自動換行（wrappingWidth）；Mermaid 對一般標籤只依 <br/> 換行，Pele 預設在 200px 處折行
 */
import { mountAsync, type MountOptions } from "pele/lazy";

const OPTIONS: MountOptions = {
	// 內建的縮放按鈕會蓋到圖的右上角
	zoom: false,
	config: {
		flowchart: { wrappingWidth: 10000 },
	},
};

/** 把 Mermaid 的字元實體（#名稱; 或 #數字;）轉成實際字元；無法辨識的保持原樣 */
function decodeEntities(src: string): string {
	const decoder = document.createElement("textarea");
	return src.replace(/#(\w+);/g, (match, name: string) => {
		decoder.innerHTML = `&${/^\d+$/.test(name) ? "#" : ""}${name};`;
		const value = decoder.value;
		return value.startsWith("&") ? match : value;
	});
}

export async function renderDiagrams(blocks: HTMLElement[]): Promise<void> {
	await Promise.all(blocks.map(async code => {
		const source = code.textContent!;
		const pre = code.parentElement!;
		const div = document.createElement("div");
		div.className = "diagram";
		pre.replaceWith(div);
		try {
			await mountAsync(div, decodeEntities(source), OPTIONS);
		} catch(e) {
			// 無法渲染時改回顯示原始碼，至少內容還在
			console.error("[myLab] 無法渲染圖表", e);
			div.replaceWith(pre);
		}
	}));
}
