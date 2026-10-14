<?php
/**
 * Trilha de navegação da página atual. A mesma trilha alimenta o bloco
 * Breadcrumbs e o BreadcrumbList do JSON-LD, então os dois nunca divergem.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\Seo;

use RolckGalaxy\Core\Content\Projects;
use RolckGalaxy\Core\I18n\Languages;
use RolckGalaxy\Core\I18n\Strings;
use RolckGalaxy\Core\I18n\Switcher;
use WP_Post;
use WP_Term;

defined( 'ABSPATH' ) || exit;

final class Breadcrumbs {

	/** @return array<int,array{name:string,url:string}> Do início até a página atual. */
	public static function trail(): array {
		static $trail = null;
		if ( null !== $trail ) {
			return $trail;
		}

		$trail = array(
			array(
				'name' => Strings::get( 'home' ),
				'url'  => Languages::home_url( Languages::current() ),
			),
		);
		if ( is_front_page() ) {
			return $trail;
		}

		$object = get_queried_object();

		if ( is_singular() && $object instanceof WP_Post ) {
			if ( Projects::POST_TYPE === $object->post_type ) {
				$trail[] = self::projects_archive();
				$types   = get_the_terms( $object, Projects::TYPE_TAX );
				if ( is_array( $types ) && $types ) {
					$trail[] = self::term( $types[0] );
				}
			} elseif ( 'post' === $object->post_type ) {
				$trail[] = self::blog();
			} elseif ( 'page' === $object->post_type ) {
				foreach ( array_reverse( get_post_ancestors( $object ) ) as $ancestor ) {
					$trail[] = array(
						'name' => get_the_title( $ancestor ),
						'url'  => (string) get_permalink( $ancestor ),
					);
				}
			}
			$trail[] = array(
				'name' => get_the_title( $object ),
				'url'  => (string) get_permalink( $object ),
			);
		} elseif ( is_post_type_archive( Projects::POST_TYPE ) ) {
			$trail[] = self::projects_archive();
		} elseif ( is_tax( Projects::TYPE_TAX ) || is_tax( Projects::TECH_TAX ) ) {
			$trail[] = self::projects_archive();
			if ( $object instanceof WP_Term ) {
				$trail[] = self::term( $object );
			}
		} elseif ( is_home() ) {
			$trail[] = self::blog();
		} elseif ( ( is_category() || is_tag() ) && $object instanceof WP_Term ) {
			$trail[] = self::blog();
			$trail[] = self::term( $object );
		} elseif ( is_search() ) {
			$trail[] = array(
				'name' => Strings::get( 'search_results' ),
				'url'  => Switcher::current_url(),
			);
		}

		// Remove passos repetidos (ex.: blog que é a própria página atual).
		$seen  = array();
		$trail = array_values(
			array_filter(
				$trail,
				static function ( array $step ) use ( &$seen ): bool {
					if ( '' === $step['url'] || isset( $seen[ $step['url'] ] ) ) {
						return false;
					}
					return $seen[ $step['url'] ] = true;
				}
			)
		);
		return $trail;
	}

	/** @return array{name:string,url:string} */
	private static function projects_archive(): array {
		return array(
			'name' => Strings::get( 'projects' ),
			'url'  => (string) get_post_type_archive_link( Projects::POST_TYPE ),
		);
	}

	/** @return array{name:string,url:string} */
	private static function blog(): array {
		$page = (int) get_option( 'page_for_posts' );
		return array(
			'name' => $page ? get_the_title( $page ) : Strings::get( 'blog' ),
			'url'  => $page ? (string) get_permalink( $page ) : home_url( '/' ),
		);
	}

	/** @return array{name:string,url:string} */
	private static function term( WP_Term $term ): array {
		$link = get_term_link( $term );
		return array(
			'name' => $term->name,
			'url'  => is_wp_error( $link ) ? '' : $link,
		);
	}
}

