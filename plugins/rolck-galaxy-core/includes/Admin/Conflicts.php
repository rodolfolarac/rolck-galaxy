<?php
/**
 * Avisa no painel quando há plugins que repetem o que o Rolck Galaxy já faz.
 *
 * Dois plugins de SEO geram canonical, metadados e sitemap em dobro (o Google
 * vê URLs conflitantes); dois avisos de cookies confundem o consentimento.
 * Muitas hospedagens instalam alguns deles junto com o WordPress.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\Admin;

defined( 'ABSPATH' ) || exit;

final class Conflicts {

	/** Arquivo do plugin → o que ele repete. */
	private const PLUGINS = array(
		'wordpress-seo/wp-seo.php'                       => 'SEO (canonical, metadados, dados estruturados e sitemap em dobro)',
		'wordpress-seo-premium/wp-seo-premium.php'       => 'SEO (canonical, metadados, dados estruturados e sitemap em dobro)',
		'seo-by-rank-math/rank-math.php'                 => 'SEO (canonical, metadados, dados estruturados e sitemap em dobro)',
		'all-in-one-seo-pack/all_in_one_seo_pack.php'    => 'SEO (canonical, metadados, dados estruturados e sitemap em dobro)',
		'cookieadmin/cookieadmin.php'                    => 'aviso de cookies (LGPD) e registro de consentimento',
		'cookieadmin-pro/cookieadmin-pro.php'            => 'aviso de cookies (LGPD) e registro de consentimento',
		'cookie-law-info/cookie-law-info.php'            => 'aviso de cookies (LGPD) e registro de consentimento',
		'complianz-gdpr/complianz-gpdr.php'              => 'aviso de cookies (LGPD) e registro de consentimento',
		'cookie-notice/cookie-notice.php'                => 'aviso de cookies (LGPD) e registro de consentimento',
		'polylang/polylang.php'                          => 'idiomas e seletor de idioma',
		'sitepress-multilingual-cms/sitepress.php'       => 'idiomas e seletor de idioma',
		'translatepress-multilingual/index.php'          => 'idiomas e seletor de idioma',
	);

	public static function register(): void {
		add_action( 'admin_notices', array( self::class, 'notice' ) );
	}

	/** @return array<string,string> Arquivo → nome e função repetida, só dos ativos. */
	public static function active(): array {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$installed = get_plugins();
		$found     = array();
		foreach ( self::PLUGINS as $file => $what ) {
			if ( is_plugin_active( $file ) ) {
				$found[ $file ] = ( $installed[ $file ]['Name'] ?? $file ) . ' — ' . $what;
			}
		}
		return $found;
	}

	public static function notice(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		$found = self::active();
		if ( ! $found ) {
			return;
		}
		echo '<div class="notice notice-warning"><p><strong>Rolck Galaxy:</strong> estes plugins repetem funções que o site já tem e atrapalham o SEO e o PageSpeed. Desative-os em Plugins:</p><ul style="list-style:disc;padding-left:1.5em">';
		foreach ( $found as $line ) {
			echo '<li>' . esc_html( $line ) . '</li>';
		}
		printf( '</ul><p><a class="button" href="%s">Abrir Plugins</a></p></div>', esc_url( admin_url( 'plugins.php?plugin_status=active' ) ) );
	}
}
