<?php
/**
 * Ficha do projeto: status, preço, botões (demo, compra, código), ficha
 * técnica e destaques. Os dados vêm do painel "Detalhes do projeto".
 *
 * @package RolckGalaxy
 *
 * @var array    $attributes Atributos do bloco.
 * @var WP_Block $block      Bloco (contexto postId).
 */

use RolckGalaxy\Core\Content\Projects;
use RolckGalaxy\Core\I18n\Strings;

defined( 'ABSPATH' ) || exit;

$rolck_id = (int) ( $block->context['postId'] ?? 0 );
if ( ! $rolck_id && isset( $_GET['post_id'] ) && current_user_can( 'edit_posts' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- só pré-visualização do editor.
	$rolck_id = absint( $_GET['post_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}
if ( ! $rolck_id ) {
	$rolck_id = get_the_ID();
}
if ( ! $rolck_id || Projects::POST_TYPE !== get_post_type( $rolck_id ) ) {
	return;
}

$rolck_data  = Projects::data( $rolck_id );
$rolck_show  = $attributes['show'] ?? 'all';
$rolck_types = get_the_terms( $rolck_id, Projects::TYPE_TAX );
$rolck_tech  = get_the_terms( $rolck_id, Projects::TECH_TAX );
$rolck_title = get_the_title( $rolck_id );

$rolck_specs = array_filter(
	array(
		'category'      => is_array( $rolck_types ) ? implode( ', ', wp_list_pluck( $rolck_types, 'name' ) ) : '',
		'version'       => $rolck_data['version'],
		'released'      => $rolck_data['released'] ? date_i18n( get_option( 'date_format' ), strtotime( $rolck_data['released'] ) ) : '',
		'compatibility' => $rolck_data['compat'],
	)
);
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'rolck-details rolck-details--' . $rolck_show ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( in_array( $rolck_show, array( 'all', 'actions' ), true ) ) : ?>
		<div class="rolck-details__top">
			<span class="rolck-badge rolck-badge--<?php echo esc_attr( $rolck_data['status'] ); ?>"><?php echo esc_html( Strings::get( 'status_' . $rolck_data['status'] ) ); ?></span>
			<?php if ( $rolck_data['price'] ) : ?>
				<p class="rolck-details__price"><span class="rolck-sr-only"><?php echo esc_html( Strings::get( 'price' ) ); ?>: </span><?php echo esc_html( $rolck_data['price'] ); ?></p>
			<?php endif; ?>
		</div>
		<div class="rolck-details__actions">
			<?php if ( $rolck_data['demo_url'] ) : ?>
				<a class="rolck-btn rolck-btn--primary" href="<?php echo esc_url( $rolck_data['demo_url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( Strings::get( 'live_demo' ) ); ?> <span aria-hidden="true">↗</span></a>
			<?php endif; ?>
			<?php if ( $rolck_data['buy_url'] ) : ?>
				<a class="rolck-btn rolck-btn--ghost" href="<?php echo esc_url( $rolck_data['buy_url'] ); ?>" target="_blank" rel="noopener sponsored"><?php echo esc_html( Strings::get( 'buy' ) ); ?></a>
			<?php endif; ?>
			<?php if ( $rolck_data['repo_url'] ) : ?>
				<a class="rolck-btn rolck-btn--ghost" href="<?php echo esc_url( $rolck_data['repo_url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( Strings::get( 'source' ) ); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( in_array( $rolck_show, array( 'all', 'specs' ), true ) && ( $rolck_specs || $rolck_tech ) ) : ?>
		<div class="rolck-details__box">
			<h2 class="rolck-details__heading"><?php echo esc_html( Strings::get( 'details' ) ); ?></h2>
			<dl class="rolck-details__specs">
				<?php foreach ( $rolck_specs as $rolck_key => $rolck_value ) : ?>
					<div>
						<dt><?php echo esc_html( Strings::get( $rolck_key ) ); ?></dt>
						<dd><?php echo esc_html( $rolck_value ); ?></dd>
					</div>
				<?php endforeach; ?>
				<?php if ( is_array( $rolck_tech ) && $rolck_tech ) : ?>
					<div class="rolck-details__stack">
						<dt><?php echo esc_html( Strings::get( 'stack' ) ); ?></dt>
						<dd>
							<ul>
								<?php foreach ( $rolck_tech as $rolck_term ) : ?>
									<li><?php echo esc_html( $rolck_term->name ); ?></li>
								<?php endforeach; ?>
							</ul>
						</dd>
					</div>
				<?php endif; ?>
			</dl>
		</div>
	<?php endif; ?>

	<?php if ( in_array( $rolck_show, array( 'all', 'highlights' ), true ) && $rolck_data['highlights'] ) : ?>
		<div class="rolck-details__box">
			<h2 class="rolck-details__heading"><?php echo esc_html( Strings::get( 'highlights' ) ); ?></h2>
			<ul class="rolck-details__highlights">
				<?php foreach ( (array) $rolck_data['highlights'] as $rolck_item ) : ?>
					<li><?php echo esc_html( $rolck_item ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>
</div>
