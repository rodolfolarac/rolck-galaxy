<?php
/**
 * Contrato de um serviço de tradução.
 *
 * Para acrescentar um serviço: crie uma classe aqui que estenda Provider,
 * implemente translate() e inclua em Translator::providers().
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\I18n\AutoTranslate\Providers;

use WP_Error;

defined( 'ABSPATH' ) || exit;

abstract class Provider {

	/** Nome exibido no painel. */
	abstract public static function label(): string;

	/**
	 * Traduz uma lista de trechos de HTML, mantendo as tags.
	 *
	 * @param string[] $texts Trechos (HTML).
	 * @param string   $from  Idioma de origem (código BCP 47: pt-BR).
	 * @param string   $to    Idioma de destino (en).
	 * @param string   $key   Chave de API.
	 * @return string[]|WP_Error Trechos traduzidos, na mesma ordem.
	 */
	abstract public static function translate( array $texts, string $from, string $to, string $key ): array|WP_Error;

	/** Quantos trechos cabem em uma chamada. */
	public static function batch_size(): int {
		return 40;
	}

	/**
	 * POST JSON com tratamento de erro padrão.
	 *
	 * @param string $url     Endpoint.
	 * @param array  $body    Corpo (vira JSON).
	 * @param array  $headers Cabeçalhos extras.
	 * @return array|WP_Error Resposta decodificada.
	 */
	protected static function post_json( string $url, array $body, array $headers = array() ): array|WP_Error {
		$response = wp_remote_post(
			$url,
			array(
				'timeout' => 60,
				'headers' => array( 'Content-Type' => 'application/json' ) + $headers,
				'body'    => wp_json_encode( $body ),
			)
		);
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$code = (int) wp_remote_retrieve_response_code( $response );
		$data = json_decode( (string) wp_remote_retrieve_body( $response ), true );
		if ( $code < 200 || $code >= 300 || ! is_array( $data ) ) {
			$message = is_array( $data ) ? (string) ( $data['error']['message'] ?? $data['message'] ?? '' ) : '';
			return new WP_Error( 'rolck_translate_http', sprintf( '%s respondeu %d. %s', static::label(), $code, $message ) );
		}
		return $data;
	}
}
