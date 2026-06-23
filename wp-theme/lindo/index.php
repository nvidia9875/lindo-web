<?php
/**
 * 汎用フォールバックテンプレート。
 * 通常トップは front-page.php が使われる。固定ページ/投稿の保険。
 *
 * @package LINDO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<section class="sec">
	<div class="wrap sec-grid">
		<div class="sec-no rv">—<small><?php echo esc_html( get_the_archive_title() ? wp_strip_all_tags( get_the_archive_title() ) : 'Page' ); ?></small></div>
		<div class="sec-body rv d1">
			<?php
			if ( have_posts() ) :
				while ( have_posts() ) :
					the_post();
					?>
					<article <?php post_class(); ?>>
						<h2><?php the_title(); ?></h2>
						<div class="sub"><?php the_content(); ?></div>
					</article>
					<?php
				endwhile;
			else :
				?>
				<h2>ページが見つかりません</h2>
				<p class="sub"><a class="viewall" href="<?php echo esc_url( home_url( '/' ) ); ?>">トップへ戻る</a></p>
				<?php
			endif;
			?>
		</div>
	</div>
</section>
<?php
get_footer();
