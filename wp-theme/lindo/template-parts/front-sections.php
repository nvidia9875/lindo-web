<?php
/**
 * フロントページ本文セクションの並び（共有）。
 * 本番 front-page.php とプレビュー render.php の両方から呼ぶ＝DRY。
 *
 * 期待する変数:
 *   $artists            array<int,array>
 *   $site               array（inc/site-defaults.php の形。CMS 由来の文言一式）
 *   $contact_form_html  string（信頼済み）
 *
 * @package LINDO
 */

if ( ! defined( 'LINDO_PART' ) ) {
	exit;
}
$artists           = isset( $artists ) ? $artists : array();
$contact_form_html = isset( $contact_form_html ) ? $contact_form_html : '';
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
lindo_part( 'section-partners', array( 'partners' => $site['partners'] ) );
lindo_part(
	'section-contact',
	array(
		'contact'           => $site['contact'],
		'contact_form_html' => $contact_form_html,
	)
);
