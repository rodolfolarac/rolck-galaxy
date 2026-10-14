<?php
/**
 * Registra todos os blocos compilados em build/blocks/ e a categoria "Rolck Galaxy".
 *
 * Um bloco novo é só uma pasta em src/blocks/ com block.json (crie com
 * `npx @wordpress/create-block --no-plugin` dentro de src/blocks) — o
 * registro aqui é automático.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\Blocks;

defined( 'ABSPATH' ) || exit;

final class Registry {

	public static function register(): void {
		add_action( 'init', array( self::class, 'register_blocks' ) );
		add_filter( 'block_categories_all', array( self::class, 'category' ) );
	}

	public static function register_blocks(): void {
		// CSS compartilhado (botões, selos). Os blocos o pedem pelo nome "rolck-ui" no block.json.
		$ui = ROLCK_CORE_DIR . 'build/shared/ui.asset.php';
		if ( is_readable( $ui ) ) {
			wp_register_style( 'rolck-ui', ROLCK_CORE_URL . 'build/shared/ui.css', array(), ( require $ui )['version'] );
			wp_style_add_data( 'rolck-ui', 'path', ROLCK_CORE_DIR . 'build/shared/ui.css' );
		}

		$dir      = ROLCK_CORE_DIR . 'build/blocks';
		$manifest = ROLCK_CORE_DIR . 'build/blocks-manifest.php';

		// WP 6.8+: registra tudo de uma vez a partir do manifesto (mais rápido).
		if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) && is_readable( $manifest ) ) {
			wp_register_block_types_from_metadata_collection( $dir, $manifest );
			return;
		}
		foreach ( (array) glob( $dir . '/*/block.json' ) as $file ) {
			register_block_type( dirname( (string) $file ) );
		}
	}

	/**
	 * @param array $categories Categorias do inseridor.
	 */
	public static function category( array $categories ): array {
		array_unshift(
			$categories,
			array(
				'slug'  => 'rolck-galaxy',
				'title' => 'Rolck Galaxy',
				'icon'  => null,
			)
		);
		return $categories;
	}
}
