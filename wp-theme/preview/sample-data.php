<?php
/**
 * プレビュー用サンプルデータ（lindo_get_artist_data と同じ契約）。
 *
 * 画像は preview/sample-images/ に同梱した実写真（Unsplashのフリー写真をDL）。
 * ローカル配信なのでネット不要・必ず表示される。
 * 本番は WordPress 管理画面で実画像を登録する（仕組みは別）。
 *
 * @return array<int,array>
 */

define( 'LINDO_PV_IMG', 'sample-images' );   // index.html からの相対パス
define( 'LINDO_PV_SQUARES', 20 );            // s-1.jpg 〜 s-20.jpg

/**
 * アーティストごとのギャラリー（正方形プールを巡回、開始位置をずらして変化）。
 *
 * @param int    $offset 開始オフセット。
 * @param int    $count  枚数。
 * @param string $name   alt。
 * @return array
 */
function lindo_preview_gallery( $offset, $count, $name ) {
	$out = array();
	for ( $i = 0; $i < $count; $i++ ) {
		$idx   = ( ( $offset + $i ) % LINDO_PV_SQUARES ) + 1;
		$out[] = array(
			'url' => LINDO_PV_IMG . '/s-' . $idx . '.jpg',
			'w'   => 720,
			'h'   => 720,
			'alt' => $name . ' gallery ' . ( $i + 1 ),
		);
	}
	return $out;
}

/**
 * 1アーティストの「作品（Works）」一覧。
 * 各作品 = タイトル＋カバー画像1枚＋複数画像のギャラリー（モーダルで表示）。
 *
 * @param int    $i    アーティスト番号。
 * @param string $name アーティスト名。
 * @return array<int,array>
 */
function lindo_preview_works( $i, $name ) {
	$labels = array( 'Debut Visual', 'Tour Key Visual', 'Album Artwork', 'SNS Campaign', 'Magazine Editorial' );
	$n      = 3 + ( $i % 3 ); // 3〜5作品。
	$works  = array();
	for ( $k = 0; $k < $n; $k++ ) {
		$cover_idx = ( ( ( $i - 1 ) * 3 + $k ) % LINDO_PV_SQUARES ) + 1;
		$title     = $labels[ $k % count( $labels ) ];
		$works[]   = array(
			'slug'    => 'work-' . $i . '-' . ( $k + 1 ),
			'title'   => $title,
			'cover'   => array(
				'url' => LINDO_PV_IMG . '/s-' . $cover_idx . '.jpg',
				'w'   => 720,
				'h'   => 720,
				'alt' => $name . ' ' . $title,
			),
			'gallery' => lindo_preview_gallery( ( $i - 1 ) * 4 + $k * 2, 6 + ( $k % 3 ), $name . ' ' . $title ),
		);
	}
	return $works;
}

/**
 * 1アーティスト分の雛形。
 *
 * @param int    $i             表示インデックス（1始まり、portrait-$i.jpg に対応）。
 * @param string $name          表示名。
 * @param string $sub           サブ名。
 * @param string $role          役職・カテゴリ。
 * @param int    $gallery_count グリッド枚数。
 * @return array
 */
function lindo_preview_artist( $i, $name, $sub, $role, $gallery_count = 9 ) {
	return array(
		'id'       => $i,
		'slug'     => 'artist-' . $i,
		'index'    => sprintf( '%02d', $i ),
		'name'     => $name,
		'name_sub' => $sub,
		'role'     => $role,
		'tags'     => array_values(
			array_filter(
				array_map( 'trim', preg_split( '#/#', $role ) )
			)
		),
		'profile'  => array(
			'コンセプト設計からビジュアル制作までを一貫して担当。アーティストの世界観を、誌面・広告・ステージへと一気通貫で翻訳する。',
			'近作ではデビューシングルのアートディレクション、ツアービジュアル、SNSクリエイティブを手がけ、ブランドの一貫性を構築した。',
		),
		'portrait' => array(
			'url' => LINDO_PV_IMG . '/portrait-' . ( ( ( $i - 1 ) % 6 ) + 1 ) . '.jpg',
			'w'   => 900,
			'h'   => 1125,
			'alt' => $name,
		),
		'gallery'  => lindo_preview_gallery( ( $i - 1 ) * 3, $gallery_count, $name ),
		'works'    => lindo_preview_works( $i, $name ),
		'links'    => array(
			array(
				'label' => 'Instagram',
				'url'   => 'https://instagram.com/',
			),
			array(
				'label' => 'YouTube',
				'url'   => 'https://youtube.com/',
			),
		),
	);
}

return array(
	lindo_preview_artist( 1, 'AURORA', 'オーロラ', 'Visual Creative / Branding', 9 ),
	lindo_preview_artist( 2, 'NOVA', 'ノヴァ', 'Styling / Direction', 8 ),
	lindo_preview_artist( 3, 'LUMEN', 'ルーメン', 'Brand Identity', 6 ),
	lindo_preview_artist( 4, 'KAIRO', 'カイロ', 'Visual Creative / Styling', 9 ),
	lindo_preview_artist( 5, 'SELENE', 'セレーネ', 'Concept / Art Direction', 7 ),
	lindo_preview_artist( 6, 'ECHO', 'エコー', 'Visual Creative', 9 ),
	lindo_preview_artist( 7, 'VESPER', 'ヴェスパー', 'Styling / Visual Creative', 9 ),
	lindo_preview_artist( 8, 'HALO', 'ハロ', 'Branding / Direction', 8 ),
	lindo_preview_artist( 9, 'IRIS', 'アイリス', 'Concept / Styling', 6 ),
	lindo_preview_artist( 10, 'ONYX', 'オニキス', 'Visual Creative', 9 ),
	lindo_preview_artist( 11, 'MIRAGE', 'ミラージュ', 'Art Direction / Branding', 7 ),
	lindo_preview_artist( 12, 'SOLIS', 'ソリス', 'Visual Creative / Styling', 9 ),
);
