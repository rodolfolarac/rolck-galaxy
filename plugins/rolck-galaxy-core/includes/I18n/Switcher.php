<?php
/**
 * Endereço da página atual em cada idioma.
 *
 * Usado pelo bloco seletor de idioma e pelas tags hreflang do <head>.
 * - Conteúdo com versão traduzida → a versão traduzida.
 * - Conteúdo sem versão → página inicial do outro idioma (sem hreflang).
 * - Listagens, busca e arquivos → a mesma URL com o prefixo do idioma.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\I18n;

use WP_Post;

defined( 'ABSPATH' ) || exit;

final class Switcher {

	/**
	 * @return array<string,array{url:string,exists:bool,current:bool,label:string,short:string,hreflang:string,flag:string}>
	 */
	public static function links(): array {
		static $links = null;
		if ( null !== $links ) {
			return $links;
		}

		$links   = array();
		$current = Languages::current();
		$object  = get_queried_object();
		$here    = self::current_url();

		foreach ( Languages::codes() as $code ) {
			$url    = '';
			$exists = true;

			if ( is_singular() && $object instanceof WP_Post && in_array( $object->post_type, Translations::post_types(), true ) && ! is_front_page() ) {
				$id     = $code === $current ? $object->ID : Translations::translation_of( $object->ID, $code );
				$exists = (bool) $id;
				$url    = $id ? (string) get_permalink( $id ) : Languages::home_url( $code );
			} elseif ( is_front_page() ) {
				$url = Languages::home_url( $code );
			} elseif ( is_home() ) {
				$blog   = (int) get_option( 'page_for_posts' );
				$id     = $blog ? ( Translations::translation_of( $blog, $code ) ?: ( $code === $current ? $blog : 0 ) ) : 0;
				$exists = ! $blog || (bool) $id;
				$url    = $id ? (string) get_permalink( $id ) : Languages::home_url( $code );
			} elseif ( is_404() ) {
				$exists = false;
				$url    = Languages::home_url( $code );
			} else {
				$url = Languages::convert_url( $here, $code );
			}

			$links[ $code ] = array(
				'url'     => $url,
				'exists'  => $exists,
				'current' => $code === $current,
			) + Languages::info( $code );
		}
		return $links;
	}

	/** URL da página atual (com o prefixo do idioma atual), sem parâmetros de rastreio. */
	public static function current_url(): string {
		$uri  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
		$path = (string) strtok( $uri, '?' );
		$url  = home_url( substr( $path, strlen( Languages::home_path() ) - 1 ) );
		if ( is_search() ) {
			$url = add_query_arg( 's', rawurlencode( get_search_query( false ) ), $url );
		}
		return $url;
	}
}
