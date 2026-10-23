<?php
/**
 * 文章卡片網格、分頁與側欄（首頁、彙整頁、搜尋共用）。
 *
 * @package myLab
 */

defined( 'ABSPATH' ) || exit;

// 有側欄時主欄較窄，最多兩欄
$mylab_cols = mylab_has_sidebar() ? 'row-cols-1 row-cols-md-2' : 'row-cols-1 row-cols-md-2 row-cols-lg-3';
?>
<div class="container post-grid">
	<div class="row layout-row">
		<main id="main-container" class="<?php echo esc_attr( mylab_main_column_class() ); ?>">
			<?php if ( have_posts() ) : ?>
				<div class="row g-4 <?php echo esc_attr( $mylab_cols ); ?>">
					<?php
					while ( have_posts() ) :
						the_post();
						?>
						<div class="col">
							<?php get_template_part( 'template-parts/post-card' ); ?>
						</div>
					<?php endwhile; ?>
				</div>
				<?php mylab_pagination(); ?>
			<?php else : ?>
				<?php get_template_part( 'template-parts/content-none' ); ?>
			<?php endif; ?>
		</main>

		<?php get_sidebar(); ?>
	</div>
</div>
