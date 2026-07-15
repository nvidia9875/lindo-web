<?php
/**
 * フロントページ（トップ）。
 * Hero / About / Service / Artists（Worksを置換）/ Partner / Contact。
 *
 * @package LINDO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

lindo_part(
	'front-sections',
	array(
		'artists'           => lindo_get_all_artists(),
		'representative'    => lindo_get_representative(),
		'partners'          => lindo_get_partners(),
		'contact_form_html' => lindo_get_contact_form_html(),
		'contact_email'     => lindo_get_contact_email(),
	)
);

get_footer();
