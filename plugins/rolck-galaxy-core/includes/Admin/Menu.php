<?php
/**
 * Menu "Rolck Galaxy" no painel, com a tela de configurações em React.
 *
 * A tela (src/admin/settings) lê e grava pela REST API nativa
 * (/wp/v2/settings → rolck_settings). Aqui só entram o menu, os arquivos e
 * os dados iniciais que a tela precisa.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\Admin;

use RolckGalaxy\Core\I18n\Languages;
use RolckGalaxy\Core\I18n\Strings;
use RolckGalaxy\Core\Settings;

defined( 'ABSPATH' ) || exit;

final class Menu {

	public const SLUG = 'rolck-galaxy';

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'assets' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( ROLCK_CORE_FILE ), array( self::class, 'action_link' ) );
		ListColumns::register();
	}

	public static function menu(): void {
		$icon = 'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#a7aaad"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm0 3.2c.6 0 1 .4 1 1v4.8h4.8a1 1 0 1 1 0 2H13v4.8a1 1 0 1 1-2 0V13H6.2a1 1 0 1 1 0-2H11V6.2c0-.6.4-1 1-1Z"/></svg>' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

		add_menu_page( 'Rolck Galaxy', 'Rolck Galaxy', 'manage_options', self::SLUG, array( self::class, 'render' ), $icon, 3 );
		add_submenu_page( self::SLUG, 'Configurações do site', 'Configurações', 'manage_options', self::SLUG, array( self::class, 'render' ) );
	}

	public static function render(): void {
		echo '<div class="wrap rolck-admin"><div id="rolck-settings-root"><p>Carregando configurações…</p></div></div>';
	}

	/**
	 * @param array<string,string> $links Links da lista de plugins.
	 */
	public static function action_link( array $links ): array {
		array_unshift( $links, sprintf( '<a href="%s">Configurações</a>', esc_url( admin_url( 'admin.php?page=' . self::SLUG ) ) ) );
		return $links;
	}

	/**
	 * @param string $hook Tela atual do painel.
	 */
	public static function assets( string $hook ): void {
		if ( 'toplevel_page_' . self::SLUG !== $hook ) {
			return;
		}
		$asset_file = ROLCK_CORE_DIR . 'build/admin/settings.asset.php';
		if ( ! is_readable( $asset_file ) ) {
			return;
		}
		$asset = require $asset_file;

		wp_enqueue_media();
		wp_enqueue_script( 'rolck-settings', ROLCK_CORE_URL . 'build/admin/settings.js', $asset['dependencies'], $asset['version'], true );
		wp_enqueue_style( 'rolck-settings', ROLCK_CORE_URL . 'build/admin/settings.css', array( 'wp-components' ), $asset['version'] );

		$languages = array();
		foreach ( Languages::codes() as $code ) {
			$languages[] = array( 'code' => $code ) + Languages::info( $code );
		}

		wp_add_inline_script(
			'rolck-settings',
			'window.rolckSettings = ' . wp_json_encode(
				array(
					'option'       => Settings::OPTION,
					'defaults'     => Settings::defaults(),
					'languages'    => $languages,
					'stringGroups' => Strings::groups(),
					'siteUrl'      => home_url( '/' ),
					'fakeTranslator' => defined( 'ROLCK_FAKE_TRANSLATOR' ) && ROLCK_FAKE_TRANSLATOR,
					'links'        => array(
						'siteEditor' => admin_url( 'site-editor.php' ),
						'styles'     => admin_url( 'site-editor.php?p=%2Fstyles' ),
						'navigation' => admin_url( 'site-editor.php?p=%2Fnavigation' ),
						'parts'      => admin_url( 'site-editor.php?p=%2Fpattern&postType=wp_template_part' ),
						'projects'   => admin_url( 'edit.php?post_type=rolck_project' ),
						'pages'      => admin_url( 'edit.php?post_type=page' ),
						'posts'      => admin_url( 'edit.php' ),
						'media'      => admin_url( 'upload.php' ),
						'texts'      => admin_url( 'admin.php?page=' . self::SLUG . '&tab=texts#texts' ),
						'cta'        => admin_url( 'site-editor.php?p=' . rawurlencode( '/wp_template_part/' . get_stylesheet() . '//cta' ) . '&canvas=edit' ),
						'messages'   => admin_url( 'edit.php?post_type=rolck_message' ),
						'reading'    => admin_url( 'options-reading.php' ),
						'sitemap'    => home_url( '/wp-sitemap.xml' ),
						'pagespeed'  => 'https://pagespeed.web.dev/analysis?url=' . rawurlencode( home_url( '/' ) ),
					),
				)
			) . ';',
			'before'
		);
	}
}
