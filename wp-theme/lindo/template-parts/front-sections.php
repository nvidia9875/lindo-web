<?php
/**
 * フロントページ本文セクションの並び（共有）。
 * 本番 front-page.php とプレビュー render.php の両方から呼ぶ＝DRY。
 *
 * 期待する変数:
 *   $artists            array<int,array>（03 Works の作品データ）
 *   $talents            array<int,array>（04 Artists の一覧。空なら非表示）
 *   $site               array（inc/site-defaults.php の形。CMS 由来の文言一式）
 *   $contact_body_html  string（信頼済み。メール導線 or フォーム）
 *
 * @package LINDO
 */

if ( ! defined( 'LINDO_PART' ) ) {
	exit;
}
$artists           = isset( $artists ) ? $artists : array();
$talents           = isset( $talents ) ? $talents : array();
$contact_body_html = isset( $contact_body_html ) ? $contact_body_html : '';
// $site が渡らなかった場合も既定の文言で描画する（空白のページを出さない）。
$site = isset( $site ) && is_array( $site ) ? $site : require LINDO_DIR . '/inc/site-defaults.php';

lindo_part( 'section-hero', array( 'hero' => $site['hero'] ) );
lindo_part(
	'section-about',
	array(
		'about' => $site['about'],
		'rep'   => $site['rep'],
	)
);
lindo_part( 'section-service', array( 'service' => $site['service'] ) );
lindo_part(
	'section-artists',
	array(
		'artists' => $artists,
		'works'   => $site['works'],
	)
);
lindo_part(
	'section-talents',
	array(
		'talents' => isset( $site['talents'] ) ? $site['talents'] : array(),
		'items'   => $talents,
	)
);
lindo_part( 'section-partners', array( 'partners' => $site['partners'] ) );
lindo_part(
	'section-contact',
	array(
		'contact'           => $site['contact'],
		'contact_body_html' => $contact_body_html,
	)
);
