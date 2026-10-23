/**
 * 以 Mermaid 渲染圖表，配色與佈景主題一致。
 */
import mermaid from "mermaid";

export async function renderDiagrams(blocks: HTMLElement[]): Promise<void> {
	const style = getComputedStyle(document.body);
	mermaid.initialize({
		startOnLoad: false,
		securityLevel: "strict",
		// 只有 base 主題能透過 themeVariables 完整自訂
		theme: "base",
		fontFamily: style.fontFamily,
		themeVariables: {
			darkMode: true,
			background: "#0f172a",
			fontSize: "15px",
			primaryColor: "#1e3a6e",
			primaryTextColor: "#e2e8f0",
			primaryBorderColor: "#3b82f6",
			secondaryColor: "#164e63",
			secondaryTextColor: "#e2e8f0",
			secondaryBorderColor: "#22d3ee",
			tertiaryColor: "#1e293b",
			tertiaryTextColor: "#e2e8f0",
			tertiaryBorderColor: "#475569",
			lineColor: "#60a5fa",
			textColor: "#cbd5e1",
			noteBkgColor: "#1e293b",
			noteTextColor: "#e2e8f0",
			noteBorderColor: "#475569",
			edgeLabelBackground: "#0f172a",
			clusterBkg: "#111a2e",
			clusterBorder: "#334155",
		},
	});

	const nodes = blocks.map(code => {
		const div = document.createElement("div");
		div.className = "mermaid";
		div.textContent = code.textContent;
		code.parentElement!.replaceWith(div);
		return div;
	});
	await mermaid.run({ nodes, suppressErrors: true });
}
