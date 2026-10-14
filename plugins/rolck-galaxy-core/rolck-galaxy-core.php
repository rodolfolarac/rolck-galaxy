<?php
/**
 * Plugin Name:       Rolck Galaxy Core
 * Plugin URI:        https://rolckgalaxy.com
 * Description:       Vitrine de projetos, blocos, idiomas (pt-BR/inglês), SEO, formulário de contato e otimizações do site Rolck Galaxy. Tudo nativo, sem plugins externos.
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      8.1
 * Author:            Rolck Galaxy
 * License:           GPL-2.0-or-later
 * Text Domain:       rolck-galaxy-core
 *
 * @package RolckGalaxy
 */

defined( 'ABSPATH' ) || exit;

define( 'ROLCK_CORE_VERSION', '1.0.0' );
define( 'ROLCK_CORE_FILE', __FILE__ );
define( 'ROLCK_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'ROLCK_CORE_URL', plugin_dir_url( __FILE__ ) );

/*
 * Autoload PSR-4 simples: RolckGalaxy\Core\Seo\Schema → includes/Seo/Schema.php.
 * Sem Composer: o plugin funciona copiando a pasta para wp-content/plugins.
 */
spl_autoload_register(
	static function ( string $class ): void {
		$prefix = 'RolckGalaxy\\Core\\';
		if ( ! str_starts_with( $class, $prefix ) ) {
			return;
		}
		$file = ROLCK_CORE_DIR . 'includes/' . str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) ) . '.php';
		if ( is_readable( $file ) ) {
			require $file;
		}
	}
);

require ROLCK_CORE_DIR . 'includes/helpers.php';

/*
 * O idioma precisa ser decidido antes de tudo — inclusive antes do WordPress
 * carregar as traduções do núcleo —, por isso o roteador sobe já aqui e não
 * no plugins_loaded.
 */
RolckGalaxy\Core\I18n\Router::boot();

add_action( 'plugins_loaded', array( RolckGalaxy\Core\Plugin::class, 'boot' ) );

register_activation_hook( __FILE__, array( RolckGalaxy\Core\Plugin::class, 'activate' ) );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
