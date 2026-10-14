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

// Cores dos planetas: luz, cor e sombra (gradiente radial dá volume).
$rolck_colors = array(
	'cyan'   => array( '#cffafe', '#22d3ee', '#0e7490' ),
	'violet' => array( '#ede9fe', '#a78bfa', '#5b21b6' ),
	'pink'   => array( '#fce7f3', '#f472b6', '#9d174d' ),
	'white'  => array( '#ffffff', '#e2e8f0', '#64748b' ),
);

$rolck_ring_line = 'stroke="url(#' . esc_attr( $rolck_uid ) . '-ring)"';

// Órbitas que giram. Cada planeta: ângulo na órbita, raio, cor e se tem anel.
// Órbitas circulares sem traço (b2) põem planetas em outra velocidade sobre o mesmo anel.
$rolck_orbits = array(
	array(
		'name'    => 'e',
		'rx'      => 250,
		'ry'      => 250,
		'ring'    => 'stroke="#ffffff" stroke-opacity=".05"',
		'planets' => array( array( 70, 3, 'white', false ), array( 250, 5, 'pink', false ) ),
	),
	array(
		'name'    => 'a',
		'rx'      => 236,
		'ry'      => 92,
		'tilt'    => -24,
		'ring'    => $rolck_ring_line . ' stroke-opacity=".45" stroke-width="1.5"',
		'planets' => array( array( -20, 9, 'cyan', false ), array( 60, 6, 'pink', false ), array( 160, 5, 'violet', false ), array( 215, 3.5, 'white', false ) ),
	),
	array(
		'name'    => 'b',
		'rx'      => 190,
		'ry'      => 190,
		'ring'    => 'stroke="#ffffff" stroke-opacity=".08" stroke-dasharray="3 9"',
		'planets' => array( array( -90, 6, 'violet', false ), array( 200, 3.5, 'white', false ) ),
	),
	array(
		'name'    => 'b2',
		'rx'      => 190,
		'ry'      => 190,
		'planets' => array( array( 135, 8, 'violet', true ), array( 330, 4, 'cyan', false ) ),
	),
	array(
		'name'    => 'c',
		'rx'      => 150,
		'ry'      => 58,
		'tilt'    => 32,
		'ring'    => $rolck_ring_line . ' stroke-opacity=".35" stroke-width="1.2"',
		'planets' => array( array( 185, 5, 'white', false ), array( 355, 6.5, 'pink', false ), array( 150, 3, 'cyan', false ) ),
	),
	array(
		'name'    => 'd',
		'rx'      => 128,
		'ry'      => 128,
		'ring'    => 'stroke="#ffffff" stroke-opacity=".06" stroke-dasharray="2 6"',
		'planets' => array( array( 45, 4, 'cyan', false ), array( 225, 2.5, 'white', false ) ),
	),
);

// Planetas parados: x, y, raio, cor e se tem anel.
$rolck_still = array(
	array( 58, 132, 6, 'pink', false ),
	array( 40, 352, 7, 'violet', true ),
	array( 488, 372, 5, 'cyan', false ),
	array( 196, 492, 4, 'violet', false ),
	array( 432, 30, 3, 'white', false ),
);

// Estrelas: x, y, raio e opacidade. Uma em cada três pisca.
$rolck_stars = array(
	array( 70, 90, 1.5, .7 ),
	array( 452, 420, 1.8, .6 ),
	array( 410, 70, 1.2, .5 ),
	array( 96, 430, 1.4, .6 ),
	array( 30, 270, 1, .5 ),
	array( 500, 300, 1.2, .5 ),
	array( 150, 40, 1.3, .6 ),
	array( 330, 18, 1, .5 ),
	array( 506, 190, 1.4, .6 ),
	array( 300, 506, 1.2, .5 ),
	array( 14, 190, 1.1, .5 ),
	array( 380, 480, 1, .6 ),
	array( 240, 10, 1.4, .5 ),
	array( 120, 500, 1, .5 ),
);

