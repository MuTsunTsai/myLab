<?php
/**
 * 留言列表與留言表單的標記。
 *
 * @package myLab
 */

defined( 'ABSPATH' ) || exit;

/**
 * wp_list_comments() 的 callback。結尾的 </li> 由 Walker_Comment 輸出。
 *
 * @param WP_Comment $comment
 * @param array      $args
 * @param int        $depth
 */
function mylab_comment( $comment, $args, $depth ) {
	$post      = get_post( $comment->comment_post_ID );
	$is_author = $post && $comment->user_id && (int) $comment->user_id === (int) $post->post_author;
	$classes   = array( 'comment-item' );
	if ( ! empty( $args['has_children'] ) ) {
		$classes[] = 'parent';
	}

	if ( in_array( $comment->comment_type, array( 'pingback', 'trackback' ), true ) ) {
		?>
		<li id="comment-<?php comment_ID(); ?>" <?php comment_class( $classes, $comment ); ?>>
			<div class="comment-card">
				<div class="comment-card__body">
					<?php esc_html_e( 'Pingback：', 'mylab' ); ?>
					<?php comment_author_link( $comment ); ?>
					<?php edit_comment_link( '<i class="bi bi-pencil" aria-hidden="true"></i><span class="visually-hidden">' . esc_html__( '編輯', 'mylab' ) . '</span>' ); ?>
				</div>
			</div>
		<?php
		return;
	}
	?>
	<li id="comment-<?php comment_ID(); ?>" <?php comment_class( $classes, $comment ); ?>>
		<article class="comment-card<?php echo $is_author ? ' comment-card--author' : ''; ?>">
			<header class="comment-card__header">
				<?php echo get_avatar( $comment, 72, '', '', array( 'class' => 'comment-card__avatar' ) ); ?>
				<div class="comment-card__meta">
					<cite class="comment-card__author"><?php comment_author_link( $comment ); ?></cite>
					<?php if ( $is_author ) : ?>
						<span class="comment-card__badge"><?php esc_html_e( '作者', 'mylab' ); ?></span>
					<?php endif; ?>
					<a class="comment-card__time" href="<?php echo esc_url( get_comment_link( $comment ) ); ?>">
						<time datetime="<?php comment_time( DATE_W3C ); ?>" title="<?php echo esc_attr( get_comment_date( '', $comment ) . ' ' . get_comment_time() ); ?>">
							<?php
							/* translators: %s: 經過的時間，例如「3 天」 */
							echo esc_html( sprintf( __( '%s前', 'mylab' ), human_time_diff( get_comment_time( 'U', true ) ) ) );
							?>
						</time>
					</a>
				</div>
				<div class="comment-card__actions">
					<?php
					comment_reply_link(
						array_merge(
							$args,
							array(
								'reply_text' => '<i class="bi bi-reply" aria-hidden="true"></i><span class="visually-hidden">' . esc_html__( '回覆', 'mylab' ) . '</span>',
								'depth'      => $depth,
								'max_depth'  => $args['max_depth'],
								'before'     => '',
								'after'      => '',
							)
						)
					);
					edit_comment_link( '<i class="bi bi-pencil" aria-hidden="true"></i><span class="visually-hidden">' . esc_html__( '編輯', 'mylab' ) . '</span>' );
					?>
				</div>
			</header>
			<div class="comment-card__body">
				<?php if ( '0' === $comment->comment_approved ) : ?>
					<p class="comment-card__moderation"><?php esc_html_e( '您的留言正在等待審核。', 'mylab' ); ?></p>
				<?php endif; ?>
				<?php comment_text(); ?>
			</div>
		</article>
	<?php
}

/**
 * 留言表單的設定。
 *
 * @param array $defaults
 * @return array
 */
