<?php
/**
 * フロントページ本文セクションの並び（共有）。
 * 本番 front-page.php とプレビュー render.php の両方から呼ぶ＝DRY。
 *
 * 期待する変数:
 *   $artists            array<int,array>
 *   $representative     array{name,title,profile[]}
 *   $contact_form_html  string（信頼済み）
 *   $contact_email      string
 *
 * @package LINDO
 */

if ( ! defined( 'LINDO_PART' ) ) {
	exit;
}
$artists           = isset( $artists ) ? $artists : array();
$representative    = isset( $representative ) ? $representative : array();
$contact_form_html = isset( $contact_form_html ) ? $contact_form_html : '';
$contact_email     = isset( $contact_email ) ? $contact_email : 'contact@styledbylindo.com';

lindo_part( 'section-hero' );
lindo_part( 'section-about', array( 'representative' => $representative ) );
lindo_part( 'section-service' );
lindo_part( 'section-artists', array( 'artists' => $artists ) );
lindo_part( 'section-partners' );
lindo_part(
	'section-contact',
	array(
		'contact_form_html' => $contact_form_html,
		'contact_email'     => $contact_email,
	)
);
