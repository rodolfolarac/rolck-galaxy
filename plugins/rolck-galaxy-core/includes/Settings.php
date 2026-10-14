<?php
/**
 * Configurações do painel Rolck Galaxy.
 *
 * Tudo fica numa única opção (rolck_settings), exposta na REST API nativa
 * (/wp/v2/settings) para a tela em React do painel. O esquema e a
 * sanitização são gerados a partir de defaults(): para criar um campo novo,
 * basta acrescentá-lo lá e na tela do painel.
 *
 * Textos com versão por idioma são pares { pt, en }.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core;

use RolckGalaxy\Core\I18n\Strings;

defined( 'ABSPATH' ) || exit;

final class Settings {

	public const OPTION = 'rolck_settings';

	/** @var array<string,mixed>|null Cache por requisição. */
	private static ?array $cache = null;

	public static function register(): void {
		add_action( 'init', array( self::class, 'register_setting' ) );
		add_action( 'update_option_' . self::OPTION, array( self::class, 'on_update' ), 10, 2 );
	}

	/** Valores de fábrica. A forma deste array define o esquema da REST API. */
	public static function defaults(): array {
		$pair = static fn( string $pt, string $en ): array => array(
			'pt' => $pt,
			'en' => $en,
		);

		return array(
			'general'      => array(
				'company_name' => 'Rolck Galaxy',
				'tagline'      => $pair( 'Temas WordPress, plugins e produtos digitais feitos para performar.', 'WordPress themes, plugins and digital products built to perform.' ),
				'email'        => 'contato@rolckgalaxy.com',
				'phone'        => '',
				'whatsapp'     => '',
				'address'      => $pair( 'Brasil', 'Brazil' ),
				'founded_year' => 2026,
				'logo_id'      => 0,
				'social'       => array(
					'github'      => '',
					'linkedin'    => '',
					'instagram'   => '',
					'youtube'     => '',
					'x'           => '',
					'dribbble'    => '',
				),
			),
			'languages'    => array(
				'default' => 'pt',
			),
			'seo'          => array(
				'separator'           => '·',
				'home_title'          => $pair( 'Rolck Galaxy — Temas WordPress e produtos digitais de alta performance', 'Rolck Galaxy — High-performance WordPress themes and digital products' ),
				'home_description'    => $pair( 'A Rolck Galaxy cria temas WordPress, plugins e templates rápidos, acessíveis e prontos para ranquear. Veja as demos ao vivo de todos os projetos.', 'Rolck Galaxy builds fast, accessible, SEO-ready WordPress themes, plugins and templates. Explore the live demo of every project.' ),
				'default_image_id'    => 0,
				'organization_type'   => 'Organization',
				'twitter_handle'      => '',
				'google_verification' => '',
				'bing_verification'   => '',
				'noindex_search'      => true,
				'noindex_paged'       => false,
			),
			'projects'     => array(
				'slug'        => $pair( 'projetos', 'projects' ),
				'type_slug'   => $pair( 'categoria-projeto', 'project-category' ),
			),
			'contact'      => array(
				'recipient'      => '',
				'subject_prefix' => '[Rolck Galaxy]',
				'store_messages' => true,
				'success'        => $pair( 'Mensagem enviada! Obrigado pelo contato.', 'Message sent! Thanks for getting in touch.' ),
			),
			'performance'  => array(
				'disable_emojis'    => true,
				'disable_embeds'    => true,
				'clean_head'        => true,
				'disable_xmlrpc'    => true,
				'disable_dashicons' => true,
				'webp_uploads'      => true,
				'inline_css'        => true,
				'speculation'       => 'prerender',
			),
			'integrations' => array(
				'ga4_id'       => '',
				'ga4_on_input' => true,
				'head_code'    => '',
			),
			'translation'  => array(
				'provider'     => '',
				'google_key'   => '',
				'deepl_key'    => '',
				'claude_key'   => '',
				'auto_publish' => true,
				'glossary'     => "Rolck Galaxy, Galaxy Academy, Galaxy Cards, Galaxy Health, WordPress, WooCommerce, Gutenberg, React",
			),
			'strings'      => Strings::defaults(),
		);
	}

	public static function register_setting(): void {
		register_setting(
			'rolck',
			self::OPTION,
			array(
				'type'              => 'object',
				'default'           => self::defaults(),
				'sanitize_callback' => array( self::class, 'sanitize' ),
				'show_in_rest'      => array(
					'name'   => self::OPTION,
					'schema' => self::schema( self::defaults() ),
				),
			)
		);
	}

	/** Todas as configurações, já mescladas com os valores de fábrica. */
	public static function all(): array {
		if ( null === self::$cache ) {
			$saved       = get_option( self::OPTION, array() );
			self::$cache = self::merge( self::defaults(), is_array( $saved ) ? $saved : array() );
		}
		return self::$cache;
	}

	/**
	 * Lê uma chave pelo caminho com pontos ('seo.home_title.pt').
	 *
	 * @param string $path     Caminho.
	 * @param mixed  $fallback Valor se não existir.
	 */
	public static function get( string $path, mixed $fallback = null ): mixed {
		$value = self::all();
		foreach ( explode( '.', $path ) as $key ) {
			if ( ! is_array( $value ) || ! array_key_exists( $key, $value ) ) {
				return $fallback;
			}
			$value = $value[ $key ];
		}
		return $value;
	}

	/** Quando os slugs de projeto mudam, as URLs amigáveis são regravadas. */
	public static function on_update( mixed $old, mixed $new ): void {
		self::$cache = null;
		if ( ( $old['projects'] ?? null ) !== ( $new['projects'] ?? null ) || ( $old['languages']['default'] ?? null ) !== ( $new['languages']['default'] ?? null ) ) {
			update_option( 'rolck_flush_rewrites', 1 );
		}
	}

	/**
	 * Mantém só as chaves conhecidas e limpa cada valor conforme o tipo do default.
	 *
	 * @param mixed $input Valor recebido da REST API.
	 */
	public static function sanitize( mixed $input ): array {
		return self::clean( self::defaults(), is_array( $input ) ? $input : array(), '' );
	}

	/**
	 * @param array  $defaults Forma esperada.
	 * @param array  $input    Valores recebidos.
	 * @param string $path     Caminho atual (decide o tipo de limpeza de textos).
	 */
	private static function clean( array $defaults, array $input, string $path ): array {
		$out = array();
		foreach ( $defaults as $key => $default ) {
			$here  = ltrim( "$path.$key", '.' );
			$value = $input[ $key ] ?? $default;

			if ( is_array( $default ) ) {
				$out[ $key ] = self::clean( $default, is_array( $value ) ? $value : array(), $here );
			} elseif ( is_bool( $default ) ) {
				$out[ $key ] = (bool) $value;
			} elseif ( is_int( $default ) ) {
				$out[ $key ] = absint( $value );
			} else {
				$out[ $key ] = self::clean_text( (string) $value, $here );
			}
		}
		return $out;
	}

	/**
	 * @param string $value Texto.
	 * @param string $path  Caminho da chave.
	 */
	private static function clean_text( string $value, string $path ): string {
		return match ( true ) {
			str_starts_with( $path, 'general.social.' )                       => esc_url_raw( $value ),
			str_ends_with( $path, 'email' ) || str_ends_with( $path, 'recipient' ) => sanitize_email( $value ),
			'integrations.head_code' === $path                                => current_user_can( 'unfiltered_html' ) ? $value : wp_kses_post( $value ),
			'translation.glossary' === $path => sanitize_textarea_field( $value ),
			str_ends_with( $path, '_key' ) => preg_replace( '/[^A-Za-z0-9:_\-\.]/', '', $value ),
			str_contains( $path, 'description' ) || str_contains( $path, 'address' ) || str_starts_with( $path, 'strings.' ) || str_contains( $path, 'tagline' ) || str_contains( $path, 'success' ) => sanitize_textarea_field( $value ),
			default                                                           => sanitize_text_field( $value ),
		};
	}

	/**
	 * Junta o salvo com os defaults, sem perder chaves novas criadas depois.
	 *
	 * @param array $defaults Valores de fábrica.
	 * @param array $saved    Valores salvos.
	 */
	private static function merge( array $defaults, array $saved ): array {
		foreach ( $defaults as $key => $default ) {
			if ( ! array_key_exists( $key, $saved ) ) {
				continue;
			}
			$defaults[ $key ] = is_array( $default ) && is_array( $saved[ $key ] )
				? self::merge( $default, $saved[ $key ] )
				: $saved[ $key ];
		}
		return $defaults;
	}

	/**
	 * Esquema JSON da REST API, deduzido dos tipos dos defaults.
	 *
	 * @param array $defaults Forma esperada.
	 */
	private static function schema( array $defaults ): array {
		$properties = array();
		foreach ( $defaults as $key => $default ) {
			$properties[ $key ] = match ( true ) {
				is_array( $default ) => self::schema( $default ),
				is_bool( $default )  => array( 'type' => 'boolean' ),
				is_int( $default )   => array( 'type' => 'integer' ),
				default              => array( 'type' => 'string' ),
			};
		}
		return array(
			'type'                 => 'object',
			'properties'           => $properties,
			'additionalProperties' => false,
		);
	}
}
