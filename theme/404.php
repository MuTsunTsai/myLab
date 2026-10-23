<?php
/**
 * 找不到頁面。
 *
 * @package myLab
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-container" class="container error-404">
	<p class="error-404__code" aria-hidden="true">404</p>
	<h1 class="error-404__title"><?php esc_html_e( '找不到這個頁面', 'mylab' ); ?></h1>
	<p class="text-body-secondary"><?php esc_html_e( '網址可能有誤，或是頁面已經移除。試試搜尋看看：', 'mylab' ); ?></p>
	<?php get_search_form(); ?>
	<a class="btn btn-outline-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<i class="bi bi-house-door" aria-hidden="true"></i> <?php esc_html_e( '回到首頁', 'mylab' ); ?>
	</a>
</main>
<?php
get_footer();
