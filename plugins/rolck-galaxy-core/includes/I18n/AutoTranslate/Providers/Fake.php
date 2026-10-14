<?php
/**
 * Tradutor de teste (sem API, sem custo): devolve o texto com "[EN] " na frente.
 * Só aparece quando a constante ROLCK_FAKE_TRANSLATOR está ligada (ambiente local
 * e testes automatizados). Serve para conferir todo o fluxo sem gastar nada.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\I18n\AutoTranslate\Providers;

use WP_Error;

defined( 'ABSPATH' ) || exit;

final class Fake extends Provider {

	public static function label(): string {
		return 'Teste local (sem API)';
	}

	public static function translate( array $texts, string $from, string $to, string $key ): array|WP_Error {
		return array_map(
			static fn( string $html ): string => (string) preg_replace( '/^(\s*(?:<[^>]+>\s*)*)(?=[^<\s])/u', '$1[EN] ', $html, 1 ),
			array_values( $texts )
		);
	}
}
