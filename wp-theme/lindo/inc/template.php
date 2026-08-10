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

/*
 * ── CMS入力テキストの描画規則 ──────────────────────────────
 *
 * 先方が管理画面で打った改行を、そのまま見た目に反映するための共通規則。
 * サイト全体でこの2つに統一する（場所ごとに違うと説明できない）。
 *
 *   改行1つ → その位置で改行（<br>）
 *   空行1つ → 段落が変わる（<p> が分かれる）
 *
 * 以前はプロフィール欄が「空行のみ段落」で、改行1つは HTML の空白に潰れていた。
 * 打った通りに出ないのは編集者にとって不具合にしか見えないため、改行も効かせる。
 */

if ( ! function_exists( 'lindo_lines' ) ) {
	/**
	 * 1段落ぶんのテキストを、エスケープしつつ改行を <br> にして返す。
	 *
	 * @param string $text 入力テキスト。
	 * @return string HTML。
	 */
	function lindo_lines( $text ) {
		return nl2br( esc_html( trim( (string) $text ) ), false );
	}
}

if ( ! function_exists( 'lindo_split_paras' ) ) {
	/**
	 * 空行区切りのテキスト → 段落ごとの生テキスト配列。
	 *
	 * エスケープはしない（描画側で lindo_lines を通すこと）。
	 *
	 * @param string $text 入力テキスト。
	 * @return array<int,string>
	 */
	function lindo_split_paras( $text ) {
		$normalized = str_replace( array( "\r\n", "\r" ), "\n", (string) $text );
		$out        = array();
		foreach ( preg_split( "/\n[ \t]*\n/", trim( $normalized ) ) as $para ) {
			$para = trim( $para );
			if ( '' !== $para ) {
				$out[] = $para;
			}
		}
		return $out;
	}
}

if ( ! function_exists( 'lindo_heading' ) ) {
	/**
	 * セクション見出し用。改行を「折り返してよい位置」（<wbr>）に変換する。
	 *
	 * 見出しは `.sec-body h2 { word-break: keep-all }`（lindo.css）で、指定した位置
	 * 以外では折り返さない。<wbr> が無いと保険の `overflow-wrap: anywhere` が働き、
	 * 日本語が文節を無視した位置でぶつ切りになる。
	 *
	 * つまり見出しの改行は <br>（必ず改行）ではなく <wbr>（折り返してよい）になる。
	 * 本文と規則が違うのは意図的で、CMS 側の説明文にもそう書く。
	 *
	 * @param string $text 入力テキスト。
	 * @return string HTML。
	 */
	function lindo_heading( $text ) {
		$normalized = str_replace( array( "\r\n", "\r" ), "\n", (string) $text );
		$segments   = array();
		foreach ( explode( "\n", trim( $normalized ) ) as $segment ) {
			$segment = trim( $segment );
			if ( '' !== $segment ) {
				$segments[] = esc_html( $segment );
			}
		}
		return implode( '<wbr>', $segments );
	}
}

if ( ! function_exists( 'lindo_tel_href' ) ) {
	/**
	 * 表示用の電話番号 → tel: の href。
	 *
	 * 先方には「03-5308-5822」のように読める形で入力してもらい、
	 * リンク用の数字列はこちらで組み立てる（2箇所に同じ番号を入力させない）。
	 *
	 * @param string $tel 表示用の電話番号。
	 * @return string
	 */
	function lindo_tel_href( $tel ) {
		return 'tel:' . preg_replace( '/[^0-9+]/', '', (string) $tel );
	}
}
