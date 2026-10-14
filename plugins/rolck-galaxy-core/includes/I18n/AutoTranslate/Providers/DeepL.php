<?php
/**
 * DeepL. Chaves do plano gratuito terminam em ":fx" e usam outro endereço.
 * Chave: deepl.com/pro-api → Conta → Chave de autenticação.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\I18n\AutoTranslate\Providers;

use WP_Error;

defined( 'ABSPATH' ) || exit;

final class DeepL extends Provider {

	public static function label(): string {
		return 'DeepL';
	}

	public static function batch_size(): int {
		return 50;
	}

	public static function translate( array $texts, string $from, string $to, string $key ): array|WP_Error {
		$host = str_ends_with( $key, ':fx' ) ? 'https://api-free.deepl.com' : 'https://api.deepl.com';
		$data = self::post_json(
			$host . '/v2/translate',
			array(
				'text'         => array_values( $texts ),
				'source_lang'  => strtoupper( substr( $from, 0, 2 ) ),
				'target_lang'  => 'en' === strtolower( substr( $to, 0, 2 ) ) ? 'EN-US' : strtoupper( $to ),
				'tag_handling' => 'html',
				'ignore_tags'  => array( 'rg-keep' ),
			),
			array( 'Authorization' => 'DeepL-Auth-Key ' . $key )
		);
		if ( is_wp_error( $data ) ) {
			return $data;
		}
		return array_map( static fn( $item ) => (string) ( $item['text'] ?? '' ), (array) ( $data['translations'] ?? array() ) );
	}
}
