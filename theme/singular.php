<?php
/**
 * 單篇文章與頁面。
 *
 * @package myLab
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/entry-header' );
	?>
	<div class="container">
		<div class="row layout-row">
			<main id="main-container" class="<?php echo esc_attr( mylab_main_column_class() ); ?>">
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>
					<?php if ( has_post_thumbnail() ) : ?>
						<figure class="entry-thumbnail">
							<?php the_post_thumbnail( 'large' ); ?>
							<?php if ( get_the_post_thumbnail_caption() ) : ?>
								<figcaption><?php the_post_thumbnail_caption(); ?></figcaption>
							<?php endif; ?>
						</figure>
					<?php endif; ?>

					<div class="entry-content">
						<?php
						the_content();
						wp_link_pages(
							array(
								'before' => '<nav class="page-links">' . esc_html__( '分頁：', 'mylab' ),
								'after'  => '</nav>',
							)
						);
						?>
					</div>

					<?php mylab_sponsor(); ?>
					<?php get_template_part( 'template-parts/entry-footer' ); ?>
				</article>

				<?php
				if ( is_singular( 'post' ) ) {
					the_post_navigation(
						array(
							'prev_text' => '<span class="nav-label"><i class="bi bi-arrow-left" aria-hidden="true"></i> ' . esc_html__( '上一篇', 'mylab' ) . '</span><span class="nav-title">%title</span>',
							'next_text' => '<span class="nav-label">' . esc_html__( '下一篇', 'mylab' ) . ' <i class="bi bi-arrow-right" aria-hidden="true"></i></span><span class="nav-title">%title</span>',
						)
					);
				}

				comments_template();
				?>
			</main>

			<?php get_sidebar(); ?>
		</div>
	</div>
	<?php
endwhile;

get_footer();
