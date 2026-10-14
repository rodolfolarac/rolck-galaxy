<?php
/**
 * Marcação do cartão de projeto, compartilhada pelos blocos Grade de projetos
 * e Projeto em destaque. Mudar o cartão aqui muda em todo o site.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\Blocks;

use RolckGalaxy\Core\Content\Projects;
use RolckGalaxy\Core\I18n\Strings;
use WP_Post;

defined( 'ABSPATH' ) || exit;

final class ProjectCard {

	/**
	 * @param WP_Post $post    Projeto.
	 * @param array   $options heading (h2|h3|h4), excerpt (bool), eager (bool: imagem acima da dobra), extra_attrs (string).
	 */
	public static function render( WP_Post $post, array $options = array() ): string {
		$heading = in_array( $options['heading'] ?? 'h3', array( 'h2', 'h3', 'h4' ), true ) ? $options['heading'] : 'h3';
		$data    = Projects::data( $post->ID );
		$url     = (string) get_permalink( $post );
		$title   = get_the_title( $post );
		$types   = get_the_terms( $post, Projects::TYPE_TAX );
		$tech    = get_the_terms( $post, Projects::TECH_TAX );
		$text    = $data['tagline'] ?: ( has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( wp_strip_all_tags( excerpt_remove_blocks( $post->post_content ) ), 22 ) );
		$style   = $data['accent'] ? sprintf( ' style="--rolck-accent:%s"', esc_attr( $data['accent'] ) ) : '';

		ob_start();
		?>
		<article class="rolck-card"<?php echo $style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado acima. ?> <?php echo $options['extra_attrs'] ?? ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- atributos montados pelo bloco com wp_interactivity_data_wp_context(). ?>>
			<div class="rolck-card__media">
				<?php
				if ( has_post_thumbnail( $post ) ) {
					echo get_the_post_thumbnail(
						$post,
						'large',
						array(
							'class'         => 'rolck-card__image',
							'alt'           => '',
							'sizes'         => '(max-width: 640px) 100vw, (max-width: 1100px) 50vw, 400px',
							'loading'       => empty( $options['eager'] ) ? 'lazy' : 'eager',
							'fetchpriority' => empty( $options['eager'] ) ? 'auto' : 'high',
							'decoding'      => 'async',
						)
					);
				} else {
					printf( '<span class="rolck-card__placeholder" aria-hidden="true">%s</span>', esc_html( mb_substr( $title, 0, 1 ) ) );
				}
				?>
				<?php if ( $data['featured'] ) : ?>
					<span class="rolck-card__flag"><?php echo esc_html( Strings::get( 'featured' ) ); ?></span>
				<?php endif; ?>
			</div>
			<div class="rolck-card__body">
				<p class="rolck-card__meta">
					<span class="rolck-badge rolck-badge--<?php echo esc_attr( $data['status'] ); ?>"><?php echo esc_html( Strings::get( 'status_' . $data['status'] ) ); ?></span>
					<?php if ( is_array( $types ) && $types ) : ?>
						<span class="rolck-card__type"><?php echo esc_html( $types[0]->name ); ?></span>
					<?php endif; ?>
				</p>
				<<?php echo esc_html( $heading ); ?> class="rolck-card__title"><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $title ); ?></a></<?php echo esc_html( $heading ); ?>>
				<?php if ( ( $options['excerpt'] ?? true ) && $text ) : ?>
					<p class="rolck-card__text"><?php echo esc_html( $text ); ?></p>
				<?php endif; ?>
				<?php if ( is_array( $tech ) && $tech ) : ?>
					<ul class="rolck-card__tech" aria-label="<?php echo esc_attr( Strings::get( 'stack' ) ); ?>">
						<?php foreach ( array_slice( $tech, 0, 4 ) as $term ) : ?>
							<li><?php echo esc_html( $term->name ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<div class="rolck-card__actions">
					<span class="rolck-card__more" aria-hidden="true"><?php echo esc_html( Strings::get( 'view_project' ) ); ?> <span class="rolck-arrow">→</span></span>
					<?php if ( $data['price'] ) : ?>
						<span class="rolck-card__price"><?php echo esc_html( $data['price'] ); ?></span>
					<?php endif; ?>
					<?php if ( $data['demo_url'] ) : ?>
						<a class="rolck-card__demo" href="<?php echo esc_url( $data['demo_url'] ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( Strings::get( 'live_demo' ) . ': ' . $title ); ?>">
							<?php echo esc_html( Strings::get( 'live_demo' ) ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</article>
		<?php
		return (string) ob_get_clean();
	}
}
