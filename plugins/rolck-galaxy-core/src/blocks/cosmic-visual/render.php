<?php
/**
 * Visual cósmico decorativo em 3D: cada anel é um círculo inclinado no espaço,
 * projetado na tela. A metade de trás do anel fica atrás da esfera e a da
 * frente passa por cima dela; planetas na frente ficam um pouco maiores.
 *
 * O HTML já sai com tudo no lugar (sem JavaScript o desenho fica parado).
 * O view.js anda com os planetas que têm velocidade e inverte o sentido
 * quando um deles vai encostar em outro. Quem prefere menos movimento vê o
 * desenho parado. Fica escondido dos leitores de tela.
 *
 * @package RolckGalaxy
 *
 * @var array $attributes Atributos do bloco.
 */

defined( 'ABSPATH' ) || exit;

$rolck_chips  = array_slice( array_filter( array_map( 'strval', (array) ( $attributes['chips'] ?? array() ) ) ), 0, 4 );
$rolck_center = mb_substr( (string) ( $attributes['center'] ?? '' ), 0, 3 );
$rolck_uid    = wp_unique_id( 'rolck-cosmic-' );

// Cores dos planetas: luz, cor e sombra (gradiente radial dá volume).
$rolck_colors = array(
	'cyan'   => array( '#cffafe', '#22d3ee', '#0e7490' ),
	'violet' => array( '#ede9fe', '#a78bfa', '#5b21b6' ),
	'pink'   => array( '#fce7f3', '#f472b6', '#9d174d' ),
	'white'  => array( '#ffffff', '#e2e8f0', '#64748b' ),
);

$rolck_ring = 'url(#' . $rolck_uid . '-ring)';

