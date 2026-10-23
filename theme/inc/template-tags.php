<?php
/**
 * 模板中使用的輸出函式。
 *
 * @package myLab
 */

defined( 'ABSPATH' ) || exit;

/**
 * 導覽列左側的品牌：自訂 logo 或網站圖示，加上網站名稱。
 */
function mylab_site_brand() {
	$logo_id = get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		$image = wp_get_attachment_image( $logo_id, 'full', false, array( 'alt' => '' ) );
	} elseif ( has_site_icon() ) {
		$image = sprintf( '<img src="%s" alt="" width="32" height="32">', esc_url( get_site_icon_url( 64 ) ) );
	} else {
		$image = '';
	}
	printf(
		'<a class="site-brand" href="%1$s" rel="home">%2$s<span class="site-brand__name">%3$s</span></a>',
		esc_url( home_url( '/' ) ),
		$image, // 已由 wp_get_attachment_image() 或上方的 esc_url 處理
		esc_html( get_bloginfo( 'name' ) )
	);
}

/**
 * 發佈日期。
 *
 * @param bool $relative 是否附加「N 天前」。
 */
function mylab_posted_on( $relative = false ) {
	printf(
		'<time datetime="%1$s" title="%2$s">%3$s</time>',
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_attr( get_the_date() . ' ' . get_the_time() ),
		esc_html( get_the_date() )
	);
	if ( $relative ) {
		printf(
			'<span>%s</span>',
			/* translators: %s: 經過的時間，例如「3 天」 */
			esc_html( sprintf( __( '%s前', 'mylab' ), human_time_diff( get_post_time( 'U', true ) ) ) )
		);
	}
}

/**
 * 估計閱讀時間（分鐘）。中文以每分鐘 400 字、英文以每分鐘 200 字計。
 *
 * @param int|WP_Post|null $post
 * @return int
 */
function mylab_reading_time( $post = null ) {
	$text  = wp_strip_all_tags( get_post_field( 'post_content', $post ) );
	$cjk   = preg_match_all( '/[\p{Han}\p{Hiragana}\p{Katakana}]/u', $text );
	$words = preg_match_all( '/[A-Za-z0-9]+/', $text );
	return max( 1, (int) ceil( $cjk / 400 + $words / 200 ) );
}

/**
 * 頂層分類列表（依名稱排序，含空分類），用來決定每個分類的配色。
 *
 * @return WP_Term[]
 */
function mylab_top_categories() {
	static $categories = null;
	if ( null === $categories ) {
		$categories = get_categories(
			array(
				'parent'     => 0,
				'hide_empty' => false,
			)
		);
	}
	return $categories;
}

/**
 * 分類的配色編號（0–9）。子分類沿用其頂層分類的顏色。
 *
 * @param WP_Term $term
 * @return int
 */
function mylab_category_color( $term ) {
	$ancestors = get_ancestors( $term->term_id, 'category', 'taxonomy' );
	$top_id    = $ancestors ? end( $ancestors ) : $term->term_id;
	foreach ( mylab_top_categories() as $i => $category ) {
		if ( $category->term_id === $top_id ) {
			return $i % 10;
		}
	}
	return 0;
}

/**
 * 分類標籤的 HTML。
 *
 * @param WP_Term $term
 * @return string
 */
function mylab_category_chip( $term ) {
	return sprintf(
		'<a class="chip chip-%1$d" href="%2$s">%3$s</a>',
		mylab_category_color( $term ),
		esc_url( get_category_link( $term ) ),
		esc_html( $term->name )
	);
}

/**
 * 目前文章的分類標籤（主要分類在前，見 mylab_primary_category_first()）。
 *
 * @param int $limit 最多顯示幾個。
 */
function mylab_post_category_chips( $limit = 2 ) {
	foreach ( array_slice( get_the_category(), 0, $limit ) as $term ) {
		echo mylab_category_chip( $term ); // phpcs:ignore WordPress.Security.EscapeOutput -- 已於函式內跳脫
	}
}

/**
 * 首頁介紹區的分類標籤（只列頂層分類）。可閱讀私密文章的使用者會連空分類一併列出。
 */
function mylab_category_labels() {
	$categories = get_categories(
		array(
			'parent'     => 0,
			'hide_empty' => ! current_user_can( 'read_private_posts' ),
		)
	);
	foreach ( $categories as $term ) {
		echo mylab_category_chip( $term ); // phpcs:ignore WordPress.Security.EscapeOutput -- 已於函式內跳脫
	}
}

