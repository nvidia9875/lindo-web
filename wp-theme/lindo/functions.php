<?php
/**
 * LINDO theme bootstrap.
 *
 * テーマの各機能はモジュールに分割し、ここから読み込む（高凝集・低結合）。
 *
 * @package LINDO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // 直アクセス禁止。
}

define( 'LINDO_VERSION', '1.0.0' );
define( 'LINDO_DIR', get_template_directory() );
define( 'LINDO_URI', get_template_directory_uri() );

/**
 * モジュール読み込み。
 *
 * - setup.php        : テーマサポート / メニュー / 画像サイズ
 * - enqueue.php      : CSS / JS の読み込み
 * - artist-cpt.php   : Artist カスタム投稿タイプ＋分類
 * - artist-meta.php  : Artist メタ（サブ名 / 役職 / プロフィール / SNS / グリッド画像）
 * - artist-data.php  : 表示用データ整形ヘルパー（プレゼンテーション契約）
 * - contact.php      : Contact Form 7 連携＋Customizer設定
 */
$lindo_modules = array(
	'/inc/template.php',
	'/inc/setup.php',
	'/inc/enqueue.php',
	'/inc/artist-cpt.php',
	'/inc/artist-meta.php',
	'/inc/artist-data.php',
	'/inc/company.php',
	'/inc/contact.php',
);

foreach ( $lindo_modules as $lindo_module ) {
	$lindo_path = LINDO_DIR . $lindo_module;
	if ( is_readable( $lindo_path ) ) {
		require $lindo_path;
	}
}
unset( $lindo_modules, $lindo_module, $lindo_path );
