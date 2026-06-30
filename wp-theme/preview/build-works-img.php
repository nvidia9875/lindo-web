<?php
/**
 * works-img/ ビルダー（content-manifest.php 駆動）。
 *
 * 生ソース preview/artist-src/ホームページ用/ を走査し、各 work の src フォルダ画像を結合・
 * 等間隔サンプリング（最大 cap 枚）して、WebP（最長辺1280px / q70）に最適化した版を
 * works-img/<artistFolder>/<workKey>/NN.webp として出力する。
 *
 * WebP は JPEG(q66) 比で約 1/4 のサイズ・ほぼ無劣化。生ソース(~340MB)は .gitignore 済、
 * 公開・コミットするのは works-img/（軽量版）のみ。ファイル名は 01.webp, 02.webp… と連番化。
 *
 * パイプライン（2段）:
 *   1) sips … EXIF 回転を自動適用＋最長辺1280へ縮小（cwebp は EXIF 回転を見ないため必須）。
 *             小さい画像は拡大しない（縦横とも1280以下ならリサイズせず向き補正のみ）。中間は PNG（無劣化）。
 *   2) cwebp … PNG 中間を WebP へエンコード（-q 70 -m 6）。
 *
 * 必要コマンド: sips（macOS 標準）, cwebp（`brew install webp`）。
 *
 * 実行:
 *   php wp-theme/preview/build-works-img.php          # 実生成
 *   DRY=1 php wp-theme/preview/build-works-img.php     # 計画のみ表示（変換しない）
 *   Q=66 php wp-theme/preview/build-works-img.php      # 品質を上書き（既定70）
 *
 * @package LINDO\Preview
 */

$manifest = require __DIR__ . '/content-manifest.php';
$cap      = isset( $manifest['cap'] ) ? (int) $manifest['cap'] : 15;

$src_root = __DIR__ . '/artist-src/ホームページ用';
$out_root = __DIR__ . '/works-img';
$dry      = (bool) getenv( 'DRY' );

$max_edge = 1280;
$quality  = (int) ( getenv( 'Q' ) ?: 70 );

if ( ! is_dir( $src_root ) ) {
	fwrite( STDERR, "[ERROR] ソースが見つかりません: {$src_root}\n" );
	exit( 1 );
}
if ( ! $dry && ! lindo_has_cmd( 'cwebp' ) ) {
	fwrite( STDERR, "[ERROR] cwebp が見つかりません。`brew install webp` を実行してください。\n" );
	exit( 1 );
}

/** コマンドの有無。 */
function lindo_has_cmd( $cmd ) {
	exec( 'command -v ' . escapeshellarg( $cmd ) . ' 2>/dev/null', $o, $rc );
	return 0 === $rc;
}

/** ディレクトリ直下の画像（jpg/jpeg/png）を自然順で返す。 */
function lindo_list_images( $dir ) {
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
		if ( preg_match( '/\.(jpe?g|png)$/i', $f ) ) {
			$out[] = $f;
		}
	}
	return array_values( $out );
}

/** 0..n-1 から cap 個を等間隔抽出（先頭と末尾を必ず含む）。n<=cap なら全件。 */
function lindo_pick_even( $n, $cap ) {
	if ( $n <= $cap ) {
		return range( 0, $n - 1 );
	}
	$idx = array();
	for ( $i = 0; $i < $cap; $i++ ) {
		$idx[] = (int) round( $i * ( $n - 1 ) / ( $cap - 1 ) );
	}
	return array_values( array_unique( $idx ) );
}

/** 1枚を WebP 化（sips で向き補正＋縮小 → cwebp）。戻り値: 成否。 */
function lindo_make_webp( $src, $out, $max_edge, $quality ) {
	$sz   = @getimagesize( $src );
	$long = $sz ? max( (int) $sz[0], (int) $sz[1] ) : 0;
	$tmp  = $out . '.tmp.png';

	// sips: 常に PNG 化（＝EXIF 回転をピクセルに焼き込み）。長辺が上限超のときだけ縮小（拡大しない）。
	$resize = ( $long > $max_edge ) ? ( '-Z ' . (int) $max_edge . ' ' ) : '';
	$cmd1   = 'sips ' . $resize . '-s format png ' . escapeshellarg( $src )
		. ' --out ' . escapeshellarg( $tmp ) . ' 2>/dev/null';
	exec( $cmd1, $o1, $rc1 );
	if ( 0 !== $rc1 || ! file_exists( $tmp ) ) {
		fwrite( STDERR, "[ERROR] sips 失敗: {$src}\n" );
		return false;
	}

	$cmd2 = 'cwebp -q ' . (int) $quality . ' -m 6 -mt ' . escapeshellarg( $tmp )
		. ' -o ' . escapeshellarg( $out ) . ' 2>/dev/null';
	exec( $cmd2, $o2, $rc2 );
	@unlink( $tmp );
	if ( 0 !== $rc2 || ! file_exists( $out ) ) {
		fwrite( STDERR, "[ERROR] cwebp 失敗: {$src}\n" );
		return false;
	}
	return true;
}

// 出力ルートを作り直す（全再生成）。
if ( ! $dry && is_dir( $out_root ) ) {
	exec( 'rm -rf ' . escapeshellarg( $out_root ) );
}
if ( ! $dry ) {
	mkdir( $out_root, 0755, true );
}

$total_in  = 0;
$total_out = 0;

foreach ( $manifest['artists'] as $artist_folder => $artist ) {
	$works = isset( $artist['works'] ) ? $artist['works'] : array();
	foreach ( $works as $work ) {
		$key = $work['key'];

		// src 複数フォルダの画像を順に結合（絶対パス）。
		$abs_images = array();
		foreach ( $work['src'] as $rel ) {
			$dir = $src_root . '/' . $rel;
			foreach ( lindo_list_images( $dir ) as $f ) {
				$abs_images[] = $dir . '/' . $f;
			}
		}
		$n = count( $abs_images );
		if ( 0 === $n ) {
			fwrite( STDERR, "[WARN] 画像なし: {$artist_folder} / {$key}\n" );
			continue;
		}
		$total_in += $n;

		$pick   = lindo_pick_even( $n, $cap );
		$outdir = $out_root . '/' . $artist_folder . '/' . $key;
		if ( ! $dry ) {
			mkdir( $outdir, 0755, true );
		}

		printf( "%-22s %-18s %3d枚 → %2d枚\n", $artist_folder, $key, $n, count( $pick ) );

		$seq = 0;
		foreach ( $pick as $i ) {
			$seq++;
			$total_out++;
			if ( $dry ) {
				continue;
			}
			$out = $outdir . '/' . sprintf( '%02d.webp', $seq );
			lindo_make_webp( $abs_images[ $i ], $out, $max_edge, $quality );
		}
	}
}

printf(
	"\n%s 入力 %d枚 → 出力 %d枚（WebP q%d / 最長辺%dpx / cap=%d）\n",
	$dry ? '[DRY]' : '[DONE]',
	$total_in,
	$total_out,
	$quality,
	$max_edge,
	$cap
);
