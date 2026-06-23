<?php
/**
 * Contact — Contact Form 7 連携。
 *
 * 方針:
 *   - 管理画面の Customizer「LINDO — Contact」で
 *       1) CF7 のショートコード（例: [contact-form-7 id="123" title="お問い合わせ"]）
 *       2) 直接連絡用メール
 *     を設定。
 *   - CF7 が有効でショートコードが入っていれば do_shortcode を出力。
 *   - 未設定時はテーマ同梱の静的フォールバックフォーム（デザイン確認用）。
 *
 * メール送信＋蓄積:
 *   - CF7 がメール送信、Flamingo プラグインが送信内容を管理画面に蓄積。
 *   - 送信先は CF7 のフォーム編集「メール」タブで contact@styledbylindo.com に設定。
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
function lindo_customize_contact( $wp_customize ) {
	$wp_customize->add_section(
		'lindo_contact',
		array(
			'title'    => __( 'LINDO — Contact', 'lindo' ),
			'priority' => 130,
		)
	);

	$wp_customize->add_setting(
		'lindo_cf7_shortcode',
		array(
			'default'           => '',
			'sanitize_callback' => 'wp_kses_post',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'lindo_cf7_shortcode',
		array(
			'label'       => __( 'Contact Form 7 ショートコード', 'lindo' ),
			'description' => __( '例: [contact-form-7 id="123" title="お問い合わせ"]。空ならフォールバックの静的フォームを表示。', 'lindo' ),
			'section'     => 'lindo_contact',
			'type'        => 'textarea',
		)
	);

	$wp_customize->add_setting(
		'lindo_contact_email',
		array(
			'default'           => 'contact@styledbylindo.com',
			'sanitize_callback' => 'sanitize_email',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'lindo_contact_email',
		array(
			'label'   => __( '直接連絡用メールアドレス', 'lindo' ),
			'section' => 'lindo_contact',
			'type'    => 'email',
		)
	);
}
add_action( 'customize_register', 'lindo_customize_contact' );

/**
 * 直接連絡用メール。
 *
 * @return string
 */
function lindo_get_contact_email() {
	$email = get_theme_mod( 'lindo_contact_email', 'contact@styledbylindo.com' );
	return $email ? $email : 'contact@styledbylindo.com';
}

/**
 * Contact フォームのHTMLを返す（CF7 or フォールバック）。
 *
 * @return string 信頼済みHTML。
 */
function lindo_get_contact_form_html() {
	$shortcode = trim( (string) get_theme_mod( 'lindo_cf7_shortcode', '' ) );

	if ( '' !== $shortcode && shortcode_exists( 'contact-form-7' ) ) {
		// CF7 出力を Swiss 調にするためラッパに lindo-form を付与。
		return '<div class="lindo-form">' . do_shortcode( $shortcode ) . '</div>';
	}

	return lindo_fallback_form_html();
}

/**
 * フォールバックの静的フォーム（デザイン確認用 / CF7 未導入時）。
 *
 * 実送信は行わない。本番では CF7 ショートコードを設定すること。
 *
 * @return string
 */
function lindo_fallback_form_html() {
	ob_start();
	lindo_part( 'contact-form-fallback' );
	return (string) ob_get_clean();
}
