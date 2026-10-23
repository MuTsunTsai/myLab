<?php
/**
 * 留言區。
 *
 * @package myLab
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() || ( ! comments_open() && ! get_comments_number() ) ) {
	return;
}
?>
<section id="comments" class="comments-area">
	<h2 class="section-title">
		<?php
		/* translators: %s: 留言數 */
		echo esc_html( sprintf( __( '留言（%s）', 'mylab' ), number_format_i18n( get_comments_number() ) ) );
		?>
	</h2>

	<?php if ( have_comments() ) : ?>
		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'    => 'ol',
					'callback' => 'mylab_comment',
				)
			);
			?>
		</ol>
		<?php
		the_comments_pagination(
			array(
				'prev_text' => '<i class="bi bi-chevron-left" aria-hidden="true"></i><span class="visually-hidden">' . esc_html__( '較舊的留言', 'mylab' ) . '</span>',
				'next_text' => '<span class="visually-hidden">' . esc_html__( '較新的留言', 'mylab' ) . '</span><i class="bi bi-chevron-right" aria-hidden="true"></i>',
			)
		);
		?>
	<?php endif; ?>

	<?php if ( ! comments_open() ) : ?>
		<p class="comments-closed"><?php esc_html_e( '留言功能已關閉。', 'mylab' ); ?></p>
	<?php endif; ?>

	<?php comment_form(); ?>
</section>
