<?php
/**
 * 実アーティストデータ（描画用）。
 *
 * content-manifest.php（単一情報源）＋ build-works-img.php が生成した works-img/ から、
 * section-artists / section-works-feature / artist-card / work-modal が期待する
 * artist 配列を構築する。
 *
 * 公開するのは works-img/（1280px/q66 の軽量版）のみ。生ソース artist-src/ は .gitignore 済。
 * 画像を差し替えたら: php wp-theme/preview/build-works-img.php → 本ファイルは再生成不要（自動走査）。
 *
 * @package LINDO\Preview
 */

$manifest = require __DIR__ . '/content-manifest.php';

define( 'LINDO_REAL_BASE', __DIR__ . '/works-img' );
define( 'LINDO_REAL_BASE_URL', 'works-img' );

/** パス各セグメントを URL エンコードして結合（日本語/空白に対応）。 */
function lindo_real_url( array $segments ) {
	$parts = array_map( 'rawurlencode', $segments );
	return LINDO_REAL_BASE_URL . '/' . implode( '/', $parts );
}

/** ディレクトリ直下の画像（webp/jpg/jpeg/png）を自然順で返す（出力は 01.webp…）。 */
function lindo_real_images( $dir ) {
	$out = array();
	if ( ! is_dir( $dir ) ) {
		return $out;
	}
	$files = scandir( $dir );
	natcasesort( $files );
	foreach ( $files as $f ) {
		if ( '' === $f || '.' === $f[0] ) {
			continue;
		}
		if ( preg_match( '/\.(webp|jpe?g|png)$/i', $f ) ) {
			$out[] = $f;
		}
	}
	return array_values( $out );
}

/** {url,w,h,alt}。実寸は getimagesize で取得（出力は最大231枚なので全件取得で十分軽い）。 */
function lindo_real_img( $abs, $url, $alt ) {
	$w  = 1000;
	$h  = 1000;
	$sz = @getimagesize( $abs );
	if ( $sz ) {
		$w = (int) $sz[0];
		$h = (int) $sz[1];
	}
	return array(
		'url' => $url,
		'w'   => $w,
		'h'   => $h,
		'alt' => $alt,
	);
}

$artists = array();
$index   = 0;

foreach ( $manifest['order'] as $artist_folder ) {
	if ( ! isset( $manifest['artists'][ $artist_folder ] ) ) {
		continue;
	}
	$def = $manifest['artists'][ $artist_folder ];

	$works = array();
	foreach ( $def['works'] as $wdef ) {
		$key   = $wdef['key'];
		$dir   = LINDO_REAL_BASE . '/' . $artist_folder . '/' . $key;
		$files = lindo_real_images( $dir );
		if ( empty( $files ) ) {
			continue; // 未ビルド/画像なしはスキップ。
		}

		$title   = isset( $wdef['title'] ) ? $wdef['title'] : '';
		$alt     = trim( $def['name'] . ' ' . $title );
		$gallery = array();
		foreach ( $files as $f ) {
			$gallery[] = lindo_real_img(
				$dir . '/' . $f,
				lindo_real_url( array( $artist_folder, $key, $f ) ),
				$alt
			);
		}

		$works[] = array(
			'slug'    => 'work-' . ( $index + 1 ) . '-' . $key,
			'title'   => $title,
			'role'    => isset( $wdef['role'] ) ? $wdef['role'] : '',
			'cover'   => $gallery[0],
			'gallery' => $gallery,
		);
	}

	if ( empty( $works ) ) {
		continue; // 表示できる作品が無いアーティストは出さない。
	}
	$index++;

	$artists[] = array(
		'id'       => $index,
		'slug'     => 'artist-' . $index,
		'index'    => sprintf( '%02d', $index ),
		'name'     => $def['name'],
		'name_sub' => isset( $def['name_sub'] ) ? $def['name_sub'] : '',
		'role'     => isset( $def['role'] ) ? $def['role'] : '',
		'tags'     => isset( $def['tags'] ) ? $def['tags'] : array(),
		'profile'  => isset( $def['profile'] ) ? $def['profile'] : array(),
		'portrait' => $works[0]['cover'],
		'gallery'  => array_map(
			function ( $wk ) {
				return $wk['cover'];
			},
			$works
		),
		'works'    => $works,
		'links'    => isset( $def['links'] ) ? $def['links'] : array(),
	);
}

return $artists;
