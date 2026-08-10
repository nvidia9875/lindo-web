<?php
/**
 * microCMS の `site`（オブジェクト形式）からサイト文言を取得する。
 *
 * 目的は **「開発者を通さずに文言を直せる」** こと。ここが無いと、ヒーローの一行を
 * 変えるだけでコード修正の依頼が発生する。
 *
 * 方針:
 *   - 取得できた項目だけ既定値（inc/site-defaults.php）を上書きする＝**空欄は既定値のまま**。
 *     先方が誤って全消ししてもサイトは壊れない。
 *   - `site` API がまだ無い（404）なら、警告だけ出して既定値で描画を続行する。
 *     文言はあくまで見た目の問題で、ビルドを落としてまで止める理由が無い。
 *     （artists は0件だと「空のサイト」が本番に出るので例外にする。扱いが違うのは意図的）
 *
 * 環境変数:
 *   MICROCMS_SITE_FIXTURE  開発用。APIを叩かずローカルJSONで動かす（マージの確認に使う）。
 *
 * @package LINDO\Preview
 */

require_once __DIR__ . '/microcms-client.php';

if ( ! function_exists( 'lindo_site_str' ) ) {
	/**
	 * 文字列項目を取り出す。空文字なら既定値を残す。
	 *
	 * @param array  $json     APIレスポンス。
	 * @param string $key      フィールドID。
	 * @param string $fallback 既定値。
	 * @return string
	 */
	function lindo_site_str( array $json, $key, $fallback ) {
		$value = isset( $json[ $key ] ) ? trim( (string) $json[ $key ] ) : '';
		return '' !== $value ? $value : $fallback;
	}
}

if ( ! function_exists( 'lindo_site_list' ) ) {
	/**
	 * 1行1件のテキストエリア → 配列。空行は捨てる。
	 *
	 * @param string $raw テキスト。
	 * @return array<int,string>
	 */
	function lindo_site_list( $raw ) {
		$out = array();
		foreach ( preg_split( '/\R/', (string) $raw ) as $line ) {
			$line = trim( $line );
			if ( '' !== $line ) {
				$out[] = $line;
			}
		}
		return $out;
	}
}

if ( ! function_exists( 'lindo_site_merge' ) ) {
	/**
	 * APIレスポンスを既定値の上に重ねる。
	 *
	 * 空欄・未設定の項目は既定値のまま残す。microCMS は未入力の任意フィールドを
	 * **キーごと返さない**ので、必ず isset で見ること。
	 *
	 * @param array $site 既定値（inc/site-defaults.php の形）。
	 * @param array $j    APIレスポンス。
	 * @return array
	 */
	function lindo_site_merge( array $site, array $j ) {

		// ── 共通 / SEO ────────────────────────────────
		$site['title']       = lindo_site_str( $j, 'siteTitle', $site['title'] );
		$site['description'] = lindo_site_str( $j, 'siteDescription', $site['description'] );
		// 真偽値は false も「設定済み」なので isset ではなく array_key_exists で見る。
		if ( array_key_exists( 'noindex', $j ) ) {
			$site['noindex'] = (bool) $j['noindex'];
		}
		if ( isset( $j['ogImage']['url'] ) ) {
			$site['ogImage'] = (string) $j['ogImage']['url'];
		}

		// ── ローダー ──────────────────────────────────
		$site['loaderSub'] = lindo_site_str( $j, 'loaderSub', $site['loaderSub'] );

		// ── ヒーロー ──────────────────────────────────
		$site['hero']['label']       = lindo_site_str( $j, 'heroLabel', $site['hero']['label'] );
		$site['hero']['labelStrong'] = lindo_site_str( $j, 'heroLabelStrong', $site['hero']['labelStrong'] );
		$site['hero']['meta']        = lindo_site_str( $j, 'heroMeta', $site['hero']['meta'] );
		$site['hero']['line1']       = lindo_site_str( $j, 'heroLine1', $site['hero']['line1'] );
		$site['hero']['line2']       = lindo_site_str( $j, 'heroLine2', $site['hero']['line2'] );
		$site['hero']['lead']        = lindo_site_str( $j, 'heroLead', $site['hero']['lead'] );
		$site['hero']['tags']        = lindo_site_str( $j, 'heroTags', $site['hero']['tags'] );

		// ── 01 About ─────────────────────────────────
		$site['about']['label']   = lindo_site_str( $j, 'aboutLabel', $site['about']['label'] );
		$site['about']['heading'] = lindo_site_str( $j, 'aboutHeading', $site['about']['heading'] );
		$site['about']['body']    = lindo_site_str( $j, 'aboutBody', $site['about']['body'] );

		$site['rep']['name']    = lindo_site_str( $j, 'repName', $site['rep']['name'] );
		$site['rep']['title']   = lindo_site_str( $j, 'repTitle', $site['rep']['title'] );
		$site['rep']['profile'] = lindo_site_str( $j, 'repProfile', $site['rep']['profile'] );

		// ── 02 What We Do ────────────────────────────
		$site['service']['label'] = lindo_site_str( $j, 'serviceLabel', $site['service']['label'] );
		if ( isset( $j['services'] ) && is_array( $j['services'] ) ) {
			$items = array();
			foreach ( $j['services'] as $item ) {
				if ( ! is_array( $item ) ) {
					continue;
				}
				$title = isset( $item['title'] ) ? trim( (string) $item['title'] ) : '';
				if ( '' === $title ) {
					continue; // 見出しの無い行は出せない。
				}
				$items[] = array(
					'title'       => $title,
					'description' => isset( $item['description'] ) ? trim( (string) $item['description'] ) : '',
				);
			}
			if ( ! empty( $items ) ) {
				$site['service']['items'] = $items;
			}
		}

		// ── 03 Works ─────────────────────────────────
		$site['works']['label'] = lindo_site_str( $j, 'worksLabel', $site['works']['label'] );
		$site['works']['lead']  = lindo_site_str( $j, 'worksLead', $site['works']['lead'] );

		// ── 04 Business Partner ──────────────────────
		$site['partners']['label'] = lindo_site_str( $j, 'partnersLabel', $site['partners']['label'] );
		if ( isset( $j['partners'] ) ) {
			$items = lindo_site_list( $j['partners'] );
			if ( ! empty( $items ) ) {
				$site['partners']['items'] = $items;
			}
		}

		// ── Contact ──────────────────────────────────
		$site['contact']['label'] = lindo_site_str( $j, 'contactLabel', $site['contact']['label'] );
		$site['contact']['lead']  = lindo_site_str( $j, 'contactLead', $site['contact']['lead'] );
		$site['contact']['email'] = lindo_site_str( $j, 'contactEmail', $site['contact']['email'] );

		// ── 会社情報 ─────────────────────────────────
		$site['company']['name']      = lindo_site_str( $j, 'companyName', $site['company']['name'] );
		$site['company']['shortName'] = lindo_site_str( $j, 'companyShortName', $site['company']['shortName'] );
		$site['company']['address']   = lindo_site_str( $j, 'companyAddress', $site['company']['address'] );
		$site['company']['tel']       = lindo_site_str( $j, 'companyTel', $site['company']['tel'] );
		$site['company']['note']      = lindo_site_str( $j, 'companyNote', $site['company']['note'] );

		return $site;
	}
}

