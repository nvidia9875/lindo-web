<?php
/**
 * フッターテンプレート。
 *
 * @package LINDO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</main>
<?php
lindo_part(
	'site-footer',
	array(
		'lindo_year'    => gmdate( 'Y' ),
		'contact_email' => lindo_get_contact_email(),
	)
);
?>
<?php wp_footer(); ?>
</body>
</html>
