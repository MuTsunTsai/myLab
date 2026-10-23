<?php
/**
 * 搜尋結果。
 *
 * @package myLab
 */

defined( 'ABSPATH' ) || exit;

get_header();

get_template_part(
	'template-parts/archive-header',
	null,
	array(
		'label'       => __( '搜尋', 'mylab' ),
		'title'       => get_search_query( false ),
		'search_form' => true,
	)
);

get_template_part( 'template-parts/post-grid' );

get_footer();
