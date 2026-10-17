<?php
/**
 * Sitemap com as URLs que o sitemap nativo não lista:
 *
 * - archives: a vitrine de projetos em cada idioma (/projetos/, /en/projects/);
 * - terms:    as categorias e tecnologias nos outros idiomas (/en/project-category/…),
 *             que o sitemap nativo só lista no idioma padrão.
 *
 * Aparece no índice como /wp-sitemap-rolck-archives-1.xml e
 * /wp-sitemap-rolck-terms-1.xml.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\Seo;

use RolckGalaxy\Core\Content\ProjectRewrites;
use RolckGalaxy\Core\Content\Projects;
use RolckGalaxy\Core\I18n\Languages;
use RolckGalaxy\Core\I18n\TermTranslations;
use WP_Sitemaps_Provider;
use WP_Term;

defined( 'ABSPATH' ) || exit;

final class LanguageSitemap extends WP_Sitemaps_Provider {

	public function __construct() {
		$this->name        = 'rolck';
		$this->object_type = 'rolck';
	}

	/** @return array<string,object> */
	public function get_object_subtypes() {
		return array(
			'archives' => (object) array( 'name' => 'archives' ),
			'terms'    => (object) array( 'name' => 'terms' ),
		);
	}

	/**
	 * @param int    $page_num       Página do sitemap.
	 * @param string $object_subtype archives ou terms.
	 * @return array<int,array{loc:string}>
	 */
	public function get_url_list( $page_num, $object_subtype = '' ) {
		if ( $page_num > 1 ) {
			return array();
		}
		$urls = 'archives' === $object_subtype ? self::archives() : self::terms();
		return array_map( static fn( string $loc ): array => array( 'loc' => $loc ), array_values( array_unique( $urls ) ) );
	}

	/**
	 * @param string $object_subtype archives ou terms.
	 */
	public function get_max_num_pages( $object_subtype = '' ) {
		return 1;
	}

	/** @return string[] */
	private static function archives(): array {
		$base = get_post_type_archive_link( Projects::POST_TYPE );
		if ( ! $base ) {
			return array();
		}
		return array_map( static fn( string $code ): string => self::in_language( $base, $code ), Languages::codes() );
	}

	/** @return string[] Termos com conteúdo, só nos idiomas além do padrão. */
	private static function terms(): array {
		$taxonomies = array_intersect( TermTranslations::taxonomies(), array_keys( wp_get_sitemap_providers()['taxonomies']->get_object_subtypes() ) );
		$terms      = get_terms(
			array(
				'taxonomy'   => array_values( $taxonomies ),
				'hide_empty' => true,
			)
		);
		if ( ! is_array( $terms ) ) {
			return array();
		}

		$urls = array();
		foreach ( $terms as $term ) {
			$link = $term instanceof WP_Term ? get_term_link( $term ) : '';
			if ( ! is_string( $link ) ) {
				continue;
			}
			foreach ( Languages::codes() as $code ) {
				if ( ! Languages::is_default( $code ) ) {
					$urls[] = self::in_language( $link, $code );
				}
			}
		}
		return $urls;
	}

	/** URL do idioma padrão convertida para outro idioma, com os slugs traduzidos. */
	private static function in_language( string $url, string $code ): string {
		return Languages::is_default( $code ) ? $url : ProjectRewrites::localize( Languages::convert_url( $url, $code ), $code );
	}
}
