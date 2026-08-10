<?php
/**
 * コンテンツ・マニフェスト（実アーティスト作品の単一情報源）。
 *
 * 支給フォルダ `preview/artist-src/<base>/<アーティスト>/<作品フォルダ>/<画像>` と、
 * 同梱 docx のテキストをここに集約する。ここが唯一の真実(source of truth):
 *   - build-works-img.php ……… この定義に従って works-img/ を生成
 *   - real-data.php ……………… この定義＋works-img/ から描画用 artist 配列を構築
 *   - scripts/gen-artists-input … microCMS への投入リファレンスを生成
 *
 * ── 2026-08-10 全面改訂 ─────────────────────────────
 * 先方から「ホームページ載せる用」一式（332枚）を再支給。**選別済み・連番付き**で、
 * 各アーティストの docx に作品名と担当が明記されていたので、それに全面的に合わせた。
 *
 * 前回からの主な変更:
 *   - 福本大晴 を新規追加（9組 → 10組）
 *   - 表示順が全面変更（BMSG/No No GIRLS が繰り上がり、SugarNote が最後尾へ）
 *   - 高嶺のなでしこ「生きてりゃいい」MV を追加、「夏衣装」は1枚に減
 *   - SugarNote のアー写を pink/outdoor に分けず **1作品に統合**（docx が「1.2共通」）
 *   - **cap を撤廃**。先方が選別済みなので、こちらで間引いてはいけない
 *
 * 用語:
 *   - artist … 第1階層フォルダ（= 章）。キーが src/出力のフォルダ名、`name` が表示名。
 *   - work   … 章の中の1作品。`src` の複数フォルダを結合して1ギャラリーにする。
 *   - `key`  … works-img 内の出力サブフォルダ名 兼 スラッグ。ASCII 固定。
 *   - `url`  … （任意）その work を外部リンク（MV等）として提示する。
 *   - `pending` … docx に記載が無く、先方確認待ちの素材。出力には含めない。
 *
 * 表記の補正: docx のタイポは公式スペルへ補正済（CANDY PANK→PINK / SAVEGE→SAVAGE）。
 *            先方承認済み（2026-08-10）。補正内容は HANDOFF-content.md に記録。
 *
 * @package LINDO\Preview
 */

