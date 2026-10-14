<?php
/**
 * Plugin Name:       Rolck Galaxy — Conteúdo inicial
 * Description:       Uso único: cria páginas, projetos, menus, imagens e configurações do site Rolck Galaxy num WordPress limpo. Depois de importar, desative e exclua.
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      8.1
 * Author:            Rolck Galaxy
 * License:           GPL-2.0-or-later
 * Text Domain:       rolck-galaxy-setup
 *
 * Para servidores sem SSH/WP-CLI. Faz o mesmo que `wp eval-file seed.php`,
 * só que por um botão em Ferramentas → Conteúdo Rolck Galaxy.
 *
 * @package RolckGalaxy
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'admin_menu',
	static function (): void {
		add_management_page( 'Conteúdo Rolck Galaxy', 'Conteúdo Rolck Galaxy', 'manage_options', 'rolck-galaxy-setup', 'rolck_setup_page' );
	}
);

add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	static function ( array $links ): array {
		array_unshift( $links, sprintf( '<a href="%s">Importar conteúdo</a>', esc_url( admin_url( 'tools.php?page=rolck-galaxy-setup' ) ) ) );
		return $links;
	}
);

/** O que precisa estar pronto antes de importar. */
function rolck_setup_missing(): array {
	$missing = array();
	if ( 'rolck-galaxy' !== get_stylesheet() ) {
		$missing[] = 'Ative o tema Rolck Galaxy em Aparência → Temas.';
	}
	if ( ! class_exists( 'RolckGalaxy\Core\Settings' ) ) {
		$missing[] = 'Ative o plugin Rolck Galaxy Core em Plugins.';
	}
	return $missing;
}

function rolck_setup_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$missing = rolck_setup_missing();
	$log     = '';
	$ran     = false;

	if ( ! $missing && isset( $_POST['rolck_setup'] ) && check_admin_referer( 'rolck_setup' ) ) {
		wp_raise_memory_limit( 'admin' );
		if ( function_exists( 'set_time_limit' ) ) {
			set_time_limit( 300 ); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged -- gerar as imagens pode passar dos 30 s padrão.
		}
		ob_start();
		include __DIR__ . '/seed.php';
		$log = (string) ob_get_clean();
		$ran = true;
		update_option( 'rolck_setup_done', time() );
	}
	?>
	<div class="wrap">
		<h1>Conteúdo inicial do site Rolck Galaxy</h1>
		<?php if ( $ran ) : ?>
			<div class="notice notice-success"><p><strong>Conteúdo importado.</strong> <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank">Ver o site ↗</a></p></div>
			<pre style="background:#fff;border:1px solid #c3c4c7;padding:12px;max-width:800px;white-space:pre-wrap"><?php echo esc_html( $log ); ?></pre>
			<p>Agora vá em <a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>">Plugins</a>, desative e exclua <strong>Rolck Galaxy — Conteúdo inicial</strong>. Ele não é mais necessário.</p>
		<?php elseif ( $missing ) : ?>
			<div class="notice notice-warning"><p>Antes de importar:</p><ul style="list-style:disc;margin-left:20px">
				<?php foreach ( $missing as $item ) : ?>
					<li><?php echo esc_html( $item ); ?></li>
				<?php endforeach; ?>
			</ul></div>
		<?php else : ?>
			<p style="max-width:700px">Cria nos dois idiomas: páginas (Início, Serviços, Sobre, Contato, Blog, Privacidade), os 3 projetos com capas, categorias e tecnologias, 1 artigo, logo, ícone, imagem de compartilhamento, configurações, página inicial e links permanentes.</p>
			<p style="max-width:700px">Pode rodar mais de uma vez: o que já existe é mantido. Leva de alguns segundos a 1 minuto.</p>
			<?php if ( get_option( 'rolck_setup_done' ) ) : ?>
				<p><em>O conteúdo já foi importado uma vez neste site.</em></p>
			<?php endif; ?>
			<form method="post">
				<?php wp_nonce_field( 'rolck_setup' ); ?>
				<?php submit_button( 'Importar conteúdo', 'primary', 'rolck_setup' ); ?>
			</form>
		<?php endif; ?>
	</div>
	<?php
}
