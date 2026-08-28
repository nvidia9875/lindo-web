<?php
/**
 * サイト文言の既定値。
 *
 * ここが「microCMS の site API が無い / 項目が空」のときに使われる値であり、
 * かつ **microCMS に初期投入する内容の原本** でもある。
 *
 * 【重要】template-parts に文言を直書きしないこと。
 * 直書きすると先方が自分で直せず、必ずこちら（開発者）への依頼が発生する。
 * 文言はすべてこの配列を経由して部品へ渡す。
 *
 * 改行の扱い（先方への説明もこの規則で統一）:
 *   - 改行1つ  → その位置で改行（<br>）
 *   - 空行1つ  → 段落が変わる（<p> が分かれる）
 *
 * @package LINDO\Preview
 */

return array(

	// ── 共通 / SEO ────────────────────────────────
	// 公開先のURL。canonical / og:url / sitemap.xml の基準。末尾のスラッシュ無し。
	//
	// 独自ドメインへの切替前から「切替後の正しい値」を入れてある。公開までは
	// noindex なのでクローラーには影響せず、切替日に触る箇所を1つ減らせる。
	//
	// microCMS には出していない（意図的）。ここを間違えるとSEO上の指し先が全部
	// 壊れるので、先方が触れる場所には置かない。
	'siteUrl'     => 'https://styledbylindo.com',

	'title'       => 'LINDO — Visual Creative Studio',
	'description' => 'アーティストの世界観をひとつのビジュアルへ。K-POPのブランディング実績を生かし、コンセプト設計からスタイリング、デザインまでを一貫して手がけるビジュアルクリエイティブスタジオ。',
	// microCMS「検索エンジンに載せない」が未取得・未設定のときの値。
	//
	// 【false である理由】2026-08-27 に公開済み。ここが true だと、CMS の項目が
	// 取れなかっただけでサイトが丸ごと検索結果から消える（しかも無言で）。
	// 公開前は true だったが、公開後の既定値としては危険な向きなので反転させた。
	// 検索から外したいときは microCMS のスイッチで明示的に ON にする。
	'noindex'     => false,
	'ogImage'     => '',

	// ── ローダー ──────────────────────────────────
	'loaderSub'   => 'Visual Creative',

	// ── ヒーロー ──────────────────────────────────
	'hero'        => array(
		'label'       => 'LINDO Co., Ltd.',
		'labelStrong' => 'Visual Creative',
		'meta'        => "Tokyo, JP\nCreative Studio",
		// 見出しは2行固定（散らばるアニメーションが行単位で動くため）。
		// 末尾のピンクの「.」は自動で付くので入力しない。
		'line1'       => 'VISUAL',
		'line2'       => 'CREATIVE',
		'lead'        => "アーティストの世界観をひとつのビジュアルへ。\nコンセプト設計からスタイリング、デザインまでを一貫して。",
		'tags'        => "Creative / Branding\nDesign / Styling",
	),

	// ── 01 About ─────────────────────────────────
	'about'       => array(
		'label'   => 'About',
		// 見出しの改行は「折り返してよい位置」の指定（<wbr>）。必ず折り返すわけではない。
		// 指定が無いと日本語が文節を無視した位置でぶつ切りになる（lindo_heading 参照）。
		'heading' => "ビジュアルの\nコンセプトを\n最重要視する。",
		'body'    => 'K-POPのブランディング実績を生かし、アーティストのイメージ作り・ストーリー設計、アルバムや広告のビジュアルコンセプト企画／制作を行います。写真・映像・衣装・スタイリングを含むビジュアルクリエイティブを、トータルで。',
	),

	// 代表者（About の中）。
	'rep'         => array(
		'name'    => 'MAI ITO',
		'title'   => '代表取締役 / CEO',
		'profile' => "文化女子大学卒業後、株式会社LDH apparelにて衣装デザイナー兼ディレクターを担当。2019年にフリーランスへ転向。\n\n韓国事務所主催のオーディションプログラムにてスタイルディレクターとして1年間渡韓。帰国後、韓国・日本のアーティストのスタイルディレクション及びビジュアルプロデュースを手がける。\n\n2024年、アーティストのビジュアル作りに特化した撮影の企画／制作をトータルプロデュースする株式会社LINDOを設立。",
	),

	// ── 02 What We Do ────────────────────────────
	'service'     => array(
		'label' => 'What We Do',
		// 連番（01,02…）は表示時に自動で振るので入力しない。
		'items' => array(
			array(
				'title'       => 'Visual Creative',
				'description' => 'アーティスト・アルバム・出演番組に合わせたビジュアルの企画／制作。',
			),
			array(
				'title'       => 'Branding',
				'description' => '戦略的なイメージ設計。ストーリーから世界観を強固に。',
			),
			array(
				'title'       => 'Styling',
				'description' => '衣装・ヘアメイクを含むトータルスタイリング、アサイン／協業。',
			),
			array(
				'title'       => 'Design Direction',
				'description' => 'B.I（ロゴ）など、デザインの方向性を設計。',
			),
		),
	),

	// ── 03 Works ─────────────────────────────────
	'works'       => array(
		'label' => 'Works',
		'lead'  => '私たちが手がけたアーティストのビジュアルワーク。名前を選ぶと、プロフィールと作品ギャラリーをご覧いただけます。',
		'empty' => '準備中です。まもなく公開します。',
	),

	// ── 04 Artists ───────────────────────────────
	// 「関わっているアーティスト」の一覧。写真をクリックすると公式サイトへ飛ぶ。
	//
	// 【03 Works との違い】
	//   03 Works    … LINDO が手がけた**作品**。モーダルでギャラリーを見せる。
	//   04 Artists  … アーティスト**そのもの**の紹介。外部の公式サイトへ送り出す。
	// microCMS では 03 が `artists`、04 が `talents` という別APIになっている。
	// （`artists` は 03 が先に取ってしまったので、04 の中身は `talents` に入っている）
	'talents'     => array(
		'label' => 'Artists',
		'lead'  => '',
		// 一覧の中身は microCMS の `talents` API から取る（preview/talents-data.php）。
		// API がまだ無いときだけ、下の初期値が使われる。
	),

	// ── 05 Business Partner ──────────────────────
	'partners'    => array(
		'label' => 'Business Partner',
		// 1行1社。
		'items' => array(
			'avex',
			'universal music',
			'sony music',
			'HYBE JAPAN',
			'LDH JAPAN',
			'BMSG',
			'吉本興業',
			'TWIN PLANET',
			'ホリプロ',
			'VANTAN',
		),
	),

	// ── Contact ──────────────────────────────────
	'contact'     => array(
		// 末尾のピンクの「.」は自動で付くので入力しない。
		'label' => 'Contact',
		'lead'  => "お仕事のご依頼・ご相談はこちらから。\n内容を確認のうえ、担当者よりご連絡いたします。",
		'email' => 'contact@styledbylindo.com',
	),

	// ── 会社情報（フッター） ───────────────────────
	'company'     => array(
		'name'      => '株式会社LINDO（LINDO Co.,Ltd.）',
		'shortName' => '株式会社LINDO',
		'address'   => '〒151-0066 東京都渋谷区西原2-34-9',
		// 表示用。tel: リンクは数字だけ抜いて自動生成する。
		'tel'       => '03-5308-5822',
		'note'      => 'Visual Creative Studio',
	),
);
