<?php
/**
 * Claude (Anthropic) pela Messages API, com HTTP do próprio WordPress
 * (o plugin não usa Composer, então não usa o SDK em PHP).
 * Chave: console.anthropic.com → API Keys.
 *
 * Bom para textos de venda: traduz o sentido, não palavra por palavra.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\I18n\AutoTranslate\Providers;

use WP_Error;

defined( 'ABSPATH' ) || exit;

final class Claude extends Provider {

	private const MODEL = 'claude-opus-5-5';

	public static function label(): string {
		return 'Claude (Anthropic)';
	}

	public static function batch_size(): int {
		return 30;
	}

	public static function translate( array $texts, string $from, string $to, string $key ): array|WP_Error {
		$texts  = array_values( $texts );
		$system = sprintf(
			'You translate website copy for a software company from %1$s to %2$s. Each item is an HTML fragment. ' .
			'Translate only the human-readable text: keep every tag, attribute, URL and HTML entity exactly as it is, ' .
			'and leave the content of <rg-keep> elements untouched. Write natural, persuasive %2$s for a professional ' .
			'audience, keeping the meaning and tone. Return one translation per item, in the same order.',
			$from,
			$to
		);

		$data = self::post_json(
			'https://api.anthropic.com/v1/messages',
			array(
				'model'         => self::MODEL,
				'max_tokens'    => 16000,
				'fallbacks'     => 'default',
				'output_config' => array(
					'effort' => 'low',
					'format' => array(
						'type'   => 'json_schema',
						'schema' => array(
							'type'                 => 'object',
							'properties'           => array(
								'translations' => array(
									'type'  => 'array',
									'items' => array( 'type' => 'string' ),
								),
							),
							'required'             => array( 'translations' ),
							'additionalProperties' => false,
						),
					),
				),
				'system'        => $system,
				'messages'      => array(
					array(
						'role'    => 'user',
						'content' => wp_json_encode( array( 'items' => $texts ), JSON_UNESCAPED_UNICODE ),
					),
				),
			),
			array(
				'x-api-key'         => $key,
				'anthropic-version' => '2023-06-01',
				'anthropic-beta'    => 'server-side-fallback-2026-07-01',
			)
		);
		if ( is_wp_error( $data ) ) {
			return $data;
		}
		if ( 'refusal' === ( $data['stop_reason'] ?? '' ) ) {
			return new WP_Error( 'rolck_translate_refusal', 'O Claude recusou traduzir este trecho.' );
		}
		if ( 'max_tokens' === ( $data['stop_reason'] ?? '' ) ) {
			return new WP_Error( 'rolck_translate_truncated', 'A tradução ficou longa demais para uma chamada.' );
		}

		$text = '';
		foreach ( (array) ( $data['content'] ?? array() ) as $block ) {
			if ( 'text' === ( $block['type'] ?? '' ) ) {
				$text .= (string) $block['text'];
			}
		}
		$parsed = json_decode( $text, true );
		$out    = is_array( $parsed['translations'] ?? null ) ? array_map( 'strval', $parsed['translations'] ) : array();
		if ( count( $out ) !== count( $texts ) ) {
			return new WP_Error( 'rolck_translate_mismatch', 'O Claude devolveu uma quantidade diferente de trechos.' );
		}
		return $out;
	}
}
