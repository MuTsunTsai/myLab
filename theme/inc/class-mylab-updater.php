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
		// 在後台按下「再次檢查」時一併清除快取
		add_action( 'delete_site_transient_update_themes', array( __CLASS__, 'flush' ) );
		add_action( 'upgrader_process_complete', array( __CLASS__, 'flush' ) );
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
	 * 取得最新的 Release（快取 6 小時；失敗時快取 1 小時，避免頻繁打 API）。
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

		set_site_transient( self::TRANSIENT, $release ? $release : array(), $release ? 6 * HOUR_IN_SECONDS : HOUR_IN_SECONDS );
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
