<?php
/**
 * Tabela de cookies para a política de privacidade: a mesma lista do painel
 * "Preferências de cookies", gerada a partir das integrações ligadas. Ao
 * ativar o Google Analytics, por exemplo, os cookies dele aparecem aqui.
 *
 * Textos em Rolck Galaxy → Textos do site → Privacidade (LGPD).
 *
 * @package RolckGalaxy
 *
 * @var array $attributes Atributos do bloco.
 */

use RolckGalaxy\Core\I18n\Strings;
use RolckGalaxy\Core\Privacy\Consent;

defined( 'ABSPATH' ) || exit;

$rolck_tag = in_array( $attributes['headingLevel'] ?? 'h3', array( 'h2', 'h3', 'h4' ), true ) ? $attributes['headingLevel'] : 'h3';
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'rolck-cookies' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php foreach ( Consent::cookies() as $rolck_cat => $rolck_rows ) : ?>
		<<?php echo esc_attr( $rolck_tag ); ?>><?php echo esc_html( Strings::get( 'consent_cat_' . $rolck_cat ) ); ?></<?php echo esc_attr( $rolck_tag ); ?>>
		<p><?php echo esc_html( Strings::get( 'consent_cat_' . $rolck_cat . '_text' ) ); ?></p>
		<?php
		if ( $rolck_rows ) {
			Consent::table( $rolck_rows );
		} else {
			echo '<p>' . esc_html( Strings::get( 'consent_third_party_note' ) ) . '</p>';
		}
		?>
	<?php endforeach; ?>
</div>
