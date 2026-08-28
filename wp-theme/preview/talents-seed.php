<?php
/**
 * 04 Artists の初期値。
 *
 * **microCMS に `talents` API がまだ無いときだけ** 使われる（talents-data.php 参照）。
 * API を作った時点でこちらは一切参照されなくなる＝先方の編集内容が常に勝つ。
 *
 * 画像は preview/talents-img/ に置き、リポジトリにコミットする。
 * 03 Works の画像（microCMS 配信）とは別扱いなので注意。
 *
 * @package LINDO\Preview
 */

return array(
	array(
		'name'     => 'SugarNote',
		'name_sub' => '',
		'url'      => 'https://sugarnote.jp/',
		'image'    => array(
			// index.html（dist/ 直下）からの相対。works-img と同じ規則。
			'url' => 'talents-img/sugarnote.webp',
			'w'   => 1280,
			'h'   => 858,
			'alt' => 'SugarNote',
		),
	),
);
