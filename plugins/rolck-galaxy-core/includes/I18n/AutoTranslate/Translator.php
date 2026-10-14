<?php
/**
 * Tradução automática de textos (usa o serviço escolhido em Rolck Galaxy → Idiomas).
 *
 * - Nomes do glossário (Rolck Galaxy, Galaxy Academy…) nunca são traduzidos.
 * - Cada trecho traduzido fica em cache por 30 dias: editar um parágrafo de
 *   uma página só paga a tradução daquele parágrafo.
 * - Trechos sem letras (só tags, números, espaços) não vão para a API.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\I18n\AutoTranslate;

use RolckGalaxy\Core\I18n\AutoTranslate\Providers\Claude;
use RolckGalaxy\Core\I18n\AutoTranslate\Providers\DeepL;
use RolckGalaxy\Core\I18n\AutoTranslate\Providers\Fake;
use RolckGalaxy\Core\I18n\AutoTranslate\Providers\Google;
use RolckGalaxy\Core\I18n\Languages;
use RolckGalaxy\Core\Settings;
use WP_Error;

defined( 'ABSPATH' ) || exit;

final class Translator {

	private const CACHE_TTL = 30 * DAY_IN_SECONDS;

	/** @return array<string,class-string<Providers\Provider>> Código → classe. */
	public static function providers(): array {
		$list = array(
			'google' => Google::class,
			'deepl'  => DeepL::class,
			'claude' => Claude::class,
		);
		if ( defined( 'ROLCK_FAKE_TRANSLATOR' ) && ROLCK_FAKE_TRANSLATOR ) {
			$list['fake'] = Fake::class;
		}
		return $list;
	}

	public static function provider(): string {
		$code = (string) Settings::get( 'translation.provider', '' );
		return isset( self::providers()[ $code ] ) ? $code : '';
	}

	private static function key( string $provider ): string {
		return 'fake' === $provider ? 'fake' : trim( (string) Settings::get( "translation.{$provider}_key", '' ) );
	}

	/** Tradução automática ligada e com chave preenchida? */
	public static function enabled(): bool {
		$provider = self::provider();
		return '' !== $provider && '' !== self::key( $provider );
	}

	/**
	 * Traduz um texto (HTML ou texto puro).
	 *
	 * @param string      $text Texto.
	 * @param string      $to   Idioma de destino (código do site: en).
	 * @param string|null $from Idioma de origem (padrão: o idioma principal).
	 */
	public static function translate( string $text, string $to, ?string $from = null ): string|WP_Error {
		$out = self::translate_many( array( $text ), $to, $from );
		return is_wp_error( $out ) ? $out : $out[0];
	}

	/**
	 * Traduz vários textos de uma vez, mantendo as chaves do array.
	 *
	 * @param array<int|string,string> $texts Textos.
	 * @param string                   $to    Idioma de destino.
	 * @param string|null              $from  Idioma de origem.
	 * @return array<int|string,string>|WP_Error
	 */
	public static function translate_many( array $texts, string $to, ?string $from = null ): array|WP_Error {
		$from = $from ?? Languages::default();
		if ( $from === $to || ! $texts ) {
			return $texts;
		}
		if ( ! self::enabled() ) {
			return new WP_Error( 'rolck_translate_off', 'Escolha um serviço de tradução e preencha a chave em Rolck Galaxy → Configurações → Idiomas.' );
		}

		$provider = self::provider();
		$class    = self::providers()[ $provider ];
		$out      = array();
		$missing  = array();

		foreach ( $texts as $index => $text ) {
			$text = (string) $text;
			if ( ! self::has_words( $text ) ) {
				$out[ $index ] = $text;
				continue;
			}
			$cached = get_transient( self::cache_key( $provider, $to, $text ) );
			if ( false !== $cached ) {
				$out[ $index ] = (string) $cached;
				continue;
			}
			$missing[ $index ] = $text;
		}

		foreach ( array_chunk( $missing, $class::batch_size(), true ) as $chunk ) {
			$protected = array_map( array( self::class, 'protect' ), $chunk );
			$result    = $class::translate(
				array_values( $protected ),
				Languages::info( $from )['hreflang'],
				Languages::info( $to )['hreflang'],
				self::key( $provider )
			);
			if ( is_wp_error( $result ) ) {
				return $result;
			}
			if ( count( $result ) !== count( $chunk ) ) {
				return new WP_Error( 'rolck_translate_count', 'O serviço de tradução devolveu uma quantidade diferente de trechos.' );
			}
			foreach ( array_keys( $chunk ) as $i => $index ) {
				$translated    = self::unprotect( (string) $result[ $i ] );
				$out[ $index ] = $translated;
				set_transient( self::cache_key( $provider, $to, $chunk[ $index ] ), $translated, self::CACHE_TTL );
			}
		}

		// Mantém a ordem original das chaves.
		return array_replace( array_intersect_key( $texts, $out ), $out );
	}

	/**
	 * Tem alguma palavra para traduzir (fora das tags)?
	 *
	 * @param string $text Texto.
	 */
	public static function has_words( string $text ): bool {
		return (bool) preg_match( '/\p{L}{2,}/u', wp_strip_all_tags( $text ) );
	}

	/** @return string[] Termos que nunca são traduzidos. */
	private static function glossary(): array {
		$terms = preg_split( '/[\n,]+/', (string) Settings::get( 'translation.glossary', '' ) );
		$terms = array_filter( array_map( 'trim', (array) $terms ) );
		usort( $terms, static fn( $a, $b ) => mb_strlen( $b ) <=> mb_strlen( $a ) );
		return $terms;
	}

	/**
	 * Envolve os termos do glossário (só no texto, nunca dentro das tags).
	 *
	 * @param string $html Trecho.
	 */
	private static function protect( string $html ): string {
		$terms = self::glossary();
		if ( ! $terms ) {
			return $html;
		}
		$pattern = '/(?<![\p{L}\p{N}])(' . implode( '|', array_map( static fn( $t ) => preg_quote( $t, '/' ), $terms ) ) . ')(?![\p{L}\p{N}])/u';
		$parts   = preg_split( '/(<[^>]+>)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE );
		foreach ( $parts as $i => $part ) {
			if ( '' !== $part && '<' !== $part[0] ) {
				$parts[ $i ] = (string) preg_replace( $pattern, '<rg-keep translate="no">$1</rg-keep>', $part );
			}
		}
		return implode( '', $parts );
	}

	/**
	 * @param string $html Trecho traduzido.
	 */
	private static function unprotect( string $html ): string {
		return (string) preg_replace( '#<rg-keep[^>]*>(.*?)</rg-keep>#su', '$1', $html );
	}

	private static function cache_key( string $provider, string $to, string $text ): string {
		return 'rolck_tr_' . md5( $provider . '|' . $to . '|' . (string) Settings::get( 'translation.glossary', '' ) . '|' . $text );
	}
}
