<?php
/**
 * Cache de navegador e compressão no Apache/LiteSpeed (HostGator e afins).
 *
 * Grava um bloco próprio no .htaccess com insert_with_markers() — a mesma
 * função que o WordPress usa para as URLs amigáveis. Fontes, imagens, CSS e
 * JS ficam guardados por 1 ano no navegador (o WordPress muda o ?ver= dos
 * arquivos quando eles mudam). As páginas HTML não entram no cache.
 *
 * O bloco é gravado quando um administrador abre o painel (deploy por FTP
 * não passa pela ativação do plugin) e sai ao desligar a opção em
 * Rolck Galaxy → Desempenho ou ao desativar o plugin.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\Performance;

use RolckGalaxy\Core\Settings;

defined( 'ABSPATH' ) || exit;

final class BrowserCache {

	private const MARKER = 'Rolck Galaxy';
	private const OPTION = 'rolck_htaccess_rules';

	public static function register(): void {
		add_action( 'admin_init', array( self::class, 'sync' ) );
	}

	/** Grava (ou remove) o bloco quando as regras ou a opção mudaram. */
	public static function sync(): void {
		$rules = (bool) Settings::get( 'performance.browser_cache', true ) ? self::rules() : array();
		$hash  = md5( implode( "\n", $rules ) );
		if ( get_option( self::OPTION ) === $hash || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( self::write( $rules ) ) {
			update_option( self::OPTION, $hash, false );
		}
	}

	/** Na desativação do plugin: tira o bloco do .htaccess. */
	public static function remove(): void {
		self::write( array() );
		delete_option( self::OPTION );
	}

	/** @param string[] $rules Linhas do bloco (vazio remove). */
	private static function write( array $rules ): bool {
		global $is_apache;
		if ( ! $is_apache ) {
			return false;
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/misc.php';
		$file = get_home_path() . '.htaccess';
		if ( file_exists( $file ) ? ! wp_is_writable( $file ) : ! wp_is_writable( dirname( $file ) ) ) {
			return false;
		}
		return insert_with_markers( $file, self::MARKER, $rules );
	}

	/** @return string[] */
	public static function rules(): array {
		$year  = 'access plus 1 year';
		$types = array( 'font/woff2', 'font/woff', 'image/avif', 'image/webp', 'image/png', 'image/jpeg', 'image/gif', 'image/svg+xml', 'image/x-icon', 'image/vnd.microsoft.icon', 'text/css', 'text/javascript', 'application/javascript' );

		$lines   = array( '<IfModule mod_expires.c>', '	ExpiresActive On' );
		foreach ( $types as $type ) {
			$lines[] = sprintf( '	ExpiresByType %s "%s"', $type, $year );
		}
		$lines[] = '</IfModule>';

		return array_merge(
			$lines,
			array(
				'<IfModule mod_headers.c>',
				'	<FilesMatch "\.(woff2?|avif|webp|png|jpe?g|gif|svg|ico|css|js|mjs)$">',
				'		Header set Cache-Control "public, max-age=31536000, immutable"',
				'	</FilesMatch>',
				'</IfModule>',
				'<IfModule mod_deflate.c>',
				'	AddOutputFilterByType DEFLATE text/html text/plain text/css text/xml text/javascript application/javascript application/json application/xml application/rss+xml image/svg+xml',
				'</IfModule>',
			)
		);
	}
}
