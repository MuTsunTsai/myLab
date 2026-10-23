<?php
/**
 * 在前台停用 Easy Table of Contents 外掛，改用佈景主題內建的目錄（MyLab_Toc）。
 *
 * 外掛比佈景主題先載入，所以無法阻止它啟用，只能在頁面輸出前把它掛上的功能拔掉。
 * 這樣在預覽本佈景主題（外掛仍啟用中）時就能看到正確的結果，不會出現兩份目錄或重複的錨點；
 * 正式切換後仍建議停用該外掛。
 *
 * - 移除外掛在各個 hook 上的 callback（類別名稱以 ezTOC 開頭、或函式名稱以 eztoc_ 開頭）
 * - 側欄中的 Easy TOC 小工具原地換成「myLab：目錄」
 *
 * 以 Easy TOC 2.0.89 驗證。
 *
 * @package myLab
 */

defined( 'ABSPATH' ) || exit;

/** 取代 Easy TOC 小工具所用的虛擬小工具 ID */
const MYLAB_EZTOC_REPLACEMENT = 'mylab_toc-replace-eztoc';

/**
 * 是否需要處理（外掛啟用中，且為前台頁面）。
 *
 * @return bool
 */
function mylab_eztoc_active() {
	return class_exists( 'ezTOC' ) && ! is_admin();
}

/**
 * 判斷 callback 是否屬於 Easy TOC。
 *
 * @param callable $callback
 * @return bool
 */
function mylab_is_eztoc_callback( $callback ) {
	if ( is_string( $callback ) ) {
		return 0 === stripos( $callback, 'eztoc_' );
	}
	if ( is_array( $callback ) && isset( $callback[0] ) ) {
		$class = is_object( $callback[0] ) ? get_class( $callback[0] ) : (string) $callback[0];
		return 0 === stripos( $class, 'ezTOC' );
	}
	return false;
}

/**
 * 移除 Easy TOC 在所有 hook 上的 callback；在 wp 時執行，早於它使用的 template_redirect 與 wp_enqueue_scripts。
 */
function mylab_disable_eztoc_hooks() {
	global $wp_filter;
	if ( ! mylab_eztoc_active() ) {
		return;
	}
	foreach ( $wp_filter as $hook => $wp_hook ) {
		foreach ( $wp_hook->callbacks as $priority => $callbacks ) {
			foreach ( $callbacks as $callback ) {
				if ( mylab_is_eztoc_callback( $callback['function'] ) ) {
					remove_filter( $hook, $callback['function'], $priority );
				}
			}
		}
	}
	// 文章中若有手動插入的短代碼，輸出空白而不是顯示短代碼原文
	foreach ( array( 'ez-toc', 'toc', 'ez-toc-widget-sticky' ) as $tag ) {
		if ( shortcode_exists( $tag ) ) {
			add_shortcode( $tag, '__return_empty_string' );
		}
	}
}
add_action( 'wp', 'mylab_disable_eztoc_hooks' );

/**
 * 註冊取代用的虛擬小工具。
 */
function mylab_register_eztoc_replacement() {
	if ( ! mylab_eztoc_active() ) {
		return;
	}
	wp_register_sidebar_widget(
		MYLAB_EZTOC_REPLACEMENT,
		__( 'myLab：目錄', 'mylab' ),
		static function ( $args ) {
			( new MyLab_Toc_Widget() )->widget( $args, array() );
		},
		array( 'classname' => 'widget_mylab_toc' )
	);
}
add_action( 'widgets_init', 'mylab_register_eztoc_replacement', 20 );

/**
 * 側欄中的 Easy TOC 小工具換成「myLab：目錄」；同一個側欄已經有 myLab 目錄時則直接移除。
 *
 * @param array $sidebars
 * @return array
 */
function mylab_replace_eztoc_widgets( $sidebars ) {
	if ( ! mylab_eztoc_active() ) {
		return $sidebars;
	}
	foreach ( $sidebars as $sidebar => $widgets ) {
		if ( 'wp_inactive_widgets' === $sidebar || ! is_array( $widgets ) ) {
			continue;
		}
		$has_mylab = (bool) preg_grep( '/^mylab_toc-/', $widgets );
		$result    = array();
		foreach ( $widgets as $id ) {
			if ( preg_match( '/^(ezw_tco|ez_toc_widget_sticky)-/', $id ) ) {
				if ( ! $has_mylab ) {
					$result[]  = MYLAB_EZTOC_REPLACEMENT;
					$has_mylab = true;
				}
				continue;
			}
			$result[] = $id;
		}
		$sidebars[ $sidebar ] = $result;
	}
	return $sidebars;
}
add_filter( 'sidebars_widgets', 'mylab_replace_eztoc_widgets' );
