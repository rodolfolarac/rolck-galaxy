<?php
/**
 * Atualizações de conteúdo que acompanham uma versão do código.
 *
 * O deploy só envia código; o conteúdo do site no ar fica no banco. Quando uma
 * versão precisa preencher algo no banco (um link novo, por exemplo), o passo
 * entra aqui e roda uma única vez depois do deploy. Cada passo só preenche
 * campo vazio, então nunca sobrescreve o que foi editado no painel.
 *
 * Para criar um passo: acrescente-o em steps() com a próxima chave numérica.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\Content;

use RolckGalaxy\Core\I18n\Translations;
use RolckGalaxy\Core\Settings;

defined( 'ABSPATH' ) || exit;

final class Updates {

	/** Último passo aplicado neste site. */
	private const OPTION = 'rolck_content_updates';

	public static function register(): void {
		add_action( 'init', array( self::class, 'run' ), 20 );
	}

	/**
	 * Passos, na ordem em que rodam.
	 *
	 * @return array<int,callable>
	 */
	private static function steps(): array {
		return array(
			// Demo pública do Galaxy Cards (versão de teste, dados no navegador do visitante).
			1 => static fn() => self::fill_project_field( 'galaxy-cards', 'demo_url', 'https://galaxy-cards.vercel.app/?demo' ),
			// Títulos e descrições de SEO com o que as pessoas pesquisam (só onde está vazio).
			2 => static function () {
				foreach ( self::seo_texts() as $row ) {
					self::fill_seo( $row );
				}
			},
			// Título e descrição da home: só troca se ainda forem os do primeiro lançamento.
			3 => static fn() => self::replace_home_seo(),
			// Categoria de aplicativo correta para o Google (o padrão é Desenvolvimento).
			4 => static function () {
				self::fill_project_field( 'galaxy-cards', 'app_category', 'EducationalApplication' );
				self::fill_project_field( 'galaxy-health', 'app_category', 'HealthApplication' );
			},
			// Título principal da home com o que as pessoas pesquisam (só se ainda for o original).
			5 => static fn() => self::replace_in_pages(
				array(
					'Produtos digitais que <em>carregam rápido</em> e vendem mais' => 'Sites e produtos WordPress que <em>carregam rápido</em> e vendem mais',
					'Digital products that <em>load fast</em> and sell more' => 'WordPress websites and products that <em>load fast</em> and sell more',
				)
			),
		);
	}

	/**
	 * Título e descrição de SEO de cada página: [ tipo, slug, idioma, título, descrição ].
	 *
	 * @return array<int,array{0:string,1:string,2:string,3:string,4:string}>
	 */
	private static function seo_texts(): array {
		$project = Projects::POST_TYPE;
		return array(
			array( 'page', 'servicos', 'pt', 'Criação de sites WordPress e plugins sob medida · Rolck Galaxy', 'Desenvolvimento de sites WordPress, temas e plugins sob medida: SEO técnico, Core Web Vitals no verde, sites em mais de um idioma e edição sem código.' ),
			array( 'page', 'services', 'en', 'Custom WordPress websites and plugins · Rolck Galaxy', 'Custom WordPress websites, themes and plugins: technical SEO, green Core Web Vitals, multilingual sites and no-code editing for your team.' ),
			array( 'page', 'sobre', 'pt', 'Sobre a Rolck Galaxy: estúdio de sites e produtos WordPress', 'Estúdio de produtos digitais: temas e plugins WordPress prontos para usar e projetos sob medida, com foco em performance, acessibilidade e SEO.' ),
			array( 'page', 'about', 'en', 'About Rolck Galaxy: a WordPress product studio', 'A digital product studio building ready-to-use WordPress themes and plugins and custom projects focused on performance, accessibility and SEO.' ),
			array( 'page', 'contato', 'pt', 'Orçamento de site WordPress e contato · Rolck Galaxy', 'Peça um orçamento de site, tema ou plugin WordPress sob medida. Conte o que você precisa e receba escopo, prazo e investimento.' ),
			array( 'page', 'contact', 'en', 'Get a WordPress website quote · Rolck Galaxy', 'Request a quote for a custom WordPress website, theme or plugin. Tell us what you need and get the scope, timeline and investment.' ),
			array( 'page', 'blog', 'pt', 'Blog: WordPress, performance e SEO técnico · Rolck Galaxy', 'Bastidores e guias práticos sobre WordPress sem plugins, performance, SEO técnico e acessibilidade.' ),
			array( 'page', 'blog', 'en', 'Blog: WordPress, performance and technical SEO · Rolck Galaxy', 'Behind the scenes and practical guides on plugin-free WordPress, performance, technical SEO and accessibility.' ),
			array( $project, 'galaxy-academy', 'pt', 'Galaxy Academy: tema WordPress para cursos online · Rolck Galaxy', 'Tema WordPress para plataformas de cursos em vídeo: módulos, aulas, progresso, certificados e venda pelo WooCommerce, tudo personalizável sem código.' ),
			array( $project, 'galaxy-academy', 'en', 'Galaxy Academy: WordPress theme for online courses · Rolck Galaxy', 'WordPress theme for video course platforms: modules, lessons, progress, certificates and WooCommerce sales, all customizable without code.' ),
			array( $project, 'galaxy-cards', 'pt', 'Galaxy Cards: flashcards para aprender inglês · Rolck Galaxy', 'App de flashcards para memorizar vocabulário em inglês com repetição espaçada e áudio nativo. Teste a demo ao vivo, sem cadastro.' ),
			array( $project, 'galaxy-cards', 'en', 'Galaxy Cards: English vocabulary flashcards · Rolck Galaxy', 'Flashcard app to memorize English vocabulary with spaced repetition and native audio. Try the live demo, no sign-up needed.' ),
			array( $project, 'galaxy-health', 'pt', 'Galaxy Health: ficha de treino com elásticos · Rolck Galaxy', 'Ficha de treino para elásticos (superband) com carga estimada, timer de descanso, recordes e comparativo com o último treino.' ),
			array( $project, 'galaxy-health', 'en', 'Galaxy Health: resistance band workout log · Rolck Galaxy', 'Workout log for resistance bands with estimated load, rest timer, personal records and a comparison with your last session.' ),
		);
	}

	/**
	 * Preenche título e descrição de SEO de uma página num idioma, campo a campo, se estiverem vazios.
	 *
	 * @param array{0:string,1:string,2:string,3:string,4:string} $row Tipo, slug, idioma, título, descrição.
	 */
	private static function fill_seo( array $row ): void {
		[ $type, $slug, $lang, $title, $description ] = $row;
		foreach ( self::find( $type, $slug ) as $id ) {
			if ( Translations::language_of( $id ) !== $lang ) {
				continue;
			}
			foreach ( array( '_rolck_seo_title' => $title, '_rolck_seo_description' => $description ) as $key => $value ) {
				if ( '' === trim( (string) get_post_meta( $id, $key, true ) ) ) {
					update_post_meta( $id, $key, $value );
				}
			}
		}
	}

	private static function replace_home_seo(): void {
		$first = array(
			'home_title'       => array(
				'pt' => 'Rolck Galaxy — Temas WordPress e produtos digitais de alta performance',
				'en' => 'Rolck Galaxy — High-performance WordPress themes and digital products',
			),
			'home_description' => array(
				'pt' => 'A Rolck Galaxy cria temas WordPress, plugins e templates rápidos, acessíveis e prontos para ranquear. Veja as demos ao vivo de todos os projetos.',
				'en' => 'Rolck Galaxy builds fast, accessible, SEO-ready WordPress themes, plugins and templates. Explore the live demo of every project.',
			),
		);
		$saved = get_option( Settings::OPTION );
		if ( ! is_array( $saved ) ) {
			return; // Nada salvo: o site já usa os padrões novos.
		}
		$defaults = Settings::defaults()['seo'];
		foreach ( $first as $key => $old ) {
			foreach ( $old as $lang => $text ) {
				if ( trim( (string) ( $saved['seo'][ $key ][ $lang ] ?? '' ) ) === $text ) {
					$saved['seo'][ $key ][ $lang ] = $defaults[ $key ][ $lang ];
				}
			}
		}
		update_option( Settings::OPTION, $saved );
	}

	/**
	 * Troca trechos de texto no conteúdo das páginas onde eles ainda aparecem.
	 *
	 * @param array<string,string> $pairs Texto antigo => texto novo.
	 */
	private static function replace_in_pages( array $pairs ): void {
		$ids = get_posts(
			array(
				'post_type'        => 'page',
				'post_status'      => 'any',
				'posts_per_page'   => -1,
				'fields'           => 'ids',
				'suppress_filters' => true,
			)
		);
		foreach ( $ids as $id ) {
			$content = (string) get_post_field( 'post_content', $id, 'raw' );
			$updated = strtr( $content, $pairs );
			if ( $updated !== $content ) {
				wp_update_post( wp_slash( array( 'ID' => $id, 'post_content' => $updated ) ) );
			}
		}
	}

	/** @return int[] Posts do tipo com o slug, em todos os idiomas. */
	private static function find( string $type, string $slug ): array {
		return get_posts(
			array(
				'post_type'        => $type,
				'name'             => $slug,
				'post_status'      => 'any',
				'posts_per_page'   => -1,
				'fields'           => 'ids',
				'suppress_filters' => true,
			)
		);
	}

	public static function run(): void {
		$done  = (int) get_option( self::OPTION, 0 );
		$steps = self::steps();
		$last  = max( array_keys( $steps ) );
		if ( $done >= $last ) {
			return;
		}
		foreach ( $steps as $number => $step ) {
			if ( $number > $done ) {
				$step();
				$done = $number;
			}
		}
		update_option( self::OPTION, $done, true );
	}

	/** Preenche um campo do projeto (em todos os idiomas) quando ele está vazio. */
	private static function fill_project_field( string $slug, string $field, string $value ): void {
		foreach ( self::find( Projects::POST_TYPE, $slug ) as $id ) {
			if ( '' === (string) get_post_meta( $id, 'rolck_' . $field, true ) ) {
				update_post_meta( $id, 'rolck_' . $field, $value );
			}
		}
	}
}
