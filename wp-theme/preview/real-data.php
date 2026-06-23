<?php
/**
 * 実アーティストデータ（preview/artist-src/ホームページ用 を走査して構築）。
 *
 * 構造（ユーザー支給フォルダの規則）:
 *   ホームページ用/<アーティスト>/<作品>/<画像...>
 *   - 第1階層フォルダ = アーティスト（フォルダ名 = アーティスト名）
 *   - その中のフォルダ = 作品（番号 or 名前）。各作品 = カバー1枚＋複数画像
 *   - サブフォルダが無く直下に画像のみ = 1作品扱い（タイトルは TODO）
 *   - 判明している情報（docx）は $LINDO_META で上書き。無いものは TODOTODOTODO。
 *   - Instagram 等のリンクは今は付けない（links: 空）。
 *
 * 返り値: section-works-feature.php / artist-card.php が期待する artist 配列。
 *
 * @package LINDO\Preview
 */

// Web最適化済み画像（sips で 1280px/q66 にリサイズ・圧縮した版）。
// 生データ(artist-src/, 246MB)は .gitignore 済。公開するのはこの軽量版のみ。
define( 'LINDO_REAL_BASE', __DIR__ . '/works-img' );
define( 'LINDO_REAL_BASE_URL', 'works-img' );
define( 'LINDO_TODO', 'TODOTODOTODO' );

/** パス各セグメントを URL エンコードして結合（日本語/空白/記号に対応）。 */
function lindo_real_url( array $segments ) {
	$parts = array_map( 'rawurlencode', $segments );
	return LINDO_REAL_BASE_URL . '/' . implode( '/', $parts );
}

/** ディレクトリ直下の画像ファイル（jpg/jpeg/png）を natural sort で返す。 */
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
		if ( ! preg_match( '/\.(jpe?g|png)$/i', $f ) ) {
			continue;
		}
		$out[] = $f;
	}
	return array_values( $out );
}

/** {url,w,h,alt}。$measure=true のときだけ getimagesize で実寸取得（カバーのみ）。 */
function lindo_real_img( $abs, $url, $alt, $measure = false ) {
	$w = 1000;
	$h = 1000;
	if ( $measure ) {
		$sz = @getimagesize( $abs );
		if ( $sz ) {
			$w = (int) $sz[0];
			$h = (int) $sz[1];
		}
	}
	return array(
		'url' => $url,
		'w'   => $w,
		'h'   => $h,
		'alt' => $alt,
	);
}

/* docx から判明したタイトル/役割の上書き。無いものは TODO。 */
$LINDO_META = array(
	'NMB48'     => array(
		'role'  => 'Style Direction / Styling',
		'works' => array(
			'1'         => array( 'title' => 'これが愛なのか', 'role' => 'Style Direction / Styling' ),
			'2'         => array( 'title' => 'がんばらぬわい', 'role' => 'Style Direction / Styling' ),
			'andMIKANA' => array( 'title' => 'andMIKANA（山本望叶）', 'role' => 'Style Direction / Styling' ),
		),
	),
	'SEVENTEEN' => array(
		'role'  => 'Style Directing',
		'works' => array(
			'1' => array( 'title' => 'JP 4th Single「消費期限」', 'role' => 'Style Directing' ),
			'2' => array( 'title' => 'JP 4th Single「消費期限」', 'role' => 'Style Directing' ),
			'3' => array( 'title' => 'JP 4th Single「消費期限」', 'role' => 'Style Directing' ),
		),
	),
);

/* 表示順（支給フォルダのうち実在するものだけ）。 */
$LINDO_ORDER = array( 'SEVENTEEN', 'LESSERAFIM', 'TOMORROW X TOGETHER', 'NMB48', '&AUDITION', 'OCTOPATH', 'NoNoGirls' );

$artists = array();
$index   = 0;

foreach ( $LINDO_ORDER as $artist_name ) {
	$artist_dir = LINDO_REAL_BASE . '/' . $artist_name;
	if ( ! is_dir( $artist_dir ) ) {
		continue;
	}
	$index++;

	$meta_artist = isset( $LINDO_META[ $artist_name ] ) ? $LINDO_META[ $artist_name ] : array();
	$artist_role = isset( $meta_artist['role'] ) ? $meta_artist['role'] : LINDO_TODO;

	/* 作品フォルダ（番号/名前）。 */
	$subdirs = array();
	foreach ( scandir( $artist_dir ) as $d ) {
		if ( '' === $d || '.' === $d[0] ) {
			continue;
		}
		if ( is_dir( $artist_dir . '/' . $d ) ) {
			$subdirs[] = $d;
		}
	}
	natcasesort( $subdirs );
	$subdirs = array_values( $subdirs );

	$works = array();

	$add_work = function ( $folder, $images, $base_segments ) use ( $artist_name, $meta_artist, &$works, $index ) {
		if ( empty( $images ) ) {
			return;
		}
		$wmeta = ( null !== $folder && isset( $meta_artist['works'][ $folder ] ) ) ? $meta_artist['works'][ $folder ] : array();
		if ( isset( $wmeta['title'] ) ) {
			$title = $wmeta['title'];
		} elseif ( null !== $folder && ! ctype_digit( (string) $folder ) ) {
			$title = $folder; // 名前付きフォルダ（AERA 等）はフォルダ名をタイトルに。
		} else {
			$title = LINDO_TODO;
		}
		$wrole = isset( $wmeta['role'] ) ? $wmeta['role'] : LINDO_TODO;

		$gallery = array();
		foreach ( $images as $i => $f ) {
			$segments  = array_merge( $base_segments, array( $f ) );
			$abs       = LINDO_REAL_BASE . '/' . implode( '/', $base_segments ) . '/' . $f;
			$gallery[] = lindo_real_img( $abs, lindo_real_url( $segments ), $artist_name . ' ' . $title, 0 === $i );
		}
		$slug_tail = null === $folder ? '1' : preg_replace( '/[^a-zA-Z0-9]+/', '-', (string) $folder );
		$works[]   = array(
			'slug'    => 'work-' . $index . '-' . $slug_tail,
			'title'   => $title,
			'role'    => $wrole,
			'cover'   => $gallery[0],
			'gallery' => $gallery,
		);
	};

	if ( $subdirs ) {
		foreach ( $subdirs as $w ) {
			$add_work( $w, lindo_real_images( $artist_dir . '/' . $w ), array( $artist_name, $w ) );
		}
	} else {
		// サブフォルダ無し＝直下画像を1作品扱い。
		$add_work( null, lindo_real_images( $artist_dir ), array( $artist_name ) );
	}

	$artists[] = array(
		'id'       => $index,
		'slug'     => 'artist-' . $index,
		'index'    => sprintf( '%02d', $index ),
		'name'     => $artist_name,
		'name_sub' => '',
		'role'     => $artist_role,
		'tags'     => array(),
		'profile'  => array(),
		'portrait' => $works ? $works[0]['cover'] : null,
		'gallery'  => array_map(
			function ( $wk ) {
				return $wk['cover'];
			},
			$works
		),
		'works'    => $works,
		'links'    => array(), // インスタ等は今は付けない。
	);
}

return $artists;
