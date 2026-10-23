<?php
/**
 * 頁尾。
 *
 * @package myLab
 */

defined( 'ABSPATH' ) || exit;
?>
</div><!-- .site-content -->

<footer class="site-footer">
	<?php if ( is_active_sidebar( 'sidebar-2' ) ) : ?>
		<div class="site-footer__widgets">
			<div class="container">
				<div class="row">
					<?php dynamic_sidebar( 'sidebar-2' ); ?>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<div class="container site-footer__bar">
		<div class="site-footer__copyright"><?php mylab_site_info(); ?></div>
		<div class="site-footer__links">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer-menu',
					'container'      => 'nav',
					'container_attr' => array( 'aria-label' => __( '頁尾選單', 'mylab' ) ),
					'menu_class'     => 'footer-menu',
					'depth'          => 1,
					'fallback_cb'    => false,
				)
			);
			the_privacy_policy_link();
			?>
		</div>
	</div>
</footer>

<?php if ( is_singular() && is_active_sidebar( 'sidebar-3' ) ) : ?>
	<button type="button" class="drawer-toggle d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebar-drawer" aria-controls="sidebar-drawer" aria-label="<?php esc_attr_e( '開啟目錄', 'mylab' ); ?>">
		<i class="bi bi-list-nested" aria-hidden="true"></i>
	</button>
<?php endif; ?>

<a href="#" class="go-top" aria-label="<?php esc_attr_e( '回到頂端', 'mylab' ); ?>"><i class="bi bi-arrow-up" aria-hidden="true"></i></a>

<?php wp_footer(); ?>
</body>
</html>
