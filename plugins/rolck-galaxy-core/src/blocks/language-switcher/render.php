<?php
/**
 * Seletor de idioma: links simples (sem JavaScript), cada um com hreflang.
 *
 * Se a página atual não tem versão no outro idioma, o link leva para a
 * página inicial daquele idioma.
 *
 * @package RolckGalaxy
 *
 * @var array $attributes Atributos do bloco.
 */

use RolckGalaxy\Core\I18n\Strings;
use RolckGalaxy\Core\I18n\Switcher;

defined( 'ABSPATH' ) || exit;

$rolck_links = Switcher::links();
?>
<nav <?php echo get_block_wrapper_attributes( array( 'class' => 'rolck-lang' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-label="<?php echo esc_attr( Strings::get( 'language' ) ); ?>">
	<?php if ( ! empty( $attributes['showIcon'] ) ) : ?>
		<svg class="rolck-lang__icon" aria-hidden="true" viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm6.9 6h-2.9a15.7 15.7 0 0 0-1.4-3.6A8 8 0 0 1 18.9 8ZM12 4c.8 1.2 1.5 2.5 1.9 4h-3.8c.4-1.5 1.1-2.8 1.9-4ZM4.3 14a8.2 8.2 0 0 1 0-4h3.4a16.5 16.5 0 0 0 0 4H4.3Zm.8 2h2.9c.3 1.3.8 2.5 1.4 3.6A8 8 0 0 1 5.1 16ZM8 8H5.1a8 8 0 0 1 4.3-3.6C8.8 5.5 8.3 6.7 8 8Zm4 12c-.8-1.2-1.5-2.5-1.9-4h3.8c-.4 1.5-1.1 2.8-1.9 4Zm2.3-6H9.7a14.7 14.7 0 0 1 0-4h4.6a14.7 14.7 0 0 1 0 4Zm.3 5.6c.6-1.1 1.1-2.3 1.4-3.6h2.9a8 8 0 0 1-4.3 3.6Zm1.7-5.6a16.5 16.5 0 0 0 0-4h3.4a8.2 8.2 0 0 1 0 4h-3.4Z"/></svg>
	<?php endif; ?>
	<ul class="rolck-lang__list">
		<?php foreach ( $rolck_links as $rolck_code => $rolck_link ) : ?>
			<li>
				<a
					class="rolck-lang__link"
					href="<?php echo esc_url( $rolck_link['url'] ); ?>"
					hreflang="<?php echo esc_attr( $rolck_link['hreflang'] ); ?>"
					lang="<?php echo esc_attr( $rolck_link['hreflang'] ); ?>"
					<?php if ( $rolck_link['current'] ) : ?>
						aria-current="true"
					<?php endif; ?>
					<?php if ( empty( $attributes['fullName'] ) ) : ?>
						aria-label="<?php echo esc_attr( $rolck_link['short'] . ' — ' . $rolck_link['label'] ); ?>"
					<?php endif; ?>
				><?php echo esc_html( empty( $attributes['fullName'] ) ? $rolck_link['short'] : $rolck_link['label'] ); ?></a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
