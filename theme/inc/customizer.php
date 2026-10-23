<?php
/**
 * 「自訂」中的佈景主題選項。刻意只保留少數實用的設定，其餘版面都固定在程式中。
 *
 * @package myLab
 */

defined( 'ABSPATH' ) || exit;

/**
 * 註冊選項。
 *
 * @param WP_Customize_Manager $wp_customize
 */
function mylab_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'mylab_options',
		array(
			'title'    => __( 'myLab 佈景主題', 'mylab' ),
			'priority' => 130,
		)
	);

	$wp_customize->add_setting(
		'mylab_toc_inline',
		array(
			'default'           => true,
			'sanitize_callback' => 'wp_validate_boolean',
		)
	);
	$wp_customize->add_control(
		'mylab_toc_inline',
		array(
			'section'     => 'mylab_options',
			'type'        => 'checkbox',
			'label'       => __( '在文章開頭顯示目錄', 'mylab' ),
			'description' => __( '標題數量達到下方門檻時，於第一個標題前插入可折疊的目錄。側欄目錄請使用「myLab：目錄」小工具。', 'mylab' ),
		)
	);

	$wp_customize->add_setting(
		'mylab_toc_min_headings',
		array(
			'default'           => 4,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		'mylab_toc_min_headings',
		array(
			'section'     => 'mylab_options',
			'type'        => 'number',
			'label'       => __( '文內目錄的最少標題數', 'mylab' ),
			'input_attrs' => array(
				'min' => 1,
				'max' => 20,
			),
		)
	);

	$wp_customize->add_setting(
		'mylab_sponsor_slug',
		array(
			'default'           => 'mutsuntsai',
			'sanitize_callback' => 'sanitize_title',
		)
	);
	$wp_customize->add_control(
		'mylab_sponsor_slug',
		array(
			'section'     => 'mylab_options',
			'type'        => 'text',
			'label'       => __( 'Buy Me a Coffee 帳號', 'mylab' ),
			'description' => __( '顯示在文章與頁面的結尾；留空則不顯示。', 'mylab' ),
		)
	);

	$wp_customize->add_setting(
		'mylab_sponsor_text',
		array(
			'default'           => __( '我的文章對您有幫助嗎？請我喝杯咖啡吧：', 'mylab' ),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'mylab_sponsor_text',
		array(
			'section' => 'mylab_options',
			'type'    => 'text',
			'label'   => __( '贊助區塊的說明文字', 'mylab' ),
		)
	);
}
add_action( 'customize_register', 'mylab_customize_register' );