$rolck_planet = static function ( float $x, float $y, float $r, string $color, bool $rings ) use ( $rolck_uid, $rolck_colors ): void {
	$fill = 'url(#' . $rolck_uid . '-' . $color . ')';
	if ( $rings ) {
		printf(
			'<ellipse cx="%1$.1F" cy="%2$.1F" rx="%3$.1F" ry="%4$.1F" fill="none" stroke="%5$s" stroke-opacity=".75" stroke-width="1.2" transform="rotate(-18 %1$.1F %2$.1F)"/>',
			$x,
			$y,
			$r * 1.9,
			$r * 0.5,
			esc_attr( $rolck_colors[ $color ][1] )
		);
	}
	printf( '<circle cx="%.1F" cy="%.1F" r="%s" fill="%s"/>', $x, $y, esc_attr( (string) $r ), esc_attr( $fill ) );
};
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
			<?php foreach ( $rolck_colors as $rolck_name => [ $rolck_light, $rolck_base, $rolck_dark ] ) : ?>
				<radialGradient id="<?php echo esc_attr( $rolck_uid . '-' . $rolck_name ); ?>" cx="50%" cy="50%" r="60%" fx="32%" fy="30%">
					<stop offset="0%" stop-color="<?php echo esc_attr( $rolck_light ); ?>"/>
					<stop offset="55%" stop-color="<?php echo esc_attr( $rolck_base ); ?>"/>
					<stop offset="100%" stop-color="<?php echo esc_attr( $rolck_dark ); ?>"/>
				</radialGradient>
			<?php endforeach; ?>
		</defs>

		<circle cx="260" cy="260" r="230" fill="url(#<?php echo esc_attr( $rolck_uid ); ?>-glow)"/>

		<?php foreach ( $rolck_orbits as $rolck_orbit ) : ?>
			<g class="rolck-cosmic__orbit rolck-cosmic__orbit--<?php echo esc_attr( $rolck_orbit['name'] ); ?>">
				<g transform="rotate(<?php echo (float) ( $rolck_orbit['tilt'] ?? 0 ); ?> 260 260)">
					<?php if ( ! empty( $rolck_orbit['ring'] ) ) : ?>
						<ellipse cx="260" cy="260" rx="<?php echo (float) $rolck_orbit['rx']; ?>" ry="<?php echo (float) $rolck_orbit['ry']; ?>" fill="none" <?php echo $rolck_orbit['ring']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- atributos fixos definidos acima. ?>/>
					<?php endif; ?>
					<?php
					foreach ( $rolck_orbit['planets'] as [ $rolck_angle, $rolck_r, $rolck_color, $rolck_rings ] ) {
						$rolck_planet( 260 + $rolck_orbit['rx'] * cos( deg2rad( $rolck_angle ) ), 260 + $rolck_orbit['ry'] * sin( deg2rad( $rolck_angle ) ), $rolck_r, $rolck_color, $rolck_rings );
					}
					?>
				</g>
			</g>
		<?php endforeach; ?>

		<g class="rolck-cosmic__still">
			<?php
			foreach ( $rolck_still as [ $rolck_x, $rolck_y, $rolck_r, $rolck_color, $rolck_rings ] ) {
				$rolck_planet( $rolck_x, $rolck_y, $rolck_r, $rolck_color, $rolck_rings );
			}
			?>
		</g>

		<g class="rolck-cosmic__blob">
			<circle cx="260" cy="260" r="106" fill="url(#<?php echo esc_attr( $rolck_uid ); ?>-blob)"/>
			<ellipse cx="226" cy="208" rx="46" ry="18" fill="#ffffff" fill-opacity=".14" transform="rotate(-28 226 208)"/>
			<text x="260" y="260" dominant-baseline="central" text-anchor="middle" class="rolck-cosmic__center"><?php echo esc_html( $rolck_center ); ?></text>
		</g>

		<g fill="#ffffff">
			<?php foreach ( $rolck_stars as $rolck_i => [ $rolck_x, $rolck_y, $rolck_r, $rolck_o ] ) : ?>
				<circle cx="<?php echo (int) $rolck_x; ?>" cy="<?php echo (int) $rolck_y; ?>" r="<?php echo (float) $rolck_r; ?>" opacity="<?php echo (float) $rolck_o; ?>"<?php echo 0 === $rolck_i % 3 ? ' class="rolck-cosmic__twinkle" style="animation-delay:-' . (int) $rolck_i . 's"' : ''; ?>/>
			<?php endforeach; ?>
		</g>
	</svg>
	<?php foreach ( $rolck_chips as $rolck_i => $rolck_chip ) : ?>
		<span class="rolck-cosmic__chip rolck-cosmic__chip--<?php echo (int) $rolck_i + 1; ?>"><?php echo esc_html( $rolck_chip ); ?></span>
	<?php endforeach; ?>
</div>
