<?php
/**
 * 04 Artists の一覧を microCMS の `talents` API から取得する。
 *
 * 【エンドポイント名が `talents` な理由】
 * `artists` は 03 Works（作品データ）が先に使っている。同じ名前は付けられないので、
 * 04 のデータは `talents` に入っている。microCMS の管理画面には日本語のAPI名
 * 「Artists（04・公式サイトリンク）」で出るため、先方が迷うことはない。
 *
 * 【欠けているときの扱い】
 *   404（API未作成）→ 警告だけ出して talents-seed.php の初期値で描画する。
 *                      site（文言）と同じ考え方。ビルドは止めない。
 *   200 で0件        → 0件のまま返す＝セクションごと消える。先方が全部消したのなら
 *                      それが意図なので、初期値を復活させてはいけない。
 *   その他のHTTP     → 例外。障害を黙って初期値で隠すと誰も気づけない。
 *
 * 環境変数:
 *   MICROCMS_TALENTS_FIXTURE  開発用。APIを叩かずローカルJSONで動かす。
 *
 * @package LINDO\Preview
 */

require_once __DIR__ . '/microcms-client.php';
require_once __DIR__ . '/microcms-img.php';

/**
 * microCMS の contents → 描画用配列。
 *
 * @param array<int,array> $contents API の contents。
 * @return array<int,array{name:string,name_sub:string,url:string,image:array}>
 */
function lindo_talents_map( array $contents ) {
	$out = array();
	foreach ( $contents as $c ) {
		$name = isset( $c['name'] ) ? trim( (string) $c['name'] ) : '';
		if ( '' === $name ) {
			continue; // 名前が無いと誰の写真か分からない。出せない。
		}
		// 未入力の任意フィールドはキーごと返ってこない。必ず isset で見る。
		$img = isset( $c['image'] ) && is_array( $c['image'] ) ? $c['image'] : array();
		if ( empty( $img['url'] ) ) {
			lindo_mc_warn( sprintf( 'Artists（04）: %s に写真がありません。写真が無いと一覧に出せないので、スキップしました。', $name ) );
			continue;
		}
		$out[] = array(
			'name'     => $name,
			'name_sub' => isset( $c['nameSub'] ) ? trim( (string) $c['nameSub'] ) : '',
			'url'      => isset( $c['url'] ) ? trim( (string) $c['url'] ) : '',
			'image'    => lindo_mc_img( $img, $name ),
		);
	}
	return $out;
}

// 開発用の抜け道: APIを叩かずローカルJSONで動かす。
$lindo_talents_fixture = (string) getenv( 'MICROCMS_TALENTS_FIXTURE' );
if ( '' !== $lindo_talents_fixture ) {
	if ( ! is_readable( $lindo_talents_fixture ) ) {
		throw new RuntimeException( 'MICROCMS_TALENTS_FIXTURE のファイルが読めません: ' . $lindo_talents_fixture );
	}
	$lindo_talents_json = json_decode( (string) file_get_contents( $lindo_talents_fixture ), true );
	if ( ! is_array( $lindo_talents_json ) || ! isset( $lindo_talents_json['contents'] ) ) {
		throw new RuntimeException( 'MICROCMS_TALENTS_FIXTURE の JSON に contents がありません。' );
	}
	return lindo_talents_map( $lindo_talents_json['contents'] );
}

// 旧方式（works-img 走査）で動かしているときはAPIを叩かない。初期値で描画する。
if ( '' === lindo_mc_key() ) {
	return require __DIR__ . '/talents-seed.php';
}

list( $lindo_talents_status, $lindo_talents_body ) = lindo_mc_request(
	'talents',
	array(
		'limit'  => 100,
		'orders' => 'order',
	)
);

if ( 404 === $lindo_talents_status ) {
	lindo_mc_warn( 'microCMS に talents API（Artists・04）がまだありません。初期値（SugarNote）で描画します。microcms-schema/api-talents.json をインポートすると先方が編集できるようになります。' );
	return require __DIR__ . '/talents-seed.php';
}
if ( 200 !== $lindo_talents_status ) {
	throw new RuntimeException( 'microCMS の talents API が HTTP ' . $lindo_talents_status . ' を返しました。' );
}

$lindo_talents_json = json_decode( (string) $lindo_talents_body, true );
if ( ! is_array( $lindo_talents_json ) || ! isset( $lindo_talents_json['contents'] ) || ! is_array( $lindo_talents_json['contents'] ) ) {
	throw new RuntimeException( 'microCMS の talents API のレスポンスを解釈できませんでした（contents が見つかりません）。' );
}

return lindo_talents_map( $lindo_talents_json['contents'] );
