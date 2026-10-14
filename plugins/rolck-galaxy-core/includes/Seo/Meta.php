<?php
/**
 * Campos de SEO por conteúdo e as regras de título, descrição, imagem e robots.
 *
 * Os campos aparecem no painel "SEO" do editor de blocos. Quando um campo fica
 * vazio, o site usa um valor automático (resumo, conteúdo, imagem destacada,
 * padrões do painel Rolck Galaxy → SEO).
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\Seo;

use RolckGalaxy\Core\Content\Projects;
use RolckGalaxy\Core\I18n\Strings;
use RolckGalaxy\Core\I18n\Translations;
use RolckGalaxy\Core\Settings;
use WP_Post;
use WP_Term;

defined( 'ABSPATH' ) || exit;

final class Meta {

	public const FIELDS = array(
		'_rolck_seo_title'       => 'string',
		'_rolck_seo_description' => 'string',
		'_rolck_seo_image'       => 'integer',
		'_rolck_seo_noindex'     => 'boolean',
	);

	public static function register(): void {
		add_action( 'init', array( self::class, 'register_fields' ), 20 );
		add_filter( 'pre_get_document_title', array( self::class, 'document_title' ) );
		add_filter( 'document_title_parts', array( self::class, 'archive_title' ) );
		add_filter( 'document_title_separator', static fn(): string => (string) Settings::get( 'seo.separator', '·' ) );
		add_filter( 'wp_robots', array( self::class, 'robots' ) );
		if ( ! is_admin() ) {
			add_filter( 'option_blogdescription', static fn( $value ) => rolck_l( Settings::get( 'general.tagline' ) ) ?: $value );
		}
	}

	public static function register_fields(): void {
		foreach ( Translations::post_types() as $type ) {
			foreach ( self::FIELDS as $key => $kind ) {
				register_post_meta(
					$type,
					$key,
					array(
						'type'              => $kind,
						'single'            => true,
						'show_in_rest'      => true,
						'default'           => 'boolean' === $kind ? false : ( 'integer' === $kind ? 0 : '' ),
						'sanitize_callback' => match ( $kind ) {
							'integer' => 'absint',
							'boolean' => 'rest_sanitize_boolean',
							default   => '_rolck_seo_description' === $key ? 'sanitize_textarea_field' : 'sanitize_text_field',
						},
						'auth_callback'     => static fn( $allowed, $meta_key, $post_id ): bool => current_user_can( 'edit_post', $post_id ),
					)
				);
			}
		}
	}

	/** Post ou página que representa a página atual (inclui a página do blog). */
	public static function current_post(): ?WP_Post {
		if ( is_singular() ) {
			$post = get_queried_object();
			return $post instanceof WP_Post ? $post : null;
		}
		if ( is_home() && get_option( 'page_for_posts' ) ) {
			return get_post( (int) get_option( 'page_for_posts' ) );
		}
		return null;
	}

	/**
	 * Título personalizado do campo SEO (ou o título da página inicial do painel).
	 *
	 * @param string $title Título calculado até aqui.
	 */
	public static function document_title( string $title ): string {
		$post   = self::current_post();
		$custom = $post ? trim( (string) get_post_meta( $post->ID, '_rolck_seo_title', true ) ) : '';
		if ( '' !== $custom ) {
			return $custom;
		}
		if ( is_front_page() ) {
			return rolck_l( Settings::get( 'seo.home_title' ) ) ?: $title;
		}
		return $title;
	}

	/**
	 * A vitrine usa o texto "Projetos" da aba Textos, no idioma da página
	 * (o nome do tipo de conteúdo só existe em português).
	 *
	 * @param array<string,string> $parts Partes do título.
	 */
	public static function archive_title( array $parts ): array {
		if ( is_post_type_archive( Projects::POST_TYPE ) ) {
			$parts['title'] = Strings::get( 'projects' );
		}
		return $parts;
	}

	/** Título final da página, como aparece na aba e nos compartilhamentos. */
	public static function title(): string {
		return wp_get_document_title();
	}

	public static function description(): string {
		$post = self::current_post();
		$text = $post ? trim( (string) get_post_meta( $post->ID, '_rolck_seo_description', true ) ) : '';

		if ( '' === $text && is_front_page() ) {
			$text = rolck_l( Settings::get( 'seo.home_description' ) );
		}
		if ( '' === $text && $post && ! is_home() ) {
			$text = has_excerpt( $post ) ? $post->post_excerpt : (string) get_post_meta( $post->ID, 'rolck_tagline', true );
			if ( '' === trim( $text ) ) {
				$text = wp_strip_all_tags( strip_shortcodes( excerpt_remove_blocks( $post->post_content ) ) );
			}
		}
		if ( '' === $text && ( is_category() || is_tag() || is_tax() ) ) {
			$term = get_queried_object();
			$text = $term instanceof WP_Term ? $term->description : '';
		}
		if ( '' === trim( $text ) ) {
			$text = rolck_l( Settings::get( 'general.tagline' ) );
		}

		$text = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $text ) ) );
		return mb_strlen( $text ) > 160 ? rtrim( mb_substr( $text, 0, 157 ), ' ,.;:' ) . '…' : $text;
	}

	/**
	 * Imagem de compartilhamento: campo SEO → imagem destacada → padrão do painel → logo.
	 *
	 * @return array{url:string,width:int,height:int,alt:string}|null
	 */
	public static function image(): ?array {
		$post = self::current_post();
		$ids  = array(
			$post ? (int) get_post_meta( $post->ID, '_rolck_seo_image', true ) : 0,
			$post ? (int) get_post_thumbnail_id( $post ) : 0,
			(int) Settings::get( 'seo.default_image_id', 0 ),
			(int) Settings::get( 'general.logo_id', 0 ),
			(int) get_theme_mod( 'custom_logo' ),
		);
		foreach ( $ids as $id ) {
			$src = $id ? wp_get_attachment_image_src( $id, 'large' ) : false;
			if ( $src ) {
				return array(
					'url'    => $src[0],
					'width'  => (int) $src[1],
					'height' => (int) $src[2],
					'alt'    => (string) get_post_meta( $id, '_wp_attachment_image_alt', true ),
				);
			}
		}
		return null;
	}

	/**
	 * @param array<string,bool|string> $robots Diretivas.
	 */
	public static function robots( array $robots ): array {
		$post    = self::current_post();
		$noindex = ( $post && get_post_meta( $post->ID, '_rolck_seo_noindex', true ) )
			|| ( is_search() && Settings::get( 'seo.noindex_search', true ) )
			|| ( is_paged() && Settings::get( 'seo.noindex_paged', false ) )
			|| is_attachment();

		if ( $noindex ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
			unset( $robots['max-image-preview'] );
		} elseif ( ! is_404() && get_option( 'blog_public' ) ) {
			$robots['max-snippet']       = '-1';
			$robots['max-video-preview'] = '-1';
		}
		return $robots;
	}

	/** A página atual pode ser indexada? */
	public static function indexable(): bool {
		$robots = apply_filters( 'wp_robots', array() );
		return empty( $robots['noindex'] ) && ! is_404();
	}
}