return array(

	// 支給フォルダ名（artist-src/ 直下）。支給のたびに変わるのでここで持つ。
	'base'  => 'ホームページ載せる用',

	// 1作品あたりのギャラリー最大枚数。**0 = 無制限。**
	// 以前は15枚で機械的に間引いていたが、2026-08-10 の再支給で先方が選別済みの
	// ため撤廃した。ここを戻すと先方の選定を壊すので注意。
	'cap'   => 0,

	// 章の表示順（キー = 第1階層フォルダ名）。先頭の数字が先方指定の順序。
	'order' => array(
		'01_SEVENTEEN',
		'02_LESSERAFIM',
		'03_TOMORROW X TOGETHER',
		'04_BMSG',
		'05_NoNoGirls',
		'06_NMB48',
		'07_OCTOPATH',
		'08_福本大晴',
		'09_高嶺のなでしこ',
		'10_SugarNote',
	),

	'artists' => array(

		'01_SEVENTEEN' => array(
			'name'  => 'SEVENTEEN',
			'role'  => 'Style Direction',
			'works' => array(
				array(
					// docx「1.2.3共有」
					'key'   => '1-shohikigen',
					'title' => 'JP 4th Single「消費期限」',
					'role'  => 'Style Direction',
					'src'   => array( '01_SEVENTEEN/01', '01_SEVENTEEN/02', '01_SEVENTEEN/03' ),
				),
				array(
					// docx「4.5.6.7共通」
					'key'   => '2-always-yours',
					'title' => 'JAPAN BEST ALBUM「ALWAYS YOURS」',
					'role'  => 'Style Direction',
					'src'   => array( '01_SEVENTEEN/04', '01_SEVENTEEN/05', '01_SEVENTEEN/06', '01_SEVENTEEN/07' ),
				),
			),
		),

		'02_LESSERAFIM' => array(
			'name'  => 'LE SSERAFIM',
			'role'  => 'Style Direction',
			'works' => array(
				array(
					'key'   => '1-black-insanity',
					'title' => 'JAPAN 3rd Single「CRAZY」CONCEPT PHOTO｜BLACK INSANITY',
					'role'  => 'Style Direction',
					'src'   => array( '02_LESSERAFIM/01' ),
				),
				array(
					'key'   => '2-crazy-dusk',
					'title' => 'JAPAN 3rd Single「CRAZY」CONCEPT PHOTO｜CRAZY DUSK',
					'role'  => 'Style Direction',
					'src'   => array( '02_LESSERAFIM/02' ),
				),
				array(
					'key'   => '3-airy-bloom',
					'title' => 'JP 2nd SG「UNFORGIVEN」CONCEPT PHOTO｜AIRY BLOOM',
					'role'  => 'Style Direction',
					'src'   => array( '02_LESSERAFIM/03' ),
				),
				array(
					// docx は CANDY PANK（タイポ）。公式表記へ補正・先方承認済み。
					'key'   => '4-candy-pink',
					'title' => 'JP 2nd SG「UNFORGIVEN」CONCEPT PHOTO｜CANDY PINK',
					'role'  => 'Style Direction',
					'src'   => array( '02_LESSERAFIM/04' ),
				),
				array(
					// docx は SAVEGE NOIR（タイポ）。公式表記へ補正・先方承認済み。
					'key'   => '5-savage-noir',
					'title' => 'JP 2nd SG「UNFORGIVEN」CONCEPT PHOTO｜SAVAGE NOIR',
					'role'  => 'Style Direction',
					'src'   => array( '02_LESSERAFIM/05' ),
				),
				array(
					// docx「6、7共通」
					'key'   => '6-fearless',
					'title' => 'Japan debut single『FEARLESS Japanese ver.』',
					'role'  => 'Style Direction',
					'src'   => array( '02_LESSERAFIM/06', '02_LESSERAFIM/07' ),
				),
				array(
					'key'   => '7-aera',
					'title' => 'AERA（2024年12月）',
					'role'  => 'Style Direction',
					'src'   => array( '02_LESSERAFIM/08' ),
				),
			),
		),

		'03_TOMORROW X TOGETHER' => array(
			'name'  => 'TOMORROW X TOGETHER',
			'role'  => 'Style Direction',
			'works' => array(
				array(
					// docx「1.2共通」
					'key'   => '1-sweet',
					'title' => 'JP 2nd Album「SWEET」',
					'role'  => 'Style Direction',
					'src'   => array( '03_TOMORROW X TOGETHER/1', '03_TOMORROW X TOGETHER/2' ),
				),
			),
		),

		'04_BMSG' => array(
			'name'    => 'BMSG',
			'role'    => 'Creative / Style Direction',
			'works'   => array(
				array(
					'key'   => '1-new-year-photo',
					'title' => 'BMSG ARTIST｜New Year Photo',
					'role'  => 'Style Direction / Prop Design',
					'src'   => array( '04_BMSG/01' ),
				),
				array(
					'key'   => '2-fes2025',
					'title' => 'BMSG ARTIST｜BMSG FES2025 Package',
					'role'  => 'Creative Direction',
					'src'   => array( '04_BMSG/02' ),
				),
				array(
					'key'   => '3-forked-road',
					'title' => 'BMSG TRAINEE｜RUI TAIKI KANON Digital EP「Forked Road」',
					'role'  => 'Jacket Direction / Design',
					'src'   => array( '04_BMSG/03' ),
				),
			),
			// docx に記載が無い。作品名・担当が分かるまで出さない。
			'pending' => array( '04_BMSG/04' ),
		),

		'05_NoNoGirls' => array(
			'name'  => 'No No GIRLS',
			// docx が同梱されていないため、前回支給時の表記を踏襲。要確認。
			'role'  => 'KEY VISUAL DIRECTION / DESIGN, BRANDING DESIGN',
			'works' => array(
				array(
					'key'   => 'main',
					'title' => 'GIRLS GROUP AUDITION「No No GIRLS」',
					'role'  => 'KEY VISUAL DIRECTION / DESIGN, BRANDING DESIGN',
					'src'   => array( '05_NoNoGirls' ),
				),
			),
		),

		'06_NMB48' => array(
			'name'  => 'NMB48',
			'role'  => 'Style Direction / Styling',
			'works' => array(
				array(
					'key'   => '1-korega-ai',
					'title' => 'これが愛なのか',
					'role'  => 'Style Direction / Styling',
					'src'   => array( '06_NMB48/1' ),
				),
				array(
					'key'   => '2-ganbaranuwai',
					'title' => 'がんばらぬわい',
					'role'  => 'Style Direction / Styling',
					'src'   => array( '06_NMB48/2' ),
				),
				array(
					'key'   => '3-andmikana',
					'title' => 'andMIKANA（山本望叶）',
					'role'  => 'Style Direction / Styling',
					'src'   => array( '06_NMB48/andMIKANA' ),
				),
			),
		),

		'07_OCTOPATH' => array(
			// docx の表記どおり OCTPATH（フォルダ名 OCTOPATH は打ち間違い）。先方承認済み。
			'name'  => 'OCTPATH',
			'role'  => 'Styling / Design',
			'works' => array(
				array(
					'key'   => '1-up-to-the-sky',
					'title' => 'LIVE -UP TO THE SKY-',
					'role'  => 'Styling / Design',
					'src'   => array( '07_OCTOPATH/1' ),
				),
				array(
					'key'   => '2-sparkle',
					'title' => 'ARENA LIVE -SPARKLE-',
					'role'  => 'Styling / Design',
					'src'   => array( '07_OCTOPATH/2' ),
				),
			),
		),

		'08_福本大晴' => array(
			// 2026-08-10 の再支給で新規追加。
			'name'  => '福本大晴',
			'role'  => 'Visual Creative / Styling',
			'works' => array(
				array(
					'key'   => '1-calendar-2026',
					'title' => '福本大晴2026カレンダー',
					'role'  => 'Visual Creative / Styling',
					'src'   => array( '08_福本大晴/01' ),
				),
				array(
					'key'   => '2-angel-wing-jacket',
					'title' => 'Angel Wing Jacket',
					'role'  => 'Visual Creative / Styling',
					'src'   => array( '08_福本大晴/02' ),
				),
			),
		),

		'09_高嶺のなでしこ' => array(
			'name'  => '高嶺のなでしこ',
			'role'  => 'Style Direction / Styling / Design',
			'works' => array(
				array(
					'key'   => '1-idol-isho-mv',
					'title' => '「アイドル衣装」MV',
					'role'  => 'Style Direction / Styling / Design',
					'src'   => array( '09_高嶺のなでしこ/1' ),
				),
				array(
					// 前回は10枚。今回の支給は1枚のみ（先方の選別と解釈）。
					'key'   => '2-natsu-isho',
					'title' => '夏衣装',
					'role'  => 'Style Direction / Styling / Design',
					'src'   => array( '09_高嶺のなでしこ/2' ),
				),
				array(
					// 2026-08-10 の再支給で新規追加。
					'key'   => '3-ikiterya-ii-mv',
					'title' => '「生きてりゃいい」MV',
					'role'  => 'Style Direction / Styling',
					'src'   => array( '09_高嶺のなでしこ/3' ),
				),
			),
		),

		'10_SugarNote' => array(
			'name'    => 'SugarNote',
			'role'    => 'Visual Creative',
			'works'   => array(
				array(
					// docx「1.2共通」。前回は pink/outdoor に分けていたが、今回の指定で統合。
					'key'   => '1-artist-photo',
					'title' => 'Artist photo',
					'role'  => 'Visual Creative',
					'src'   => array( '10_SugarNote/1', '10_SugarNote/2' ),
				),
			),
			// docx に記載が無い。前回支給時は「嘘だよ」MV のサムネイルだった（1枚）。
			// 同じ扱いでよいか確認できるまで出さない。
			'pending' => array( '10_SugarNote/3' ),
		),
	),
);