/**
 * 文章頁的麵包屑：首頁 › 分類 › 文章；頁面則列出上層頁面。
 *
 * 分類以主要分類（排在第一個，見 mylab_primary_category_first()）為準：
 * - 主要分類是子分類時，列出它的上層分類鏈
 * - 主要分類是頂層分類時，若文章也屬於它底下的某個子分類，再接上該子分類
 */
function mylab_breadcrumb() {
	$items = array(
		array( home_url( '/' ), '<i class="bi bi-house-door" aria-hidden="true"></i><span class="visually-hidden">' . esc_html__( '首頁', 'mylab' ) . '</span>' ),
	);

	if ( is_singular( 'post' ) ) {
		$terms = get_the_category();
		$chain = array();
		if ( $terms ) {
			$primary = $terms[0];
			if ( $primary->parent ) {
				foreach ( array_reverse( get_ancestors( $primary->term_id, 'category', 'taxonomy' ) ) as $ancestor_id ) {
					$chain[] = get_category( $ancestor_id );
				}
				$chain[] = $primary;
			} else {
				$chain[] = $primary;
				foreach ( $terms as $term ) {
					if ( $term->parent === $primary->term_id ) {
						$chain[] = $term;
						break;
					}
				}
			}
		}
		foreach ( $chain as $term ) {
			$items[] = array( get_category_link( $term ), esc_html( $term->name ) );
		}
	} elseif ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_post() ) ) as $ancestor ) {
			$items[] = array( get_permalink( $ancestor ), esc_html( get_the_title( $ancestor ) ) );
		}
	}

	echo '<nav aria-label="' . esc_attr__( '麵包屑', 'mylab' ) . '"><ol class="breadcrumb">';
	foreach ( $items as $item ) {
		printf( '<li class="breadcrumb-item"><a href="%1$s">%2$s</a></li>', esc_url( $item[0] ), $item[1] ); // phpcs:ignore WordPress.Security.EscapeOutput -- 標籤文字已跳脫
	}
	printf( '<li class="breadcrumb-item active" aria-current="page">%s</li>', esc_html( get_the_title() ) );
	echo '</ol></nav>';
}

/**
 * 目前頁面是否顯示側欄。
 *
 * @return bool
 */
function mylab_has_sidebar() {
	if ( is_singular() ) {
		return is_active_sidebar( 'sidebar-1' ) || is_active_sidebar( 'sidebar-3' );
	}
	if ( is_archive() || is_search() ) {
		return is_active_sidebar( 'sidebar-7' );
	}
	return false;
}

/**
 * 主欄的 class。
 *
 * @return string
 */
function mylab_main_column_class() {
	return mylab_has_sidebar() ? 'col-lg-8' : 'col-12';
}

/**
 * 文章標頭的作者與日期。
 */
function mylab_entry_byline() {
	$author_id = (int) get_the_author_meta( 'ID' );
	?>
	<div class="entry-byline">
		<?php echo get_avatar( $author_id, 80, '', '', array( 'class' => 'entry-byline__avatar' ) ); ?>
		<div>
			<a class="entry-byline__author" href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>"><?php the_author(); ?></a>
			<div class="entry-byline__details">
				<?php mylab_posted_on( true ); ?>
				<span>
					<?php
					/* translators: %d: 分鐘數 */
					echo esc_html( sprintf( __( '約 %d 分鐘', 'mylab' ), mylab_reading_time() ) );
					?>
				</span>
			</div>
		</div>
	</div>
	<?php
}

/**
 * 文章標頭右側的按鈕：版面切換、編輯、留言數。
 */
