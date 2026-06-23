<?php
/**
 * ヘッダーテンプレート。
 *
 * @package LINDO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="theme-color" content="#eae5d7" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php
// イントロローダーはトップ（フロントページ）のみ。
if ( is_front_page() ) {
	lindo_part( 'site-loader' );
}
?>
<?php lindo_part( 'site-header', array( 'lindo_nav_base' => is_front_page() ? '' : home_url( '/' ) ) ); ?>
<main id="main">
