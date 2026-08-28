<?php
/**
 * microCMS の画像URL → 配信用URL / 表示サイズ の変換。
 *
 * 03 Works（microcms-data.php）と 04 Artists（talents-data.php）の両方が使うので
 * 独立させてある。片方だけ最適化規則が違う、という事故を防ぐため。
 *
 * @package LINDO\Preview
 */

/** 長辺の上限。build-works-img.php（旧方式）の 1280px と揃える。 */
define( 'LINDO_MC_MAX_EDGE', 1280 );

/**
 * microCMS の画像URLを、配信用URLに変換する。
 *
 * 【重要】素朴に `?w=1280` を付けてはいけない。imgix の `w` は「幅をその値にする」指定であり、
 * 縦長画像（素材の約7割）が引き伸ばされる。実測: 853x1280 の原本に `?w=1280` を付けると
 * 1280x1921 に**拡大**され、28KB → 88KB と3倍に膨らんだ上に画質も落ちた。
 * 長辺を上限に「収める」には `fit=max` が要る（拡大はせず、収まっていればそのまま）。
 *
 * また、既に最適化済み（長辺1280以下のWebP）の画像に変換をかけると、再エンコードで
 * かえって太る（実測 28KB → 49KB）。そのため必要なときだけ変換する。
 *
 * @param string $url 原本URL。
 * @param int    $w   原本の幅。
 * @param int    $h   原本の高さ。
 * @return string
 */
function lindo_mc_img_url( $url, $w, $h ) {
	$fits    = ( $w <= LINDO_MC_MAX_EDGE && $h <= LINDO_MC_MAX_EDGE );
	$path    = (string) strtok( (string) $url, '?' ); // クエリを除いた拡張子で判定。
	$is_webp = (bool) preg_match( '/\.webp$/i', $path );

	if ( $fits && $is_webp ) {
		return $url; // 変換不要。原本が最小。
	}

	return $url . '?' . http_build_query(
		array(
			'fit' => 'max', // 拡大しない。長辺を上限に収めるだけ。
			'w'   => LINDO_MC_MAX_EDGE,
			'h'   => LINDO_MC_MAX_EDGE,
			'fm'  => 'webp',
			'q'   => 70,
		)
	);
}

/**
 * 長辺 LINDO_MC_MAX_EDGE に収めたときの表示サイズ。
 *
 * img の width/height 属性用。比率が合っていれば CLS は防げるが、実寸と合わせておく方が正確。
 *
 * @param int $w 原本の幅。
 * @param int $h 原本の高さ。
 * @return array{0:int,1:int}
 */
function lindo_mc_scaled( $w, $h ) {
	$w = max( 1, (int) $w );
	$h = max( 1, (int) $h );
	if ( $w <= LINDO_MC_MAX_EDGE && $h <= LINDO_MC_MAX_EDGE ) {
		return array( $w, $h );
	}
	$ratio = min( LINDO_MC_MAX_EDGE / $w, LINDO_MC_MAX_EDGE / $h );
	return array( (int) round( $w * $ratio ), (int) round( $h * $ratio ) );
}

/**
 * microCMS の1画像 → 描画用 {url,w,h,alt}。
 *
 * @param array  $img microCMS の画像オブジェクト（url/width/height）。
 * @param string $alt 代替テキスト。
 * @return array{url:string,w:int,h:int,alt:string}
 */
function lindo_mc_img( array $img, $alt ) {
	$src = isset( $img['url'] ) ? (string) $img['url'] : '';
	$ow  = isset( $img['width'] ) ? (int) $img['width'] : 0;
	$oh  = isset( $img['height'] ) ? (int) $img['height'] : 0;

	list( $w, $h ) = lindo_mc_scaled( $ow, $oh );

	return array(
		'url' => lindo_mc_img_url( $src, $ow, $oh ),
		'w'   => $w,
		'h'   => $h,
		'alt' => $alt,
	);
}
