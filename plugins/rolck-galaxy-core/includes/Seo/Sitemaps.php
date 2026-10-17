<?php
/**
 * Ajustes no sitemap nativo do WordPress (/wp-sitemap.xml):
 * sem a lista de usuários, sem conteúdos marcados como "não indexar", sem
 * taxonomias internas e com a vitrine e os termos de todos os idiomas
 * (LanguageSitemap).
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\Seo;

use RolckGalaxy\Core\Content\Projects;
use RolckGalaxy\Core\I18n\Translations;
use RolckGalaxy\Core\Settings;

defined( 'ABSPATH' ) || exit;

final class Sitemaps {

	public static function register(): void {
		add_filter( 'wp_sitemaps_add_provider', array( self::class, 'providers' ), 10, 2 );
		add_filter( 'wp_sitemaps_posts_query_args', array( self::class, 'skip_noindex' ) );
		add_filter( 'wp_sitemaps_taxonomies', array( self::class, 'taxonomies' ) );
		add_filter( 'wp_sitemaps_post_types', array( self::class, 'post_types' ) );
		add_action( 'wp_sitemaps_init', static fn() => wp_register_sitemap_provider( 'rolck', new LanguageSitemap() ) );
	}

	/**
	 * A lista de autores expõe nomes de usuário e não ajuda no ranqueamento.
	 *
	 * @param mixed  $provider Provedor.
	 * @param string $name     Nome.
	 */
	public static function providers( mixed $provider, string $name ): mixed {
		return 'users' === $name ? false : $provider;
	}

	/**
	 * @param array $args Argumentos da consulta do sitemap.
	 */
	public static function skip_noindex( array $args ): array {
		$args['lang']       = 'all';
		$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'relation' => 'OR',
			array(
				'key'     => '_rolck_seo_noindex',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_rolck_seo_noindex',
				'value'   => '1',
				'compare' => '!=',
			),
		);
		return $args;
	}

	/**
	 * @param array $taxonomies Taxonomias.
	 */
	public static function taxonomies( array $taxonomies ): array {
		unset( $taxonomies[ Translations::TAXONOMY ], $taxonomies['post_format'] );
		// Páginas de tecnologia marcadas como "não indexar" não entram no sitemap.
		if ( Settings::get( 'seo.noindex_tech', true ) ) {
			unset( $taxonomies[ Projects::TECH_TAX ] );
		}
		return $taxonomies;
	}

	/**
	 * @param array $types Tipos.
	 */
	public static function post_types( array $types ): array {
		unset( $types['attachment'] );
		return $types;
	}
}
