<?php
/**
 * 首頁（最新文章列表），也是其它模板都不符合時的預設模板。
 *
 * @package myLab
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( is_home() && ! is_paged() ) {
	get_template_part( 'template-parts/hero' );
} else {
	get_template_part(
		'template-parts/archive-header',
		null,
		array(
			'label' => __( '文章列表', 'mylab' ),
			/* translators: %d: 頁碼 */
			'title' => sprintf( __( '第 %d 頁', 'mylab' ), max( 1, (int) get_query_var( 'paged' ) ) ),
		)
	);
}

get_template_part( 'template-parts/post-grid' );

get_footer();
