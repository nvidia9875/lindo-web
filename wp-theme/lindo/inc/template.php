<?php
/**
 * テンプレート部品ローダ。
 *
 * template-parts/ 配下の部品に変数を渡して include する小さなヘルパー。
 * WP と スタンドアロン・プレビュー（preview/render.php）の両方から使う。
 *
 * @package LINDO
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'LINDO_PART' ) ) {
	// WP外（プレビュー）では LINDO_PART 経由で読み込まれる。
}

if ( ! defined( 'LINDO_PARTS' ) ) {
	define( 'LINDO_PARTS', LINDO_DIR . '/template-parts' );
}

if ( ! function_exists( 'lindo_part' ) ) {
	/**
	 * 部品を描画する。
	 *
	 * @param string $slug template-parts 配下のファイル名（拡張子なし）。
	 * @param array  $vars 部品に渡す変数（キー名で展開）。
	 */
	function lindo_part( $slug, array $vars = array() ) {
		$file = LINDO_PARTS . '/' . $slug . '.php';
		if ( ! is_readable( $file ) ) {
			return;
		}
		// 部品側のガード用定数。
		if ( ! defined( 'LINDO_PART' ) ) {
			define( 'LINDO_PART', true );
		}
		if ( ! empty( $vars ) ) {
			extract( $vars, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- 制御された内部利用。
		}
		include $file;
	}
}
