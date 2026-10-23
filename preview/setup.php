<?php
/**
 * 本機預覽（pnpm preview）的測試資料：模擬 WP Githuber MD 輸出的文章，以及分類、小工具、選單、留言。
 * 由 preview/blueprint.json 在 WordPress Playground 啟動時執行。
 *
 * @package myLab
 */

require '/wordpress/wp-load.php';

// 以管理員身分建立內容；否則會經過 kses 過濾，例如註腳的 href="#fn:x" 會被當成未知協定而刪掉
wp_set_current_user( 1 );

update_option( 'blogname', '星君研究室' );
update_option( 'blogdescription', '熱愛開源開發的全端工程師部落格' );

// WordPress 安裝時建立的隱私權政策頁預設是草稿，發佈後頁尾才會出現連結
wp_update_post(
	array(
		'ID'          => (int) get_option( 'wp_page_for_privacy_policy' ),
		'post_title'  => '隱私權政策',
		'post_status' => 'publish',
	)
);
switch_theme( 'mylab' );

$cats = array();
foreach ( array( '初體驗', '前端技術', '後端技術', '演算法', '程式語言', '踩坑記', '開發分享', '開發工具', '隨筆' ) as $name ) {
	$cats[ $name ] = wp_insert_term( $name, 'category' )['term_id'];
}
$child = wp_insert_term( 'BPS', 'category', array( 'parent' => $cats['開發分享'] ) )['term_id'];

$content = <<<'HTML'
<p>在本系列的第 16 篇裡頭，我分享了 BPS 從 Shrewd 反應式框架轉向「分層式更新」的始末，參見 <a href="https://example.com">外部連結</a> 與 <code>inline code</code>，以及行內數學 <code class="katex-inline">e^{i\pi}+1=0</code>。</p>
<h2>paper.js 復活了</h2>
<p>這是一段說明文字，<strong>粗體</strong>與 <a href="#測試方法">站內錨點</a>。paper.js 已經好幾年沒有更新了<sup id="fnref1:paper"><a href="#fn:paper" class="footnote-ref">1</a></sup>，所以我一度考慮換成其它函式庫<sup id="fnref1:alt"><a href="#fn:alt" class="footnote-ref">2</a></sup>。</p>
<pre><code class="language-ts">// TypeScript 範例
export function add(a: number, b: number): number {
	return a + b;
}
</code></pre>
<h3>Razor 元件</h3>
<pre><code class="language-razor">@page "/counter"
&lt;MyComponent Value="@count" /&gt;
@code { int count = 0; }
</code></pre>
<h2>新的布林運算演算法</h2>
<blockquote><p>引用文字：這是一段引言。</p></blockquote>
<pre><code class="language-katex">\int_0^\infty e^{-x^2}\,dx = \frac{\sqrt{\pi}}{2}</code></pre>
<h3>只能用外框來篩選</h3>
<table><thead><tr><th>方法</th><th>時間</th></tr></thead><tbody><tr><td>舊</td><td>120ms</td></tr><tr><td>新</td><td>8ms</td></tr></tbody></table>
<h2>測試方法</h2>
<pre><code class="language-mermaid">flowchart TD
	A[開始] --> B{判斷}
	B -->|是| C[分層式更新]
	B -->|否| D[反應式框架]
</code></pre>
<pre><code>沒有指定語言的程式碼</code></pre>
<h2>測試結果</h2>
<ul><li>項目一</li><li>項目二<ul><li>巢狀項目</li></ul></li></ul>
<p>結尾段落，再引用一次第一個註解<sup id="fnref2:paper"><a href="#fn:paper" class="footnote-ref">1</a></sup>。</p>
<div class="footnotes">
<hr />
<ol>
<li id="fn:paper">
<p>最後一個正式版本是 0.12.17，之後的修正都只存在於 <code>develop</code> 分支上。&#160;<a href="#fnref1:paper" rev="footnote" class="footnote-backref">&#8617;</a> <a href="#fnref2:paper" rev="footnote" class="footnote-backref">&#8617;</a></p>
</li>
<li id="fn:alt">
<p>例如 <a href="https://github.com/luncheon/polybooljs">polybooljs</a> 或 Clipper2；不過它們各自都有一些限制，這部分會在後面的段落詳細說明。這是一段比較長的註解，用來測試預覽框在內容較多時的換行與寬度。&#160;<a href="#fnref1:alt" rev="footnote" class="footnote-backref">&#8617;</a></p>
</li>
</ol>
</div>
HTML;

