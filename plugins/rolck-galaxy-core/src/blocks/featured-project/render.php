<?php
/**
 * Projeto em destaque (capa grande + resumo + destaques + botões).
 *
 * @package RolckGalaxy
 *
 * @var array $attributes Atributos do bloco.
 */

use RolckGalaxy\Core\Content\Projects;
use RolckGalaxy\Core\I18n\Languages;
use RolckGalaxy\Core\I18n\Strings;
use RolckGalaxy\Core\I18n\Translations;

defined( 'ABSPATH' ) || exit;

$rolck_id = (int) ( $attributes['projectId'] ?? 0 );
if ( $rolck_id ) {
	// Na página em inglês, usa a versão em inglês do projeto escolhido.
	$rolck_id = Translations::translation_of( $rolck_id, Languages::current() ) ?: $rolck_id;
} else {
	$rolck_ids = get_posts(
		Translations::with_language(
			array(
				'post_type'      => Projects::POST_TYPE,
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => 'rolck_featured', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'no_found_rows'  => true,
			)
		)
	);
	$rolck_id  = (int) ( $rolck_ids[0] ?? 0 );
}
$rolck_post = $rolck_id ? get_post( $rolck_id ) : null;
if ( ! $rolck_post || 'publish' !== $rolck_post->post_status ) {
	return;
}

$rolck_data    = Projects::data( $rolck_post->ID );
$rolck_types   = get_the_terms( $rolck_post, Projects::TYPE_TAX );
$rolck_eyebrow = trim( (string) ( $attributes['eyebrow'] ?? '' ) ) ?: ( is_array( $rolck_types ) && $rolck_types ? $rolck_types[0]->name : Strings::get( 'featured' ) );
$rolck_url     = (string) get_permalink( $rolck_post );
$rolck_text    = $rolck_data['tagline'] ?: get_the_excerpt( $rolck_post );
$rolck_style   = $rolck_data['accent'] ? '--rolck-accent:' . $rolck_data['accent'] : '';
?>
<article <?php echo get_block_wrapper_attributes( array( 'class' => 'rolck-spotlight' . ( ! empty( $attributes['reverse'] ) ? ' is-reverse' : '' ), 'style' => $rolck_style ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<a class="rolck-spotlight__media" href="<?php echo esc_url( $rolck_url ); ?>" tabindex="-1" aria-hidden="true">
		<?php
		if ( has_post_thumbnail( $rolck_post ) ) {
			echo get_the_post_thumbnail(
				$rolck_post,
				'large',
				array(
					'alt'           => '',
					'sizes'         => '(max-width: 900px) 100vw, 640px',
					'loading'       => empty( $attributes['eager'] ) ? 'lazy' : 'eager',
					'fetchpriority' => empty( $attributes['eager'] ) ? 'auto' : 'high',
					'decoding'      => 'async',
				)
			);
		}
		?>
	</a>
	<div class="rolck-spotlight__body">
		<p class="rolck-spotlight__eyebrow">
			<span><?php echo esc_html( $rolck_eyebrow ); ?></span>
			<span class="rolck-badge rolck-badge--<?php echo esc_attr( $rolck_data['status'] ); ?>"><?php echo esc_html( Strings::get( 'status_' . $rolck_data['status'] ) ); ?></span>
		</p>
		<h2 class="rolck-spotlight__title"><a href="<?php echo esc_url( $rolck_url ); ?>"><?php echo esc_html( get_the_title( $rolck_post ) ); ?></a></h2>
		<?php if ( $rolck_text ) : ?>
			<p class="rolck-spotlight__text"><?php echo esc_html( $rolck_text ); ?></p>
		<?php endif; ?>
		<?php if ( $rolck_data['highlights'] ) : ?>
			<ul class="rolck-spotlight__list">
				<?php foreach ( array_slice( (array) $rolck_data['highlights'], 0, 4 ) as $rolck_item ) : ?>
					<li><?php echo esc_html( $rolck_item ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<div class="rolck-spotlight__actions">
			<a class="rolck-btn rolck-btn--primary" href="<?php echo esc_url( $rolck_url ); ?>"><?php echo esc_html( Strings::get( 'view_project' ) ); ?><span class="rolck-sr-only">: <?php echo esc_html( get_the_title( $rolck_post ) ); ?></span></a>
			<?php if ( $rolck_data['demo_url'] ) : ?>
				<a class="rolck-btn rolck-btn--ghost" href="<?php echo esc_url( $rolck_data['demo_url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( Strings::get( 'live_demo' ) ); ?> <span aria-hidden="true">↗</span></a>
			<?php endif; ?>
			<?php if ( $rolck_data['price'] ) : ?>
				<span class="rolck-spotlight__price"><?php echo esc_html( $rolck_data['price'] ); ?></span>
			<?php endif; ?>
		</div>
	</div>
</article>
