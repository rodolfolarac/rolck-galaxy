<?php
/**
 * Tags de SEO no <head>: descrição, canonical, hreflang, Open Graph, cartão
 * do X/Twitter e verificação do Google/Bing.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\Seo;

use RolckGalaxy\Core\I18n\Languages;
use RolckGalaxy\Core\I18n\Switcher;
use RolckGalaxy\Core\Settings;

defined( 'ABSPATH' ) || exit;

final class Head {

	public static function register(): void {
		if ( is_admin() ) {
			return;
		}
		remove_action( 'wp_head', 'rel_canonical' );
		add_action( 'wp_head', array( self::class, 'output' ), 1 );
		add_filter( 'language_attributes', array( self::class, 'html_lang' ) );
	}

	/**
	 * <html lang> com o código BCP 47 do idioma (pt-BR / en).
	 *
	 * @param string $output Atributos.
	 */
	public static function html_lang( string $output ): string {
		return (string) preg_replace( '/lang="[^"]*"/', 'lang="' . esc_attr( Languages::info( Languages::current() )['hreflang'] ) . '"', $output );
	}

	public static function canonical(): string {
		if ( is_404() || is_search() ) {
			return '';
		}
		if ( is_front_page() ) {
			return Languages::home_url( Languages::current() );
		}
		if ( is_singular() ) {
			return (string) wp_get_canonical_url();
		}
		$paged = max( 1, (int) get_query_var( 'paged' ) );
		return $paged > 1 ? get_pagenum_link( $paged, false ) : Switcher::current_url();
	}

	public static function output(): void {
		$title       = Meta::title();
		$description = Meta::description();
		$canonical   = self::canonical();
		$image       = Meta::image();
		$info        = Languages::info( Languages::current() );

		$tags = array();

		$tags[] = array( 'meta', 'name', 'description', $description );
		foreach ( array( 'google' => 'google_verification', 'msvalidate.01' => 'bing_verification' ) as $name => $key ) {
			$code = (string) Settings::get( "seo.$key", '' );
			if ( '' !== $code ) {
				$tags[] = array( 'meta', 'name', 'google' === $name ? 'google-site-verification' : $name, $code );
			}
		}

		// Open Graph.
		$tags[] = array( 'meta', 'property', 'og:type', is_singular( 'post' ) ? 'article' : 'website' );
		$tags[] = array( 'meta', 'property', 'og:site_name', (string) Settings::get( 'general.company_name' ) );
		$tags[] = array( 'meta', 'property', 'og:title', $title );
		$tags[] = array( 'meta', 'property', 'og:description', $description );
		$tags[] = array( 'meta', 'property', 'og:locale', $info['locale'] );
		foreach ( Languages::codes() as $code ) {
			if ( $code !== Languages::current() ) {
				$tags[] = array( 'meta', 'property', 'og:locale:alternate', Languages::info( $code )['locale'] );
			}
		}
		if ( $canonical ) {
			$tags[] = array( 'meta', 'property', 'og:url', $canonical );
		}
		if ( $image ) {
			$tags[] = array( 'meta', 'property', 'og:image', $image['url'] );
			$tags[] = array( 'meta', 'property', 'og:image:width', (string) $image['width'] );
			$tags[] = array( 'meta', 'property', 'og:image:height', (string) $image['height'] );
			if ( $image['alt'] ) {
				$tags[] = array( 'meta', 'property', 'og:image:alt', $image['alt'] );
			}
		}
		if ( is_singular( 'post' ) ) {
			$tags[] = array( 'meta', 'property', 'article:published_time', (string) get_the_date( 'c' ) );
			$tags[] = array( 'meta', 'property', 'article:modified_time', (string) get_the_modified_date( 'c' ) );
		}

		// Cartão do X/Twitter.
		$tags[] = array( 'meta', 'name', 'twitter:card', $image ? 'summary_large_image' : 'summary' );
		$handle = ltrim( (string) Settings::get( 'seo.twitter_handle', '' ), '@' );
		if ( '' !== $handle ) {
			$tags[] = array( 'meta', 'name', 'twitter:site', '@' . $handle );
		}

		echo "\n<!-- Rolck Galaxy SEO -->\n";
		if ( $canonical ) {
			printf( "<link rel=\"canonical\" href=\"%s\">\n", esc_url( $canonical ) );
		}
		self::hreflang();
		foreach ( $tags as [ , $attr, $key, $value ] ) {
			if ( '' !== trim( (string) $value ) ) {
				printf( "<meta %s=\"%s\" content=\"%s\">\n", esc_attr( $attr ), esc_attr( $key ), esc_attr( $value ) );
			}
		}
		echo "<!-- /Rolck Galaxy SEO -->\n";
	}

	/** <link rel="alternate" hreflang> só para versões que existem de verdade. */
	private static function hreflang(): void {
		if ( is_404() || is_search() || ! Meta::indexable() ) {
			return;
		}
		$links = array_filter( Switcher::links(), static fn( array $l ): bool => $l['exists'] );
		if ( count( $links ) < 2 ) {
			return;
		}
		foreach ( $links as $link ) {
			printf( "<link rel=\"alternate\" hreflang=\"%s\" href=\"%s\">\n", esc_attr( $link['hreflang'] ), esc_url( $link['url'] ) );
		}
		$default = $links[ Languages::default() ] ?? null;
		if ( $default ) {
			printf( "<link rel=\"alternate\" hreflang=\"x-default\" href=\"%s\">\n", esc_url( $default['url'] ) );
		}
	}
}
