<?php
/**
 * 構造化データ（Organization）。
 *
 * 「株式会社LINDO」という**社名での検索**と、このドメインを Google に結び付ける
 * ための情報。これが無いと、検索側は本文から社名を推測するしかない。
 * 表示は一切変わらない（<script type="application/ld+json"> はレンダリングされない）。
 *
 * 期待する変数:
 *   $site      array（inc/site-defaults.php の形）
 *   $site_url  string（末尾スラッシュ無しの公開URL）
 *
 * @package LINDO
 */

if ( ! defined( 'LINDO_PART' ) ) {
	exit;
}
$site     = isset( $site ) && is_array( $site ) ? $site : array();
$site_url = isset( $site_url ) ? rtrim( (string) $site_url, '/' ) : '';
if ( '' === $site_url ) {
	return; // 絶対URLが作れないなら出さない。相対URLの構造化データは無効。
}

$company = isset( $site['company'] ) && is_array( $site['company'] ) ? $site['company'] : array();
$name    = isset( $company['shortName'] ) ? trim( (string) $company['shortName'] ) : '';
if ( '' === $name ) {
	return;
}

$data = array(
	'@context'    => 'https://schema.org',
	'@type'       => 'Organization',
	'name'        => $name,
	// 英字表記・略称でも同じ会社だと分かるようにする。
	'alternateName' => array( 'LINDO', 'LINDO Co., Ltd.' ),
	'url'         => $site_url . '/',
	'logo'        => $site_url . '/logo.png',
	'image'       => $site_url . '/ogp.png',
	'description' => isset( $site['description'] ) ? (string) $site['description'] : '',
);

// 住所。「〒151-0066 東京都渋谷区西原2-34-9」の形を分解する。
// 形が変わったら PostalAddress にはせず文字列のまま出す（schema.org は
// address に Text も許す）。無理に解析して間違った市区町村を送るより安全。
$addr_raw = isset( $company['address'] ) ? trim( (string) $company['address'] ) : '';
if ( '' !== $addr_raw ) {
	if ( preg_match( '/^〒?\s*(\d{3}-\d{4})\s*(.+?[都道府県])(.+?[市区町村])(.+)$/u', $addr_raw, $m ) ) {
		$data['address'] = array(
			'@type'           => 'PostalAddress',
			'postalCode'      => $m[1],
			'addressRegion'   => $m[2],
			'addressLocality' => $m[3],
			'streetAddress'   => trim( $m[4] ),
			'addressCountry'  => 'JP',
		);
	} else {
		$data['address'] = $addr_raw;
	}
}

$tel = isset( $company['tel'] ) ? trim( (string) $company['tel'] ) : '';
if ( '' !== $tel ) {
	$data['telephone'] = $tel;
}

$email = isset( $site['contact']['email'] ) ? trim( (string) $site['contact']['email'] ) : '';
if ( '' !== $email ) {
	$data['email'] = $email;
}

// SNS（sameAs）は URL が判明したらここに足す。社名検索での結び付きが更に強くなる。
// 現時点では未支給のため出さない。空配列を出すと「SNSが無い」と主張することになる。

// JSON_UNESCAPED_SLASHES: URL の / が \/ になるのを防ぐ（無効ではないが読めない）。
// JSON_HEX_TAG など: </script> でスクリプトを閉じられないようにする。
$json = json_encode(
	$data,
	JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
);
if ( false === $json ) {
	return;
}
?>
<script type="application/ld+json"><?php echo $json; ?></script>
