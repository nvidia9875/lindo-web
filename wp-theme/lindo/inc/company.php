<?php
/**
 * 会社・代表者情報。
 *
 * 代表者の「名前 / 肩書 / プロフィール（文章のみ・画像なし）」を
 * Customizer「LINDO — 代表 / Company」で編集できるようにする。
 * About セクション内の「代表 / Representative」ブロックに表示。
 * 代表者名が空のときはブロックごと非表示。
 *
 * @package LINDO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customizer 設定。
 *
 * @param WP_Customize_Manager $wp_customize Customizer。
 */
function lindo_customize_company( $wp_customize ) {
	$wp_customize->add_section(
		'lindo_company',
		array(
			'title'    => __( 'LINDO — 代表 / Company', 'lindo' ),
			'priority' => 125,
		)
	);

	$wp_customize->add_setting(
		'lindo_rep_name',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'lindo_rep_name',
		array(
			'label'       => __( '代表者名', 'lindo' ),
			'description' => __( '例: 山田 太郎（空にするとこのブロックは表示されません）', 'lindo' ),
			'section'     => 'lindo_company',
			'type'        => 'text',
		)
	);

	$wp_customize->add_setting(
		'lindo_rep_title',
		array(
			'default'           => '代表取締役',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'lindo_rep_title',
		array(
			'label'   => __( '肩書・役職', 'lindo' ),
			'section' => 'lindo_company',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'lindo_rep_profile',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_textarea_field',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'lindo_rep_profile',
		array(
			'label'       => __( '代表プロフィール / メッセージ', 'lindo' ),
			'description' => __( '空行で段落が分かれます。', 'lindo' ),
			'section'     => 'lindo_company',
			'type'        => 'textarea',
		)
	);
}
add_action( 'customize_register', 'lindo_customize_company' );

/**
 * 代表者情報を表示用に取得。
 *
 * @return array{name:string,title:string,profile:array<int,string>}
 */
function lindo_get_representative() {
	$name        = trim( (string) get_theme_mod( 'lindo_rep_name', '' ) );
	$title       = trim( (string) get_theme_mod( 'lindo_rep_title', '代表取締役' ) );
	$profile_raw = (string) get_theme_mod( 'lindo_rep_profile', '' );

	$profile = array();
	foreach ( preg_split( "/\n\s*\n/", trim( $profile_raw ) ) as $para ) {
		$para = trim( $para );
		if ( '' !== $para ) {
			$profile[] = $para;
		}
	}

	return array(
		'name'    => $name,
		'title'   => $title,
		'profile' => $profile,
	);
}
