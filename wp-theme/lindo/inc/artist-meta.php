<?php
/**
 * Artist のメタフィールド。
 *
 * 保存キー（すべて単一値）:
 *   _lindo_name_sub  サブ名 / かな
 *   _lindo_role      役職・カテゴリ（例: Visual Creative / Styling）
 *   _lindo_profile   プロフィール本文（改行→段落）
 *   _lindo_ig        Instagram URL
 *   _lindo_x         X (Twitter) URL
 *   _lindo_yt        YouTube URL
 *   _lindo_web       Website URL
 *   _lindo_gallery   グリッド画像の添付ID（カンマ区切り）
 *
 * @package LINDO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * メタフィールド定義（キー => 入力種別）。
 *
 * @return array
 */
function lindo_artist_text_fields() {
	return array(
		'_lindo_name_sub' => array(
			'label' => __( 'サブ名 / かな', 'lindo' ),
			'type'  => 'text',
			'desc'  => __( '見出しの下に小さく出る補足名（例: オーロラ）', 'lindo' ),
		),
		'_lindo_role'     => array(
			'label' => __( '役職・カテゴリ', 'lindo' ),
			'type'  => 'text',
			'desc'  => __( '例: Visual Creative / Styling', 'lindo' ),
		),
		'_lindo_profile'  => array(
			'label' => __( 'プロフィール', 'lindo' ),
			'type'  => 'textarea',
			'desc'  => __( '空行で段落が分かれます。', 'lindo' ),
		),
		'_lindo_ig'       => array(
			'label' => __( 'Instagram URL', 'lindo' ),
			'type'  => 'url',
		),
		'_lindo_x'        => array(
			'label' => __( 'X (Twitter) URL', 'lindo' ),
			'type'  => 'url',
		),
		'_lindo_yt'       => array(
			'label' => __( 'YouTube URL', 'lindo' ),
			'type'  => 'url',
		),
		'_lindo_web'      => array(
			'label' => __( 'Website URL', 'lindo' ),
			'type'  => 'url',
		),
	);
}

/**
 * メタボックスを登録。
 */
function lindo_add_artist_metaboxes() {
	add_meta_box(
		'lindo_artist_info',
		__( 'アーティスト情報', 'lindo' ),
		'lindo_render_artist_info_box',
		'artist',
		'normal',
		'high'
	);
	add_meta_box(
		'lindo_artist_gallery',
		__( 'Instagram風グリッド画像', 'lindo' ),
		'lindo_render_artist_gallery_box',
		'artist',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'lindo_add_artist_metaboxes' );

/**
 * 情報メタボックスの描画。
 *
 * @param WP_Post $post 投稿。
 */
function lindo_render_artist_info_box( $post ) {
	wp_nonce_field( 'lindo_artist_save', 'lindo_artist_nonce' );
	echo '<div class="lindo-meta">';
	foreach ( lindo_artist_text_fields() as $key => $field ) {
		$value = get_post_meta( $post->ID, $key, true );
		$id    = 'fld' . $key;
		echo '<p class="lindo-meta-row">';
		printf(
			'<label for="%s"><strong>%s</strong></label>',
			esc_attr( $id ),
			esc_html( $field['label'] )
		);
		if ( 'textarea' === $field['type'] ) {
			printf(
				'<textarea id="%s" name="%s" rows="6" class="widefat">%s</textarea>',
				esc_attr( $id ),
				esc_attr( $key ),
				esc_textarea( $value )
			);
		} else {
			$input_type = ( 'url' === $field['type'] ) ? 'url' : 'text';
			printf(
				'<input type="%s" id="%s" name="%s" value="%s" class="widefat" />',
				esc_attr( $input_type ),
				esc_attr( $id ),
				esc_attr( $key ),
				esc_attr( $value )
			);
		}
		if ( ! empty( $field['desc'] ) ) {
			printf( '<span class="description">%s</span>', esc_html( $field['desc'] ) );
		}
		echo '</p>';
	}
	echo '</div>';
}

/**
 * グリッド画像メタボックスの描画（wp.media ピッカー）。
 *
 * @param WP_Post $post 投稿。
 */
function lindo_render_artist_gallery_box( $post ) {
	$ids_raw = get_post_meta( $post->ID, '_lindo_gallery', true );
	$ids     = array_filter( array_map( 'absint', explode( ',', (string) $ids_raw ) ) );
	?>
	<div class="lindo-gallery" data-lindo-gallery>
		<p class="description">
			<?php esc_html_e( '正方形に切り出して3列で表示されます。表示したい順に並べてください（ドラッグで並び替え）。', 'lindo' ); ?>
		</p>
		<input type="hidden" name="_lindo_gallery" value="<?php echo esc_attr( implode( ',', $ids ) ); ?>" data-gallery-input />
		<ul class="lindo-gallery-list" data-gallery-list>
			<?php foreach ( $ids as $id ) : ?>
				<?php $thumb = wp_get_attachment_image( $id, array( 110, 110 ) ); ?>
				<?php if ( $thumb ) : ?>
					<li data-id="<?php echo esc_attr( $id ); ?>">
						<?php echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image はエスケープ済み。 ?>
						<button type="button" class="lindo-gallery-remove" aria-label="<?php esc_attr_e( '削除', 'lindo' ); ?>">×</button>
					</li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>
		<p>
			<button type="button" class="button button-primary" data-gallery-add>
				<?php esc_html_e( '画像を選択 / 追加', 'lindo' ); ?>
			</button>
			<button type="button" class="button" data-gallery-clear>
				<?php esc_html_e( 'すべて外す', 'lindo' ); ?>
			</button>
		</p>
	</div>
	<?php
}

/**
 * メタの保存。
 *
 * @param int $post_id 投稿ID。
 */
function lindo_save_artist_meta( $post_id ) {
	if ( ! isset( $_POST['lindo_artist_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_key( $_POST['lindo_artist_nonce'] ), 'lindo_artist_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// テキスト系。
	foreach ( lindo_artist_text_fields() as $key => $field ) {
		$raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
		if ( 'url' === $field['type'] ) {
			$clean = esc_url_raw( trim( $raw ) );
		} elseif ( 'textarea' === $field['type'] ) {
			$clean = sanitize_textarea_field( $raw );
		} else {
			$clean = sanitize_text_field( $raw );
		}
		if ( '' === $clean ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $clean );
		}
	}

	// グリッド画像（添付IDのカンマ区切り）。
	$gallery_raw = isset( $_POST['_lindo_gallery'] ) ? wp_unslash( $_POST['_lindo_gallery'] ) : '';
	$gallery_ids = array_filter( array_map( 'absint', explode( ',', (string) $gallery_raw ) ) );
	if ( empty( $gallery_ids ) ) {
		delete_post_meta( $post_id, '_lindo_gallery' );
	} else {
		update_post_meta( $post_id, '_lindo_gallery', implode( ',', $gallery_ids ) );
	}
}
add_action( 'save_post_artist', 'lindo_save_artist_meta' );
