<?php
/**
 * 側欄。文章與頁面使用「文章側欄」＋「文章側欄（固定）」，彙整頁使用「彙整側欄」。
 *
 * 固定側欄在 lg 以下會變成從右側拉出的抽屜（Bootstrap offcanvas-lg），由 footer.php 中的按鈕開啟。
 *
 * @package myLab
 */

defined( 'ABSPATH' ) || exit;

if ( ! mylab_has_sidebar() ) {
	return;
}
?>
<aside id="aside-container" class="col-lg-4 site-sidebar" aria-label="<?php esc_attr_e( '側欄', 'mylab' ); ?>">
	<?php if ( is_singular() ) : ?>
		<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
			<div id="sidebar">
				<?php dynamic_sidebar( 'sidebar-1' ); ?>
			</div>
		<?php endif; ?>
		<?php if ( is_active_sidebar( 'sidebar-3' ) ) : ?>
			<div class="sidebar-sticky">
				<div id="sidebar-drawer" class="offcanvas-lg offcanvas-end sidebar-drawer" tabindex="-1" aria-labelledby="sidebar-drawer-title">
					<div class="offcanvas-header">
						<h2 id="sidebar-drawer-title" class="offcanvas-title"><?php esc_html_e( '目錄', 'mylab' ); ?></h2>
						<button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebar-drawer" aria-label="<?php esc_attr_e( '關閉', 'mylab' ); ?>"></button>
					</div>
					<div class="offcanvas-body">
						<?php dynamic_sidebar( 'sidebar-3' ); ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<?php dynamic_sidebar( 'sidebar-7' ); ?>
	<?php endif; ?>
</aside>
