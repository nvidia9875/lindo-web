<?php
/**
 * microCMS の「サイト設定」（オブジェクト形式 site）へ、既定値を一括投入する。
 *
 * 32項目を管理画面で手打ちすると必ず打ち間違いが出るので、
 * inc/site-defaults.php（＝現在サイトに出ている文言そのもの）から機械的に流し込む。
 *
 * 前提:
 *   - microCMS 側に site API（オブジェクト形式）が作成済みであること
 *   - APIキーに **PATCH 権限** があること（GET だけでは動かない）
 *
 * 実行:
 *   set -a; . ./.env.local; set +a
 *   php scripts/push-site-settings.php          # 中身を確認（送信しない）
 *   php scripts/push-site-settings.php --write  # 実際に送信
 *
 * @package LINDO\Scripts
 */

$defaults = require __DIR__ . '/../wp-theme/lindo/inc/site-defaults.php';
$service  = getenv( 'MICROCMS_SERVICE_ID' ) ?: 'lindo-web';
$key      = getenv( 'MICROCMS_API_KEY' );
$write    = in_array( '--write', $argv, true );

if ( '' === (string) $key ) {
	fwrite( STDERR, "MICROCMS_API_KEY が未設定です。set -a; . ./.env.local; set +a を先に。\n" );
	exit( 1 );
}

// site-defaults の構造 → microCMS のフィールドIDへの対応。
// ここの対応は MICROCMS-SCHEMA.md「2.5」と一致させること。
$body = array(
	'siteTitle'        => $defaults['title'],
	'siteDescription'  => $defaults['description'],
	// 公開日まで検索エンジンに出さない。
	'noindex'          => (bool) $defaults['noindex'],
	'loaderSub'        => $defaults['loaderSub'],
	'heroLabel'        => $defaults['hero']['label'],
	'heroLabelStrong'  => $defaults['hero']['labelStrong'],
	'heroMeta'         => $defaults['hero']['meta'],
	'heroLine1'        => $defaults['hero']['line1'],
	'heroLine2'        => $defaults['hero']['line2'],
	'heroLead'         => $defaults['hero']['lead'],
	'heroTags'         => $defaults['hero']['tags'],
	'aboutLabel'       => $defaults['about']['label'],
	'aboutHeading'     => $defaults['about']['heading'],
	'aboutBody'        => $defaults['about']['body'],
	'repName'          => $defaults['rep']['name'],
	'repTitle'         => $defaults['rep']['title'],
	'repProfile'       => $defaults['rep']['profile'],
	'serviceLabel'     => $defaults['service']['label'],
	'worksLabel'       => $defaults['works']['label'],
	'worksLead'        => $defaults['works']['lead'],
	'partnersLabel'    => $defaults['partners']['label'],
	// 1行1社のテキストエリア。
	'partners'         => implode( "\n", $defaults['partners']['items'] ),
	'contactLabel'     => $defaults['contact']['label'],
	'contactLead'      => $defaults['contact']['lead'],
	'contactEmail'     => $defaults['contact']['email'],
	'companyName'      => $defaults['company']['name'],
	'companyShortName' => $defaults['company']['shortName'],
	'companyAddress'   => $defaults['company']['address'],
	'companyTel'       => $defaults['company']['tel'],
	'companyNote'      => $defaults['company']['note'],
);

// 繰り返しフィールド（カスタムフィールド service）。
$body['services'] = array();
foreach ( $defaults['service']['items'] as $item ) {
	$body['services'][] = array(
		'fieldId'     => 'service',
		'title'       => $item['title'],
		'description' => $item['description'],
	);
}

printf( "サービス: %s\n項目数: %d（うち services %d行）\n\n", $service, count( $body ), count( $body['services'] ) );
foreach ( $body as $k => $v ) {
	$show = is_array( $v ) ? count( $v ) . '行' : str_replace( "\n", '⏎', mb_strimwidth( (string) $v, 0, 54, '…' ) );
	printf( "  %-17s %s\n", $k, $show );
}

if ( ! $write ) {
	echo "\n（確認のみ。実際に送るには --write を付けてください）\n";
	exit( 0 );
}

$ch = curl_init( sprintf( 'https://%s.microcms.io/api/v1/site', rawurlencode( $service ) ) );
curl_setopt_array(
	$ch,
	array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_CUSTOMREQUEST  => 'PATCH',
		CURLOPT_HTTPHEADER     => array( 'X-MICROCMS-API-KEY: ' . $key, 'Content-Type: application/json' ),
		CURLOPT_POSTFIELDS     => json_encode( $body, JSON_UNESCAPED_UNICODE ),
		CURLOPT_TIMEOUT        => 30,
	)
);
$res    = curl_exec( $ch );
$status = (int) curl_getinfo( $ch, CURLINFO_HTTP_CODE );
unset( $ch );

printf( "\nHTTP %d\n%s\n", $status, (string) $res );
if ( $status >= 200 && $status < 300 ) {
	echo "\n投入しました。管理画面で内容を確認し、**「公開」を押してください**。\n";
} else {
	echo "\n失敗しました。APIキーに PATCH 権限があるか、site API が作成済みかを確認してください。\n";
	exit( 1 );
}
