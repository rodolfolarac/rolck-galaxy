<?php
/**
 * Rolck Galaxy → Consentimentos (LGPD): consulta e exportação dos registros.
 *
 * Se alguém pedir comprovação (o próprio titular, a ANPD ou um processo),
 * basta buscar pelo código que o visitante vê em "Preferências de cookies"
 * ou exportar tudo em CSV.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\Admin;

use RolckGalaxy\Core\Privacy\ConsentLog;

defined( 'ABSPATH' ) || exit;

final class ConsentPage {

	public const SLUG = 'rolck-consents';

	private const LABELS = array(
		'accept_all'  => 'Aceitou todos',
		'reject_all'  => 'Rejeitou os opcionais',
		'custom'      => 'Escolheu por categoria',
		'acknowledge' => 'Ciente (só cookies necessários)',
	);

	private const CATEGORIES = array(
		'analytics' => 'Estatística',
		'marketing' => 'Marketing',
	);

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'menu' ), 20 );
		add_action( 'admin_post_rolck_consent_export', array( self::class, 'export' ) );
	}

	public static function menu(): void {
		add_submenu_page( Menu::SLUG, 'Consentimentos (LGPD)', 'Consentimentos', 'manage_options', self::SLUG, array( self::class, 'render' ) );
	}

	public static function export(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Sem permissão.', 403 );
		}
		check_admin_referer( 'rolck_consent_export' );
		ConsentLog::export_csv();
	}

	/** @param string $list Categorias separadas por vírgula. */
	private static function cats( string $list ): string {
		$names = array_map( static fn( $c ) => self::CATEGORIES[ $c ] ?? $c, array_filter( explode( ',', $list ) ) );
		return $names ? implode( ', ', $names ) : '—';
	}

	public static function render(): void {
		$search  = isset( $_GET['consent'] ) ? strtolower( sanitize_text_field( wp_unslash( $_GET['consent'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- só leitura.
		$rows    = ConsentLog::latest( $search ? 200 : 50, $search );
		$summary = ConsentLog::summary();
		$offset  = (int) ( (float) get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
		?>
		<div class="wrap">
			<h1>Consentimentos (LGPD)</h1>
			<p>
				Cada escolha feita no aviso de cookies fica registrada aqui como prova do consentimento (LGPD, art. 8º, § 2º).
				O IP não é guardado, só um código derivado dele (hash), e os registros são apagados automaticamente após
				<?php echo esc_html( (string) ConsentLog::retention_months() ); ?> meses.
				Textos, validade e prazo de guarda ficam em <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . Menu::SLUG . '&tab=privacy#privacy' ) ); ?>">Configurações → Privacidade (LGPD)</a>.
			</p>

			<h2>Últimos 30 dias</h2>
			<ul style="display:flex;flex-wrap:wrap;gap:1.5rem">
				<?php foreach ( $summary as $action => $total ) : ?>
					<li><strong style="font-size:1.4em"><?php echo esc_html( number_format_i18n( $total ) ); ?></strong><br><?php echo esc_html( self::LABELS[ $action ] ?? $action ); ?></li>
				<?php endforeach; ?>
			</ul>

			<form method="get" style="display:flex;gap:.5rem;align-items:center;margin:1rem 0">
				<input type="hidden" name="page" value="<?php echo esc_attr( self::SLUG ); ?>">
				<label for="rolck-consent-search" class="screen-reader-text">Código do consentimento</label>
				<input type="search" id="rolck-consent-search" name="consent" value="<?php echo esc_attr( $search ); ?>" placeholder="Código do consentimento" size="40">
				<button class="button">Buscar</button>
				<?php if ( $search ) : ?>
					<a class="button-link" href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::SLUG ) ); ?>">Limpar</a>
				<?php endif; ?>
				<a class="button button-primary" style="margin-left:auto" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=rolck_consent_export' ), 'rolck_consent_export' ) ); ?>">Exportar tudo (CSV)</a>
			</form>

			<table class="widefat striped">
				<thead>
					<tr>
						<th>Data e hora</th>
						<th>Código do consentimento</th>
						<th>Escolha</th>
						<th>Aceitas</th>
						<th>Oferecidas</th>
						<th>Versão do aviso</th>
						<th>Idioma</th>
						<th>Página</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! $rows ) : ?>
						<tr><td colspan="8"><?php echo esc_html( $search ? 'Nenhum registro com esse código.' : 'Nenhum consentimento registrado ainda.' ); ?></td></tr>
					<?php endif; ?>
					<?php foreach ( $rows as $row ) : ?>
						<tr>
							<td><?php echo esc_html( gmdate( 'd/m/Y H:i:s', strtotime( $row->created_at ) + $offset ) ); ?></td>
							<td><code><?php echo esc_html( $row->consent_id ); ?></code></td>
							<td><?php echo esc_html( self::LABELS[ $row->action ] ?? $row->action ); ?></td>
							<td><?php echo esc_html( self::cats( $row->granted ) ); ?></td>
							<td><?php echo esc_html( self::cats( $row->offered ) ); ?></td>
							<td><?php echo esc_html( $row->version ); ?></td>
							<td><?php echo esc_html( strtoupper( $row->lang ) ); ?></td>
							<td><?php echo esc_html( (string) wp_parse_url( $row->page, PHP_URL_PATH ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p class="description">Horários no fuso do site (Configurações → Geral). No CSV, em UTC.</p>
		</div>
		<?php
	}
}