function mylab_entry_actions() {
	echo '<div class="entry-actions">';

	if ( mylab_has_sidebar() ) {
		printf(
			'<button type="button" class="action-btn d-none d-lg-inline-flex" data-mylab-toggle="sidebar" aria-pressed="true" title="%1$s"><i class="bi bi-layout-sidebar-inset-reverse" aria-hidden="true"></i><span class="visually-hidden">%1$s</span></button>',
			esc_attr__( '顯示側欄', 'mylab' )
		);
		if ( is_active_sidebar( 'sidebar-1' ) && is_active_sidebar( 'sidebar-3' ) ) {
			printf(
				'<button type="button" class="action-btn d-none d-lg-inline-flex" data-mylab-toggle="widgets" aria-pressed="true" title="%1$s"><i class="bi bi-list-ul" aria-hidden="true"></i><span class="visually-hidden">%1$s</span></button>',
				esc_attr__( '顯示一般側欄小工具（關閉時只保留目錄）', 'mylab' )
			);
		}
	}

	if ( current_user_can( 'edit_post', get_the_ID() ) ) {
		printf(
			'<a class="action-btn" href="%1$s"><i class="bi bi-pencil-square" aria-hidden="true"></i>%2$s</a>',
			esc_url( get_edit_post_link() ),
			esc_html__( '編輯', 'mylab' )
		);
	}

	if ( comments_open() || get_comments_number() ) {
		printf(
			'<a class="action-btn" href="#comments" title="%1$s"><i class="bi bi-chat-dots" aria-hidden="true"></i><span class="action-btn__count">%2$s</span></a>',
			esc_attr__( '留言', 'mylab' ),
			esc_html( number_format_i18n( get_comments_number() ) )
		);
	}

	echo '</div>';
}

/**
 * 文末的贊助區塊（Buy Me a Coffee），可在「自訂」中設定或關閉。
 */
function mylab_sponsor() {
	$slug = get_theme_mod( 'mylab_sponsor_slug', 'mutsuntsai' );
	if ( ! $slug ) {
		return;
	}
	$button = add_query_arg(
		array(
			'text'           => 'Buy me a coffee',
			'emoji'          => '☕',
			'slug'           => $slug,
			'button_colour'  => '6f431f',
			'font_colour'    => 'ffffff',
			'font_family'    => 'Lato',
			'outline_colour' => 'ffffff',
			'coffee_colour'  => 'FFDD00',
		),
		'https://img.buymeacoffee.com/button-api/'
	);
	?>
	<section class="sponsor">
		<p><?php echo esc_html( get_theme_mod( 'mylab_sponsor_text', __( '我的文章對您有幫助嗎？請我喝杯咖啡吧：', 'mylab' ) ) ); ?></p>
		<a href="<?php echo esc_url( 'https://www.buymeacoffee.com/' . rawurlencode( $slug ) ); ?>" target="_blank" rel="noopener">
			<img width="235" height="50" loading="lazy" src="<?php echo esc_url( $button ); ?>" alt="Buy me a coffee">
		</a>
	</section>
	<?php
}

/**
 * 列表頁的分頁。
 */
function mylab_pagination() {
	global $wp_query;
	$total = (int) $wp_query->max_num_pages;
	if ( $total < 2 ) {
		return;
	}
	$current = max( 1, (int) get_query_var( 'paged' ) );
	$links   = paginate_links(
		array(
			'type'      => 'array',
			'mid_size'  => 2,
			'prev_text' => '<i class="bi bi-chevron-left" aria-hidden="true"></i><span class="visually-hidden">' . esc_html__( '上一頁', 'mylab' ) . '</span>',
			'next_text' => '<i class="bi bi-chevron-right" aria-hidden="true"></i><span class="visually-hidden">' . esc_html__( '下一頁', 'mylab' ) . '</span>',
		)
	);
	if ( ! $links ) {
		return;
	}

	echo '<nav class="pagination-wrapper" aria-label="' . esc_attr__( '分頁', 'mylab' ) . '"><ul class="pagination justify-content-center flex-wrap">';
	printf( '<li class="page-item disabled d-none d-lg-block"><span class="page-link">%1$d / %2$d</span></li>', (int) $current, (int) $total );
	foreach ( $links as $link ) {
		$state = '';
		if ( false !== strpos( $link, 'current' ) ) {
			$state = ' active';
		} elseif ( false !== strpos( $link, 'dots' ) ) {
			$state = ' disabled';
		}
		$link = str_replace( 'page-numbers', 'page-link', $link );
		echo '<li class="page-item' . esc_attr( $state ) . '">' . $link . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput -- paginate_links() 的輸出
	}
	echo '</ul></nav>';
}

/**
 * 頁尾的版權宣告。
 */
function mylab_site_info() {
	printf(
		/* translators: 1: 年份, 2: 網站名稱連結 */
		esc_html__( 'Copyright © %1$s %2$s. All rights reserved.', 'mylab' ),
		esc_html( wp_date( 'Y' ) ),
		'<strong><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( get_bloginfo( 'name' ) ) . '</a></strong>'
	);
}
