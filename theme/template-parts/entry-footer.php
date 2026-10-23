<?php
/**
 * 文末：最後更新時間與標籤。
 *
 * @package myLab
 */

defined( 'ABSPATH' ) || exit;
?>
<footer class="entry-footer">
	<p class="entry-updated">
		<i class="bi bi-clock-history" aria-hidden="true"></i><?php esc_html_e( '最後更新：', 'mylab' ); ?>
		<time datetime="<?php echo esc_attr( get_the_modified_date( DATE_W3C ) ); ?>"><?php the_modified_date(); ?></time>
	</p>
	<?php if ( has_tag() ) : ?>
		<div class="entry-tags"><?php the_tags( '', '' ); ?></div>
	<?php endif; ?>
</footer>
