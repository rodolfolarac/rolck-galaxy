<?php
/**
 * Google Cloud Translation (API básica v2).
 * Chave: console.cloud.google.com → APIs → "Cloud Translation API" → Credenciais.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\I18n\AutoTranslate\Providers;

use WP_Error;

defined( 'ABSPATH' ) || exit;

final class Google extends Provider {

	public static function label(): string {
		return 'Google Cloud Translation';
	}

	public static function batch_size(): int {
		return 100;
	}

	public static function translate( array $texts, string $from, string $to, string $key ): array|WP_Error {
		$data = self::post_json(
			'https://translation.googleapis.com/language/translate/v2?key=' . rawurlencode( $key ),
			array(
				'q'      => array_values( $texts ),
				'source' => strtolower( substr( $from, 0, 2 ) ),
				'target' => strtolower( substr( $to, 0, 2 ) ),
				'format' => 'html',
			)
		);
		if ( is_wp_error( $data ) ) {
			return $data;
		}
		return array_map( static fn( $item ) => (string) ( $item['translatedText'] ?? '' ), (array) ( $data['data']['translations'] ?? array() ) );
	}
}
