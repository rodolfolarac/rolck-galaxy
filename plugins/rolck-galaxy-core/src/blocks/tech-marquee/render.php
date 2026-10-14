<?php
/**
 * Faixa de tecnologias em movimento contínuo, 100% CSS.
 * A lista é repetida uma vez (escondida dos leitores de tela) para o loop não ter emenda.
 *
 * @package RolckGalaxy
 *
 * @var array $attributes Atributos do bloco.
 */

defined( 'ABSPATH' ) || exit;

$rolck_items = array_values( array_filter( array_map( 'strval', (array) ( $attributes['items'] ?? array() ) ) ) );
if ( ! $rolck_items ) {
	return;
}
$rolck_speed = max( 10, min( 120, (int) ( $attributes['speed'] ?? 40 ) ) );
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'rolck-marquee', 'style' => '--rolck-marquee-duration:' . $rolck_speed . 's' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="rolck-marquee__viewport">
		<ul class="rolck-marquee__track" aria-label="<?php echo esc_attr( (string) ( $attributes['label'] ?? '' ) ); ?>">
			<?php foreach ( $rolck_items as $rolck_item ) : ?>
				<li><?php echo esc_html( $rolck_item ); ?></li>
			<?php endforeach; ?>
		</ul>
		<ul class="rolck-marquee__track" aria-hidden="true">
			<?php foreach ( $rolck_items as $rolck_item ) : ?>
				<li><?php echo esc_html( $rolck_item ); ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
