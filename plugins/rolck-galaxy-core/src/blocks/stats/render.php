<?php
/**
 * Números em destaque. O HTML já sai com o valor final (bom para SEO e sem
 * JavaScript); a contagem animada é só um enfeite quando o bloco entra na tela.
 *
 * @package RolckGalaxy
 *
 * @var array $attributes Atributos do bloco.
 */

defined( 'ABSPATH' ) || exit;

$rolck_items = array_filter( (array) ( $attributes['items'] ?? array() ), static fn( $i ) => is_array( $i ) && '' !== ( $i['value'] ?? '' ) );
if ( ! $rolck_items ) {
	return;
}
$rolck_animate = ! empty( $attributes['animate'] );
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'rolck-stats' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo $rolck_animate ? ' data-wp-interactive="rolck/stats"' : ''; ?>>
	<dl class="rolck-stats__list">
		<?php foreach ( $rolck_items as $rolck_item ) : ?>
			<?php
			$rolck_value  = (string) $rolck_item['value'];
			$rolck_number = is_numeric( str_replace( array( '.', ',' ), '', $rolck_value ) );
			?>
			<div class="rolck-stats__item">
				<dt class="rolck-stats__label"><?php echo wp_kses_post( (string) ( $rolck_item['label'] ?? '' ) ); ?></dt>
				<dd class="rolck-stats__value">
					<?php echo esc_html( (string) ( $rolck_item['prefix'] ?? '' ) ); ?><span
						class="rolck-stats__number"
						<?php if ( $rolck_animate && $rolck_number ) : ?>
							data-wp-init="callbacks.countUp"
							data-target="<?php echo esc_attr( $rolck_value ); ?>"
						<?php endif; ?>
					><?php echo esc_html( $rolck_value ); ?></span><?php echo esc_html( (string) ( $rolck_item['suffix'] ?? '' ) ); ?>
				</dd>
			</div>
		<?php endforeach; ?>
	</dl>
</div>
