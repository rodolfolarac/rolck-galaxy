<?php
/**
 * Registro dos idiomas do site e conversão de URLs entre eles.
 *
 * O idioma padrão fica na raiz (/projetos/x) e os demais ganham um prefixo
 * (/en/projetos/x). Para acrescentar um idioma, inclua-o em LIST — roteador,
 * seletor, hreflang, SEO e painel leem daqui.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\I18n;

use RolckGalaxy\Core\Settings;

defined( 'ABSPATH' ) || exit;

final class Languages {

	/** @var array<string,array{locale:string,label:string,short:string,hreflang:string,flag:string}> */
	public const LIST = array(
		'pt' => array(
			'locale'   => 'pt_BR',
			'label'    => 'Português',
			'short'    => 'PT',
			'hreflang' => 'pt-BR',
			'flag'     => '🇧🇷',
		),
		'en' => array(
			'locale'   => 'en_US',
			'label'    => 'English',
			'short'    => 'EN',
			'hreflang' => 'en',
			'flag'     => '🇺🇸',
		),
	);

	private static ?string $current = null;

	/** @return string[] Códigos de todos os idiomas. */
	public static function codes(): array {
		return array_keys( self::LIST );
	}

	public static function default(): string {
		$code = (string) Settings::get( 'languages.default', 'pt' );
		return isset( self::LIST[ $code ] ) ? $code : 'pt';
	}

	public static function is_valid( string $code ): bool {
		return isset( self::LIST[ $code ] );
	}

	public static function current(): string {
		return self::$current ?? self::default();
	}

	public static function set_current( string $code ): void {
		if ( self::is_valid( $code ) ) {
			self::$current = $code;
		}
	}

	public static function is_default( ?string $code = null ): bool {
		return ( $code ?? self::current() ) === self::default();
	}

	/**
	 * @param string $code Código.
	 * @return array{locale:string,label:string,short:string,hreflang:string,flag:string}
	 */
	public static function info( string $code ): array {
		return self::LIST[ $code ] ?? self::LIST[ self::default() ];
	}

	/**
	 * Valor do idioma atual num par { pt, en }, com o idioma padrão como reserva.
	 *
	 * @param array<string,string>|string|null $pair Par.
	 * @param string|null                      $code Idioma (padrão: o atual).
	 */
	public static function pick( array|string|null $pair, ?string $code = null ): string {
		if ( ! is_array( $pair ) ) {
			return (string) $pair;
		}
		$code = $code ?? self::current();
		$text = trim( (string) ( $pair[ $code ] ?? '' ) );
		return '' !== $text ? $text : (string) ( $pair[ self::default() ] ?? '' );
	}

	/** Caminho base do site (/ ou /subpasta/). */
	public static function home_path(): string {
		$path = (string) wp_parse_url( get_option( 'home' ), PHP_URL_PATH );
		return trailingslashit( '/' . ltrim( $path, '/' ) );
	}

	/**
	 * Separa o prefixo de idioma de um caminho: '/en/projects/' → [ 'en', '/projects/' ].
	 *
	 * @param string $path Caminho a partir da raiz do domínio.
	 * @return array{0:string|null,1:string}
	 */
	public static function split_path( string $path ): array {
		$base = self::home_path();
		if ( ! str_starts_with( $path, $base ) && rtrim( $path, '/' ) . '/' !== $base ) {
			return array( null, $path );
		}
		$rest    = substr( $path, strlen( $base ) );
		$segment = strtok( $rest, '/' );
		if ( false !== $segment && self::is_valid( $segment ) && ! self::is_default( $segment ) ) {
			$after = (string) substr( $rest, strlen( $segment ) );
			return array( $segment, $base . ltrim( $after, '/' ) );
		}
		return array( null, $path );
	}

	/**
	 * Converte uma URL do site para outro idioma.
	 *
	 * @param string $url  URL.
	 * @param string $lang Idioma de destino.
	 */
	public static function convert_url( string $url, string $lang ): string {
		$parts = wp_parse_url( $url );
		$home  = wp_parse_url( get_option( 'home' ) );
		if ( empty( $parts['host'] ) || ( $parts['host'] ?? '' ) !== ( $home['host'] ?? '' ) ) {
			return $url;
		}

		[ , $path ] = self::split_path( $parts['path'] ?? '/' );
		if ( ! self::is_default( $lang ) ) {
			$base = self::home_path();
			$path = $base . $lang . '/' . ltrim( substr( $path, strlen( $base ) ), '/' );
		}

		$port  = isset( $parts['port'] ) ? ':' . $parts['port'] : '';
		$query = isset( $parts['query'] ) ? '?' . $parts['query'] : '';
		$frag  = isset( $parts['fragment'] ) ? '#' . $parts['fragment'] : '';
		return ( $parts['scheme'] ?? 'https' ) . '://' . $parts['host'] . $port . $path . $query . $frag;
	}

	/** Página inicial de um idioma. */
	public static function home_url( string $lang ): string {
		return self::convert_url( trailingslashit( get_option( 'home' ) ), $lang );
	}
}
