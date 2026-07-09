<?php
/**
 * コンテンツ・マニフェスト（実アーティスト作品の単一情報源）。
 *
 * ユーザー支給フォルダ `preview/artist-src/ホームページ用/<アーティスト>/<作品フォルダ>/<画像>`
 * と、同梱 docx のテキストをここに集約する。ここが唯一の真実(source of truth):
 *   - build-works-img.php …… この定義に従って works-img/ を生成（1280px/q66・1作品=最大CAP枚）
 *   - real-data.php       …… この定義＋生成済み works-img/ から描画用 artist 配列を構築
 *
 * 用語:
 *   - artist … 第1階層フォルダ（= 章）。`folder` が src/出力のキー、`name` が表示名。
 *   - work   … 章の中の1作品。`src` の複数フォルダ画像を結合して1ギャラリーにする（共通/共有まとめ対応）。
 *   - `key`  … works-img 内の出力サブフォルダ名 兼 スラッグ。ASCII 固定（日本語/記号の生ファイル名を避ける）。
 *   - `url`  … （任意）その work を外部リンク（MV等）として提示する。指定時はモーダルで
 *              サムネ（カバー）＋▶バッジのリンクタイルとして描画（ライトボックスではなく外部遷移）。
 *
 * 表記の補正: docx の明らかなタイポは公式スペルへ補正済（CANDY PANK→PINK 等）。
 *            補正内容は HANDOFF-content.md に記録。
 *
 * @package LINDO\Preview
 */

