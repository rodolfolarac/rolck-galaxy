<?php
/**
 * Textos padrão de blocos nativos vindos de Rolck Galaxy → Configurações → Textos.
 *
 * Um bloco de busca colocado num modelo sem texto definido usa "Buscar" e
 * "Buscar no site…" da aba Textos, no idioma da página. Assim os modelos do
 * tema não carregam nenhum texto fixo, e quem quiser um texto diferente num
 * bloco específico ainda pode escrever direto nele.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\Blocks;

use RolckGalaxy\Core\I18n\Strings;

defined( 'ABSPATH' ) || exit;

final class CoreDefaults {

	/** Atributo do bloco → chave da aba Textos. */
	private const SEARCH = array(
		'label'       => 'search',
		'buttonText'  => 'search',
		'placeholder' => 'search_placeholder',
	);

	public static function register(): void {
		if ( ! is_admin() ) {
			add_filter( 'render_block_data', array( self::class, 'fill' ) );
		}
	}

	/**
	 * @param array $block Bloco prestes a ser renderizado.
	 */
	public static function fill( array $block ): array {
		if ( 'core/search' !== ( $block['blockName'] ?? '' ) || wp_is_serving_rest_request() ) {
			return $block;
		}
		foreach ( self::SEARCH as $attr => $key ) {
			if ( '' === (string) ( $block['attrs'][ $attr ] ?? '' ) ) {
				$block['attrs'][ $attr ] = Strings::get( $key );
			}
		}
		return $block;
	}
}
