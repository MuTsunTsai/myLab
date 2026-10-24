<?php
/**
 * 從 GitHub Releases 更新佈景主題。
 *
 * style.css 的 Update URI 指向 github.com，WordPress（6.1+）便會透過
 * update_themes_github.com filter 詢問新版本，而不是去 WordPress.org 查詢。
 * 新版本由 GitHub Actions 在推送 v* tag 時建置並上傳為 Release 附件（mylab.zip），
 * 見 .github/workflows/release.yml。
 *
 * repo 若為私有，在 wp-config.php 定義 MYLAB_GITHUB_TOKEN（需有 Contents 讀取權限）。
 *
 * @package myLab
 */

defined( 'ABSPATH' ) || exit;

final class MyLab_Updater {

	const ASSET_NAME = 'mylab.zip';
	const TRANSIENT  = 'mylab_github_release';

	public static function init() {
		add_filter( 'update_themes_github.com', array( __CLASS__, 'check_update' ), 10, 3 );
		add_filter( 'http_request_args', array( __CLASS__, 'authorize' ), 10, 2 );
		// 在「控制台 › 更新」按下「再次檢查」時清除快取；必須早於 WordPress 在同一個 hook 上執行的 wp_update_themes()
		add_action( 'load-update-core.php', array( __CLASS__, 'flush_on_force_check' ), 1 );
		add_action( 'delete_site_transient_update_themes', array( __CLASS__, 'flush' ) );
		add_action( 'upgrader_process_complete', array( __CLASS__, 'after_upgrade' ), 10, 2 );
	}

	/**
	 * 更新（或以上傳 zip 覆蓋安裝）本佈景主題後：清除版本快取，並清除頁面快取。
	 *
	 * 打包後的 CSS／JS 檔名帶有 hash，更新時舊檔會被刪除；頁面快取若仍指向舊檔，頁面就會失去樣式。
	 * 目前網站使用 WP Fastest Cache，透過它的公開勾點清除全部快取（含壓縮過的 CSS／JS）；
	 * 沒安裝該外掛時，這個勾點不會有任何作用。
	 *
	 * @param WP_Upgrader $upgrader
	 * @param array       $options
	 */
	public static function after_upgrade( $upgrader, $options ) {
		self::flush();
		if ( ! isset( $options['type'] ) || 'theme' !== $options['type'] ) {
			return;
		}
		$themes  = isset( $options['themes'] ) ? (array) $options['themes'] : array();
		$is_ours = in_array( get_template(), $themes, true )
			|| ( isset( $upgrader->result['destination_name'] ) && get_template() === $upgrader->result['destination_name'] );
		if ( $is_ours ) {
			do_action( 'wpfc_clear_all_cache', true );
		}
	}

	public static function flush_on_force_check() {
		if ( ! empty( $_GET['force-check'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- 只是清除快取
			self::flush();
		}
	}

	/**
	 * 回報可用的新版本。
	 *
	 * @param array|false $update
	 * @param array       $theme_data style.css 的標頭資料。
	 * @param string      $stylesheet 佈景主題的資料夾名稱。
	 * @return array|false
	 */
	public static function check_update( $update, $theme_data, $stylesheet ) {
		if ( get_template() !== $stylesheet ) {
			return $update;
		}
		$release = self::latest_release();
		if ( ! $release ) {
			return $update;
		}
		return array(
			'theme'   => $stylesheet,
			'version' => $release['version'],
			'url'     => $release['url'],
			'package' => $release['package'],
		);
	}

	/**
	 * 取得最新的 Release，結果快取 1 小時。
	 * 「控制台 › 更新」頁面每次開啟都可能重新檢查，快取可避免超過 GitHub API 未驗證請求每小時 60 次的限制。
	 *
	 * @return array{version: string, url: string, package: string}|null
	 */
	private static function latest_release() {
		$cached = get_site_transient( self::TRANSIENT );
		if ( is_array( $cached ) ) {
			return empty( $cached['version'] ) ? null : $cached;
		}

		$release  = null;
		$response = wp_remote_get(
			'https://api.github.com/repos/' . MYLAB_GITHUB_REPO . '/releases/latest',
			array(
				'timeout' => 10,
				'headers' => array( 'Accept' => 'application/vnd.github+json' ),
			)
		);
		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
			$data = json_decode( wp_remote_retrieve_body( $response ), true );
			foreach ( isset( $data['assets'] ) ? $data['assets'] : array() as $asset ) {
				if ( self::ASSET_NAME !== $asset['name'] ) {
					continue;
				}
				$release = array(
					'version' => ltrim( (string) $data['tag_name'], 'v' ),
					'url'     => (string) $data['html_url'],
					// 私有 repo 的附件只能透過 API 下載
					'package' => self::token() ? (string) $asset['url'] : (string) $asset['browser_download_url'],
				);
				break;
			}
		}

		set_site_transient( self::TRANSIENT, $release ? $release : array(), HOUR_IN_SECONDS );
		return $release;
	}

	/**
	 * 對 GitHub API 的請求附上 token（私有 repo 才需要）。
	 *
	 * @param array  $args
	 * @param string $url
	 * @return array
	 */
	public static function authorize( $args, $url ) {
		$token = self::token();
		if ( ! $token || 0 !== strpos( $url, 'https://api.github.com/repos/' . MYLAB_GITHUB_REPO . '/' ) ) {
			return $args;
		}
		$args['headers']['Authorization'] = 'Bearer ' . $token;
		if ( false !== strpos( $url, '/releases/assets/' ) ) {
			$args['headers']['Accept'] = 'application/octet-stream';
		}
		return $args;
	}

	public static function flush() {
		delete_site_transient( self::TRANSIENT );
	}

	/**
	 * @return string
	 */
	private static function token() {
		return defined( 'MYLAB_GITHUB_TOKEN' ) ? (string) MYLAB_GITHUB_TOKEN : '';
	}
}