// Órbitas: raio, inclinação (0 = vista de frente, como um círculo), giro na
// tela e estilo do traço. Planetas: ângulo na órbita (0 = direita, 90 = mais
// perto de quem olha), raio, cor, se tem anel e velocidade em graus por
// segundo (0 = parado no anel; negativo = sentido contrário).
$rolck_orbits = array(
	array(
		'R'       => 248,
		'inc'     => 0,
		'rot'     => 0,
		'stroke'  => array( '#ffffff', 0.05, 1, '' ),
		'planets' => array( array( 70, 3, 'white', false, 0 ), array( 250, 5, 'pink', false, -3 ) ),
	),
	array(
		'R'       => 190,
		'inc'     => 0,
		'rot'     => 0,
		'stroke'  => array( '#ffffff', 0.08, 1, '3 9' ),
		'planets' => array( array( 270, 6, 'violet', false, -4.5 ), array( 200, 3.5, 'white', false, 0 ), array( 135, 8, 'violet', true, 6.4 ), array( 330, 4, 'cyan', false, 0 ) ),
	),
	array(
		'R'       => 128,
		'inc'     => 0,
		'rot'     => 0,
		'stroke'  => array( '#ffffff', 0.06, 1, '2 6' ),
		'planets' => array( array( 45, 4, 'cyan', false, -22 ), array( 225, 2.5, 'white', false, 0 ) ),
	),
	array(
		'R'       => 236,
		'inc'     => 68,
		'rot'     => -24,
		'stroke'  => array( $rolck_ring, 0.5, 1.5, '' ),
		'planets' => array( array( 340, 9, 'cyan', false, 12 ), array( 60, 6, 'pink', false, 0 ), array( 160, 5, 'violet', false, -8.6 ), array( 215, 3.5, 'white', false, 0 ) ),
	),
	array(
		'R'       => 150,
		'inc'     => 70,
		'rot'     => 32,
		'stroke'  => array( $rolck_ring, 0.45, 1.2, '' ),
		'planets' => array( array( 185, 5, 'white', false, 0 ), array( 355, 6.5, 'pink', false, 18 ), array( 150, 3, 'cyan', false, 0 ) ),
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

/**
 * Posição na tela de um ponto da órbita: x, y, profundidade (-1 atrás, 1 na frente) e escala.
 *
 * @return array{0:float,1:float,2:float,3:float}
 */
$rolck_project = static function ( array $orbit, float $angle ): array {
	$t   = deg2rad( $angle );
	$rot = deg2rad( $orbit['rot'] );
	$lx  = $orbit['R'] * cos( $t );
	$ly  = $orbit['R'] * cos( deg2rad( $orbit['inc'] ) ) * sin( $t );
	$z   = sin( $t ) * sin( deg2rad( $orbit['inc'] ) );
	return array( 260 + $lx * cos( $rot ) - $ly * sin( $rot ), 260 + $lx * sin( $rot ) + $ly * cos( $rot ), $z, 1 + 0.2 * $z );
};

$rolck_planet = static function ( float $x, float $y, float $r, string $color, bool $rings, float $scale = 1, string $attrs = '' ) use ( $rolck_uid, $rolck_colors ): void {
	printf( '<g class="rolck-cosmic__planet" transform="translate(%.1F %.1F) scale(%.3F)"%s>', $x, $y, $scale, $attrs ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- números e atributos montados aqui.
	if ( $rings ) {
		printf( '<ellipse rx="%.1F" ry="%.1F" fill="none" stroke="%s" stroke-opacity=".75" stroke-width="1.2" transform="rotate(-18)"/>', $r * 1.9, $r * 0.5, esc_attr( $rolck_colors[ $color ][1] ) );
	}
	printf( '<circle r="%s" fill="%s"/></g>', esc_attr( (string) $r ), esc_attr( 'url(#' . $rolck_uid . '-' . $color . ')' ) );
};

// Separa o que fica atrás da esfera do que passa na frente, e monta os dados do view.js.
$rolck_layers = array(
	'back'  => array(),
	'front' => array(),
);
$rolck_bodies = array();
foreach ( $rolck_orbits as $rolck_o => $rolck_orbit ) {
	foreach ( $rolck_orbit['planets'] as [ $rolck_angle, $rolck_r, $rolck_color, $rolck_rings, $rolck_speed ] ) {
		[ $rolck_x, $rolck_y, $rolck_z, $rolck_s ] = $rolck_project( $rolck_orbit, $rolck_angle );
		$rolck_layers[ $rolck_z > 0 ? 'front' : 'back' ][] = array( $rolck_x, $rolck_y, $rolck_r, $rolck_color, $rolck_rings, $rolck_s, count( $rolck_bodies ) );
		$rolck_bodies[] = array(
			'o' => $rolck_o,
			't' => $rolck_angle,
			'r' => $rolck_r,
			'v' => $rolck_speed,
		);
	}
}
$rolck_motion = array(
	'orbits' => array_map(
		static fn( array $o ): array => array(
			'R'   => $o['R'],
			'inc' => $o['inc'],
			'rot' => $o['rot'],
		),
		$rolck_orbits
	),
	'bodies' => $rolck_bodies,
	'still'  => array_map( static fn( array $p ): array => array( $p[0], $p[1], $p[2] ), $rolck_still ),
);

/**
 * Traço do anel: inteiro (vista de frente) ou só a metade de trás/da frente.
 *
 * @param array  $orbit Órbita.
 * @param string $half  'all', 'back' ou 'front'.
 */
$rolck_ring_path = static function ( array $orbit, string $half ): void {
	[ $color, $opacity, $width, $dash ] = $orbit['stroke'];
	$r  = (float) $orbit['R'];
	$ry = $r * cos( deg2rad( $orbit['inc'] ) );
	$d  = match ( $half ) {
		'front' => sprintf( 'M%.1F,260 A%.1F,%.1F 0 0 1 %.1F,260', 260 + $r, $r, $ry, 260 - $r ),
		'back'  => sprintf( 'M%.1F,260 A%.1F,%.1F 0 0 1 %.1F,260', 260 - $r, $r, $ry, 260 + $r ),
		default => sprintf( 'M%1$.1F,260 A%2$.1F,%3$.1F 0 1 1 %4$.1F,260 A%2$.1F,%3$.1F 0 1 1 %1$.1F,260', 260 + $r, $r, $ry, 260 - $r ),
	};
	printf(
		'<path d="%s" fill="none" stroke="%s" stroke-opacity="%.2F" stroke-width="%s"%s transform="rotate(%s 260 260)"/>',
		esc_attr( $d ),
		esc_attr( $color ),
		'back' === $half ? $opacity * 0.55 : $opacity,
		esc_attr( (string) $width ),
		$dash ? ' stroke-dasharray="' . esc_attr( $dash ) . '"' : '',
		esc_attr( (string) $orbit['rot'] )
	);
};
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'rolck-cosmic' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-hidden="true">
	<svg class="rolck-cosmic__svg" viewBox="0 0 520 520" width="520" height="520" focusable="false" data-motion="<?php echo esc_attr( wp_json_encode( $rolck_motion ) ); ?>">
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
			<radialGradient id="<?php echo esc_attr( $rolck_uid ); ?>-shade" cx="38%" cy="32%" r="78%">
				<stop offset="0%" stop-color="#ffffff" stop-opacity=".22"/>
				<stop offset="45%" stop-color="#ffffff" stop-opacity="0"/>
				<stop offset="100%" stop-color="#05051a" stop-opacity=".6"/>
			</radialGradient>
			<?php foreach ( $rolck_colors as $rolck_name => [ $rolck_light, $rolck_base, $rolck_dark ] ) : ?>
				<radialGradient id="<?php echo esc_attr( $rolck_uid . '-' . $rolck_name ); ?>" cx="50%" cy="50%" r="60%" fx="32%" fy="30%">
					<stop offset="0%" stop-color="<?php echo esc_attr( $rolck_light ); ?>"/>
					<stop offset="55%" stop-color="<?php echo esc_attr( $rolck_base ); ?>"/>
					<stop offset="100%" stop-color="<?php echo esc_attr( $rolck_dark ); ?>"/>
				</radialGradient>
			<?php endforeach; ?>
		</defs>

		<g class="rolck-cosmic__system">
			<circle cx="260" cy="260" r="230" fill="url(#<?php echo esc_attr( $rolck_uid ); ?>-glow)"/>

			<g data-layer="back">
				<?php
				foreach ( $rolck_orbits as $rolck_orbit ) {
					$rolck_ring_path( $rolck_orbit, $rolck_orbit['inc'] ? 'back' : 'all' );
				}
				foreach ( $rolck_layers['back'] as [ $rolck_x, $rolck_y, $rolck_r, $rolck_color, $rolck_rings, $rolck_s, $rolck_i ] ) {
					$rolck_planet( $rolck_x, $rolck_y, $rolck_r, $rolck_color, $rolck_rings, $rolck_s, ' data-p="' . (int) $rolck_i . '"' );
				}
				?>
			</g>

			<g class="rolck-cosmic__blob">
				<circle cx="260" cy="260" r="106" fill="url(#<?php echo esc_attr( $rolck_uid ); ?>-blob)"/>
				<circle cx="260" cy="260" r="106" fill="url(#<?php echo esc_attr( $rolck_uid ); ?>-shade)"/>
				<ellipse cx="222" cy="206" rx="40" ry="15" fill="#ffffff" fill-opacity=".16" transform="rotate(-28 222 206)"/>
				<circle cx="260" cy="260" r="107" fill="none" stroke="#c4b5fd" stroke-opacity=".35" stroke-width="1.5"/>
				<?php if ( '' !== trim( $rolck_center ) ) : ?>
					<text x="260" y="260" dominant-baseline="central" text-anchor="middle" class="rolck-cosmic__center"><?php echo esc_html( $rolck_center ); ?></text>
				<?php endif; ?>
			</g>

			<g data-layer="front">
				<?php
				foreach ( $rolck_orbits as $rolck_orbit ) {
					if ( $rolck_orbit['inc'] ) {
						$rolck_ring_path( $rolck_orbit, 'front' );
					}
				}
				foreach ( $rolck_layers['front'] as [ $rolck_x, $rolck_y, $rolck_r, $rolck_color, $rolck_rings, $rolck_s, $rolck_i ] ) {
					$rolck_planet( $rolck_x, $rolck_y, $rolck_r, $rolck_color, $rolck_rings, $rolck_s, ' data-p="' . (int) $rolck_i . '"' );
				}
				?>
			</g>
		</g>

		<g class="rolck-cosmic__still">
			<?php
			foreach ( $rolck_still as [ $rolck_x, $rolck_y, $rolck_r, $rolck_color, $rolck_rings ] ) {
				$rolck_planet( $rolck_x, $rolck_y, $rolck_r, $rolck_color, $rolck_rings );
			}
			?>
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
