<?php
/**
 * 掛在 WordPress hook 上、調整輸出內容的函式。
 *
 * @package myLab
 */

defined( 'ABSPATH' ) || exit;

/**
 * 在 body 上加入版面相關的 class。
 *
 * @param string[] $classes
 * @return string[]
 */
function mylab_body_classes( $classes ) {
	if ( mylab_has_sidebar() ) {
		$classes[] = 'has-sidebar';
	}
	if ( is_singular() ) {
		$classes[] = sanitize_html_class( get_post_field( 'post_name' ) );
	}
	return $classes;
}
add_filter( 'body_class', 'mylab_body_classes' );

/**
 * 摘要長度（中文以字元計）。
 *
 * @return int
 */
function mylab_excerpt_length() {
	return 100;
}
add_filter( 'excerpt_length', 'mylab_excerpt_length', 999 );

/**
 * 摘要結尾；卡片本身就是連結，不需要「閱讀全文」。
 *
 * @return string
 */
function mylab_excerpt_more() {
	return '…';
}
add_filter( 'excerpt_more', 'mylab_excerpt_more' );

/**
 * 把 Yoast SEO 設定的主要分類排在最前面，麵包屑與卡片標籤都會以它為準。
 *
 * @param WP_Term[]  $categories
 * @param int|false  $post_id 呼叫 get_the_category() 時未指定文章則為 false，此時以目前文章為準。
 * @return WP_Term[]
 */
function mylab_primary_category_first( $categories, $post_id ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$primary = (int) get_post_meta( $post_id, '_yoast_wpseo_primary_category', true );
	if ( ! $primary ) {
		return $categories;
	}
	foreach ( $categories as $i => $term ) {
		if ( $term->term_id === $primary ) {
			array_splice( $categories, $i, 1 );
			array_unshift( $categories, $term );
			break;
		}
	}
	return $categories;
}
add_filter( 'get_the_categories', 'mylab_primary_category_first', 10, 2 );

/**
 * 文章內文中的連結一律開新分頁（站內錨點除外）。
 *
 * @param string $content
 * @return string
 */
function mylab_external_links( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	$html = new WP_HTML_Tag_Processor( $content );
	while ( $html->next_tag( 'a' ) ) {
		$href = $html->get_attribute( 'href' );
		if ( ! is_string( $href ) || '' === $href || false !== strpos( $href, '#' ) ) {
			continue;
		}
		$html->set_attribute( 'target', '_blank' );
		$rel = (string) $html->get_attribute( 'rel' );
		if ( false === strpos( $rel, 'noopener' ) ) {
			$html->set_attribute( 'rel', trim( $rel . ' noopener' ) );
		}
	}
	return $html->get_updated_html();
}
add_filter( 'the_content', 'mylab_external_links', 30 );

/**
 * 留言內容只保留基本的格式標籤，避免 XSS。
 * 在 wpautop（優先順序 30）之前執行，所以段落與換行不受影響。
 *
 * @param string $text
 * @return string
 */
function mylab_sanitize_comment( $text ) {
	return wp_kses_data( $text );
}
add_filter( 'comment_text', 'mylab_sanitize_comment', 5 );

/**
 * 不輸出「近期留言」小工具的 inline 樣式。
 */
add_filter( 'show_recent_comments_widget_style', '__return_false' );
