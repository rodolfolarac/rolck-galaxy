<?php
/**
 * Rolck Galaxy — funções do tema.
 *
 * O tema é só apresentação: modelos, partes, padrões, theme.json e CSS.
 * Funcionalidade (idiomas, SEO, projetos, blocos) fica no plugin Rolck Galaxy Core.
 *
 * @package RolckGalaxy
 */

defined( 'ABSPATH' ) || exit;

define( 'ROLCK_THEME_VERSION', wp_get_theme( 'rolck-galaxy' )->get( 'Version' ) ?: '1.0.0' );

/**
 * Texto no idioma da página, usado pelos padrões do tema.
 * Sem o plugin, fica sempre em português.
 *
 * @param string $pt Texto em português.
 * @param string $en Texto em inglês.
 */
function rolck_theme_t( string $pt, string $en ): string {
	return function_exists( 'rolck_lang' ) && 'en' === rolck_lang() ? $en : $pt;
}

/**
 * Texto da interface (Rolck Galaxy → Textos do site), com reserva sem o plugin.
 *
 * @param string $key      Chave do texto.
 * @param string $fallback Texto se o plugin estiver desligado.
 */
function rolck_theme_string( string $key, string $fallback = '' ): string {
	return function_exists( 'rolck_t' ) ? rolck_t( $key ) : $fallback;
}

/**
 * URL interna no idioma da página: rolck_theme_url( '/contato/', '/contact/' ).
 *
 * @param string $pt Caminho em português.
 * @param string $en Caminho em inglês (sem o prefixo /en).
 */
function rolck_theme_url( string $pt, string $en ): string {
	return home_url( rolck_theme_t( $pt, $en ) );
}

add_action(
	'after_setup_theme',
	static function (): void {
		add_theme_support( 'editor-styles' );
		add_editor_style( 'assets/css/theme.css' );
		remove_theme_support( 'core-block-patterns' );
		// Resumo nas páginas: aparece no topo da página e vira a descrição do Google.
		add_post_type_support( 'page', 'excerpt' );
	}
);

// Padrões remotos do wordpress.org: requisição extra no editor e nada a ver com o visual do site.
add_filter( 'should_load_remote_block_patterns', '__return_false' );

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		// O .min é gerado pelo `npm run build`; com SCRIPT_DEBUG ligado, carrega o original.
		$name = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) || ! file_exists( get_theme_file_path( 'assets/css/theme.min.css' ) ) ? 'theme.css' : 'theme.min.css';
		$file = get_theme_file_path( "assets/css/$name" );
		wp_enqueue_style( 'rolck-galaxy', get_theme_file_uri( "assets/css/$name" ), array(), (string) filemtime( $file ) );
		// Com "path", o WordPress pode embutir o CSS no HTML (uma requisição a menos).
		wp_style_add_data( 'rolck-galaxy', 'path', $file );
	}
);

// Pré-carrega a fonte: o texto aparece com a fonte certa já na primeira pintura.
add_action(
	'wp_head',
	static function (): void {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( get_theme_file_uri( 'assets/fonts/inter-latin.woff2' ) )
		);
	},
	2
);

add_action(
	'init',
	static function (): void {
		register_block_pattern_category( 'rolck-galaxy', array( 'label' => 'Rolck Galaxy' ) );
		register_block_pattern_category( 'rolck-galaxy-pages', array( 'label' => 'Rolck Galaxy — páginas' ) );

		// Variações de estilo dos blocos nativos (aparecem em "Estilos" na barra lateral do bloco).
		$styles = array(
			'core/group'     => array(
				'glass'           => 'Cartão de vidro',
				'gradient-border' => 'Borda em gradiente',
				'section'         => 'Seção com respiro',
			),
			'core/column'    => array( 'glass' => 'Cartão de vidro' ),
			'core/heading'   => array( 'gradient-text' => 'Texto em gradiente', 'eyebrow' => 'Chamada (sobretítulo)' ),
			'core/paragraph' => array( 'eyebrow' => 'Chamada (sobretítulo)', 'lead' => 'Parágrafo de abertura' ),
			'core/button'    => array( 'ghost' => 'Contorno' ),
			'core/list'      => array( 'check' => 'Lista com check' ),
			'core/image'     => array( 'glow-frame' => 'Moldura com brilho' ),
			'core/separator' => array( 'fade' => 'Linha esmaecida' ),
			'core/details'   => array( 'faq' => 'Pergunta frequente' ),
		);
		foreach ( $styles as $block => $variations ) {
			foreach ( $variations as $name => $label ) {
				register_block_style(
					$block,
					array(
						'name'  => $name,
						'label' => $label,
					)
				);
			}
		}
	}
);
