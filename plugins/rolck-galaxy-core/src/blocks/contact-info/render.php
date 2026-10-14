<?php
/**
 * Dados de contato da empresa, vindos do painel.
 *
 * @package RolckGalaxy
 *
 * @var array $attributes Atributos do bloco.
 */

use RolckGalaxy\Core\I18n\Strings;
use RolckGalaxy\Core\Settings;

defined( 'ABSPATH' ) || exit;

$rolck_email    = (string) Settings::get( 'general.email' );
$rolck_phone    = (string) Settings::get( 'general.phone' );
$rolck_whatsapp = preg_replace( '/\D+/', '', (string) Settings::get( 'general.whatsapp' ) );
$rolck_address  = rolck_l( Settings::get( 'general.address' ) );

$rolck_rows = array();
foreach ( (array) ( $attributes['fields'] ?? array() ) as $rolck_field ) {
	if ( 'email' === $rolck_field && $rolck_email ) {
		$rolck_rows[] = array( 'mail', 'mailto:' . antispambot( $rolck_email ), antispambot( $rolck_email ), Strings::get( 'contact_email' ) );
	} elseif ( 'whatsapp' === $rolck_field && $rolck_whatsapp ) {
		$rolck_rows[] = array( 'chat', 'https://wa.me/' . $rolck_whatsapp, 'WhatsApp', 'WhatsApp' );
	} elseif ( 'phone' === $rolck_field && $rolck_phone ) {
		$rolck_rows[] = array( 'phone', 'tel:' . preg_replace( '/[^\d+]/', '', $rolck_phone ), $rolck_phone, '' );
	} elseif ( 'address' === $rolck_field && $rolck_address ) {
		$rolck_rows[] = array( 'pin', '', $rolck_address, '' );
	}
}
if ( ! $rolck_rows ) {
	return;
}

$rolck_icons = array(
	'mail'  => 'M3 5h18a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Zm9 7.4L4.6 7H4v.9l8 5.6 8-5.6V7h-.6L12 12.4Z',
	'chat'  => 'M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm5 13.6c-.2.6-1.2 1.2-1.7 1.2-.5.1-1 .2-3.3-.7a11.5 11.5 0 0 1-4.4-3.9c-.3-.4-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.2.1.3 0 .5l-.3.5-.4.4c-.1.1-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.2 1 2.1 1.3 2.4 1.5.3.1.5.1.7-.1l1-1.2c.2-.3.4-.2.7-.1l1.9.9c.3.1.5.2.5.3.1.2.1.7-.2 1.3Z',
	'phone' => 'M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1A17 17 0 0 1 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1l-2.3 2.2Z',
	'pin'   => 'M12 2a7 7 0 0 0-7 7c0 5.3 7 13 7 13s7-7.7 7-13a7 7 0 0 0-7-7Zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5Z',
);
?>
<ul <?php echo get_block_wrapper_attributes( array( 'class' => 'rolck-info is-' . sanitize_html_class( (string) ( $attributes['layout'] ?? 'stack' ) ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php foreach ( $rolck_rows as [ $rolck_icon, $rolck_href, $rolck_text, $rolck_label ] ) : ?>
		<li>
			<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path fill="currentColor" d="<?php echo esc_attr( $rolck_icons[ $rolck_icon ] ); ?>"/></svg>
			<?php if ( $rolck_href ) : ?>
				<a href="<?php echo esc_url( $rolck_href ); ?>"<?php echo str_starts_with( $rolck_href, 'http' ) ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( $rolck_text ); ?></a>
			<?php else : ?>
				<span><?php echo esc_html( $rolck_text ); ?></span>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
</ul>
