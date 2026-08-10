<?php
/**
 * サイトフッター（会社情報）。
 *
 * 期待する変数:
 *   $lindo_year  string
 *   $company     array{name,shortName,address,tel,note}
 *   $contact     array{email}
 *
 * 会社情報・メールは直書きしないこと。CMS で直したときにフッターだけ
 * 古い値が残る、が必ず起きるため。
 *
 * @package LINDO
 */

if ( ! defined( 'LINDO_PART' ) ) {
	exit;
}

$lindo_year    = isset( $lindo_year ) ? $lindo_year : '2026';
$company       = isset( $company ) && is_array( $company ) ? $company : array();
$contact       = isset( $contact ) && is_array( $contact ) ? $contact : array();
$contact_email = isset( $contact['email'] ) ? (string) $contact['email'] : '';
$company_tel   = isset( $company['tel'] ) ? (string) $company['tel'] : '';
?>
<footer class="ft">
	<div class="wrap ft-in">
		<div class="b">LIND<span>O</span></div>
		<div class="a"><?php echo lindo_lines( isset( $company['name'] ) ? $company['name'] : '' ); ?><br><?php echo lindo_lines( isset( $company['address'] ) ? $company['address'] : '' ); ?></div>
		<div class="c">
			<?php if ( '' !== $company_tel ) : ?>
				<a href="<?php echo esc_url( lindo_tel_href( $company_tel ) ); ?>">tel. <?php echo esc_html( $company_tel ); ?></a>
			<?php endif; ?>
			<?php if ( '' !== $contact_email ) : ?>
				<a href="mailto:<?php echo esc_attr( $contact_email ); ?>"><?php echo esc_html( $contact_email ); ?></a>
			<?php endif; ?>
		</div>
		<div class="cp">© <?php echo esc_html( $lindo_year ); ?> <?php echo esc_html( isset( $company['shortName'] ) ? $company['shortName'] : '' ); ?> ・ <?php echo esc_html( isset( $company['note'] ) ? $company['note'] : '' ); ?></div>
	</div>
</footer>
