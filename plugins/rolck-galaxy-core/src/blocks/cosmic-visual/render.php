<?php
/**
 * Visual cósmico decorativo: SVG inline + animações CSS (pausadas para quem
 * prefere menos movimento). Fica escondido dos leitores de tela.
 *
 * @package RolckGalaxy
 *
 * @var array $attributes Atributos do bloco.
 */

defined( 'ABSPATH' ) || exit;

$rolck_chips  = array_slice( array_filter( array_map( 'strval', (array) ( $attributes['chips'] ?? array() ) ) ), 0, 4 );
$rolck_center = mb_substr( (string) ( $attributes['center'] ?? 'RG' ), 0, 3 );
$rolck_uid    = wp_unique_id( 'rolck-cosmic-' );
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'rolck-cosmic' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-hidden="true">
	<svg class="rolck-cosmic__svg" viewBox="0 0 520 520" width="520" height="520" focusable="false">
		<defs>
			<radialGradient id="<?php echo esc_attr( $rolck_uid ); ?>-glow" cx="50%" cy="50%" r="50%">
				<stop offset="0%" stop-color="#a78bfa" stop-opacity=".9"/>
				<stop offset="45%" stop-color="#7c3aed" stop-opacity=".45"/>
				<stop offset="100%" stop-color="#0a0a1a" stop-opacity="0"/>
			</radialGradient>
			<linearGradient id="<?php echo esc_attr( $rolck_uid ); ?>-ring" x1="0" y1="0" x2="1" y2="1">
				<stop offset="0%" stop-color="#8b5cf6"/>
				<stop offset="100%" stop-color="#22d3ee"/>
			</linearGradient>
			<linearGradient id="<?php echo esc_attr( $rolck_uid ); ?>-blob" x1="0" y1="0" x2="1" y2="1">
				<stop offset="0%" stop-color="#c084fc"/>
				<stop offset="50%" stop-color="#7c3aed"/>
				<stop offset="100%" stop-color="#22d3ee"/>
			</linearGradient>
		</defs>

		<circle cx="260" cy="260" r="230" fill="url(#<?php echo esc_attr( $rolck_uid ); ?>-glow)"/>

		<g class="rolck-cosmic__orbit rolck-cosmic__orbit--a">
			<ellipse cx="260" cy="260" rx="236" ry="92" fill="none" stroke="url(#<?php echo esc_attr( $rolck_uid ); ?>-ring)" stroke-opacity=".45" stroke-width="1.5" transform="rotate(-24 260 260)"/>
			<circle cx="476" cy="166" r="9" fill="#22d3ee"/>
		</g>
		<g class="rolck-cosmic__orbit rolck-cosmic__orbit--b">
			<ellipse cx="260" cy="260" rx="190" ry="190" fill="none" stroke="#ffffff" stroke-opacity=".08" stroke-dasharray="3 9"/>
			<circle cx="260" cy="70" r="6" fill="#a78bfa"/>
		</g>
		<g class="rolck-cosmic__orbit rolck-cosmic__orbit--c">
			<ellipse cx="260" cy="260" rx="150" ry="58" fill="none" stroke="url(#<?php echo esc_attr( $rolck_uid ); ?>-ring)" stroke-opacity=".35" stroke-width="1.2" transform="rotate(32 260 260)"/>
			<circle cx="134" cy="182" r="5" fill="#f5f5fa"/>
		</g>

		<g class="rolck-cosmic__blob">
			<circle cx="260" cy="260" r="106" fill="url(#<?php echo esc_attr( $rolck_uid ); ?>-blob)"/>
			<ellipse cx="226" cy="208" rx="46" ry="18" fill="#ffffff" fill-opacity=".14" transform="rotate(-28 226 208)"/>
			<text x="260" y="260" dominant-baseline="central" text-anchor="middle" class="rolck-cosmic__center"><?php echo esc_html( $rolck_center ); ?></text>
		</g>

		<g fill="#ffffff">
			<circle cx="70" cy="90" r="1.5" opacity=".7"/><circle cx="452" cy="420" r="1.8" opacity=".6"/>
			<circle cx="410" cy="70" r="1.2" opacity=".5"/><circle cx="96" cy="430" r="1.4" opacity=".6"/>
			<circle cx="30" cy="270" r="1" opacity=".5"/><circle cx="500" cy="300" r="1.2" opacity=".5"/>
		</g>
	</svg>
	<?php foreach ( $rolck_chips as $rolck_i => $rolck_chip ) : ?>
		<span class="rolck-cosmic__chip rolck-cosmic__chip--<?php echo (int) $rolck_i + 1; ?>"><?php echo esc_html( $rolck_chip ); ?></span>
	<?php endforeach; ?>
</div>
