<?php
/**
 * Cabeçalho e rodapé por idioma, editáveis no Editor do site.
 *
 * Em uma página em inglês, a parte de modelo "header" é trocada por
 * "header-en" (e "footer" por "footer-en"), quando ela existe. Cada idioma
 * tem o próprio menu, botões e textos, todos editados visualmente.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\I18n;

defined( 'ABSPATH' ) || exit;

final class TemplateParts {

	public static function register(): void {
		if ( ! is_admin() ) {
			add_filter( 'render_block_data', array( self::class, 'swap' ) );
		}
	}

	/**
	 * @param array $block Bloco prestes a ser renderizado.
	 */
	public static function swap( array $block ): array {
		if ( 'core/template-part' !== ( $block['blockName'] ?? '' ) || Languages::is_default() || wp_is_serving_rest_request() ) {
			return $block;
		}
		$slug = (string) ( $block['attrs']['slug'] ?? '' );
		$lang = Languages::current();
		if ( '' === $slug || str_ends_with( $slug, '-' . $lang ) ) {
			return $block;
		}
		$theme = (string) ( $block['attrs']['theme'] ?? get_stylesheet() );
		if ( self::exists( $theme, "$slug-$lang" ) ) {
			$block['attrs']['slug'] = "$slug-$lang";
		}
		return $block;
	}

	/**
	 * @param string $theme Tema.
	 * @param string $slug  Slug da parte.
	 */
	private static function exists( string $theme, string $slug ): bool {
		static $cache = array();
		return $cache[ "$theme//$slug" ] ??= null !== get_block_template( "$theme//$slug", 'wp_template_part' );
	}
}
