<?php
/**
 * Enxuga o que o WordPress carrega por padrão e que este site não usa.
 * Cada item liga e desliga em Rolck Galaxy → Desempenho.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\Performance;

use RolckGalaxy\Core\Settings;

defined( 'ABSPATH' ) || exit;

final class Optimizations {

	public static function register(): void {
		$on = static fn( string $key ): bool => (bool) Settings::get( "performance.$key", true );

		if ( $on( 'disable_emojis' ) ) {
			add_action( 'init', array( self::class, 'disable_emojis' ) );
		}
		if ( $on( 'disable_embeds' ) ) {
			add_action( 'init', array( self::class, 'disable_embeds' ), 9999 );
		}
		if ( $on( 'clean_head' ) ) {
			add_action( 'init', array( self::class, 'clean_head' ) );
		}
		if ( $on( 'disable_xmlrpc' ) ) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
			add_filter( 'wp_headers', static fn( array $h ): array => array_diff_key( $h, array( 'X-Pingback' => 1 ) ) );
		}
		if ( $on( 'disable_dashicons' ) ) {
			add_action( 'wp_enqueue_scripts', array( self::class, 'dashicons' ), 100 );
		}

		if ( $on( 'inline_css' ) ) {
			// CSS pequeno (dos blocos e do tema) vai embutido no HTML: nenhum arquivo bloqueia a primeira pintura.
			add_filter( 'styles_inline_size_limit', static fn(): int => 100000 );
		}
		if ( $on( 'webp_uploads' ) ) {
			add_filter( 'image_editor_output_format', array( self::class, 'webp' ) );
		}

		add_filter( 'wp_speculation_rules_configuration', array( self::class, 'speculation' ) );

		// Blocos carregam só o CSS dos blocos que aparecem na página.
		add_filter( 'should_load_separate_core_block_assets', '__return_true' );
		add_filter( 'wp_img_tag_add_auto_sizes', '__return_true' );
	}

	public static function disable_emojis(): void {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
		remove_action( 'admin_enqueue_scripts', 'wp_enqueue_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'emoji_svg_url', '__return_false' );
	}

	public static function disable_embeds(): void {
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );
		add_filter( 'embed_oembed_discover', '__return_false' );
		wp_deregister_script( 'wp-embed' );
	}

	public static function clean_head(): void {
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		remove_action( 'wp_head', 'rest_output_link_wp_head' );
		remove_action( 'wp_head', 'feed_links_extra', 3 );
		// Com os comentários fechados, o feed de comentários é uma página vazia para o Google.
		add_filter( 'feed_links_show_comments_feed', static fn(): bool => 'open' === get_option( 'default_comment_status' ) );
		remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
		remove_action( 'template_redirect', 'rest_output_link_header', 11 );
	}

	/** Dashicons só para quem está logado (barra de administração). */
	public static function dashicons(): void {
		if ( ! is_user_logged_in() ) {
			wp_dequeue_style( 'dashicons' );
		}
	}

	/**
	 * Os tamanhos gerados de JPEG e PNG enviados saem em WebP (bem mais leves),
	 * quando o servidor suporta.
	 *
	 * @param array<string,string> $formats Mapeamento tipo original → tipo gerado.
	 */
	public static function webp( array $formats ): array {
		if ( wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
			$formats['image/jpeg'] = 'image/webp';
			$formats['image/png']  = 'image/webp';
		}
		return $formats;
	}

	/**
	 * Pré-carregamento nativo das próximas páginas (Speculation Rules, WP 6.8+):
	 * o clique abre a página quase instantaneamente.
	 *
	 * @param array|null $config Configuração padrão.
	 */
	public static function speculation( ?array $config ): ?array {
		$mode = (string) Settings::get( 'performance.speculation', 'prerender' );
		if ( 'off' === $mode ) {
			return null;
		}
		return array(
			'mode'      => in_array( $mode, array( 'prefetch', 'prerender' ), true ) ? $mode : 'prerender',
			'eagerness' => 'moderate',
		);
	}
}
