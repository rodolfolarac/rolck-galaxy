<?php
/**
 * Textos fixos da interface do site (botões, rótulos, mensagens) nos dois idiomas.
 *
 * Todos aparecem no painel em Rolck Galaxy → Textos do site, onde podem ser
 * reescritos sem tocar em código. Para criar um texto novo: acrescente a chave
 * aqui e use rolck_t( 'chave' ) no PHP (ou receba pelo estado do bloco no JS).
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\I18n;

use RolckGalaxy\Core\Settings;

defined( 'ABSPATH' ) || exit;

final class Strings {

	/**
	 * Grupos exibidos no painel → chave → [ pt, en ].
	 *
	 * @var array<string,array<string,array{0:string,1:string}>>
	 */
	private const GROUPS = array(
		'Geral'     => array(
			'skip_link'      => array( 'Pular para o conteúdo', 'Skip to content' ),
			'language'       => array( 'Idioma', 'Language' ),
			'home'           => array( 'Início', 'Home' ),
			'back_home'      => array( 'Voltar ao início', 'Back to home' ),
			'rights'         => array( 'Todos os direitos reservados.', 'All rights reserved.' ),
			'made_with'      => array( 'Feito com WordPress, sem plugins de terceiros.', 'Built with WordPress, no third-party plugins.' ),
			'read_more'      => array( 'Ler artigo', 'Read article' ),
			'reading_time'   => array( '%d min de leitura', '%d min read' ),
			'breadcrumb'     => array( 'Navegação estrutural', 'Breadcrumb' ),
			'social'         => array( 'Redes sociais', 'Social profiles' ),
			'no_translation' => array( 'Esta página ainda não tem versão em português.', 'This page is not available in English yet.' ),
		),
		'Projetos'  => array(
			'projects'          => array( 'Projetos', 'Projects' ),
			'projects_intro'    => array( 'Temas WordPress, plugins e produtos digitais com demo ao vivo. Teste tudo antes de comprar.', 'WordPress themes, plugins and digital products with live demos. Try everything before you buy.' ),
			'filter_all'        => array( 'Todos', 'All' ),
			'filter_label'      => array( 'Filtrar projetos por categoria', 'Filter projects by category' ),
			'view_project'      => array( 'Ver projeto', 'View project' ),
			'live_demo'         => array( 'Ver demo ao vivo', 'Live demo' ),
			'buy'               => array( 'Comprar agora', 'Buy now' ),
			'source'            => array( 'Código no GitHub', 'Source on GitHub' ),
			'price'             => array( 'Preço', 'Price' ),
			'version'           => array( 'Versão', 'Version' ),
			'released'          => array( 'Lançamento', 'Release' ),
			'compatibility'     => array( 'Compatibilidade', 'Compatibility' ),
			'stack'             => array( 'Tecnologias', 'Tech stack' ),
			'category'          => array( 'Categoria', 'Category' ),
			'status'            => array( 'Status', 'Status' ),
			'status_available'  => array( 'Disponível', 'Available' ),
			'status_soon'       => array( 'Em breve', 'Coming soon' ),
			'status_development' => array( 'Em desenvolvimento', 'In development' ),
			'featured'          => array( 'Destaque', 'Featured' ),
			'highlights'        => array( 'Destaques do projeto', 'Project highlights' ),
			'empty'             => array( 'Nenhum projeto nesta categoria por enquanto.', 'No projects in this category yet.' ),
			'related'           => array( 'Outros projetos', 'More projects' ),
			'details'           => array( 'Ficha técnica', 'Project details' ),
		),
		'Contato'   => array(
			'contact_name'     => array( 'Nome', 'Name' ),
			'contact_email'    => array( 'E-mail', 'Email' ),
			'contact_subject'  => array( 'Assunto', 'Subject' ),
			'contact_message'  => array( 'Mensagem', 'Message' ),
			'contact_send'     => array( 'Enviar mensagem', 'Send message' ),
			'contact_sending'  => array( 'Enviando…', 'Sending…' ),
			'contact_invalid'  => array( 'Preencha nome, um e-mail válido e a mensagem.', 'Please fill in your name, a valid email and a message.' ),
			'contact_error'    => array( 'Não foi possível enviar agora. Tente de novo ou escreva para o nosso e-mail.', 'We could not send it right now. Try again or email us directly.' ),
			'contact_too_many' => array( 'Muitas mensagens seguidas. Aguarde alguns minutos.', 'Too many messages in a row. Please wait a few minutes.' ),
		),
		'Blog, busca e 404' => array(
			'search'             => array( 'Buscar', 'Search' ),
			'search_placeholder' => array( 'Buscar no site…', 'Search the site…' ),
			'search_results'     => array( 'Resultados da busca', 'Search results' ),
			'search_empty'       => array( 'Nada encontrado. Tente outras palavras.', 'Nothing found. Try different words.' ),
			'not_found_title'    => array( 'Esta página se perdeu no espaço', 'This page drifted into space' ),
			'not_found_text'     => array( 'O endereço pode ter mudado ou nunca existiu. Que tal voltar ao início ou ver os projetos?', 'The address may have changed or never existed. Head back home or browse the projects.' ),
			'blog'               => array( 'Blog', 'Blog' ),
			'blog_intro'         => array( 'Bastidores, tutoriais e novidades dos nossos temas e produtos.', 'Behind the scenes, tutorials and news about our themes and products.' ),
			'latest_posts'       => array( 'Artigos recentes', 'Latest articles' ),
		),
	);

	/** @return array<string,array{pt:string,en:string}> Chave → { pt, en } (formato salvo nas configurações). */
	public static function defaults(): array {
		$out = array();
		foreach ( self::GROUPS as $items ) {
			foreach ( $items as $key => [ $pt, $en ] ) {
				$out[ $key ] = array(
					'pt' => $pt,
					'en' => $en,
				);
			}
		}
		return $out;
	}

	/** @return array<string,string[]> Grupo → chaves, para a tela do painel. */
	public static function groups(): array {
		return array_map( 'array_keys', self::GROUPS );
	}

	/**
	 * @param string      $key  Chave.
	 * @param string|null $lang Idioma (padrão: o atual).
	 */
	public static function get( string $key, ?string $lang = null ): string {
		$pair = Settings::get( 'strings.' . $key );
		if ( null === $pair ) {
			$pair = self::defaults()[ $key ] ?? $key;
		}
		return Languages::pick( $pair, $lang );
	}

	/**
	 * Vários textos de uma vez, para enviar ao JavaScript dos blocos.
	 *
	 * @param string[] $keys Chaves.
	 * @return array<string,string>
	 */
	public static function many( array $keys ): array {
		return array_combine( $keys, array_map( array( self::class, 'get' ), $keys ) );
	}
}