function mylab_comment_form_defaults( $defaults ) {
	return array_merge(
		$defaults,
		array(
			'title_reply'          => __( '發表留言', 'mylab' ),
			'title_reply_to'       => __( '回覆給 %s', 'mylab' ),
			'title_reply_before'   => '<h3 id="reply-title" class="section-title comment-reply-title">',
			'title_reply_after'    => '</h3>',
			'cancel_reply_link'    => __( '取消回覆', 'mylab' ),
			'label_submit'         => __( '送出', 'mylab' ),
			'class_submit'         => 'btn btn-primary',
			'submit_button'        => '<button name="%1$s" type="submit" id="%2$s" class="%3$s"><i class="bi bi-send" aria-hidden="true"></i> %4$s</button>',
			'comment_notes_before' => '<p class="comment-notes">' . esc_html__( '發佈留言必須填寫的電子郵件地址不會公開。您的留言可能會在審核之後才出現在頁面上。', 'mylab' ) . '</p>',
			'comment_notes_after'  => '',
			'comment_field'        => sprintf(
				'<div class="comment-form-comment"><label class="visually-hidden" for="comment">%1$s</label><textarea id="comment" name="comment" class="form-control" rows="6" maxlength="65525" placeholder="%2$s" required></textarea></div>',
				esc_html__( '留言', 'mylab' ),
				esc_attr__( '留言內容…', 'mylab' )
			),
		)
	);
}
add_filter( 'comment_form_defaults', 'mylab_comment_form_defaults' );

/**
 * 留言者資料欄位。保留 WordPress 的 Cookie 同意欄位。
 *
 * @param array $fields
 * @return array
 */
function mylab_comment_form_fields( $fields ) {
	$commenter = wp_get_current_commenter();
	$required  = (bool) get_option( 'require_name_email' );

	$input = static function ( $name, $type, $icon, $placeholder, $value, $maxlength, $is_required ) {
		return sprintf(
			'<div class="comment-form-%1$s"><div class="input-group"><span class="input-group-text"><i class="bi bi-%3$s" aria-hidden="true"></i></span><label class="visually-hidden" for="%1$s">%4$s</label><input id="%1$s" name="%1$s" type="%2$s" class="form-control" placeholder="%4$s" value="%5$s" maxlength="%6$d"%7$s></div></div>',
			esc_attr( $name ),
			esc_attr( $type ),
			esc_attr( $icon ),
			esc_attr( $placeholder ),
			esc_attr( $value ),
			(int) $maxlength,
			$is_required ? ' required' : ''
		);
	};

	$custom = array(
		'author' => $input( 'author', 'text', 'person', $required ? __( '名稱（必填）', 'mylab' ) : __( '名稱', 'mylab' ), $commenter['comment_author'], 245, $required ),
		'email'  => $input( 'email', 'email', 'envelope', $required ? __( '電子郵件（必填）', 'mylab' ) : __( '電子郵件', 'mylab' ), $commenter['comment_author_email'], 100, $required ),
		'url'    => $input( 'url', 'url', 'globe2', __( '網站（非必填）', 'mylab' ), $commenter['comment_author_url'], 200, false ),
	);
	if ( isset( $fields['cookies'] ) ) {
		$custom['cookies'] = $fields['cookies'];
	}
	return $custom;
}
add_filter( 'comment_form_default_fields', 'mylab_comment_form_fields' );

/**
 * 把留言內容欄移到名稱、信箱欄位之後（WordPress 預設放在最前面）。
 *
 * @param array $fields
 * @return array
 */
function mylab_comment_field_order( $fields ) {
	if ( isset( $fields['comment'] ) ) {
		$comment = $fields['comment'];
		unset( $fields['comment'] );
		$cookies = null;
		if ( isset( $fields['cookies'] ) ) {
			$cookies = $fields['cookies'];
			unset( $fields['cookies'] );
		}
		$fields['comment'] = $comment;
		if ( null !== $cookies ) {
			$fields['cookies'] = $cookies;
		}
	}
	return $fields;
}
add_filter( 'comment_form_fields', 'mylab_comment_field_order' );
