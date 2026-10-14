<?php
/**
 * Grade de projetos.
 *
 * O filtro por categoria é feito de links reais para as páginas de
 * categoria (bons para SEO e funcionam sem JavaScript). Com JavaScript, a
 * Interactivity API filtra os cartões na própria página, sem recarregar.
 *
 * @package RolckGalaxy
 *
 * @var array $attributes Atributos do bloco.
 */

use RolckGalaxy\Core\Blocks\ProjectCard;
use RolckGalaxy\Core\Content\Projects;
use RolckGalaxy\Core\I18n\Strings;
use RolckGalaxy\Core\I18n\Translations;

defined( 'ABSPATH' ) || exit;

$rolck_inherit = ! empty( $attributes['inheritQuery'] ) && ( is_post_type_archive( Projects::POST_TYPE ) || is_tax( array( Projects::TYPE_TAX, Projects::TECH_TAX ) ) );

if ( $rolck_inherit ) {
	global $wp_query;
	$rolck_query = $wp_query;
} else {
	$rolck_args = array(
		'post_type'           => Projects::POST_TYPE,
		'post_status'         => 'publish',
		'posts_per_page'      => (int) $attributes['count'] > 0 ? (int) $attributes['count'] : 100,
		'orderby'             => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		),
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
	);
	if ( ! empty( $attributes['category'] ) ) {
		$rolck_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			array(
				'taxonomy' => Projects::TYPE_TAX,
				'terms'    => (int) $attributes['category'],
			),
		);
	}
	if ( ! empty( $attributes['featuredOnly'] ) ) {
		$rolck_args['meta_key']   = 'rolck_featured'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		$rolck_args['meta_value'] = '1'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
	}
	if ( ! empty( $attributes['excludeCurrent'] ) && is_singular( Projects::POST_TYPE ) ) {
		$rolck_args['post__not_in'] = array( get_queried_object_id() );
	}
	$rolck_query = new WP_Query( Translations::with_language( $rolck_args ) );
}

$rolck_posts = $rolck_query->posts;
$rolck_attrs = get_block_wrapper_attributes(
	array(
		'class' => 'rolck-projects',
		'style' => '--rolck-columns:' . max( 1, min( 4, (int) $attributes['columns'] ) ),
	)
);

if ( ! $rolck_posts ) {
	printf( '<div %s><p class="rolck-projects__empty">%s</p></div>', $rolck_attrs, esc_html( Strings::get( 'empty' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}

// Categorias presentes nesta grade, na ordem de quantidade de projetos.
$rolck_terms = array();
$rolck_cats  = array();
foreach ( $rolck_posts as $rolck_post ) {
	$rolck_post_terms           = get_the_terms( $rolck_post, Projects::TYPE_TAX );
	$rolck_cats[ $rolck_post->ID ] = array();
	foreach ( is_array( $rolck_post_terms ) ? $rolck_post_terms : array() as $rolck_term ) {
		$rolck_cats[ $rolck_post->ID ][] = $rolck_term->slug;
		$rolck_terms[ $rolck_term->slug ] ??= array(
			'term'  => $rolck_term,
			'count' => 0,
		);
		++$rolck_terms[ $rolck_term->slug ]['count'];
	}
}
uasort( $rolck_terms, static fn( $a, $b ) => $b['count'] <=> $a['count'] );

// Mesmo estado derivado do view.js, calculado no servidor para o HTML já sair certo.
wp_interactivity_state(
	'rolck/project-grid',
	array(
		'isVisible' => static function (): bool {
			$context = wp_interactivity_get_context();
			$active  = $context['active'] ?? 'all';
			return 'all' === $active || in_array( $active, (array) ( $context['cats'] ?? array() ), true );
		},
		'isCurrent' => static function (): ?string {
			$context = wp_interactivity_get_context();
			return ( $context['active'] ?? 'all' ) === ( $context['slug'] ?? '' ) ? 'true' : null;
		},
	)
);

$rolck_show_filters = ! empty( $attributes['showFilters'] ) && ! $rolck_inherit && empty( $attributes['category'] ) && count( $rolck_terms ) > 1;
$rolck_archive      = (string) get_post_type_archive_link( Projects::POST_TYPE );
?>
<div <?php echo $rolck_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> data-wp-interactive="rolck/project-grid" <?php echo wp_interactivity_data_wp_context( array( 'active' => 'all' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $rolck_show_filters ) : ?>
		<nav class="rolck-filters" aria-label="<?php echo esc_attr( Strings::get( 'filter_label' ) ); ?>">
			<a class="rolck-filters__item" href="<?php echo esc_url( $rolck_archive ); ?>" data-wp-on--click="actions.filter" data-wp-bind--aria-current="state.isCurrent" <?php echo wp_interactivity_data_wp_context( array( 'slug' => 'all' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?php echo esc_html( Strings::get( 'filter_all' ) ); ?>
				<span class="rolck-filters__count"><?php echo esc_html( (string) count( $rolck_posts ) ); ?></span>
			</a>
			<?php foreach ( $rolck_terms as $rolck_slug => $rolck_item ) : ?>
				<a class="rolck-filters__item" href="<?php echo esc_url( (string) get_term_link( $rolck_item['term'] ) ); ?>" data-wp-on--click="actions.filter" data-wp-bind--aria-current="state.isCurrent" <?php echo wp_interactivity_data_wp_context( array( 'slug' => $rolck_slug ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php echo esc_html( $rolck_item['term']->name ); ?>
					<span class="rolck-filters__count"><?php echo esc_html( (string) $rolck_item['count'] ); ?></span>
				</a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>

	<div class="rolck-projects__grid" role="list">
		<?php
		foreach ( array_values( $rolck_posts ) as $rolck_index => $rolck_post ) {
			// O papel de item de lista fica num invólucro: <article> não aceita role="listitem".
			$rolck_extra = $rolck_show_filters ? ' data-wp-bind--hidden="!state.isVisible" ' . wp_interactivity_data_wp_context( array( 'cats' => $rolck_cats[ $rolck_post->ID ] ) ) : '';
			echo '<div class="rolck-projects__item" role="listitem"' . $rolck_extra . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- contexto gerado por wp_interactivity_data_wp_context().
			echo ProjectCard::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- cartão escapado internamente.
				$rolck_post,
				array(
					'heading' => $attributes['headingLevel'] ?? 'h3',
					'excerpt' => ! empty( $attributes['showExcerpt'] ),
					'eager'   => $rolck_index < (int) ( $attributes['eagerImages'] ?? 0 ),
				)
			);
			echo '</div>';
		}
		?>
	</div>

	<?php
	if ( $rolck_inherit ) {
		$rolck_pagination = paginate_links(
			array(
				'total'     => (int) $rolck_query->max_num_pages,
				'current'   => max( 1, (int) get_query_var( 'paged' ) ),
				'prev_text' => '←',
				'next_text' => '→',
				'type'      => 'list',
			)
		);
		if ( $rolck_pagination ) {
			echo '<nav class="rolck-pagination" aria-label="' . esc_attr( Strings::get( 'projects' ) ) . '">' . wp_kses_post( $rolck_pagination ) . '</nav>';
		}
	}
	?>
</div>
