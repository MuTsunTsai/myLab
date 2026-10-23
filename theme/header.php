<?php
/**
 * 頁首：<head> 與頂部導覽列。
 *
 * @package myLab
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<?php // data-mylab：讓網站其它地方的自訂 script 能判斷目前是否為 myLab 佈景主題 ?>
<html <?php language_attributes(); ?> data-bs-theme="dark" data-mylab>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#0a0f1c">
	<?php // 本身就是暗色設計；請 Dark Reader 等擴充功能不要改寫樣式（它會改名 CSS 變數，導致首頁圖片等失效） ?>
	<meta name="darkreader-lock">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link visually-hidden-focusable" href="#main-container"><?php esc_html_e( '跳至主要內容', 'mylab' ); ?></a>

<header id="site-header" class="site-header">
	<nav class="navbar navbar-expand-lg" aria-label="<?php esc_attr_e( '主選單', 'mylab' ); ?>">
		<div class="container">
			<?php mylab_site_brand(); ?>

			<?php if ( is_singular() ) : ?>
				<span class="site-header__post-title" aria-hidden="true"><?php single_post_title(); ?></span>
			<?php endif; ?>

			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#primary-nav" aria-controls="primary-nav" aria-expanded="false" aria-label="<?php esc_attr_e( '切換選單', 'mylab' ); ?>">
				<i class="bi bi-list" aria-hidden="true"></i>
			</button>

			<div id="primary-nav" class="collapse navbar-collapse">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'header-menu',
						'container'      => false,
						'menu_class'     => 'navbar-nav me-auto',
						'depth'          => 2,
						'fallback_cb'    => 'mylab_fallback_menu',
					)
				);
				get_search_form();
				?>
			</div>
		</div>
	</nav>

	<?php if ( is_singular() ) : ?>
		<div class="reading-progress" aria-hidden="true"><div class="reading-progress__bar"></div></div>
	<?php endif; ?>
</header>

<div class="site-content">