return array(

	// 1作品あたりのギャラリー最大枚数（先頭=カバー＋等間隔サンプリング）。多すぎるモーダルを防ぐ。
	'cap'   => 15,

	// 章の表示順（folder 名で指定）。
	'order' => array(
		'SEVENTEEN',
		'LESSERAFIM',
		'TOMORROW X TOGETHER',
		'NMB48',
		'BMSG',
		'高嶺のなでしこ',
		'OCTOPATH',
		'SugarNote',
		'NoNoGirls',
	),

	'artists' => array(

		'SEVENTEEN' => array(
			'name'  => 'SEVENTEEN',
			'role'  => 'Style Direction',
			'works' => array(
				array(
					'key'   => '1-shohikigen',
					'title' => 'JP 4th Single「消費期限」',
					'role'  => 'Style Direction',
					'src'   => array( 'SEVENTEEN/1', 'SEVENTEEN/2', 'SEVENTEEN/3' ),
				),
				array(
					'key'   => '2-always-yours',
					'title' => 'JAPAN BEST ALBUM「ALWAYS YOURS」',
					'role'  => 'Style Direction',
					'src'   => array( 'SEVENTEEN/4', 'SEVENTEEN/5', 'SEVENTEEN/6', 'SEVENTEEN/7' ),
				),
			),
		),

		'LESSERAFIM' => array(
			'name'  => 'LE SSERAFIM',
			'role'  => 'Style Direction',
			'works' => array(
				array(
					'key'   => '1-black-insanity',
					'title' => 'JP 3rd Single「CRAZY」CONCEPT PHOTO｜BLACK INSANITY',
					'role'  => 'Style Direction',
					'src'   => array( 'LESSERAFIM/1' ),
				),
				array(
					'key'   => '2-crazy-dusk',
					'title' => 'JP 3rd Single「CRAZY」CONCEPT PHOTO｜CRAZY DUSK',
					'role'  => 'Style Direction',
					'src'   => array( 'LESSERAFIM/2' ),
				),
				array(
					'key'   => '3-airy-bloom',
					'title' => "JP 2nd Single「UNFORGIVEN」CONCEPT PHOTO｜AIRY BLOOM",
					'role'  => 'Style Direction',
					'src'   => array( 'LESSERAFIM/3' ),
				),
				array(
					'key'   => '4-candy-pink', // docx「CANDY PANK」→ 公式 CANDY PINK に補正。
					'title' => "JP 2nd Single「UNFORGIVEN」CONCEPT PHOTO｜CANDY PINK",
					'role'  => 'Style Direction',
					'src'   => array( 'LESSERAFIM/4' ),
				),
				array(
					'key'   => '5-savage-noir', // docx「SAVEGE NOIR」→ 公式 SAVAGE NOIR に補正。
					'title' => "JP 2nd Single「UNFORGIVEN」CONCEPT PHOTO｜SAVAGE NOIR",
					'role'  => 'Style Direction',
					'src'   => array( 'LESSERAFIM/5' ),
				),
				array(
					'key'   => '6-fearless',
					'title' => 'Japan Debut Single「FEARLESS」Japanese ver.',
					'role'  => 'Style Direction',
					'src'   => array( 'LESSERAFIM/6', 'LESSERAFIM/7' ),
				),
				array(
					'key'   => 'aera',
					'title' => 'AERA（2024年12月号）',
					'role'  => 'Style Direction',
					'src'   => array( 'LESSERAFIM/AERA' ),
				),
			),
		),

		'TOMORROW X TOGETHER' => array(
			'name'  => 'TOMORROW X TOGETHER',
			'role'  => 'Style Direction',
			'works' => array(
				array(
					'key'   => '1-sweet',
					'title' => 'JP 2nd Album「SWEET」',
					'role'  => 'Style Direction',
					'src'   => array( 'TOMORROW X TOGETHER/1', 'TOMORROW X TOGETHER/2' ),
				),
			),
		),

		'NMB48' => array(
			'name'  => 'NMB48',
			'role'  => 'Style Direction / Styling',
			'works' => array(
				array(
					'key'   => '1-korega-ai',
					'title' => 'これが愛なのか',
					'role'  => 'Style Direction / Styling',
					'src'   => array( 'NMB48/1' ),
				),
				array(
					'key'   => '2-ganbaranuwai',
					'title' => 'がんばらぬわい',
					'role'  => 'Style Direction / Styling',
					'src'   => array( 'NMB48/2' ),
				),
				array(
					'key'   => '3-andmikana',
					'title' => 'andMIKANA（山本望叶）',
					'role'  => 'Style Direction / Styling',
					'src'   => array( 'NMB48/andMIKANA' ),
				),
			),
		),

		'BMSG' => array(
			'name'  => 'BMSG',
			'role'  => 'Creative / Style Direction',
			'works' => array(
				array(
					'key'   => '1-new-year-photo',
					'title' => 'BMSG ARTIST｜New Year Photo',
					'role'  => 'Style Direction / Prop Design',
					'src'   => array( 'BMSG/1' ),
				),
				array(
					'key'   => '2-fes2025',
					'title' => 'BMSG FES 2025 Package',
					'role'  => 'Creative Direction',
					'src'   => array( 'BMSG/2' ),
				),
				array(
					'key'   => '3-forked-road',
					'title' => 'BMSG TRAINEE｜Digital EP「Forked Road」（RUI / TAIKI / KANON）',
					'role'  => 'Jacket Direction / Design',
					'src'   => array( 'BMSG/3' ),
				),
			),
		),

		'高嶺のなでしこ' => array(
			'name'  => '高嶺のなでしこ',
			'role'  => 'Style Direction / Styling / Design',
			'works' => array(
				array(
					'key'   => '1-idol-isho-mv',
					'title' => '「アイドル衣装」MV',
					'role'  => 'Style Direction / Styling / Design',
					'src'   => array( '高嶺のなでしこ/1' ),
				),
				array(
					'key'   => '2-natsu-isho',
					'title' => '夏衣装',
					'role'  => 'Style Direction / Styling / Design',
					'src'   => array( '高嶺のなでしこ/2' ),
				),
			),
		),

		'OCTOPATH' => array(
			'name'  => 'OCTPATH', // docx 準拠（フォルダ名 OCTOPATH はタイポ）。
			'role'  => 'Styling / Design',
			'works' => array(
				array(
					'key'   => '1-up-to-the-sky',
					'title' => 'LIVE -UP TO THE SKY-',
					'role'  => 'Styling / Design',
					'src'   => array( 'OCTOPATH/1' ),
				),
				array(
					'key'   => '2-sparkle',
					'title' => 'ARENA LIVE -SPARKLE-',
					'role'  => 'Styling / Design',
					'src'   => array( 'OCTOPATH/2' ),
				),
			),
		),

		'SugarNote' => array(
			'name'  => 'SugarNote',
			'role'  => 'Visual Creative',
			'works' => array(
				// アー写を「ピンク背景（スタジオ）」と「外撮影」で分けて掲載（クライアント指定）。
				array(
					'key'   => '1-artist-photo-pink',
					'title' => 'Artist Photo（ピンク背景）',
					'role'  => 'Visual Creative',
					'src'   => array( 'SugarNote/1' ),
				),
				array(
					'key'   => '2-artist-photo-outdoor',
					'title' => 'Artist Photo（外撮影）',
					'role'  => 'Visual Creative',
					'src'   => array( 'SugarNote/2' ),
				),
				// 「嘘だよ」MV：支給サムネ（SugarNote/3）＋ YouTube への外部リンク。
				array(
					'key'   => '3-usodayo-mv',
					'title' => '「嘘だよ」MV',
					'role'  => 'Creative Produce',
					'src'   => array( 'SugarNote/3' ),
					'url'   => 'https://youtu.be/lRI7AdFnMDk',
				),
			),
		),

		'NoNoGirls' => array(
			'name'  => 'No No GIRLS',
			'role'  => 'KEY VISUAL DIRECTION / DESIGN, BRANDING DESIGN',
			'works' => array(
				array(
					'key'   => 'main',
					'title' => 'GIRLS GROUP AUDITION「No No GIRLS」',
					'role'  => 'KEY VISUAL DIRECTION / DESIGN, BRANDING DESIGN',
					'src'   => array( 'NoNoGirls' ), // 直下フラット。
				),
			),
		),

	),
);
