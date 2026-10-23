<?php
/**
 * 彙整頁：分類、標籤、作者、日期。
 *
 * @package myLab
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( is_category() ) {
	$mylab_label = __( '分類', 'mylab' );
	$mylab_title = single_term_title( '', false );
} elseif ( is_tag() ) {
	$mylab_label = __( '標籤', 'mylab' );
	$mylab_title = single_term_title( '', false );
} elseif ( is_author() ) {
	$mylab_label = __( '作者', 'mylab' );
	$mylab_title = get_the_author_meta( 'display_name', (int) get_queried_object_id() );
} else {
	$mylab_label = __( '彙整', 'mylab' );
	$mylab_title = wp_strip_all_tags( get_the_archive_title() );
}

get_template_part(
	'template-parts/archive-header',
	null,
	array(
		'label'       => $mylab_label,
		'title'       => $mylab_title,
		'description' => get_the_archive_description(),
	)
);

get_template_part( 'template-parts/post-grid' );

get_footer();
