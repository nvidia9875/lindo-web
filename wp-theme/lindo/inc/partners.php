<?php
/**
 * 主要取引先（Business Partner）。
 *
 * 取引先の社名リストを Customizer「LINDO — 取引先 / Business Partner」で
 * 編集できるようにする（1行1社・上から表示順）。Partner セクションに表示。
 * 全部空にするとセクションごと非表示。
 *
 * @package LINDO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 既定の取引先リスト。
 *
 * ※ プレビュー（preview/render.php）は WordPress 無しで動くため
 *    この関数を呼べず、同じ並びを別途持っている。変更時は両方直す。
 *
 * @return array<int,string>
 */
function lindo_default_partners() {
	return array(
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
	);
}

/**
 * Customizer 設定。
 *
 * @param WP_Customize_Manager $wp_customize Customizer。
 */
function lindo_customize_partners( $wp_customize ) {
	$wp_customize->add_section(
		'lindo_partners',
		array(
			'title'    => __( 'LINDO — 取引先 / Business Partner', 'lindo' ),
			'priority' => 126,
		)
	);

	$wp_customize->add_setting(
		'lindo_partners_list',
		array(
			'default'           => implode( "\n", lindo_default_partners() ),
			'sanitize_callback' => 'sanitize_textarea_field',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'lindo_partners_list',
		array(
			'label'       => __( '取引先（1行に1社）', 'lindo' ),
			'description' => __( '上から順に表示されます。並べ替えは行を入れ替えてください。全部消すとこのセクションは表示されません。', 'lindo' ),
			'section'     => 'lindo_partners',
			'type'        => 'textarea',
		)
	);
}
add_action( 'customize_register', 'lindo_customize_partners' );

/**
 * 取引先リストを表示用に取得。
 *
 * @return array<int,string>
 */
function lindo_get_partners() {
	$raw = (string) get_theme_mod( 'lindo_partners_list', implode( "\n", lindo_default_partners() ) );

	$partners = array();
	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$line = trim( $line );
		if ( '' !== $line ) {
			$partners[] = $line;
		}
	}

	return $partners;
}
