<?php
/**
 * Google Analytics 4 (opcional) e código extra no <head>.
 *
 * Com "carregar na primeira interação" ligado, o script do Google só baixa
 * quando o visitante rola, toca ou move o mouse — ele não entra na medição
 * do PageSpeed e não atrasa a primeira pintura.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\Integrations;

use RolckGalaxy\Core\Settings;

defined( 'ABSPATH' ) || exit;

final class Analytics {

	public static function register(): void {
		if ( ! is_admin() ) {
			add_action( 'wp_head', array( self::class, 'head_code' ), 50 );
			add_action( 'wp_footer', array( self::class, 'ga4' ), 50 );
		}
	}

	public static function head_code(): void {
		$code = (string) Settings::get( 'integrations.head_code', '' );
		if ( '' !== trim( $code ) ) {
			echo $code . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- código colado por administrador (unfiltered_html).
		}
	}

	public static function ga4(): void {
		$id = (string) Settings::get( 'integrations.ga4_id', '' );
		if ( ! preg_match( '/^G-[A-Z0-9]+$/', $id ) || is_user_logged_in() ) {
			return;
		}
		$lazy = (bool) Settings::get( 'integrations.ga4_on_input', true );
		?>
<script>
(function(){
	var id = <?php echo wp_json_encode( $id ); ?>, done = false;
	window.dataLayer = window.dataLayer || [];
	window.gtag = function(){ dataLayer.push(arguments); };
	gtag('js', new Date()); gtag('config', id);
	function load(){
		if (done) return; done = true;
		var s = document.createElement('script'); s.async = true;
		s.src = 'https://www.googletagmanager.com/gtag/js?id=' + id;
		document.head.appendChild(s);
	}
	<?php if ( $lazy ) : ?>
	['scroll','pointerdown','keydown','mousemove','touchstart'].forEach(function(e){ addEventListener(e, load, { once: true, passive: true }); });
	<?php else : ?>
	load();
	<?php endif; ?>
})();
</script>
		<?php
	}
}
