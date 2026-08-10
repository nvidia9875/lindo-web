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

/*
 * ⚠️ WordPress 経路は停止中（2026-07-15 に WP は不採用。一度も本番稼働していない）。
 *
 * front-sections は文言一式を `$site`（inc/site-defaults.php の形）で受け取るようになった。
 * ここから渡している representative / partners / contact_email は **もう読まれない**。
 * この経路で描画すると文言はすべて既定値になる（崩れはしないが Customizer は効かない）。
 *
 * 復活させるなら lindo_get_representative() 等から `$site` を組み立てて渡すこと。
 * 現状は撤去待ちのため手を入れていない（TODO.md「旧方式の撤去」）。
 */
lindo_part(
	'front-sections',
	array(
		'artists'           => lindo_get_all_artists(),
		'contact_form_html' => lindo_get_contact_form_html(),
	)
);

get_footer();
