<?php
/**
 * 目錄（取代 Easy Table of Contents 外掛）。
 *
 * - 為內文的 h2–h4 加上 id，格式與 Easy TOC 相同，所以既有的錨點連結都還能用
 * - 標題數量足夠時，在第一個標題前插入可折疊的目錄
 * - 提供目錄資料給側欄小工具（MyLab_Toc_Widget）
 * - 捲動時的目前章節標示由 src/ts/toc.ts 處理
 *
 * @package myLab
 */

defined( 'ABSPATH' ) || exit;

final class MyLab_Toc {

	/** 比對 h2–h4 的正規表示式 */
	const HEADING_PATTERN = '#<h([2-4])(\s[^>]*)?>(.*?)</h\1>#is';

	/**
	 * 依文章 ID 快取的目錄項目。
	 *
	 * @var array<int, array>
	 */
	private static $cache = array();

	public static function init() {
		// 在 wpautop、shortcode 之後執行
		add_filter( 'the_content', array( __CLASS__, 'filter_content' ), 20 );
	}

	/**
	 * 為標題加上 id，並視需要插入文內目錄。
	 *
	 * @param string $content
	 * @return string
	 */
	public static function filter_content( $content ) {
		if ( ! is_singular() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$result = self::parse( $content );
		self::$cache[ get_the_ID() ] = $result['items'];

		$min = max( 1, (int) get_theme_mod( 'mylab_toc_min_headings', 4 ) );
		if ( ! get_theme_mod( 'mylab_toc_inline', true ) || count( $result['items'] ) < $min ) {
			return $result['content'];
		}

		$toc = sprintf(
			'<details class="toc toc--inline" open><summary>%1$s</summary>%2$s</details>',
			esc_html__( '目錄', 'mylab' ),
			self::render_list( $result['items'] )
		);
		$pos = strpos( $result['content'], '<h' . $result['items'][0]['level'] );
		return substr_replace( $result['content'], $toc, (int) $pos, 0 );
	}

	/**
	 * 取得文章的目錄項目。內文已經過 the_content 時直接使用快取，否則從原始內容解析。
	 *
	 * @param int|null $post_id
	 * @return array<int, array{level: int, id: string, text: string}>
	 */
	public static function get_items( $post_id = null ) {
		$post_id = $post_id ? (int) $post_id : get_the_ID();
		if ( ! isset( self::$cache[ $post_id ] ) ) {
			$parsed                  = self::parse( (string) get_post_field( 'post_content', $post_id ) );
			self::$cache[ $post_id ] = $parsed['items'];
		}
		return self::$cache[ $post_id ];
	}

	/**
	 * 解析內文中的標題。
	 *
	 * @param string $content
	 * @return array{content: string, items: array}
	 */
	public static function parse( $content ) {
		$items = array();
		$used  = array();

		$content = preg_replace_callback(
			self::HEADING_PATTERN,
			static function ( $m ) use ( &$items, &$used ) {
				$level = (int) $m[1];
				$attrs = isset( $m[2] ) ? $m[2] : '';
				$text  = trim( html_entity_decode( wp_strip_all_tags( $m[3] ), ENT_QUOTES, 'UTF-8' ) );
				if ( '' === $text ) {
					return $m[0];
				}

				if ( preg_match( '/\sid=(["\'])(.*?)\1/i', $attrs, $id_match ) ) {
					$id = $id_match[2];
				} else {
					$id    = self::unique_id( self::generate_id( $text ), $used );
					$attrs = ' id="' . esc_attr( $id ) . '"' . $attrs;
				}
				$used[ $id ] = true;

				$items[] = array(
					'level' => $level,
					'id'    => $id,
					'text'  => $text,
				);
				return '<h' . $level . $attrs . '>' . $m[3] . '</h' . $level . '>';
			},
			$content
		);

		return array(
			'content' => (string) $content,
			'items'   => $items,
		);
	}

	/**
	 * 依標題文字產生 id，演算法與 Easy TOC 相同（已用網站上的既有文章驗證過）。
	 * 結果是經過 rawurlencode 的字串，例如「paper.js 復活了」→「paperjs_%E5%BE%A9%E6%B4%BB%E4%BA%86」。
	 *
	 * @param string $text
	 * @return string
	 */
	public static function generate_id( $text ) {
		$id = remove_accents( $text );
		$id = preg_replace( '/[\x00-\x1F\x7F]/u', '', $id );
		$id = str_replace( array( '*', "'", '(', ')', ';', '@', '&', '=', '+', '$', ',', '/', '?', '#', '[', ']', '%', '{', '}', '|', '\\', '^', '~', '`', '.', '!', '"', ':', '<', '>' ), '', $id );
		$id = preg_replace( '/\s+/u', '_', trim( $id ) );
		$id = preg_replace( '/_+/', '_', $id );
		return '' === $id ? 'section' : rawurlencode( $id );
	}

	/**
	 * 避免同一篇文章中出現重複的 id。
	 *
	 * @param string $id
	 * @param array  $used
	 * @return string
	 */
	private static function unique_id( $id, $used ) {
		$candidate = $id;
		for ( $i = 2; isset( $used[ $candidate ] ); $i++ ) {
			$candidate = $id . '-' . $i;
		}
		return $candidate;
	}

	/**
	 * 將平面的目錄項目輸出為巢狀的 <ol>。
	 *
	 * @param array $items
	 * @return string
	 */
	public static function render_list( $items ) {
		if ( ! $items ) {
			return '';
		}

		// 每一層 <ol> 所對應的標題等級；跳級（例如 h2 直接接 h4）時只內縮一層
		$stack = array();
		$html  = '';
		foreach ( $items as $item ) {
			$level = $item['level'];
			if ( ! $stack || $level > end( $stack ) ) {
				$html   .= '<ol>';
				$stack[] = $level;
			} else {
				$html .= '</li>';
				while ( count( $stack ) > 1 && $level < end( $stack ) ) {
					array_pop( $stack );
					$html .= '</ol>';
					if ( $level > end( $stack ) ) {
						$html   .= '<ol>';
						$stack[] = $level;
						break;
					}
					$html .= '</li>';
				}
			}
			$html .= sprintf( '<li><a href="#%1$s">%2$s</a>', esc_attr( $item['id'] ), esc_html( $item['text'] ) );
		}
		$html .= '</li>' . str_repeat( '</ol></li>', count( $stack ) - 1 ) . '</ol>';
		return $html;
	}
}
