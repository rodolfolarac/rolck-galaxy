<?php
/**
 * Breadcrumbs da página atual (a trilha vem de Seo\Breadcrumbs, a mesma do JSON-LD).
 *
 * @package RolckGalaxy
 */

use RolckGalaxy\Core\I18n\Strings;
use RolckGalaxy\Core\Seo\Breadcrumbs;

defined( 'ABSPATH' ) || exit;

$rolck_trail = Breadcrumbs::trail();
if ( count( $rolck_trail ) < 2 ) {
	return;
}
$rolck_last = count( $rolck_trail ) - 1;
?>
<nav <?php echo get_block_wrapper_attributes( array( 'class' => 'rolck-breadcrumbs' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-label="<?php echo esc_attr( Strings::get( 'breadcrumb' ) ); ?>">
	<ol>
		<?php foreach ( $rolck_trail as $rolck_i => $rolck_step ) : ?>
			<li<?php echo $rolck_i === $rolck_last ? ' aria-current="page"' : ''; ?>>
				<?php if ( $rolck_i === $rolck_last ) : ?>
					<?php echo esc_html( wp_strip_all_tags( $rolck_step['name'] ) ); ?>
				<?php else : ?>
					<a href="<?php echo esc_url( $rolck_step['url'] ); ?>"><?php echo esc_html( wp_strip_all_tags( $rolck_step['name'] ) ); ?></a>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
