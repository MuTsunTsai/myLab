<?php
/**
 * 列表頁的標頭。
 *
 * 參數（$args）：
 * - label       小標，例如「分類」
 * - title       標題
 * - description 說明（HTML）
 * - search_form 是否顯示搜尋框
 *
 * @package myLab
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args,
	array(
		'label'       => '',
		'title'       => '',
		'description' => '',
		'search_form' => false,
	)
);

global $wp_query;
?>
<header class="archive-header">
	<div class="container">
		<?php if ( $args['label'] ) : ?>
			<p class="archive-header__label"><?php echo esc_html( $args['label'] ); ?></p>
		<?php endif; ?>
		<h1 class="archive-header__title"><?php echo esc_html( $args['title'] ); ?></h1>
		<?php if ( $args['description'] ) : ?>
			<div class="archive-header__desc"><?php echo wp_kses_post( $args['description'] ); ?></div>
		<?php endif; ?>
		<p class="archive-header__count">
			<?php
			/* translators: %s: 文章數 */
			echo esc_html( sprintf( __( '共 %s 篇文章', 'mylab' ), number_format_i18n( $wp_query->found_posts ) ) );
			?>
		</p>
		<?php
		if ( $args['search_form'] ) {
			get_search_form();
		}
		?>
	</div>
</header>
