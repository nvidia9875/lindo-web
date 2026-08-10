<?php
/**
 * microCMS への最小HTTPクライアント。
 *
 * `artists`（作品データ）と `site`（サイト文言）の両方から使う共通部分。
 * ステータスの解釈は呼び出し側に任せる。エンドポイントごとに「落とすべきか
 * 既定値で続行すべきか」が違うため（artists は0件なら事故＝例外、
 * site は未作成なら既定値で続行）。
 *
 * @package LINDO\Preview
 */

if ( ! function_exists( 'lindo_mc_warn' ) ) {
	/**
	 * コンテンツの不備を警告する（ビルドは止めない）。
	 *
	 * stdout は生成中の HTML なので、混ぜると doctype より前に文字が出てページが壊れる。
	 * 必ず stderr へ。GitHub Actions では ::warning:: が注釈として拾われる。
	 *
	 * @param string $message 警告文。
	 */
	function lindo_mc_warn( $message ) {
		$prefix = getenv( 'GITHUB_ACTIONS' ) ? '::warning::' : '[警告] ';
		fwrite( STDERR, $prefix . $message . PHP_EOL );
	}
}

if ( ! function_exists( 'lindo_mc_service' ) ) {
	/**
	 * サービスID（既定 'lindo'）。
	 *
	 * @return string
	 */
	function lindo_mc_service() {
		$service = (string) getenv( 'MICROCMS_SERVICE_ID' );
		return '' !== $service ? $service : 'lindo';
	}
}

if ( ! function_exists( 'lindo_mc_key' ) ) {
	/**
	 * 取得用APIキー。未設定なら空文字。
	 *
	 * @return string
	 */
	function lindo_mc_key() {
		return (string) getenv( 'MICROCMS_API_KEY' );
	}
}

if ( ! function_exists( 'lindo_mc_request' ) ) {
	/**
	 * microCMS の1エンドポイントを GET する。
	 *
	 * @param string $endpoint エンドポイント名（'artists' / 'site'）。
	 * @param array  $query    クエリ。
	 * @throws RuntimeException 設定不備・通信失敗時（HTTPエラーは投げない。呼び出し側が判断する）。
	 * @return array{0:int,1:string} [HTTPステータス, ボディ]
	 */
	function lindo_mc_request( $endpoint, array $query = array() ) {
		$key = lindo_mc_key();
		if ( '' === $key ) {
			throw new RuntimeException( 'MICROCMS_API_KEY が未設定です。 .env.local を読み込んでから実行してください（例: set -a; . ./.env.local; set +a）。' );
		}

		$url = sprintf(
			'https://%s.microcms.io/api/v1/%s',
			rawurlencode( lindo_mc_service() ),
			rawurlencode( $endpoint )
		);
		if ( ! empty( $query ) ) {
			$url .= '?' . http_build_query( $query );
		}

		$ch = curl_init( $url );
		curl_setopt_array(
			$ch,
			array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT        => 30,
				CURLOPT_HTTPHEADER     => array( 'X-MICROCMS-API-KEY: ' . $key ),
			)
		);
		$body   = curl_exec( $ch );
		$status = (int) curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		$err    = curl_error( $ch );
		// curl_close() は呼ばない。PHP 8.0 以降は何もせず、8.5 で非推奨警告を出す。
		// 警告は <!doctype html> より前に出力されてしまい、ブラウザが互換モードに落ちる。
		unset( $ch );

		if ( false === $body ) {
			// $err にキーは含まれないが、念のためURLもキーも出さない。
			throw new RuntimeException( 'microCMS への接続に失敗しました: ' . $err );
		}

		return array( $status, (string) $body );
	}
}
