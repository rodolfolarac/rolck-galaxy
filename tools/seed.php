<?php
/**
 * Conteúdo inicial do site, em português e inglês, já ligado entre os idiomas.
 *
 * Roda sozinho na primeira vez do `npm run dev`. Num servidor com WP-CLI:
 *   wp eval-file tools/seed.php
 *
 * Cria: configurações, logo, ícone do site, categorias e tecnologias (com
 * nome em inglês), 3 projetos, as páginas (Início, Serviços, Sobre, Contato,
 * Blog, Privacidade), 1 artigo e define a página inicial e a do blog.
 * Pode rodar de novo: o que já existe (mesmo slug) é mantido.
 *
 * @package RolckGalaxy
 */

use RolckGalaxy\Core\Content\Projects;
use RolckGalaxy\Core\I18n\Languages;
use RolckGalaxy\Core\I18n\Router;
use RolckGalaxy\Core\I18n\Translations;
use RolckGalaxy\Core\Settings;

defined( 'ABSPATH' ) || exit;

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

if ( ! class_exists( Settings::class ) ) {
	echo "Ative o plugin Rolck Galaxy Core antes de rodar o seed.\n";
	return;
}

const ROLCK_SEED_IMAGES = __DIR__ . '/demo/images/';

/** Mensagem no terminal. */
function rolck_seed_log( string $message ): void {
	echo '  · ' . $message . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/** Envia uma imagem da pasta demo/images para a biblioteca de mídia (uma vez só). */
function rolck_seed_image( string $file, string $alt ): int {
	$existing = get_posts(
		array(
			'post_type'   => 'attachment',
			'meta_key'    => '_rolck_seed_file', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'  => $file, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'fields'      => 'ids',
			'numberposts' => 1,
		)
	);
	if ( $existing ) {
		return (int) $existing[0];
	}
	$tmp = wp_tempnam( $file );
	copy( ROLCK_SEED_IMAGES . $file, $tmp );
	$id = media_handle_sideload(
		array(
			'name'     => $file,
			'tmp_name' => $tmp,
		),
		0,
		$alt
	);
	if ( is_wp_error( $id ) ) {
		rolck_seed_log( 'Imagem ' . $file . ': ' . $id->get_error_message() );
		return 0;
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	update_post_meta( $id, '_rolck_seed_file', $file );
	return (int) $id;
}

/** Troca o idioma "atual" durante o seed (links e textos dos padrões seguem). */
function rolck_seed_lang( string $lang ): void {
	Languages::set_current( $lang );
}

/** Conteúdo de um padrão do tema, renderizado no idioma atual. */
function rolck_seed_pattern( string $slug ): string {
	ob_start();
	include get_theme_file_path( "patterns/$slug.php" );
	return trim( (string) ob_get_clean() );
}

/** Topo de página interna com textos próprios. */
function rolck_seed_page_hero( string $eyebrow, string $title, string $lead ): string {
	return '<!-- wp:group {"tagName":"section","align":"full","className":"rolck-page-header","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull rolck-page-header"><!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide"><!-- wp:rolck/breadcrumbs /-->

<!-- wp:paragraph {"className":"is-style-eyebrow"} -->
<p class="is-style-eyebrow">' . esc_html( $eyebrow ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">' . esc_html( $title ) . '</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"is-style-lead"} -->
<p class="is-style-lead">' . esc_html( $lead ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->';
}

/** Parágrafos e listas simples em blocos. */
function rolck_seed_blocks( array $items ): string {
	$out = array();
	foreach ( $items as $item ) {
		[ $type, $value ] = $item;
		if ( 'p' === $type ) {
			$out[] = "<!-- wp:paragraph -->\n<p>" . wp_kses_post( $value ) . "</p>\n<!-- /wp:paragraph -->";
		} elseif ( 'h2' === $type ) {
			$out[] = "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">" . esc_html( $value ) . "</h2>\n<!-- /wp:heading -->";
		} elseif ( 'ul' === $type ) {
			$lis = array_map( static fn( $li ) => "<!-- wp:list-item -->\n<li>" . wp_kses_post( $li ) . "</li>\n<!-- /wp:list-item -->", $value );
			$out[] = "<!-- wp:list {\"className\":\"is-style-check\"} -->\n<ul class=\"wp-block-list is-style-check\">" . implode( "\n\n", $lis ) . "</ul>\n<!-- /wp:list -->";
		}
	}
	return implode( "\n\n", $out );
}

/**
 * Cria (ou reaproveita) um conteúdo num idioma. O idioma é gravado antes de
 * publicar, para o slug poder ser igual ao da outra versão.
 */
function rolck_seed_post( array $args, string $lang ): int {
	$found = get_posts(
		array(
			'post_type'        => $args['post_type'],
			'name'             => $args['post_name'],
			'post_status'      => 'any',
			'numberposts'      => 5,
			'suppress_filters' => true,
			'lang'             => 'all',
		)
	);
	foreach ( $found as $post ) {
		if ( Translations::language_of( $post ) === $lang ) {
			return $post->ID;
		}
	}

	$meta = $args['meta'] ?? array();
	$id   = wp_insert_post(
		array(
			'post_type'     => $args['post_type'],
			'post_status'   => 'draft',
			'post_title'    => $args['post_title'],
			'post_content'  => $args['post_content'] ?? '',
			'post_excerpt'  => $args['post_excerpt'] ?? '',
			'menu_order'    => $args['menu_order'] ?? 0,
			'page_template' => $args['page_template'] ?? '',
			'post_author'   => 1,
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		rolck_seed_log( 'Erro: ' . $id->get_error_message() );
		return 0;
	}
	Translations::set_language( $id, $lang );
	wp_update_post(
		array(
			'ID'          => $id,
			'post_status' => 'publish',
			'post_name'   => $args['post_name'],
			'post_date'   => $args['post_date'] ?? current_time( 'mysql' ),
		)
	);
	foreach ( $meta as $key => $value ) {
		update_post_meta( $id, $key, $value );
	}
	foreach ( $args['terms'] ?? array() as $taxonomy => $terms ) {
		wp_set_object_terms( $id, $terms, $taxonomy );
	}
	if ( ! empty( $args['thumbnail'] ) ) {
		set_post_thumbnail( $id, $args['thumbnail'] );
	}
	return (int) $id;
}

/** Cria as duas versões e liga uma à outra. */
function rolck_seed_pair( array|Closure $pt, array|Closure $en, array $shared = array() ): array {
	rolck_seed_lang( 'pt' );
	$pt_id = rolck_seed_post( $shared + ( $pt instanceof Closure ? $pt() : $pt ), 'pt' );
	rolck_seed_lang( 'en' );
	$en_id = rolck_seed_post( $shared + ( $en instanceof Closure ? $en() : $en ), 'en' );
	rolck_seed_lang( 'pt' );
	if ( $pt_id && $en_id ) {
		Translations::link( $pt_id, $en_id );
	}
	return array( $pt_id, $en_id );
}

// Links montados durante o seed seguem o idioma atual (/en/… em inglês).
add_filter( 'home_url', array( Router::class, 'home_url' ), 10, 3 );

echo "Rolck Galaxy — criando o conteúdo de demonstração\n";

// ── 1. Básico do WordPress ───────────────────────────────────────────────
update_option( 'blogname', 'Rolck Galaxy' );
update_option( 'blogdescription', 'Temas WordPress, plugins e produtos digitais feitos para performar.' );
global $wp_rewrite;
$wp_rewrite->set_permalink_structure( '/%postname%/' );
update_option( 'timezone_string', 'America/Sao_Paulo' );
update_option( 'date_format', 'j \d\e F \d\e Y' );
update_option( 'WPLANG', 'pt_BR' );
update_option( 'blog_public', 1 );
update_option( 'posts_per_page', 9 );
update_option( 'default_comment_status', 'closed' );
rolck_seed_log( 'Opções básicas, links permanentes e idioma do painel (pt_BR)' );

// ── 2. Imagens ───────────────────────────────────────────────────────────
$rolck_logo    = rolck_seed_image( 'logo.png', 'Rolck Galaxy' );
$rolck_og      = rolck_seed_image( 'og-default.png', 'Rolck Galaxy — temas, plugins e produtos digitais' );
$rolck_academy = rolck_seed_image( 'cover-galaxy-academy.png', 'Página inicial do tema Galaxy Academy' );
$rolck_cards   = rolck_seed_image( 'cover-galaxy-cards.png', 'Tela de estudo do Galaxy Cards com a carta “reliable”' );
$rolck_health  = rolck_seed_image( 'cover-galaxy-health.png', 'Tela de treino do Galaxy Health com exercícios e timer' );
if ( $rolck_logo ) {
	set_theme_mod( 'custom_logo', $rolck_logo );
	update_option( 'site_icon', $rolck_logo );
}
rolck_seed_log( 'Logo, ícone do site, imagem de compartilhamento e capas' );

// ── 3. Configurações do painel Rolck Galaxy ──────────────────────────────
$rolck_settings                                 = Settings::all();
$rolck_settings['general']['logo_id']           = $rolck_logo;
$rolck_settings['general']['social']['github']  = 'https://github.com/rodolfolarac';
$rolck_settings['seo']['default_image_id']      = $rolck_og;
update_option( Settings::OPTION, $rolck_settings );
rolck_seed_log( 'Configurações (Rolck Galaxy → Configurações)' );

// ── 4. Categorias e tecnologias, com nome em inglês ─────────────────────
$rolck_terms = array(
	Projects::TYPE_TAX => array(
		'wordpress-themes' => array( 'Temas WordPress', 'WordPress themes', 'Temas premium prontos para usar, com demo ao vivo.', 'Premium, ready-to-use themes with a live demo.' ),
		'web-apps'         => array( 'Aplicativos web', 'Web apps', 'Aplicativos completos com React, Node.js e banco de dados.', 'Full apps built with React, Node.js and a database.' ),
		'plugins'          => array( 'Plugins', 'Plugins', 'Plugins WordPress nativos e leves.', 'Native, lightweight WordPress plugins.' ),
	),
	Projects::TECH_TAX => array(
		'wordpress'   => array( 'WordPress', 'WordPress' ),
		'php'         => array( 'PHP', 'PHP' ),
		'woocommerce' => array( 'WooCommerce', 'WooCommerce' ),
		'javascript'  => array( 'JavaScript', 'JavaScript' ),
		'react'       => array( 'React', 'React' ),
		'typescript'  => array( 'TypeScript', 'TypeScript' ),
		'nodejs'      => array( 'Node.js', 'Node.js' ),
		'postgresql'  => array( 'PostgreSQL', 'PostgreSQL' ),
		'tailwind'    => array( 'Tailwind CSS', 'Tailwind CSS' ),
	),
);
foreach ( $rolck_terms as $rolck_tax => $rolck_list ) {
	foreach ( $rolck_list as $rolck_slug => $rolck_t ) {
		$rolck_term = term_exists( $rolck_slug, $rolck_tax );
		if ( ! $rolck_term ) {
			$rolck_term = wp_insert_term( $rolck_t[0], $rolck_tax, array( 'slug' => $rolck_slug, 'description' => $rolck_t[2] ?? '' ) );
		}
		if ( ! is_wp_error( $rolck_term ) ) {
			update_term_meta( (int) $rolck_term['term_id'], 'rolck_name_en', $rolck_t[1] );
			if ( isset( $rolck_t[3] ) ) {
				update_term_meta( (int) $rolck_term['term_id'], 'rolck_description_en', $rolck_t[3] );
			}
		}
	}
}
rolck_seed_log( 'Categorias de projeto e tecnologias (com nome em inglês)' );

// ── 5. Projetos ──────────────────────────────────────────────────────────
$rolck_projects = array(
	array(
		'slug'   => 'galaxy-academy',
		'order'  => 1,
		'thumb'  => $rolck_academy,
		'terms'  => array(
			Projects::TYPE_TAX => array( 'wordpress-themes' ),
			Projects::TECH_TAX => array( 'wordpress', 'php', 'woocommerce', 'javascript' ),
		),
		'meta'   => array(
			'rolck_status'   => 'soon',
			'rolck_featured' => true,
			'rolck_version'  => '1.1.0',
			'rolck_compat'   => 'WordPress 6.4+, PHP 7.4+, WooCommerce',
			'rolck_accent'   => '#8b5cf6',
		),
		'pt'     => array(
			'title'      => 'Galaxy Academy',
			'tagline'    => 'Tema WordPress para plataformas de cursos em vídeo, com venda pelo WooCommerce.',
			'highlights' => array( 'Cursos, módulos e aulas com playlist e progresso', 'Venda pelo WooCommerce: avulso, pacotes e passe', 'Certificados, conquistas e perfil do aluno', 'Cores, fontes e seções da home sem código', 'Tradução pt_BR inclusa e compatível com idiomas RTL' ),
			'content'    => array(
				array( 'p', 'Galaxy Academy transforma o seu WordPress numa plataforma de cursos em vídeo completa. Seus alunos se cadastram, compram, assistem às aulas em sequência e acompanham o próprio progresso, e você vende pelo WooCommerce, sem pagar mensalidade de plataforma.' ),
				array( 'h2', 'O que vem no pacote' ),
				array( 'ul', array( 'Cursos → módulos → aulas, com aulas liberadas em sequência', 'Venda pelo WooCommerce: curso avulso, pacotes, passe para todos os cursos e acesso com prazo', 'Reembolso remove o acesso automaticamente', 'Certificados, conquistas e perfil do aluno', 'Cores, fontes, logo, cabeçalho, rodapé e ordem das seções da home sem código', 'Tema filho e tradução para o português inclusos' ) ),
				array( 'h2', 'Para quem é' ),
				array( 'ul', array( 'Professores e especialistas que querem vender cursos com a própria marca', 'Escolas e empresas que precisam de uma área de alunos com certificado', 'Agências que criam plataformas de cursos para clientes' ) ),
			),
		),
		'en'     => array(
			'title'      => 'Galaxy Academy',
			'tagline'    => 'WordPress theme for video course platforms, selling through WooCommerce.',
			'highlights' => array( 'Courses, modules and lessons with playlist and progress', 'WooCommerce sales: single courses, bundles and all-access pass', 'Certificates, achievements and student profile', 'Colors, fonts and home sections without code', 'Translation-ready with RTL support' ),
			'content'    => array(
				array( 'p', 'Galaxy Academy turns your WordPress site into a complete video course platform. Students sign up, buy, watch lessons in sequence and track their own progress, while you sell through WooCommerce with no platform subscription fees.' ),
				array( 'h2', 'What’s in the package' ),
				array( 'ul', array( 'Courses → modules → lessons, with lessons unlocked in sequence', 'WooCommerce sales: single courses, bundles, an all-courses pass and time-limited access', 'Refunds remove access automatically', 'Certificates, achievements and student profile', 'Colors, fonts, logo, header, footer and home section order without code', 'Child theme and translation files included' ) ),
				array( 'h2', 'Who it’s for' ),
				array( 'ul', array( 'Teachers and experts who want to sell courses under their own brand', 'Schools and companies that need a student area with certificates', 'Agencies building course platforms for clients' ) ),
			),
		),
	),
	array(
		'slug'  => 'galaxy-cards',
		'order' => 2,
		'thumb' => $rolck_cards,
		'terms' => array(
			Projects::TYPE_TAX => array( 'web-apps' ),
			Projects::TECH_TAX => array( 'react', 'typescript', 'nodejs', 'postgresql', 'tailwind' ),
		),
		'meta'  => array(
			'rolck_status'   => 'development',
			'rolck_version'  => '1.0.0',
			'rolck_compat'   => 'Navegadores modernos, desktop e celular',
			'rolck_accent'   => '#22d3ee',
			'rolck_demo_url' => 'https://galaxy-cards.vercel.app/?demo',
		),
		'pt'    => array(
			'title'      => 'Galaxy Cards',
			'tagline'    => 'Baralho de vocabulário em inglês com repetição espaçada e áudio nativo.',
			'highlights' => array( 'Repetição espaçada em degraus de 15 dias', 'Áudio com vozes neurais, guardado em cache', 'Importação de dezenas de palavras de uma vez', 'Progresso de hoje, 7 e 30 dias', 'Acesso protegido por código (bcrypt + JWT)' ),
			'content'    => array(
				array( 'p', 'Galaxy Cards é um baralho pessoal para memorizar vocabulário em inglês. Cada carta tem a palavra, a tradução e uma frase de exemplo, falada em voz nativa. O progresso fica num banco de dados, não no navegador.' ),
				array( 'p', 'Na demo ao vivo você testa sem código de acesso: ela já vem com palavras de exemplo e guarda tudo só no seu navegador.' ),
				array( 'h2', 'Como funciona' ),
				array( 'ul', array( 'Você escolhe 10, 20, 30, 40, 50 ou todas as cartas, sempre em ordem sorteada', 'Cada “fácil” seguido afasta a carta mais 15 dias; no quarto acerto ela vira permanente', '“Difícil” recomeça a escada e a carta volta no próximo baralho', 'Cada resposta grava na hora, e o cronômetro é salvo a cada 15 segundos', 'Aba de progresso com gráfico diário dos últimos 30 dias' ) ),
			),
		),
		'en'    => array(
			'title'      => 'Galaxy Cards',
			'tagline'    => 'English vocabulary deck with spaced repetition and native audio.',
			'highlights' => array( 'Spaced repetition in 15-day steps', 'Neural voice audio, cached after the first play', 'Import dozens of words at once', 'Progress for today, 7 and 30 days', 'Passcode-protected access (bcrypt + JWT)' ),
			'content'    => array(
				array( 'p', 'Galaxy Cards is a personal deck for memorizing English vocabulary. Each card has the word, its translation and an example sentence spoken by a native voice. Progress lives in a database, not in the browser.' ),
				array( 'p', 'The live demo needs no passcode: it comes with sample words and keeps everything in your own browser.' ),
				array( 'h2', 'How it works' ),
				array( 'ul', array( 'Pick 10, 20, 30, 40, 50 or all cards, always shuffled', 'Each “easy” in a row pushes the card 15 more days away; on the fourth it becomes permanent', '“Hard” restarts the ladder and the card returns in the next deck', 'Every answer is saved instantly, and the timer is saved every 15 seconds', 'Progress tab with a daily chart for the last 30 days' ) ),
			),
		),
	),
	array(
		'slug'  => 'galaxy-health',
		'order' => 3,
		'thumb' => $rolck_health,
		'terms' => array(
			Projects::TYPE_TAX => array( 'web-apps' ),
			Projects::TECH_TAX => array( 'react', 'typescript', 'nodejs', 'postgresql', 'tailwind' ),
		),
		'meta'  => array(
			'rolck_status'  => 'development',
			'rolck_version' => '1.0.0',
			'rolck_compat'  => 'Navegadores modernos, desktop e celular',
			'rolck_accent'  => '#22c55e',
		),
		'pt'    => array(
			'title'      => 'Galaxy Health',
			'tagline'    => 'Ficha de treino para quem quer controlar mais a sua rotina de exercícios físicos.',
			'highlights' => array( 'Carga estimada a partir dos elásticos e do ajuste', 'Timer de descanso e de isometria', 'Recordes e sugestão de progressão', 'Comparativo com a última vez do mesmo treino', 'Programa por dias da semana ou sequência livre' ),
			'content'    => array(
				array( 'p', 'Galaxy Health é uma ficha de treino pessoal pensada para elásticos (superband). Como elástico não tem peso exato, a carga de cada série combina as cores dos elásticos, o ajuste descrito em texto livre e um percentual opcional, gerando uma régua consistente para comparar uma semana com a outra.' ),
				array( 'h2', 'Destaques' ),
				array( 'ul', array( 'Exercícios de força, alongamento, mobilidade, peso do corpo e cardio', 'Treinos A, B, C… com séries, faixa de repetições, descanso e bi-sets', 'Sessão com “última vez” em destaque e carga pré-preenchida', 'Aviso de recorde e sugestão de subir o elástico', 'Alongamento rápido registrado com um toque' ) ),
			),
		),
		'en'    => array(
			'title'      => 'Galaxy Health',
			'tagline'    => 'Workout log for anyone who wants more control over their exercise routine.',
			'highlights' => array( 'Estimated load from bands and adjustment', 'Rest and isometric timers', 'Personal records and progression tips', 'Side-by-side with the last time you did the same workout', 'Weekly schedule or free sequence program' ),
			'content'    => array(
				array( 'p', 'Galaxy Health is a personal workout log built for resistance bands. Since bands have no exact weight, each set’s load combines the band colors, a free-text adjustment and an optional percentage, giving you a consistent scale to compare one week with the next.' ),
				array( 'h2', 'Highlights' ),
				array( 'ul', array( 'Strength, stretching, mobility, bodyweight and cardio exercises', 'Workouts A, B, C… with sets, rep ranges, rest and supersets', 'Sessions show “last time” up front with the load pre-filled', 'Record alerts and a suggestion to move up a band', 'Quick stretch logged with a single tap' ) ),
			),
		),
	),
);

foreach ( $rolck_projects as $rolck_p ) {
	$rolck_build = static function ( string $lang ) use ( $rolck_p ): array {
		$data = $rolck_p[ $lang ];
		$meta = $rolck_p['meta'] + array(
			'rolck_tagline'    => $data['tagline'],
			'rolck_highlights' => $data['highlights'],
		);
		if ( 'en' === $lang && isset( $meta['rolck_compat'] ) && str_starts_with( $meta['rolck_compat'], 'Navegadores' ) ) {
			$meta['rolck_compat'] = 'Modern browsers, desktop and mobile';
		}
		return array(
			'post_type'    => Projects::POST_TYPE,
			'post_name'    => $rolck_p['slug'],
			'post_title'   => $data['title'],
			'post_excerpt' => $data['tagline'],
			'post_content' => rolck_seed_blocks( $data['content'] ),
			'menu_order'   => $rolck_p['order'],
			'meta'         => $meta,
			'terms'        => $rolck_p['terms'],
			'thumbnail'    => $rolck_p['thumb'],
		);
	};
	rolck_seed_pair( static fn() => $rolck_build( 'pt' ), static fn() => $rolck_build( 'en' ) );
}
rolck_seed_log( 'Projetos: Galaxy Academy, Galaxy Cards e Galaxy Health (pt + en)' );

// ── 6. Páginas ───────────────────────────────────────────────────────────
$rolck_page = static function ( string $slug, string $title, string $excerpt, string $content, string $template = 'page-landing' ): array {
	return array(
		'post_type'     => 'page',
		'post_name'     => $slug,
		'post_title'    => $title,
		'post_excerpt'  => $excerpt,
		'post_content'  => $content,
		'page_template' => $template,
	);
};

[ $rolck_home_pt ] = rolck_seed_pair(
	static fn() => $rolck_page(
		'inicio',
		'Início',
		'Temas WordPress, plugins e produtos digitais de alta performance, com demo ao vivo de cada projeto.',
		implode( "\n\n", array_map( 'rolck_seed_pattern', array( 'hero', 'tech-stack', 'featured-project', 'projects', 'services', 'stats', 'process', 'about', 'faq', 'cta' ) ) )
	),
	static fn() => $rolck_page(
		'home',
		'Home',
		'High-performance WordPress themes, plugins and digital products, with a live demo of every project.',
		implode( "\n\n", array_map( 'rolck_seed_pattern', array( 'hero', 'tech-stack', 'featured-project', 'projects', 'services', 'stats', 'process', 'about', 'faq', 'cta' ) ) )
	)
);

rolck_seed_pair(
	static fn() => $rolck_page(
		'servicos',
		'Serviços',
		'Temas WordPress, plugins sob medida, performance, SEO técnico e sites multilíngues, sem plugins de terceiros.',
		rolck_seed_page_hero( 'Serviços', 'Tudo o que um site precisa para performar', 'Do tema ao topo do Google: criamos produtos e projetos WordPress rápidos, acessíveis e fáceis de editar.' ) . "\n\n" . implode( "\n\n", array_map( 'rolck_seed_pattern', array( 'services', 'process', 'tech-stack', 'faq', 'cta' ) ) )
	),
	static fn() => $rolck_page(
		'services',
		'Services',
		'WordPress themes, custom plugins, performance, technical SEO and multilingual sites, with no third-party plugins.',
		rolck_seed_page_hero( 'Services', 'Everything a website needs to perform', 'From theme to the top of Google: we build fast, accessible WordPress products and projects that are easy to edit.' ) . "\n\n" . implode( "\n\n", array_map( 'rolck_seed_pattern', array( 'services', 'process', 'tech-stack', 'faq', 'cta' ) ) )
	)
);

rolck_seed_pair(
	static fn() => $rolck_page(
		'sobre',
		'Sobre',
		'Conheça a Rolck Galaxy, estúdio brasileiro de temas WordPress e produtos digitais.',
		rolck_seed_page_hero( 'Sobre nós', 'Um estúdio brasileiro de produtos digitais', 'Criamos temas e plugins WordPress prontos para usar e projetos sob medida, com obsessão por performance e acessibilidade.' ) . "\n\n" . implode( "\n\n", array_map( 'rolck_seed_pattern', array( 'about', 'stats', 'process', 'tech-stack', 'cta' ) ) )
	),
	static fn() => $rolck_page(
		'about',
		'About',
		'Meet Rolck Galaxy, a Brazilian studio crafting WordPress themes and digital products.',
		rolck_seed_page_hero( 'About us', 'A Brazilian digital product studio', 'We build ready-to-use WordPress themes and plugins and custom projects, obsessed with performance and accessibility.' ) . "\n\n" . implode( "\n\n", array_map( 'rolck_seed_pattern', array( 'about', 'stats', 'process', 'tech-stack', 'cta' ) ) )
	)
);

rolck_seed_pair(
	static fn() => $rolck_page(
		'contato',
		'Contato',
		'Fale com a Rolck Galaxy: dúvidas sobre temas ou projetos sob medida.',
		rolck_seed_page_hero( 'Contato', 'Vamos conversar', 'Mande sua mensagem pelo formulário ou pelos nossos canais.' ) . "\n\n" . rolck_seed_pattern( 'contact' ) . "\n\n" . rolck_seed_pattern( 'faq' )
	),
	static fn() => $rolck_page(
		'contact',
		'Contact',
		'Talk to Rolck Galaxy: theme questions or custom projects.',
		rolck_seed_page_hero( 'Contact', 'Let’s talk', 'Send us a message through the form or our channels.' ) . "\n\n" . rolck_seed_pattern( 'contact' ) . "\n\n" . rolck_seed_pattern( 'faq' )
	)
);

[ $rolck_blog_pt ] = rolck_seed_pair(
	static fn() => $rolck_page( 'blog', 'Blog', 'Bastidores, tutoriais e novidades dos temas e produtos da Rolck Galaxy.', '', '' ),
	static fn() => $rolck_page( 'blog', 'Blog', 'Behind the scenes, tutorials and news about Rolck Galaxy themes and products.', '', '' )
);

[ $rolck_privacy_pt ] = rolck_seed_pair(
	static fn() => $rolck_page( 'politica-de-privacidade', 'Política de privacidade', 'Como a Rolck Galaxy trata dados pessoais e cookies, conforme a LGPD.', rolck_seed_pattern( 'privacy-policy' ), '' ),
	static fn() => $rolck_page( 'privacy-policy', 'Privacy policy', 'How Rolck Galaxy handles personal data and cookies under Brazil’s LGPD.', rolck_seed_pattern( 'privacy-policy' ), '' )
);
update_option( 'wp_page_for_privacy_policy', $rolck_privacy_pt );
rolck_seed_log( 'Páginas: Início, Serviços, Sobre, Contato, Blog e Privacidade (pt + en)' );

// ── 7. Artigo ────────────────────────────────────────────────────────────
$rolck_cat = term_exists( 'bastidores', 'category' ) ?: wp_insert_term( 'Bastidores', 'category', array( 'slug' => 'bastidores' ) );
if ( ! is_wp_error( $rolck_cat ) ) {
	update_term_meta( (int) $rolck_cat['term_id'], 'rolck_name_en', 'Behind the scenes' );
}
rolck_seed_pair(
	array(
		'post_type'    => 'post',
		'post_name'    => 'site-wordpress-sem-plugins',
		'post_title'   => 'Como este site funciona sem nenhum plugin de terceiros',
		'post_excerpt' => 'Idiomas, SEO, formulário e vitrine de projetos feitos com recursos nativos do WordPress: blocos, Interactivity API e REST API.',
		'post_content' => rolck_seed_blocks(
			array(
				array( 'p', 'Este site foi construído só com o que o WordPress já oferece: tema de blocos, theme.json, blocos próprios e a Interactivity API. Nada de construtor visual, plugin de SEO ou plugin de tradução.' ),
				array( 'h2', 'Dois idiomas sem plugin de tradução' ),
				array( 'p', 'Cada página tem uma versão em português e outra em inglês, ligadas entre si. O inglês fica em /en/, com tags hreflang, sitemap e seletor de idioma apontando de uma para a outra.' ),
				array( 'h2', 'SEO técnico nativo' ),
				array( 'ul', array( 'Título, descrição e imagem por página, com prévia do Google no editor', 'Dados estruturados (Organization, WebSite, BreadcrumbList, BlogPosting e SoftwareApplication)', 'Canonical, Open Graph e sitemap do próprio WordPress' ) ),
				array( 'h2', 'Performance' ),
				array( 'p', 'Cada bloco carrega só o próprio CSS, a fonte é local e pré-carregada, e o JavaScript no site se resume a poucos módulos pequenos da Interactivity API. As próximas páginas são pré-carregadas pelas Speculation Rules do navegador.' ),
			)
		),
		'thumbnail'    => $rolck_og,
		'terms'        => array( 'category' => array( 'bastidores' ) ),
	),
	array(
		'post_type'    => 'post',
		'post_name'    => 'wordpress-site-no-plugins',
		'post_title'   => 'How this site runs without a single third-party plugin',
		'post_excerpt' => 'Languages, SEO, contact form and project showcase built with native WordPress features: blocks, the Interactivity API and the REST API.',
		'post_content' => rolck_seed_blocks(
			array(
				array( 'p', 'This site was built only with what WordPress already offers: a block theme, theme.json, custom blocks and the Interactivity API. No page builder, no SEO plugin, no translation plugin.' ),
				array( 'h2', 'Two languages without a translation plugin' ),
				array( 'p', 'Every page has a Portuguese and an English version linked to each other. English lives under /en/, with hreflang tags, the sitemap and the language switcher pointing from one to the other.' ),
				array( 'h2', 'Native technical SEO' ),
				array( 'ul', array( 'Per-page title, description and image, with a Google preview in the editor', 'Structured data (Organization, WebSite, BreadcrumbList, BlogPosting and SoftwareApplication)', 'Canonical, Open Graph and the WordPress core sitemap' ) ),
				array( 'h2', 'Performance' ),
				array( 'p', 'Each block loads only its own CSS, the font is local and preloaded, and front-end JavaScript comes down to a few tiny Interactivity API modules. Next pages are preloaded by the browser’s Speculation Rules.' ),
			)
		),
		'thumbnail'    => $rolck_og,
		'terms'        => array( 'category' => array( 'bastidores' ) ),
	)
);
rolck_seed_log( 'Artigo de exemplo no blog (pt + en)' );

// ── 8. Leitura, limpeza e URLs ───────────────────────────────────────────
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $rolck_home_pt );
update_option( 'page_for_posts', $rolck_blog_pt );

foreach ( array( 'hello-world', 'ola-mundo' ) as $rolck_slug ) {
	$rolck_post = get_page_by_path( $rolck_slug, OBJECT, 'post' );
	if ( $rolck_post ) {
		wp_delete_post( $rolck_post->ID, true );
	}
}
foreach ( array( 'sample-page', 'pagina-exemplo' ) as $rolck_slug ) {
	$rolck_post = get_page_by_path( $rolck_slug );
	if ( $rolck_post ) {
		wp_delete_post( $rolck_post->ID, true );
	}
}
foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => 'draft', 'numberposts' => 5, 'title' => 'Privacy Policy' ) ) as $rolck_post ) {
	wp_delete_post( $rolck_post->ID, true );
}

// Posts/páginas antigos sem idioma passam a ser do idioma padrão.
foreach ( get_posts( array( 'post_type' => Translations::post_types(), 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'suppress_filters' => true ) ) as $rolck_id ) {
	if ( ! has_term( '', Translations::TAXONOMY, $rolck_id ) ) {
		Translations::set_language( $rolck_id, Languages::default() );
	}
}

RolckGalaxy\Core\Content\ProjectRewrites::add_rules();
flush_rewrite_rules( false );
rolck_seed_log( 'Página inicial, blog, limpeza do conteúdo padrão e URLs' );

// Atualizações de conteúdo (títulos de SEO, links…) também valem para o site novo.
delete_option( 'rolck_content_updates' );
RolckGalaxy\Core\Content\Updates::run();
rolck_seed_log( 'Títulos e descrições de SEO' );

echo "Pronto.\n";
