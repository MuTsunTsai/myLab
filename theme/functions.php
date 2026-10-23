<?php
/**
 * myLab 佈景主題的進入點；各項功能依職責拆分在 inc/ 底下。
 *
 * @package myLab
 */

defined( 'ABSPATH' ) || exit;

define( 'MYLAB_VERSION', wp_get_theme( get_template() )->get( 'Version' ) );
define( 'MYLAB_DIR', get_template_directory() );
define( 'MYLAB_URI', get_template_directory_uri() );

// 發佈新版用的 GitHub repo，需與 style.css 的 Update URI 一致
if ( ! defined( 'MYLAB_GITHUB_REPO' ) ) {
	define( 'MYLAB_GITHUB_REPO', 'MuTsunTsai/myLab' );
}

require MYLAB_DIR . '/inc/setup.php';
require MYLAB_DIR . '/inc/assets.php';
require MYLAB_DIR . '/inc/navigation.php';
require MYLAB_DIR . '/inc/template-tags.php';
require MYLAB_DIR . '/inc/template-functions.php';
require MYLAB_DIR . '/inc/comments.php';
require MYLAB_DIR . '/inc/customizer.php';
require MYLAB_DIR . '/inc/class-mylab-toc.php';
require MYLAB_DIR . '/inc/class-mylab-toc-widget.php';require MYLAB_DIR . '/inc/class-mylab-updater.php';

MyLab_Toc::init();
MyLab_Updater::init();
