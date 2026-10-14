<?php
/**
 * Aviso de cookies e consentimento (LGPD), sem plugin de terceiros.
 *
 * Segue a Lei 13.709/2018 (LGPD) e o Guia Orientativo de Cookies da ANPD:
 * - cookies necessários funcionam sem consentimento (legítimo interesse, art. 7º, IX);
 * - os opcionais só são ativados depois do consentimento livre, informado e
 *   inequívoco (art. 5º, XII e art. 8º), nunca pré-marcados;
 * - "Rejeitar opcionais" tem o mesmo destaque de "Aceitar todos";
 * - a escolha pode ser revista e o consentimento retirado a qualquer momento,
 *   de forma gratuita e facilitada (art. 8º, § 5º), pelo link no rodapé;
 * - cada escolha é registrada (ConsentLog), porque cabe ao controlador provar
 *   que obteve o consentimento (art. 8º, § 2º).
 *
 * Só aparecem as categorias realmente usadas pelo site (Integrações): sem
 * Google Analytics nem código de terceiros, o aviso diz que só há cookies
 * necessários. O HTML é igual para todos os visitantes (cache de página
 * continua funcionando). O aviso já vem visível no HTML, para aparecer na
 * primeira pintura (sem atrasar o Speed Index); um script de poucas linhas
 * logo depois dele o esconde antes da pintura para quem já escolheu.
 *
 * Scripts que dependem de consentimento são impressos inativos:
 *   <script type="text/plain" data-rolck-consent="analytics">…</script>
 *   <template data-rolck-consent="marketing">…</template>
 * e o JavaScript do aviso os ativa quando a categoria é aceita.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\Privacy;

use RolckGalaxy\Core\I18n\Strings;
use RolckGalaxy\Core\Settings;

defined( 'ABSPATH' ) || exit;

final class Consent {

	public const COOKIE   = 'rolck_consent';
	public const OPTIONAL = array( 'analytics', 'marketing' );

	public static function register(): void {
		ConsentLog::register();
		add_action( 'rest_api_init', array( self::class, 'routes' ) );
		if ( ! is_admin() ) {
			add_action( 'wp_footer', array( self::class, 'render' ), 5 );
			add_action( 'wp_enqueue_scripts', array( self::class, 'assets' ) );
		}
	}

	public static function enabled(): bool {
		return (bool) Settings::get( 'privacy.enabled', true );
	}

	/**
	 * Categorias opcionais em uso, conforme as integrações configuradas.
	 *
	 * @return string[]
	 */
	public static function categories(): array {
		$used = array();
		if ( preg_match( '/^G-[A-Z0-9]+$/', (string) Settings::get( 'integrations.ga4_id', '' ) ) ) {
			$used[] = 'analytics';
		}
		$head = trim( (string) Settings::get( 'integrations.head_code', '' ) );
		$cat  = self::head_code_category();
		if ( '' !== $head && 'necessary' !== $cat ) {
			$used[] = $cat;
		}
		return array_values( array_intersect( self::OPTIONAL, array_unique( $used ) ) );
	}

	/** Categoria do "Código extra no <head>" (necessary, analytics ou marketing). */
	public static function head_code_category(): string {
		$cat = (string) Settings::get( 'integrations.head_code_category', 'marketing' );
		return in_array( $cat, array( 'necessary', 'analytics', 'marketing' ), true ) ? $cat : 'marketing';
	}

	/** Validade do consentimento em dias (o aviso volta depois disso). */
	public static function days(): int {
		return max( 1, min( 730, (int) Settings::get( 'privacy.days', 180 ) ) );
	}

	/**
	 * Cookies que o site usa, por categoria (só as categorias em uso).
	 * É a mesma lista mostrada no aviso e no bloco "Tabela de cookies".
	 *
	 * @return array<string,array<int,array{name:string,provider:string,purpose:string,duration:string}>>
	 */
	public static function cookies(): array {
		$site = Strings::get( 'consent_first_party' );
		$list = array(
			'necessary' => array(
				array(
					'name'     => self::COOKIE,
					'provider' => $site,
					'purpose'  => Strings::get( 'consent_purpose_consent' ),
					'duration' => sprintf( Strings::get( 'consent_days' ), self::days() ),
				),
				array(
					'name'     => 'wordpress_logged_in_*, wordpress_sec_*, wp-settings-*',
					'provider' => $site,
					'purpose'  => Strings::get( 'consent_purpose_wp' ),
					'duration' => Strings::get( 'consent_wp_duration' ),
				),
			),
		);
		$used = self::categories();
		if ( in_array( 'analytics', $used, true ) && preg_match( '/^G-[A-Z0-9]+$/', (string) Settings::get( 'integrations.ga4_id', '' ) ) ) {
			$list['analytics'] = array(
				array(
					'name'     => '_ga',
					'provider' => 'Google Analytics',
					'purpose'  => Strings::get( 'consent_purpose_ga' ),
					'duration' => sprintf( Strings::get( 'consent_years' ), 2 ),
				),
				array(
					'name'     => '_ga_*',
					'provider' => 'Google Analytics',
					'purpose'  => Strings::get( 'consent_purpose_ga_session' ),
					'duration' => sprintf( Strings::get( 'consent_years' ), 2 ),
				),
			);
		}
		foreach ( $used as $cat ) {
			$list[ $cat ] ??= array();
		}
		return $list;
	}

	/** Contato de privacidade: encarregado (DPO), se informado, ou o e-mail do site. */
	public static function contact(): array {
		$email = (string) Settings::get( 'privacy.dpo_email', '' );
		return array(
			'name'  => (string) Settings::get( 'privacy.dpo_name', '' ),
			'email' => $email ? $email : (string) Settings::get( 'general.email', '' ),
		);
	}

	public static function assets(): void {
		if ( ! self::enabled() ) {
			return;
		}
		$asset_file = ROLCK_CORE_DIR . 'build/consent/index.asset.php';
		if ( ! is_readable( $asset_file ) ) {
			return;
		}
		$asset = require $asset_file;
		wp_enqueue_script(
			'rolck-consent',
			ROLCK_CORE_URL . 'build/consent/index.js',
			array(),
			$asset['version'],
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}

	/** Aviso (primeira camada) e painel de preferências (segunda camada). */
	public static function render(): void {
		if ( ! self::enabled() || is_customize_preview() ) {
			return;
		}
		$css = ROLCK_CORE_DIR . 'build/consent/style-index.css';
		if ( is_readable( $css ) ) {
			echo '<style id="rolck-consent-css">' . str_ireplace( '</style', '', (string) file_get_contents( $css ) ) . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- CSS do próprio plugin.
		}

		$used    = self::categories();
		$cookies = self::cookies();
		$policy  = get_privacy_policy_url();
		$contact = self::contact();
		$s       = static fn( string $key ): string => Strings::get( $key );
		$config  = array(
			'version'    => (string) Settings::get( 'privacy.version', '1' ),
			'days'       => self::days(),
			'categories' => $used,
			'log'        => (bool) Settings::get( 'privacy.log', true ),
			'endpoint'   => esc_url_raw( rest_url( 'rolck/v1/consent' ) ),
			'lang'       => \RolckGalaxy\Core\I18n\Languages::current(),
		);
		?>
<script type="application/json" id="rolck-consent-config"><?php echo wp_json_encode( $config ); ?></script>
<noscript><style>#rolck-consent{display:none}</style></noscript>
<section class="rolck-consent" id="rolck-consent" aria-labelledby="rolck-consent-title">
	<h2 class="rolck-consent__title" id="rolck-consent-title"><?php echo esc_html( $s( 'consent_title' ) ); ?></h2>
	<p class="rolck-consent__text">
		<?php echo esc_html( $used ? $s( 'consent_text' ) : $s( 'consent_text_essential' ) ); ?>
		<?php if ( $policy ) : ?>
			<a href="<?php echo esc_url( $policy ); ?>"><?php echo esc_html( $s( 'consent_policy' ) ); ?></a>
		<?php endif; ?>
	</p>
	<div class="rolck-consent__actions">
		<?php if ( $used ) : ?>
			<button type="button" class="rolck-consent__btn" data-consent="reject"><?php echo esc_html( $s( 'consent_reject' ) ); ?></button>
			<button type="button" class="rolck-consent__btn" data-consent="accept"><?php echo esc_html( $s( 'consent_accept' ) ); ?></button>
			<button type="button" class="rolck-consent__btn is-link" data-consent="open"><?php echo esc_html( $s( 'consent_customize' ) ); ?></button>
		<?php else : ?>
			<button type="button" class="rolck-consent__btn" data-consent="accept"><?php echo esc_html( $s( 'consent_ok' ) ); ?></button>
			<button type="button" class="rolck-consent__btn is-link" data-consent="open"><?php echo esc_html( $s( 'consent_details' ) ); ?></button>
		<?php endif; ?>
	</div>
</section>
<script><?php echo self::early_check(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- script fixo. ?></script>
<dialog class="rolck-consent-prefs" id="rolck-consent-prefs" aria-labelledby="rolck-consent-prefs-title">
	<form method="dialog" class="rolck-consent-prefs__inner">
		<header class="rolck-consent-prefs__head">
			<h2 id="rolck-consent-prefs-title"><?php echo esc_html( $s( 'consent_prefs_title' ) ); ?></h2>
			<button type="button" class="rolck-consent-prefs__close" data-consent="close" aria-label="<?php echo esc_attr( $s( 'consent_close' ) ); ?>">&times;</button>
		</header>
		<div class="rolck-consent-prefs__body">
			<p><?php echo esc_html( $s( 'consent_prefs_text' ) ); ?></p>
			<?php
			foreach ( $cookies as $cat => $rows ) :
				$required = 'necessary' === $cat;
				?>
				<div class="rolck-consent-cat">
					<div class="rolck-consent-cat__head">
						<h3 id="rolck-consent-cat-<?php echo esc_attr( $cat ); ?>"><?php echo esc_html( $s( 'consent_cat_' . $cat ) ); ?></h3>
						<?php if ( $required ) : ?>
							<span class="rolck-consent-cat__always"><?php echo esc_html( $s( 'consent_always_on' ) ); ?></span>
						<?php else : ?>
							<input type="checkbox" role="switch" class="rolck-consent-cat__switch" name="<?php echo esc_attr( $cat ); ?>" aria-labelledby="rolck-consent-cat-<?php echo esc_attr( $cat ); ?>">
						<?php endif; ?>
					</div>
					<p><?php echo esc_html( $s( 'consent_cat_' . $cat . '_text' ) ); ?></p>
					<?php if ( $rows ) : ?>
						<details>
							<summary><?php echo esc_html( sprintf( $s( 'consent_see_cookies' ), count( $rows ) ) ); ?></summary>
							<?php self::table( $rows ); ?>
						</details>
					<?php elseif ( 'marketing' === $cat || 'analytics' === $cat ) : ?>
						<p class="rolck-consent-cat__note"><?php echo esc_html( $s( 'consent_third_party_note' ) ); ?></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
			<dl class="rolck-consent-prefs__meta">
				<dt><?php echo esc_html( $s( 'consent_controller' ) ); ?></dt>
				<dd><?php echo esc_html( (string) Settings::get( 'general.company_name', get_bloginfo( 'name' ) ) ); ?></dd>
				<?php if ( $contact['email'] ) : ?>
					<dt><?php echo esc_html( $s( 'consent_contact' ) ); ?></dt>
					<dd><?php echo esc_html( $contact['name'] ? $contact['name'] . ' · ' : '' ); ?><a href="mailto:<?php echo esc_attr( antispambot( $contact['email'] ) ); ?>"><?php echo esc_html( antispambot( $contact['email'] ) ); ?></a></dd>
				<?php endif; ?>
				<dt><?php echo esc_html( $s( 'consent_id_label' ) ); ?></dt>
				<dd data-consent-record data-empty="<?php echo esc_attr( $s( 'consent_none' ) ); ?>"><?php echo esc_html( $s( 'consent_none' ) ); ?></dd>
			</dl>
			<?php if ( $policy ) : ?>
				<p><a href="<?php echo esc_url( $policy ); ?>"><?php echo esc_html( $s( 'consent_policy' ) ); ?></a></p>
			<?php endif; ?>
		</div>
		<footer class="rolck-consent-prefs__actions">
			<?php if ( $used ) : ?>
				<button type="button" class="rolck-consent__btn" data-consent="reject"><?php echo esc_html( $s( 'consent_reject' ) ); ?></button>
				<button type="button" class="rolck-consent__btn" data-consent="save"><?php echo esc_html( $s( 'consent_save' ) ); ?></button>
				<button type="button" class="rolck-consent__btn" data-consent="accept"><?php echo esc_html( $s( 'consent_accept' ) ); ?></button>
			<?php else : ?>
				<button type="button" class="rolck-consent__btn" data-consent="accept"><?php echo esc_html( $s( 'consent_ok' ) ); ?></button>
			<?php endif; ?>
		</footer>
	</form>
</dialog>
<p class="rolck-consent-status" role="status" data-saved="<?php echo esc_attr( $s( 'consent_saved' ) ); ?>"></p>
		<?php
	}

	/**
	 * Esconde o aviso antes da primeira pintura quando já há uma escolha válida
	 * (a mesma regra de isValid() em src/consent/index.js).
	 */
	private static function early_check(): string {
		return '(function(){try{var c=JSON.parse(document.getElementById("rolck-consent-config").textContent),'
			. 'm=document.cookie.match(/(?:^|; )' . self::COOKIE . '=([^;]*)/),s=m&&JSON.parse(decodeURIComponent(m[1]));'
			. 'if(s&&s.v===c.version&&Date.now()-s.t<c.days*864e5&&c.categories.every(function(x){return(s.o||[]).indexOf(x)>-1}))'
			. 'document.getElementById("rolck-consent").hidden=true}catch(e){}})();';
	}

	/**
	 * Tabela de cookies (nome, quem define, finalidade, duração).
	 *
	 * @param array<int,array<string,string>> $rows Cookies.
	 */
	public static function table( array $rows ): void {
		?>
		<div class="rolck-cookie-table">
			<table>
				<thead>
					<tr>
						<th scope="col"><?php echo esc_html( Strings::get( 'consent_col_name' ) ); ?></th>
						<th scope="col"><?php echo esc_html( Strings::get( 'consent_col_provider' ) ); ?></th>
						<th scope="col"><?php echo esc_html( Strings::get( 'consent_col_purpose' ) ); ?></th>
						<th scope="col"><?php echo esc_html( Strings::get( 'consent_col_duration' ) ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $rows as $row ) : ?>
						<tr>
							<td><code><?php echo esc_html( $row['name'] ); ?></code></td>
							<td><?php echo esc_html( $row['provider'] ); ?></td>
							<td><?php echo esc_html( $row['purpose'] ); ?></td>
							<td><?php echo esc_html( $row['duration'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/** POST /rolck/v1/consent — guarda a prova de cada escolha. */
	public static function routes(): void {
		register_rest_route(
			'rolck/v1',
			'/consent',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => array( self::class, 'store' ),
				'args'                => array(
					'id'         => array(
						'type'     => 'string',
						'required' => true,
						'pattern'  => '^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$',
					),
					'action'     => array(
						'type'     => 'string',
						'required' => true,
						'enum'     => ConsentLog::ACTIONS,
					),
					'granted'    => array(
						'type'    => 'array',
						'items'   => array(
							'type' => 'string',
							'enum' => self::OPTIONAL,
						),
						'default' => array(),
					),
					'offered'    => array(
						'type'    => 'array',
						'items'   => array(
							'type' => 'string',
							'enum' => self::OPTIONAL,
						),
						'default' => array(),
					),
					'version'    => array(
						'type'      => 'string',
						'maxLength' => 20,
						'default'   => '',
					),
					'lang'       => array(
						'type'      => 'string',
						'maxLength' => 5,
						'default'   => '',
					),
					'page'       => array(
						'type'      => 'string',
						'maxLength' => 255,
						'default'   => '',
					),
				),
			)
		);
	}

	/**
	 * @param \WP_REST_Request $request Pedido.
	 */
	public static function store( \WP_REST_Request $request ): \WP_REST_Response {
		if ( ! (bool) Settings::get( 'privacy.log', true ) ) {
			return new \WP_REST_Response( array( 'stored' => false ), 200 );
		}
		$ip_hash = ConsentLog::ip_hash();
		$key     = 'rolck_consent_rate_' . substr( $ip_hash, 0, 20 );
		$count   = (int) get_transient( $key );
		if ( $count >= 30 ) {
			return new \WP_REST_Response( array( 'stored' => false ), 429 );
		}
		set_transient( $key, $count + 1, 10 * MINUTE_IN_SECONDS );

		$offered = array_values( array_intersect( self::OPTIONAL, (array) $request['offered'] ) );
		$granted = array_values( array_intersect( $offered, (array) $request['granted'] ) );
		$stored  = ConsentLog::insert(
			array(
				'consent_id' => (string) $request['id'],
				'action'     => (string) $request['action'],
				'granted'    => $granted,
				'offered'    => $offered,
				'version'    => sanitize_text_field( (string) $request['version'] ),
				'lang'       => sanitize_key( (string) $request['lang'] ),
				'page'       => esc_url_raw( (string) $request['page'] ),
				'ip_hash'    => $ip_hash,
			)
		);
		return new \WP_REST_Response( array( 'stored' => $stored ), $stored ? 201 : 500 );
	}
}