// wp_insert_post() 會先 unslash，所以要先 wp_slash() 才能保留 KaTeX 的反斜線
$post_id = wp_insert_post(
	wp_slash(
		array(
			'post_title'    => 'BPS 開發分享之 25：再戰 paper.js 布林運算',
			'post_content'  => $content,
			'post_status'   => 'publish',
			'post_category' => array( $cats['開發分享'], $child, $cats['演算法'] ),
			'tags_input'    => array( 'paper.js', 'TypeScript' ),
			'post_date'     => '2026-10-22 10:00:00',
			// Yoast SEO 的主要分類；麵包屑與卡片標籤以它為準
			'meta_input'    => array( '_yoast_wpseo_primary_category' => $cats['演算法'] ),
		)
	)
);
for ( $i = 1; $i <= 14; $i++ ) {
	$keys = array_keys( $cats );
	wp_insert_post(
		array(
			'post_title'    => "測試文章 {$i}：一個稍微長一點的標題用來測試換行",
			'post_content'  => str_repeat( '<p>這是測試用的內文，用來產生摘要。Lorem ipsum dolor sit amet。</p>', 8 ),
			'post_status'   => 'publish',
			'post_category' => array( $cats[ $keys[ $i % 9 ] ] ),
			'post_date'     => sprintf( '2026-%02d-10 10:00:00', ( $i % 9 ) + 1 ),
		)
	);
}

$c1 = wp_insert_comment( array( 'comment_post_ID' => $post_id, 'comment_author' => '讀者甲', 'comment_content' => "很棒的文章！\n\n第二段<script>alert(1)</script>", 'comment_approved' => 1 ) );
wp_insert_comment( array( 'comment_post_ID' => $post_id, 'comment_author' => 'admin', 'user_id' => 1, 'comment_parent' => $c1, 'comment_content' => '謝謝支持。', 'comment_approved' => 1 ) );
wp_insert_comment( array( 'comment_post_ID' => $post_id, 'comment_author' => '讀者乙', 'comment_content' => '請問 paper.js 的版本？', 'comment_approved' => 1 ) );

// 小工具：文章側欄放近期文章與分類，固定側欄放目錄
update_option( 'widget_recent-posts', array( 2 => array( 'title' => '近期文章', 'number' => 5 ), '_multiwidget' => 1 ) );
update_option( 'widget_categories', array( 2 => array( 'title' => '分類' ), '_multiwidget' => 1 ) );
update_option( 'widget_mylab_toc', array( 2 => array( 'title' => '' ), '_multiwidget' => 1 ) );
update_option(
	'sidebars_widgets',
	array(
		'wp_inactive_widgets' => array(),
		'sidebar-1'           => array( 'recent-posts-2', 'categories-2' ),
		'sidebar-3'           => array( 'mylab_toc-2' ),
		'array_version'       => 3,
	)
);

$menu = wp_create_nav_menu( '主選單' );
wp_update_nav_menu_item( $menu, 0, array( 'menu-item-title' => '首頁', 'menu-item-url' => home_url( '/' ), 'menu-item-status' => 'publish' ) );
wp_update_nav_menu_item( $menu, 0, array( 'menu-item-title' => '關於站長', 'menu-item-url' => home_url( '/about/' ), 'menu-item-status' => 'publish' ) );
set_theme_mod( 'nav_menu_locations', array( 'header-menu' => $menu ) );

echo "post: $post_id\n";
