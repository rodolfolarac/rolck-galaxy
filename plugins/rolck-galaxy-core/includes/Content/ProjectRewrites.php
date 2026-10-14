<?php
/**
 * Slugs dos projetos traduzidos: /projetos/x em português e /en/projects/x em inglês.
 *
 * O tipo de conteúdo é registrado com o slug do idioma padrão; aqui entram as
 * regras de URL dos slugs dos outros idiomas e a troca do slug nos links.
 * Os slugs são editados em Rolck Galaxy → Projetos.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\Content;

use RolckGalaxy\Core\I18n\Languages;
use RolckGalaxy\Core\Settings;
use WP_Post;
use WP_Term;

defined( 'ABSPATH' ) || exit;

final class ProjectRewrites {

	public static function register(): void {
		add_action( 'init', array( self::class, 'add_rules' ), 11 );
		add_action( 'init', array( self::class, 'maybe_flush' ), 99 );
		add_filter( 'post_type_link', array( self::class, 'post_link' ), 30, 2 );
		add_filter( 'post_type_archive_link', array( self::class, 'archive_link' ), 30, 2 );
		add_filter( 'term_link', array( self::class, 'term_link' ), 30, 3 );
	}

	/**
	 * @param string $key  'slug' ou 'type_slug'.
	 * @param string $lang Idioma.
	 */
	private static function slug( string $key, string $lang ): string {
		$slug = sanitize_title( (string) Settings::get( "projects.$key.$lang", '' ) );
		return '' !== $slug ? $slug : sanitize_title( (string) Settings::get( "projects.$key." . Languages::default(), 'projetos' ) );
	}

	public static function add_rules(): void {
		$default = Languages::default();
		foreach ( Languages::codes() as $lang ) {
			$base = self::slug( 'slug', $lang );
			$type = self::slug( 'type_slug', $lang );
			if ( $lang === $default ) {
				continue;
			}
			if ( $base !== self::slug( 'slug', $default ) ) {
				add_rewrite_rule( "^$base/?$", 'index.php?post_type=' . Projects::POST_TYPE, 'top' );
				add_rewrite_rule( "^$base/page/([0-9]+)/?$", 'index.php?post_type=' . Projects::POST_TYPE . '&paged=$matches[1]', 'top' );
				add_rewrite_rule( "^$base/feed/(feed|rdf|rss|rss2|atom)/?$", 'index.php?post_type=' . Projects::POST_TYPE . '&feed=$matches[1]', 'top' );
				add_rewrite_rule( "^$base/([^/]+)/?$", 'index.php?' . Projects::POST_TYPE . '=$matches[1]', 'top' );
			}
			if ( $type !== self::slug( 'type_slug', $default ) ) {
				add_rewrite_rule( "^$type/([^/]+)/?$", 'index.php?' . Projects::TYPE_TAX . '=$matches[1]', 'top' );
				add_rewrite_rule( "^$type/([^/]+)/page/([0-9]+)/?$", 'index.php?' . Projects::TYPE_TAX . '=$matches[1]&paged=$matches[2]', 'top' );
			}
		}
	}

	/** Regrava as URLs uma vez depois que os slugs mudam no painel. */
	public static function maybe_flush(): void {
		if ( get_option( 'rolck_flush_rewrites' ) ) {
			delete_option( 'rolck_flush_rewrites' );
			flush_rewrite_rules( false );
		}
	}

	/**
	 * @param string  $url  Link.
	 * @param WP_Post $post Projeto.
	 */
	public static function post_link( string $url, WP_Post $post ): string {
		if ( Projects::POST_TYPE !== $post->post_type ) {
			return $url;
		}
		return self::swap( $url, 'slug', \RolckGalaxy\Core\I18n\Translations::language_of( $post ) );
	}

	/**
	 * @param string $url       Link do arquivo.
	 * @param string $post_type Tipo.
	 */
	public static function archive_link( string $url, string $post_type ): string {
		return Projects::POST_TYPE === $post_type ? self::swap( $url, 'slug', Languages::current() ) : $url;
	}

	/**
	 * @param string  $url      Link.
	 * @param WP_Term $term     Termo.
	 * @param string  $taxonomy Taxonomia.
	 */
	public static function term_link( string $url, WP_Term $term, string $taxonomy ): string {
		return Projects::TYPE_TAX === $taxonomy ? self::swap( $url, 'type_slug', Languages::current() ) : $url;
	}

	/**
	 * Troca os slugs da vitrine e das categorias para os do idioma (usado ao
	 * traduzir links: /en/projetos/ → /en/projects/).
	 *
	 * @param string $url  URL já com o prefixo do idioma.
	 * @param string $lang Idioma.
	 */
	public static function localize( string $url, string $lang ): string {
		return self::swap( self::swap( $url, 'slug', $lang ), 'type_slug', $lang );
	}

	/**
	 * Troca o slug do idioma padrão pelo do idioma do link.
	 *
	 * @param string $url  URL.
	 * @param string $key  'slug' ou 'type_slug'.
	 * @param string $lang Idioma.
	 */
	private static function swap( string $url, string $key, string $lang ): string {
		$from = self::slug( $key, Languages::default() );
		$to   = self::slug( $key, $lang );
		if ( $from === $to ) {
			return $url;
		}
		$prefix = Languages::home_path() . ( Languages::is_default( $lang ) ? '' : $lang . '/' );
		$url    = str_replace( $prefix . $from . '/', $prefix . $to . '/', $url );
		// Também vale para o fim da URL sem barra (/en/projetos).
		return (string) preg_replace( '#' . preg_quote( $prefix . $from, '#' ) . '$#', $prefix . $to, $url );
	}
}
