<?php
/**
 * Descobre o idioma pela URL e prepara a requisição para o WordPress.
 *
 * /en/projects/x chega ao WordPress como /projects/x com o idioma "en" já
 * definido. Assim todas as regras de URL nativas continuam valendo, sem
 * duplicar rewrite rules por idioma. Na volta, home_url() recebe o prefixo
 * de novo, então todo link gerado na página continua no mesmo idioma.
 *
 * Roda no carregamento do plugin (antes do plugins_loaded) porque o locale
 * precisa estar decidido antes de o WordPress carregar as traduções.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\I18n;

defined( 'ABSPATH' ) || exit;

final class Router {

	/** URI pedida pelo navegador, antes de retirarmos o prefixo de idioma. */
	private static string $original_uri = '';

	public static function boot(): void {
		if ( is_admin() || wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			return;
		}

		$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/'; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- só lemos o caminho.

		if ( self::is_rest( $uri ) ) {
			// Pedidos do editor podem informar o idioma por ?lang=en.
			$lang = isset( $_GET['lang'] ) ? sanitize_key( wp_unslash( $_GET['lang'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( Languages::is_valid( $lang ) ) {
				Languages::set_current( $lang );
			}
			return;
		}

		self::$original_uri = $uri;
		$path               = (string) strtok( $uri, '?' );
		$query              = (string) substr( $uri, strlen( $path ) );

		[ $lang, $rest ] = Languages::split_path( $path );
		if ( $lang ) {
			Languages::set_current( $lang );
			$_SERVER['REQUEST_URI'] = $rest . $query;
		}

		add_filter( 'locale', array( self::class, 'locale' ) );
		add_filter( 'home_url', array( self::class, 'home_url' ), 10, 3 );
		add_filter( 'redirect_canonical', array( self::class, 'redirect_canonical' ) );

		// Ao interpretar a URL, o WordPress tira o caminho da home do começo do
		// endereço. Com /en no home_url(), ele cortaria o "en" de slugs como
		// "entrega". O prefixo sai do home_url() só durante essa etapa.
		add_filter( 'do_parse_request', array( self::class, 'pause_home_url' ), 1 );
		add_action( 'parse_request', array( self::class, 'resume_home_url' ), 1 );
		add_action( 'wp', array( self::class, 'resume_home_url' ), 0 );
	}

	/**
	 * @param bool $parse Continuar com a interpretação nativa.
	 */
	public static function pause_home_url( bool $parse ): bool {
		remove_filter( 'home_url', array( self::class, 'home_url' ), 10 );
		return $parse;
	}

	public static function resume_home_url(): void {
		if ( ! has_filter( 'home_url', array( self::class, 'home_url' ) ) ) {
			add_filter( 'home_url', array( self::class, 'home_url' ), 10, 3 );
		}
	}

	public static function original_uri(): string {
		return self::$original_uri;
	}

	/** O locale do WordPress segue o idioma da página (pt_BR ou en_US). */
	public static function locale(): string {
		return Languages::info( Languages::current() )['locale'];
	}

	/**
	 * Links gerados na página carregam o prefixo do idioma atual.
	 *
	 * @param string      $url    URL montada pelo WordPress.
	 * @param string      $path   Caminho pedido.
	 * @param string|null $scheme Esquema.
	 */
	public static function home_url( string $url, string $path, ?string $scheme ): string {
		if ( Languages::is_default() || 'rest' === $scheme || str_starts_with( ltrim( $path, '/' ), 'wp-json' ) ) {
			return $url;
		}
		return Languages::convert_url( $url, Languages::current() );
	}

	/**
	 * O WordPress compara a URL pedida (já sem o prefixo) com a URL canônica
	 * (com prefixo) e acharia que são diferentes. Comparamos com a original.
	 *
	 * @param string|false $redirect URL para onde o WordPress quer redirecionar.
	 */
	public static function redirect_canonical( string|false $redirect ): string|false {
		if ( ! $redirect ) {
			return $redirect;
		}
		$host     = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$original = ( is_ssl() ? 'https://' : 'http://' ) . $host . self::$original_uri;
		return untrailingslashit( $redirect ) === untrailingslashit( $original ) ? false : $redirect;
	}

	/**
	 * @param string $uri URI pedida.
	 */
	private static function is_rest( string $uri ): bool {
		$prefix = '/' . trim( function_exists( 'rest_get_url_prefix' ) ? rest_get_url_prefix() : 'wp-json', '/' ) . '/';
		return str_contains( $uri, $prefix ) || isset( $_GET['rest_route'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
}
