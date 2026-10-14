<?php
/**
 * Registro dos consentimentos (prova exigida pelo art. 8º, § 2º da LGPD).
 *
 * Cada escolha feita no aviso vira uma linha numa tabela própria
 * ({prefixo}rolck_consent_log), com o código do consentimento (o mesmo que o
 * visitante vê em "Preferências de cookies"), data e hora em UTC, versão do
 * aviso, categorias oferecidas e aceitas, idioma, página e navegador.
 *
 * O IP não é guardado: fica só um hash HMAC-SHA256 com a chave secreta do
 * WordPress (pseudonimização, art. 13, § 4º). Com o IP em mãos dá para
 * conferir se ele bate com um registro, mas não dá para descobrir o IP a
 * partir do registro. As linhas saem sozinhas depois do prazo de guarda
 * configurado em Rolck Galaxy → Privacidade (LGPD).
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\Privacy;

use RolckGalaxy\Core\Settings;

defined( 'ABSPATH' ) || exit;

final class ConsentLog {

	public const ACTIONS    = array( 'accept_all', 'reject_all', 'custom', 'acknowledge' );
	private const DB_VERSION = '1';
	private const CRON       = 'rolck_consent_cleanup';

	public static function register(): void {
		add_action( 'init', array( self::class, 'maybe_install' ) );
		add_action( self::CRON, array( self::class, 'cleanup' ) );
		if ( ! wp_next_scheduled( self::CRON ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::CRON );
		}
	}

	public static function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'rolck_consent_log';
	}

	/** Cria a tabela na primeira vez (e quando a estrutura mudar). */
	public static function maybe_install(): void {
		if ( get_option( 'rolck_consent_db' ) === self::DB_VERSION ) {
			return;
		}
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$table   = self::table();
		$charset = $wpdb->get_charset_collate();
		dbDelta(
			"CREATE TABLE $table (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				consent_id char(36) NOT NULL,
				action varchar(20) NOT NULL,
				granted varchar(100) NOT NULL DEFAULT '',
				offered varchar(100) NOT NULL DEFAULT '',
				version varchar(20) NOT NULL DEFAULT '',
				lang varchar(5) NOT NULL DEFAULT '',
				page varchar(255) NOT NULL DEFAULT '',
				ip_hash char(64) NOT NULL DEFAULT '',
				user_agent varchar(255) NOT NULL DEFAULT '',
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY consent_id (consent_id),
				KEY created_at (created_at)
			) $charset;"
		);
		update_option( 'rolck_consent_db', self::DB_VERSION );
	}

	/** Hash do IP de quem fez o pedido (nunca o IP em si). */
	public static function ip_hash( ?string $ip = null ): string {
		$ip ??= isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		return hash_hmac( 'sha256', $ip, wp_salt( 'auth' ) );
	}

	/** @param array<string,mixed> $row Dados já validados. */
	public static function insert( array $row ): bool {
		global $wpdb;
		self::maybe_install();
		$agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		return (bool) $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			self::table(),
			array(
				'consent_id' => $row['consent_id'],
				'action'     => $row['action'],
				'granted'    => implode( ',', $row['granted'] ),
				'offered'    => implode( ',', $row['offered'] ),
				'version'    => $row['version'],
				'lang'       => $row['lang'],
				'page'       => mb_substr( $row['page'], 0, 255 ),
				'ip_hash'    => $row['ip_hash'],
				'user_agent' => mb_substr( $agent, 0, 255 ),
				'created_at' => gmdate( 'Y-m-d H:i:s' ),
			)
		);
	}

	/** Prazo de guarda em meses (padrão: 5 anos, prazo do art. 27 do CDC). */
	public static function retention_months(): int {
		return max( 1, (int) Settings::get( 'privacy.retention_months', 60 ) );
	}

	/** Apaga os registros mais antigos que o prazo de guarda. */
	public static function cleanup(): void {
		global $wpdb;
		$limit = gmdate( 'Y-m-d H:i:s', strtotime( '-' . self::retention_months() . ' months' ) );
		$wpdb->query( $wpdb->prepare( 'DELETE FROM %i WHERE created_at < %s', self::table(), $limit ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	}

	/**
	 * Registros mais recentes, com busca opcional pelo código do consentimento.
	 *
	 * @return array<int,object>
	 */
	public static function latest( int $limit = 50, string $consent_id = '' ): array {
		global $wpdb;
		self::maybe_install();
		if ( $consent_id ) {
			return (array) $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %i WHERE consent_id = %s ORDER BY id DESC LIMIT %d', self::table(), $consent_id, $limit ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}
		return (array) $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %i ORDER BY id DESC LIMIT %d', self::table(), $limit ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	}

	/** @return array<string,int> Ação → total nos últimos 30 dias. */
	public static function summary(): array {
		global $wpdb;
		self::maybe_install();
		$since = gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) );
		$rows  = (array) $wpdb->get_results( $wpdb->prepare( 'SELECT action, COUNT(*) AS total FROM %i WHERE created_at >= %s GROUP BY action', self::table(), $since ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$out   = array_fill_keys( self::ACTIONS, 0 );
		foreach ( $rows as $row ) {
			$out[ $row->action ] = (int) $row->total;
		}
		return $out;
	}

	/** Envia todos os registros como CSV (aberto direto no Excel/Planilhas). */
	public static function export_csv(): void {
		global $wpdb;
		self::maybe_install();
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=consentimentos-lgpd-' . gmdate( 'Y-m-d' ) . '.csv' );
		$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		fwrite( $out, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- BOM para o Excel reconhecer UTF-8.
		fputcsv( $out, array( 'id', 'codigo_consentimento', 'acao', 'categorias_aceitas', 'categorias_oferecidas', 'versao_aviso', 'idioma', 'pagina', 'hash_ip', 'navegador', 'data_hora_utc' ), ';' );
		$last = 0;
		do {
			$rows = (array) $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %i WHERE id > %d ORDER BY id ASC LIMIT 1000', self::table(), $last ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			foreach ( $rows as $row ) {
				fputcsv( $out, array( $row['id'], $row['consent_id'], $row['action'], $row['granted'], $row['offered'], $row['version'], $row['lang'], $row['page'], $row['ip_hash'], $row['user_agent'], $row['created_at'] ), ';' );
				$last = (int) $row['id'];
			}
		} while ( count( $rows ) === 1000 );
		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		exit;
	}
}
