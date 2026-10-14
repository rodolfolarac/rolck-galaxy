<?php
/**
 * Traduz conteúdo de blocos sem quebrar a marcação do editor.
 *
 * - Lê os blocos (parse_blocks), traduz o HTML de cada bloco e os atributos
 *   de texto conhecidos (ATTRIBUTES) e monta tudo de volta (serialize_blocks).
 * - Se o serviço mexer nas tags de um trecho, aquele trecho é traduzido de
 *   novo pedaço por pedaço de texto, preservando o HTML original.
 * - Links internos passam a apontar para a versão do idioma de destino.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\I18n\AutoTranslate;

use RolckGalaxy\Core\Content\ProjectRewrites;
use RolckGalaxy\Core\I18n\Languages;
use RolckGalaxy\Core\I18n\Translations;
use WP_Error;

defined( 'ABSPATH' ) || exit;

final class ContentTranslator {

	/**
	 * Atributos de bloco que guardam texto visível. Caminhos com "*" percorrem listas.
	 *
	 * @var array<string,string[]>
	 */
	private const ATTRIBUTES = array(
		'core/navigation-link'          => array( 'label', 'title' ),
		'core/navigation-submenu'       => array( 'label', 'title' ),
		'core/search'                   => array( 'label', 'placeholder', 'buttonText' ),
		'core/post-excerpt'             => array( 'moreText' ),
		'core/read-more'                => array( 'content' ),
		'core/query-pagination-next'    => array( 'label' ),
		'core/query-pagination-previous' => array( 'label' ),
		'core/post-navigation-link'     => array( 'label' ),
		'rolck/stats'                   => array( 'items.*.label', 'items.*.prefix', 'items.*.suffix' ),
		'rolck/featured-project'        => array( 'eyebrow' ),
		'rolck/tech-marquee'            => array( 'label' ),
	);

	/**
	 * @param string $content Conteúdo com blocos.
	 * @param string $to      Idioma de destino.
	 */
	public static function translate( string $content, string $to ): string|WP_Error {
		if ( '' === trim( $content ) ) {
			return $content;
		}
		$blocks   = parse_blocks( $content );
		$segments = array();
		self::collect( $blocks, $segments );

		$translated = Translator::translate_many( array_column( $segments, 'text' ), $to );
		if ( is_wp_error( $translated ) ) {
			return $translated;
		}

		// Trechos em que o serviço alterou as tags: traduz só os pedaços de texto.
		foreach ( $segments as $i => $segment ) {
			if ( 'html' === $segment['kind'] && self::tags( $segment['text'] ) !== self::tags( $translated[ $i ] ) ) {
				$safe = self::translate_text_nodes( $segment['text'], $to );
				if ( is_wp_error( $safe ) ) {
					return $safe;
				}
				$translated[ $i ] = $safe;
			}
		}

		foreach ( $segments as $i => $segment ) {
			$value = 'html' === $segment['kind'] ? self::localize_links( $translated[ $i ], $to ) : $translated[ $i ];
			self::set( $blocks, $segment['path'], $value );
		}
		self::localize_block_links( $blocks, $to );

		return serialize_blocks( $blocks );
	}

	/**
	 * Junta todos os trechos traduzíveis com o caminho até eles.
	 *
	 * @param array $blocks   Blocos.
	 * @param array $segments Saída.
	 * @param array $path     Caminho atual.
	 */
	private static function collect( array $blocks, array &$segments, array $path = array() ): void {
		foreach ( $blocks as $b => $block ) {
			$here = array_merge( $path, array( $b ) );
			foreach ( (array) ( $block['innerContent'] ?? array() ) as $c => $chunk ) {
				if ( is_string( $chunk ) && Translator::has_words( $chunk ) ) {
					$segments[] = array(
						'kind' => 'html',
						'path' => array_merge( $here, array( 'innerContent', $c ) ),
						'text' => $chunk,
					);
				}
			}
			foreach ( self::ATTRIBUTES[ $block['blockName'] ?? '' ] ?? array() as $attr ) {
				foreach ( self::attribute_paths( (array) ( $block['attrs'] ?? array() ), explode( '.', $attr ) ) as $attr_path ) {
					$value = self::get( $block['attrs'], $attr_path );
					if ( is_string( $value ) && Translator::has_words( $value ) ) {
						$segments[] = array(
							'kind' => 'text',
							'path' => array_merge( $here, array( 'attrs' ), $attr_path ),
							'text' => $value,
						);
					}
				}
			}
			if ( ! empty( $block['innerBlocks'] ) ) {
				self::collect( $block['innerBlocks'], $segments, array_merge( $here, array( 'innerBlocks' ) ) );
			}
		}
	}

	/**
	 * Expande "items.*.label" para cada item existente.
	 *
	 * @param array $attrs Atributos.
	 * @param array $parts Caminho com curingas.
	 * @return array<int,array>
	 */
	private static function attribute_paths( array $attrs, array $parts ): array {
		$paths = array( array() );
		foreach ( $parts as $part ) {
			$next = array();
			foreach ( $paths as $path ) {
				if ( '*' === $part ) {
					foreach ( array_keys( (array) self::get( $attrs, $path ) ) as $key ) {
						$next[] = array_merge( $path, array( $key ) );
					}
				} else {
					$next[] = array_merge( $path, array( $part ) );
				}
			}
			$paths = $next;
		}
		return $paths;
	}

	private static function get( mixed $data, array $path ): mixed {
		foreach ( $path as $key ) {
			if ( ! is_array( $data ) || ! array_key_exists( $key, $data ) ) {
				return null;
			}
			$data = $data[ $key ];
		}
		return $data;
	}

	private static function set( array &$data, array $path, mixed $value ): void {
		$ref = &$data;
		foreach ( $path as $key ) {
			$ref = &$ref[ $key ];
		}
		$ref = $value;
	}

	/**
	 * Sequência de tags de um trecho (para saber se o serviço mexeu na marcação).
	 *
	 * @param string $html Trecho.
	 */
	private static function tags( string $html ): string {
		preg_match_all( '/<\/?([a-z0-9-]+)/i', $html, $m );
		return strtolower( implode( ',', $m[1] ) );
	}

	/**
	 * Plano B: traduz cada pedaço de texto entre tags, sem enviar as tags.
	 *
	 * @param string $html Trecho.
	 * @param string $to   Idioma.
	 */
	private static function translate_text_nodes( string $html, string $to ): string|WP_Error {
		$parts = preg_split( '/(<[^>]+>)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE );
		$texts = array();
		foreach ( $parts as $i => $part ) {
			if ( '' !== $part && '<' !== $part[0] && Translator::has_words( $part ) ) {
				$texts[ $i ] = $part;
			}
		}
		$translated = Translator::translate_many( $texts, $to );
		if ( is_wp_error( $translated ) ) {
			return $translated;
		}
		foreach ( $translated as $i => $text ) {
			// Mantém os espaços das pontas, que separam o texto das tags vizinhas.
			preg_match( '/^(\s*).*?(\s*)$/su', $parts[ $i ], $space );
			$parts[ $i ] = ( $space[1] ?? '' ) . trim( wp_strip_all_tags( $text ) ) . ( $space[2] ?? '' );
		}
		return implode( '', $parts );
	}

	/**
	 * Troca href="…" internos pela versão no idioma de destino.
	 *
	 * @param string $html Trecho.
	 * @param string $to   Idioma.
	 */
	public static function localize_links( string $html, string $to ): string {
		return (string) preg_replace_callback(
			'/\bhref=(["\'])([^"\']+)\1/i',
			static fn( array $m ): string => 'href=' . $m[1] . esc_attr( self::localize_url( html_entity_decode( $m[2] ), $to ) ) . $m[1],
			$html
		);
	}

	/**
	 * URL interna no idioma de destino: conteúdo traduzido → link dele;
	 * outras páginas (vitrine, categorias, início) → mesmo caminho com prefixo e slugs do idioma.
	 *
	 * @param string $url URL (absoluta ou começando com /).
	 * @param string $to  Idioma.
	 */
	public static function localize_url( string $url, string $to ): string {
		$home     = untrailingslashit( (string) get_option( 'home' ) );
		$relative = str_starts_with( $url, '/' ) && ! str_starts_with( $url, '//' );
		$absolute = $relative ? $home . $url : $url;
		if ( ! str_starts_with( $absolute, $home ) || str_contains( $absolute, '/wp-content/' ) || str_contains( $absolute, '/wp-admin' ) ) {
			return $url;
		}

		$fragment = (string) wp_parse_url( $absolute, PHP_URL_FRAGMENT );
		$base     = $fragment ? substr( $absolute, 0, -strlen( $fragment ) - 1 ) : $absolute;
		$id       = url_to_postid( $base );
		$target   = $id ? Translations::translation_of( $id, $to ) : 0;
		$result   = $target ? (string) get_permalink( $target ) : ProjectRewrites::localize( Languages::convert_url( $base, $to ), $to );
		$result  .= $fragment ? '#' . $fragment : '';

		return $relative ? (string) substr( $result, strlen( $home ) ) : $result;
	}

	/**
	 * Links guardados em atributos (itens de menu): URL e ID do conteúdo.
	 *
	 * @param array  $blocks Blocos.
	 * @param string $to     Idioma.
	 */
	private static function localize_block_links( array &$blocks, string $to ): void {
		foreach ( $blocks as &$block ) {
			if ( in_array( $block['blockName'] ?? '', array( 'core/navigation-link', 'core/navigation-submenu' ), true ) ) {
				if ( ! empty( $block['attrs']['id'] ) ) {
					$target = Translations::translation_of( (int) $block['attrs']['id'], $to );
					if ( $target ) {
						$block['attrs']['id']  = $target;
						$block['attrs']['url'] = (string) get_permalink( $target );
					}
				} elseif ( ! empty( $block['attrs']['url'] ) ) {
					$block['attrs']['url'] = self::localize_url( (string) $block['attrs']['url'], $to );
				}
			}
			if ( ! empty( $block['innerBlocks'] ) ) {
				self::localize_block_links( $block['innerBlocks'], $to );
			}
		}
	}
}
