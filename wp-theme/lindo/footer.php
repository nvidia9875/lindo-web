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
<?php lindo_part( 'site-footer', array( 'lindo_year' => gmdate( 'Y' ) ) ); ?>
<?php wp_footer(); ?>
</body>
</html>
