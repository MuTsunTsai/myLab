<?php
/**
 * 搜尋表單。
 *
 * @package myLab
 */

defined( 'ABSPATH' ) || exit;

$mylab_search_id = wp_unique_id( 'search-' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="visually-hidden" for="<?php echo esc_attr( $mylab_search_id ); ?>"><?php esc_html_e( '搜尋：', 'mylab' ); ?></label>
	<div class="input-group input-group-sm">
		<span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
		<input type="search" id="<?php echo esc_attr( $mylab_search_id ); ?>" class="form-control" name="s" value="<?php echo get_search_query(); ?>" placeholder="<?php esc_attr_e( '搜尋文章…', 'mylab' ); ?>">
	</div>
</form>
