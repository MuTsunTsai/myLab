<?php
/**
 * 文章卡片。整張卡片可點擊（標題連結為 stretched-link），分類與底部連結另外疊在上層。
 *
 * @package myLab
 */

defined( 'ABSPATH' ) || exit;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="post-card__thumb">
			<?php the_post_thumbnail( 'mylab-card', array( 'alt' => '' ) ); ?>
		</div>
	<?php endif; ?>

	<div class="post-card__body">
		<?php if ( has_category() ) : ?>
			<div class="post-card__cats"><?php mylab_post_category_chips(); ?></div>
		<?php endif; ?>

		<h2 class="post-card__title">
			<?php if ( is_sticky() && is_home() ) : ?>
				<i class="bi bi-pin-angle-fill" title="<?php esc_attr_e( '置頂文章', 'mylab' ); ?>"></i>
			<?php endif; ?>
			<a class="stretched-link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>

		<div class="post-card__excerpt"><?php the_excerpt(); ?></div>
	</div>

	<footer class="post-card__footer">
		<span><i class="bi bi-calendar3" aria-hidden="true"></i><?php mylab_posted_on(); ?></span>
		<?php if ( comments_open() || get_comments_number() ) : ?>
			<a href="<?php comments_link(); ?>" title="<?php esc_attr_e( '留言', 'mylab' ); ?>">
				<i class="bi bi-chat-dots" aria-hidden="true"></i><?php echo esc_html( number_format_i18n( get_comments_number() ) ); ?>
			</a>
		<?php endif; ?>
		<?php if ( current_user_can( 'edit_post', get_the_ID() ) ) : ?>
			<a class="post-card__edit" href="<?php echo esc_url( get_edit_post_link() ); ?>">
				<i class="bi bi-pencil-square" aria-hidden="true"></i><?php esc_html_e( '編輯', 'mylab' ); ?>
			</a>
		<?php endif; ?>
	</footer>
</article>
