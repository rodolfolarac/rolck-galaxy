<?php
/**
 * © ano + empresa + direitos (textos em Rolck Galaxy → Textos do site).
 *
 * @package RolckGalaxy
 *
 * @var array $attributes Atributos do bloco.
 */

use RolckGalaxy\Core\I18n\Strings;
use RolckGalaxy\Core\Settings;

defined( 'ABSPATH' ) || exit;

$rolck_year    = (int) wp_date( 'Y' );
$rolck_founded = (int) Settings::get( 'general.founded_year', 0 );
$rolck_years   = $rolck_founded && $rolck_founded < $rolck_year ? $rolck_founded . '–' . $rolck_year : (string) $rolck_year;
?>
<p <?php echo get_block_wrapper_attributes( array( 'class' => 'rolck-copyright' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	© <?php echo esc_html( $rolck_years . ' ' . Settings::get( 'general.company_name' ) ); ?>. <?php echo esc_html( Strings::get( 'rights' ) ); ?>
	<?php if ( ! empty( $attributes['showMadeWith'] ) ) : ?>
		<?php echo esc_html( Strings::get( 'made_with' ) ); ?>
	<?php endif; ?>
</p>
