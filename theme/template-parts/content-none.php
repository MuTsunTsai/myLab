<?php
/**
 * 沒有文章時的內容。
 *
 * @package myLab
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="no-results">
	<i class="bi bi-inbox fs-1" aria-hidden="true"></i>
	<p class="mt-3">
		<?php
		if ( is_search() ) {
			esc_html_e( '找不到符合的文章，換個關鍵字試試看。', 'mylab' );
		} else {
			esc_html_e( '這裡還沒有文章。', 'mylab' );
		}
		?>
	</p>
	<?php get_search_form(); ?>
</div>
