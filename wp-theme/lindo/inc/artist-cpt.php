<?php
/**
 * Artist カスタム投稿タイプ。
 *
 * 各アーティスト = 1投稿。
 *   - タイトル        : 表示名（例 AURORA）
 *   - アイキャッチ画像 : ポートレート（一覧カード＋モーダル見出し）
 *   - メタ            : サブ名 / 役職 / プロフィール / SNSリンク / グリッド画像（artist-meta.php）
 *   - menu_order      : 並び順（管理画面でドラッグ or 数値指定）
 *
 * @package LINDO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Artist 投稿タイプを登録。
 *
 * publicly_queryable=false：個別URL（/artist/xxx）は持たず、
 * 一覧→モーダルで完結させる。アーカイブも持たない。
 */
function lindo_register_artist_cpt() {
	$labels = array(
		'name'               => __( 'アーティスト', 'lindo' ),
		'singular_name'      => __( 'アーティスト', 'lindo' ),
		'add_new'            => __( '新規追加', 'lindo' ),
		'add_new_item'       => __( 'アーティストを追加', 'lindo' ),
		'edit_item'          => __( 'アーティストを編集', 'lindo' ),
		'new_item'           => __( '新規アーティスト', 'lindo' ),
		'view_item'          => __( 'アーティストを表示', 'lindo' ),
		'search_items'       => __( 'アーティストを検索', 'lindo' ),
		'not_found'          => __( 'アーティストがありません', 'lindo' ),
		'not_found_in_trash' => __( 'ゴミ箱にアーティストはありません', 'lindo' ),
		'all_items'          => __( 'アーティスト一覧', 'lindo' ),
		'menu_name'          => __( 'アーティスト', 'lindo' ),
	);

	register_post_type(
		'artist',
		array(
			'labels'              => $labels,
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => false,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-groups',
			'has_archive'         => false,
			'rewrite'             => false,
			'exclude_from_search' => true,
			'hierarchical'        => false,
			'supports'            => array( 'title', 'thumbnail', 'page-attributes' ),
		)
	);
}
add_action( 'init', 'lindo_register_artist_cpt' );

/**
 * 一覧画面を menu_order（昇順）→ 日付で並べ替え。
 *
 * @param WP_Query $query クエリ。
 */
function lindo_artist_admin_order( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( 'artist' !== $query->get( 'post_type' ) ) {
		return;
	}
	if ( ! $query->get( 'orderby' ) ) {
		$query->set( 'orderby', 'menu_order date' );
		$query->set( 'order', 'ASC' );
	}
}
add_action( 'pre_get_posts', 'lindo_artist_admin_order' );

/**
 * 一覧テーブルにサムネイル列を追加（運用しやすさ）。
 *
 * @param array $columns 既存列。
 * @return array
 */
function lindo_artist_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		if ( 'title' === $key ) {
			$new['lindo_thumb'] = __( 'ポートレート', 'lindo' );
		}
		$new[ $key ] = $label;
	}
	$new['lindo_order'] = __( '並び順', 'lindo' );
	return $new;
}
add_filter( 'manage_artist_posts_columns', 'lindo_artist_columns' );

/**
 * 追加列の中身。
 *
 * @param string $column  列キー。
 * @param int    $post_id 投稿ID。
 */
function lindo_artist_column_content( $column, $post_id ) {
	if ( 'lindo_thumb' === $column ) {
		if ( has_post_thumbnail( $post_id ) ) {
			echo get_the_post_thumbnail( $post_id, array( 56, 70 ) );
		} else {
			echo '<span style="color:#b5121b">— 未設定 —</span>';
		}
	}
	if ( 'lindo_order' === $column ) {
		echo (int) get_post_field( 'menu_order', $post_id );
	}
}
add_action( 'manage_artist_posts_custom_column', 'lindo_artist_column_content', 10, 2 );
