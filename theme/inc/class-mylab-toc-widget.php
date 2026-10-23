<?php
/**
 * 「myLab：目錄」小工具，建議放在「文章側欄（固定）」區域。
 *
 * @package myLab
 */

defined( 'ABSPATH' ) || exit;

class MyLab_Toc_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'mylab_toc',
			__( 'myLab：目錄', 'mylab' ),
			array(
				'classname'                   => 'widget_mylab_toc',
				'description'                 => __( '文章的目錄，會標示目前閱讀到的章節。只在文章與頁面中顯示。', 'mylab' ),
				'customize_selective_refresh' => true,
			)
		);
	}

	/**
	 * 前台輸出。
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		if ( ! is_singular() ) {
			return;
		}
		$items = MyLab_Toc::get_items( get_queried_object_id() );
		if ( ! $items ) {
			return;
		}

		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( '目錄', 'mylab' );
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput -- 由 register_sidebar 定義
		echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput
		printf(
			'<nav class="toc toc--widget" aria-label="%1$s">%2$s</nav>',
			esc_attr( $title ),
			MyLab_Toc::render_list( $items ) // phpcs:ignore WordPress.Security.EscapeOutput -- 已於函式內跳脫
		);
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
	}

	/**
	 * 後台設定表單。
	 *
	 * @param array $instance
	 * @return string
	 */
	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( '標題：', 'mylab' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" placeholder="<?php esc_attr_e( '目錄', 'mylab' ); ?>">
		</p>
		<?php
		return '';
	}

	/**
	 * 儲存設定。
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		return array(
			'title' => sanitize_text_field( isset( $new_instance['title'] ) ? $new_instance['title'] : '' ),
		);
	}
}
