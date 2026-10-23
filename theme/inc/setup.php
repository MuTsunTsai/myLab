<?php
/**
 * 佈景主題支援功能、選單位置與小工具區域。
 *
 * @package myLab
 */

defined( 'ABSPATH' ) || exit;

/**
 * 設定佈景主題支援的功能。
 */
function mylab_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 64,
			'width'       => 64,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
	// 首頁介紹區的背景圖
	add_theme_support(
		'custom-header',
		array(
			'width'       => 1920,
			'height'      => 640,
			'flex-width'  => true,
			'flex-height' => true,
			'header-text' => false,
		)
	);

	add_post_type_support( 'page', 'excerpt' );
	add_image_size( 'mylab-card', 720, 405, true );

	// 選單位置名稱沿用 mynote，切換佈景主題時 WordPress 會自動對應
	register_nav_menus(
		array(
			'header-menu' => __( '主選單', 'mylab' ),
			'footer-menu' => __( '頁尾選單', 'mylab' ),
		)
	);
}
add_action( 'after_setup_theme', 'mylab_setup' );

/**
 * 內容寬度，供 oEmbed 等功能參考。
 */
function mylab_content_width() {
	$GLOBALS['content_width'] = 860;
}
add_action( 'after_setup_theme', 'mylab_content_width', 0 );

/**
 * 註冊小工具區域。
 *
 * ID 沿用 mynote（sidebar-1、sidebar-2、sidebar-3、sidebar-7），
 * 切換佈景主題時原本的小工具會自動對應過來。
 */
function mylab_widgets_init() {
	$defaults = array(
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	);

	register_sidebar(
		array(
			'id'          => 'sidebar-1',
			'name'        => __( '文章側欄', 'mylab' ),
			'description' => __( '顯示在文章與頁面的側欄上方。', 'mylab' ),
		) + $defaults
	);

	register_sidebar(
		array(
			'id'          => 'sidebar-3',
			'name'        => __( '文章側欄（固定）', 'mylab' ),
			'description' => __( '接在文章側欄之後，捲動時固定在畫面上，適合放目錄。', 'mylab' ),
		) + $defaults
	);

	register_sidebar(
		array(
			'id'          => 'sidebar-7',
			'name'        => __( '彙整側欄', 'mylab' ),
			'description' => __( '顯示在分類、標籤、搜尋等彙整頁面。未放置小工具時彙整頁不顯示側欄。', 'mylab' ),
		) + $defaults
	);

	register_sidebar(
		array(
			'id'            => 'sidebar-2',
			'name'          => __( '頁尾', 'mylab' ),
			'description'   => __( '顯示在每個頁面的頁尾，會自動排成多欄。', 'mylab' ),
			'before_widget' => '<div class="col-12 col-md-6 col-lg"><section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section></div>',
		) + $defaults
	);

	register_widget( 'MyLab_Toc_Widget' );
}
add_action( 'widgets_init', 'mylab_widgets_init' );
