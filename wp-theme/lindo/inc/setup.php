<?php
/**
 * テーマセットアップ：サポート機能・メニュー・画像サイズ。
 *
 * @package LINDO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * テーマがサポートする機能を登録。
 */
function lindo_setup() {
	load_theme_textdomain( 'lindo', LINDO_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 48,
			'width'       => 200,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'メインナビゲーション', 'lindo' ),
		)
	);

	// アーティストのポートレート（4:5）とグリッド用の正方形。
	add_image_size( 'lindo-portrait', 900, 1125, true );
	add_image_size( 'lindo-square', 720, 720, true );
}
add_action( 'after_setup_theme', 'lindo_setup' );

/**
 * コンテンツ幅。
 */
function lindo_content_width() {
	$GLOBALS['content_width'] = 1320;
}
add_action( 'after_setup_theme', 'lindo_content_width', 0 );

/**
 * フォールバックの主ナビゲーション（メニュー未設定時）。
 * front-page のアンカー構成に合わせる。
 */
function lindo_fallback_menu() {
	echo '<a href="' . esc_url( home_url( '/#about' ) ) . '">About</a>';
	echo '<a href="' . esc_url( home_url( '/#service' ) ) . '">Service</a>';
	echo '<a href="' . esc_url( home_url( '/#artists' ) ) . '">Artists</a>';
	echo '<a href="' . esc_url( home_url( '/#contact' ) ) . '">Contact</a>';
}
