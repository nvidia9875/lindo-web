<?php
/**
 * Artist 表示用データ整形（プレゼンテーション契約）。
 *
 * WP固有の取得をここに閉じ込め、template-parts には「素の配列」を渡す。
 * これにより同じ部品をスタンドアロン・プレビュー（preview/render.php）でも使える。
 *
 * 返す配列の形（lindo_get_artist_data）:
 *   id, slug, index, name, name_sub, role,
 *   tags[], profile[] (段落), portrait{url,w,h,alt}|null,
 *   gallery[]{url,w,h,alt}, links[]{label,url}
 *
 * @package LINDO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 1アーティストの表示用データを組み立てる。
 *
 * @param int|WP_Post $post  投稿。
 * @param int         $index 表示インデックス（1始まり）。
 * @return array
 */
function lindo_get_artist_data( $post, $index = 0 ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return array();
	}
	$id = $post->ID;

	$role = (string) get_post_meta( $id, '_lindo_role', true );
	$tags = array();
	foreach ( preg_split( '#[/、,]#u', $role ) as $piece ) {
		$piece = trim( $piece );
		if ( '' !== $piece ) {
			$tags[] = $piece;
		}
	}

	$profile_raw = (string) get_post_meta( $id, '_lindo_profile', true );
	$profile     = array();
	foreach ( preg_split( "/\n\s*\n/", trim( $profile_raw ) ) as $para ) {
		$para = trim( $para );
		if ( '' !== $para ) {
			$profile[] = $para;
		}
	}

	return array(
		'id'       => $id,
		'slug'     => 'artist-' . $id,
		'index'    => $index ? sprintf( '%02d', $index ) : '',
		'name'     => get_the_title( $id ),
		'name_sub' => (string) get_post_meta( $id, '_lindo_name_sub', true ),
		'role'     => $role,
		'tags'     => $tags,
		'profile'  => $profile,
		'portrait' => lindo_attachment_data( get_post_thumbnail_id( $id ), 'lindo-portrait' ),
		'gallery'  => lindo_artist_gallery_data( $id ),
		'links'    => lindo_artist_links( $id ),
	);
}

/**
 * 添付画像を {url,w,h,alt} に整形。
 *
 * @param int    $attachment_id 添付ID。
 * @param string $size          画像サイズ。
 * @return array|null
 */
function lindo_attachment_data( $attachment_id, $size = 'large' ) {
	$attachment_id = absint( $attachment_id );
	if ( ! $attachment_id ) {
		return null;
	}
	$src = wp_get_attachment_image_src( $attachment_id, $size );
	if ( ! $src ) {
		return null;
	}
	return array(
		'url' => $src[0],
		'w'   => (int) $src[1],
		'h'   => (int) $src[2],
		'alt' => (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
	);
}

/**
 * グリッド画像の配列。
 *
 * @param int $post_id 投稿ID。
 * @return array
 */
function lindo_artist_gallery_data( $post_id ) {
	$ids_raw = get_post_meta( $post_id, '_lindo_gallery', true );
	$ids     = array_filter( array_map( 'absint', explode( ',', (string) $ids_raw ) ) );
	$out     = array();
	foreach ( $ids as $id ) {
		$data = lindo_attachment_data( $id, 'lindo-square' );
		if ( $data ) {
			$out[] = $data;
		}
	}
	return $out;
}

/**
 * SNS / Web リンクの配列。
 *
 * @param int $post_id 投稿ID。
 * @return array
 */
function lindo_artist_links( $post_id ) {
	$map  = array(
		'_lindo_ig'  => 'Instagram',
		'_lindo_x'   => 'X',
		'_lindo_yt'  => 'YouTube',
		'_lindo_web' => 'Website',
	);
	$out = array();
	foreach ( $map as $key => $label ) {
		$url = (string) get_post_meta( $post_id, $key, true );
		if ( '' !== $url ) {
			$out[] = array(
				'label' => $label,
				'url'   => $url,
			);
		}
	}
	return $out;
}

/**
 * フロント表示用に全アーティストを取得して配列化。
 *
 * @param int $limit 取得件数（-1 で全件）。
 * @return array<int,array>
 */
function lindo_get_all_artists( $limit = -1 ) {
	$query = new WP_Query(
		array(
			'post_type'      => 'artist',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'orderby'        => 'menu_order date',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		)
	);

	$artists = array();
	$i       = 0;
	foreach ( $query->posts as $post ) {
		$i++;
		$artists[] = lindo_get_artist_data( $post, $i );
	}
	wp_reset_postdata();
	return $artists;
}
