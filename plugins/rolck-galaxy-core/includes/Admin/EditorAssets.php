<?php
/**
 * Painéis do editor de blocos (React): Idioma e tradução, SEO e Detalhes do projeto.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\Admin;

use RolckGalaxy\Core\Content\Projects;
use RolckGalaxy\Core\I18n\Languages;
use RolckGalaxy\Core\I18n\Strings;
use RolckGalaxy\Core\I18n\Translations;
use RolckGalaxy\Core\Settings;

defined( 'ABSPATH' ) || exit;

final class EditorAssets {

	public static function register(): void {
		add_action( 'enqueue_block_editor_assets', array( self::class, 'enqueue' ) );
	}

	public static function enqueue(): void {
		$asset_file = ROLCK_CORE_DIR . 'build/editor/index.asset.php';
		if ( ! is_readable( $asset_file ) ) {
			return;
		}
		$asset = require $asset_file;
		wp_enqueue_script( 'rolck-editor', ROLCK_CORE_URL . 'build/editor/index.js', $asset['dependencies'], $asset['version'], true );
		if ( is_readable( ROLCK_CORE_DIR . 'build/editor/index.css' ) ) {
			wp_enqueue_style( 'rolck-editor', ROLCK_CORE_URL . 'build/editor/index.css', array(), $asset['version'] );
		}

		$languages = array();
		foreach ( Languages::codes() as $code ) {
			$languages[] = array( 'code' => $code ) + Languages::info( $code );
		}
		$statuses = array();
		foreach ( Projects::statuses() as $status ) {
			$statuses[] = array(
				'value' => $status,
				'label' => Strings::get( "status_$status", 'pt' ),
			);
		}

		wp_add_inline_script(
			'rolck-editor',
			'window.rolckEditor = ' . wp_json_encode(
				array(
					'languages'         => $languages,
					'defaultLanguage'   => Languages::default(),
					'translatableTypes' => Translations::post_types(),
					'projectType'       => Projects::POST_TYPE,
					'projectStatuses'   => $statuses,
					'appCategories'     => array_map(
						static fn( string $value, string $label ): array => array(
							'value' => $value,
							'label' => $label,
						),
						array_keys( Projects::app_categories() ),
						Projects::app_categories()
					),
					'siteName'          => (string) Settings::get( 'general.company_name' ),
					'separator'         => (string) Settings::get( 'seo.separator', '·' ),
					'homeUrl'           => home_url( '/' ),
				)
			) . ';',
			'before'
		);
	}
}
