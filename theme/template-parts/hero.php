<?php
/**
 * 首頁介紹區：網站名稱、描述與分類標籤；背景為「自訂 › 頁首圖片」。
 *
 * @package myLab
 */

defined( 'ABSPATH' ) || exit;

$mylab_style = has_header_image() ? '--hero-image: url(' . esc_url( get_header_image() ) . ');' : '';
?>
<section class="hero" style="<?php echo esc_attr( $mylab_style ); ?>">
	<div class="container">
		<div class="row align-items-center g-4">
			<div class="col-md-7 text-center text-md-start">
				<h1 class="hero__title"><?php bloginfo( 'name' ); ?><span class="hero__caret" aria-hidden="true"></span></h1>
				<p class="hero__desc"><?php bloginfo( 'description' ); ?></p>
			</div>
			<div class="col-md-5">
				<nav class="hero__chips" aria-label="<?php esc_attr_e( '文章分類', 'mylab' ); ?>">
					<?php mylab_category_labels(); ?>
				</nav>
			</div>
		</div>
	</div>
	<a class="hero__scroll" href="#main-container" aria-label="<?php esc_attr_e( '捲動至文章列表', 'mylab' ); ?>"><i class="bi bi-chevron-down" aria-hidden="true"></i></a>
</section>