// 既定値はテーマ側に置く（プレビュー専用の値ではなく、サイトそのものの内容のため）。
$lindo_site = require LINDO_DIR . '/inc/site-defaults.php';

// 開発用の抜け道: APIを叩かずローカルJSONでマージを確認する。
$lindo_site_fixture = (string) getenv( 'MICROCMS_SITE_FIXTURE' );
if ( '' !== $lindo_site_fixture ) {
	if ( ! is_readable( $lindo_site_fixture ) ) {
		throw new RuntimeException( 'MICROCMS_SITE_FIXTURE のファイルが読めません: ' . $lindo_site_fixture );
	}
	$lindo_site_json = json_decode( (string) file_get_contents( $lindo_site_fixture ), true );
	if ( ! is_array( $lindo_site_json ) ) {
		throw new RuntimeException( 'MICROCMS_SITE_FIXTURE の JSON を解釈できませんでした。' );
	}
	return lindo_site_merge( $lindo_site, $lindo_site_json );
}

// 旧方式（works-img 走査）実行時はAPIを叩かない。既定値で描画する。
if ( '' === lindo_mc_key() ) {
	return $lindo_site;
}

list( $lindo_site_status, $lindo_site_body ) = lindo_mc_request( 'site' );

if ( 404 === $lindo_site_status ) {
	lindo_mc_warn( 'microCMS に site API がまだありません。サイト文言は既定値で描画します（MICROCMS-SCHEMA.md の「site」を作ると先方が自分で編集できるようになります）。' );
	return $lindo_site;
}
if ( 200 !== $lindo_site_status ) {
	// キーは artists の取得で検証済みなので、ここでの異常は設定ミスではなく本物の障害。
	// 黙って既定値に戻すと「編集したのに直らない」を誰も検知できないため落とす。
	throw new RuntimeException( 'microCMS の site API が HTTP ' . $lindo_site_status . ' を返しました。' );
}

$lindo_site_json = json_decode( $lindo_site_body, true );
if ( ! is_array( $lindo_site_json ) ) {
	throw new RuntimeException( 'microCMS の site API のレスポンスを解釈できませんでした。' );
}

return lindo_site_merge( $lindo_site, $lindo_site_json );
