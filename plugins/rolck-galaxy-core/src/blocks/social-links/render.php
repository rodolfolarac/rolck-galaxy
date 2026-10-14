<?php
/**
 * Ícones das redes sociais (SVG inline, sem fonte de ícones).
 *
 * @package RolckGalaxy
 */

use RolckGalaxy\Core\I18n\Strings;
use RolckGalaxy\Core\Settings;

defined( 'ABSPATH' ) || exit;

$rolck_networks = array(
	'github'      => array( 'GitHub', 'M12 .5a11.5 11.5 0 0 0-3.6 22.4c.6.1.8-.3.8-.6v-2c-3.2.7-3.9-1.5-3.9-1.5-.5-1.3-1.3-1.7-1.3-1.7-1-.7.1-.7.1-.7 1.2.1 1.8 1.2 1.8 1.2 1 1.8 2.8 1.3 3.5 1 .1-.8.4-1.3.7-1.6-2.6-.3-5.3-1.3-5.3-5.7 0-1.3.5-2.3 1.2-3.1-.1-.3-.5-1.5.1-3.1 0 0 1-.3 3.2 1.2a11 11 0 0 1 5.8 0C17.3 4.8 18.3 5.1 18.3 5.1c.6 1.6.2 2.8.1 3.1.8.8 1.2 1.9 1.2 3.1 0 4.4-2.7 5.4-5.3 5.7.4.4.8 1.1.8 2.2v3.2c0 .3.2.7.8.6A11.5 11.5 0 0 0 12 .5Z' ),
	'linkedin'    => array( 'LinkedIn', 'M20.4 20.5h-3.6v-5.6c0-1.3 0-3-1.8-3s-2.1 1.4-2.1 2.9v5.7H9.3V9h3.4v1.6h.1a3.8 3.8 0 0 1 3.4-1.9c3.6 0 4.3 2.4 4.3 5.5v6.3ZM5.3 7.4a2.1 2.1 0 1 1 0-4.2 2.1 2.1 0 0 1 0 4.2ZM7.1 20.5H3.5V9h3.6v11.5ZM22.2 0H1.8C.8 0 0 .8 0 1.7v20.6c0 .9.8 1.7 1.8 1.7h20.4c1 0 1.8-.8 1.8-1.7V1.7C24 .8 23.2 0 22.2 0Z' ),
	'instagram'   => array( 'Instagram', 'M12 2.2c3.2 0 3.6 0 4.8.1 3.3.1 4.8 1.7 4.9 4.9.1 1.3.1 1.6.1 4.8s0 3.6-.1 4.8c-.1 3.2-1.7 4.8-4.9 4.9-1.3.1-1.6.1-4.8.1s-3.6 0-4.8-.1c-3.3-.1-4.8-1.7-4.9-4.9C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.8C2.4 3.9 3.9 2.4 7.2 2.3 8.4 2.2 8.8 2.2 12 2.2ZM12 0C8.7 0 8.3 0 7.1.1 2.7.3.3 2.7.1 7.1 0 8.3 0 8.7 0 12s0 3.7.1 4.9c.2 4.4 2.6 6.8 7 7 1.2.1 1.6.1 4.9.1s3.7 0 4.9-.1c4.4-.2 6.8-2.6 7-7 .1-1.2.1-1.6.1-4.9s0-3.7-.1-4.9c-.2-4.4-2.6-6.8-7-7C15.7 0 15.3 0 12 0Zm0 5.8a6.2 6.2 0 1 0 0 12.4 6.2 6.2 0 0 0 0-12.4ZM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm6.4-11.8a1.4 1.4 0 1 0 0 2.9 1.4 1.4 0 0 0 0-2.9Z' ),
	'youtube'     => array( 'YouTube', 'M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.6 12 3.6 12 3.6s-7.5 0-9.4.5A3 3 0 0 0 .5 6.2 31 31 0 0 0 0 12a31 31 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.9.5 9.4.5 9.4.5s7.5 0 9.4-.5a3 3 0 0 0 2.1-2.1A31 31 0 0 0 24 12a31 31 0 0 0-.5-5.8ZM9.6 15.6V8.4l6.3 3.6-6.3 3.6Z' ),
	'x'           => array( 'X', 'M18.2 2.3h3.4l-7.4 8.4 8.7 11.5h-6.8l-5.3-7-6.1 7H1.3l7.9-9L.9 2.3h7l4.8 6.4 5.5-6.4Zm-1.2 17.9h1.9L7 4.2H5l12 16Z' ),
	'dribbble'    => array( 'Dribbble', 'M12 0a12 12 0 1 0 0 24 12 12 0 0 0 0-24Zm7.9 5.5a10.2 10.2 0 0 1 2.3 6.4c-.3-.1-3.7-.8-7.2-.4l-.3-.7-.6-1.3c3.8-1.6 5.6-3.8 5.8-4Zm-1.2-1.2c-.2.2-1.7 2.3-5.4 3.7A55 55 0 0 0 9.5 2a10.2 10.2 0 0 1 9.2 2.3ZM7.6 2.7a65 65 0 0 1 3.8 5.9c-4.8 1.3-9 1.3-9.5 1.3a10.3 10.3 0 0 1 5.7-7.2ZM1.8 12v-.3c.4 0 5.4.1 10.6-1.4l.8 1.6-.4.1c-5.4 1.8-8.3 6.5-8.6 6.9A10.2 10.2 0 0 1 1.8 12ZM12 22.2c-2.4 0-4.6-.8-6.3-2.2.2-.4 2.3-4.4 8.1-6.4h.1a42 42 0 0 1 2.2 7.7c-1.3.6-2.6.9-4.1.9Zm5.8-1.8a44 44 0 0 0-2-7.3c3.3-.5 6.2.3 6.5.4a10.2 10.2 0 0 1-4.5 6.9Z' ),
);

$rolck_social = (array) Settings::get( 'general.social', array() );
$rolck_items  = array_filter( $rolck_networks, static fn( $key ) => ! empty( $rolck_social[ $key ] ), ARRAY_FILTER_USE_KEY );
if ( ! $rolck_items ) {
	return;
}
?>
<ul <?php echo get_block_wrapper_attributes( array( 'class' => 'rolck-social' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-label="<?php echo esc_attr( Strings::get( 'social' ) ); ?>">
	<?php foreach ( $rolck_items as $rolck_key => [ $rolck_label, $rolck_path ] ) : ?>
		<li>
			<a href="<?php echo esc_url( $rolck_social[ $rolck_key ] ); ?>" target="_blank" rel="noopener me" aria-label="<?php echo esc_attr( $rolck_label ); ?>">
				<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path fill="currentColor" d="<?php echo esc_attr( $rolck_path ); ?>"/></svg>
			</a>
		</li>
	<?php endforeach; ?>
</ul>
