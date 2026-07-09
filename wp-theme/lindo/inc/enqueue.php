<?php
/**
 * CSS / JS の読み込み。
 *
 * - Google Fonts: Archivo + Zen Kaku Gothic New（display=swap）
 * - 本体CSS: assets/css/lindo.css
 * - 本体JS:  assets/js/main.js（reveal/header/nav）
 * - モーダルJS: assets/js/artist-modal.js（フロントのみ、artist が在るとき）
 * すべて self-host の JS は defer 読み込み（CSP script-src 'self' と親和）。
 *
 * @package LINDO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * フロント側のアセット。
 */
function lindo_enqueue_assets() {
	// フォント（preconnect は wp_resource_hints で付与）。
	wp_enqueue_style(
		'lindo-fonts',
		'https://fonts.googleapis.com/css2?family=Archivo:wght@500;600;700;800&family=Zen+Kaku+Gothic+New:wght@400;500;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'lindo-main',
		LINDO_URI . '/assets/css/lindo.css',
		array( 'lindo-fonts' ),
		LINDO_VERSION
	);

	wp_enqueue_script(
		'lindo-main',
		LINDO_URI . '/assets/js/main.js',
		array(),
		LINDO_VERSION,
		true
	);

	// イントロローダー＋ヒーローのシャッター演出（トップのみ）。
	if ( is_front_page() ) {
		wp_enqueue_script(
			'lindo-loader',
			LINDO_URI . '/assets/js/loader.js',
			array(),
			LINDO_VERSION,
			true
		);
		wp_enqueue_script(
			'lindo-hero-fx',
			LINDO_URI . '/assets/js/hero-fx.js',
			array(),
			LINDO_VERSION,
			true
		);
	}

	// アーティストが1件以上ある時だけモーダルJS＋ライトボックスを読む。
	if ( post_type_exists( 'artist' ) ) {
		wp_enqueue_script(
			'lindo-artist-modal',
			LINDO_URI . '/assets/js/artist-modal.js',
			array(),
			LINDO_VERSION,
			true
		);
		wp_enqueue_script(
			'lindo-lightbox',
			LINDO_URI . '/assets/js/lightbox.js',
			array(),
			LINDO_VERSION,
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'lindo_enqueue_assets' );

/**
 * フォント配信元への preconnect。
 *
 * @param array  $hints         既存のヒント。
 * @param string $relation_type 関係種別。
 * @return array
 */
function lindo_resource_hints( $hints, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$hints[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $hints;
}
add_filter( 'wp_resource_hints', 'lindo_resource_hints', 10, 2 );

/**
 * 管理画面（Artist編集）でメディアピッカー用JSを読み込む。
 */
function lindo_admin_enqueue( $hook ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'artist' !== $screen->post_type ) {
		return;
	}
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_script(
		'lindo-admin-gallery',
		LINDO_URI . '/assets/js/admin-gallery.js',
		array( 'jquery', 'jquery-ui-sortable' ),
		LINDO_VERSION,
		true
	);
	wp_enqueue_style(
		'lindo-admin',
		LINDO_URI . '/assets/css/admin.css',
		array(),
		LINDO_VERSION
	);
}
add_action( 'admin_enqueue_scripts', 'lindo_admin_enqueue' );
