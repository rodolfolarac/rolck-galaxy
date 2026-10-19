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
			'contact_phone'    => array( 'Telefone (opcional)', 'Phone (optional)' ),
			'contact_subject'  => array( 'Assunto', 'Subject' ),
			'contact_message'  => array( 'Mensagem', 'Message' ),
			'contact_send'     => array( 'Enviar mensagem', 'Send message' ),
			'contact_sending'  => array( 'Enviando…', 'Sending…' ),
			'contact_invalid'  => array( 'Preencha nome, um e-mail válido e a mensagem.', 'Please fill in your name, a valid email and a message.' ),
			'contact_error'    => array( 'Não foi possível enviar agora. Tente de novo ou escreva para o nosso e-mail.', 'We could not send it right now. Try again or email us directly.' ),
			'contact_too_many' => array( 'Muitas mensagens seguidas. Aguarde alguns minutos.', 'Too many messages in a row. Please wait a few minutes.' ),
		),
		'Privacidade (LGPD)' => array(
			'consent_title'             => array( 'Sua privacidade', 'Your privacy' ),
			'consent_text'              => array( 'Usamos cookies necessários para o site funcionar e, somente com a sua autorização, cookies opcionais para as finalidades descritas em "Personalizar". Você pode aceitar todos, rejeitar os opcionais ou escolher por categoria, e mudar de ideia a qualquer momento em "Preferências de cookies", no rodapé do site.', 'We use cookies that are necessary for the site to work and, only with your permission, optional cookies for the purposes described under "Customize". You can accept all, reject the optional ones or choose by category, and change your mind at any time under "Cookie preferences" in the site footer.' ),
			'consent_text_essential'    => array( 'Este site usa somente cookies necessários, para funcionar com segurança e lembrar a sua escolha de privacidade. Não usamos cookies de estatística nem de publicidade. Os detalhes ficam sempre disponíveis em "Preferências de cookies", no rodapé do site.', 'This site only uses necessary cookies, to work securely and remember your privacy choice. We use no analytics or advertising cookies. The details are always available under "Cookie preferences" in the site footer.' ),
			'consent_accept'            => array( 'Aceitar todos', 'Accept all' ),
			'consent_reject'            => array( 'Rejeitar opcionais', 'Reject optional' ),
			'consent_customize'         => array( 'Personalizar', 'Customize' ),
			'consent_ok'                => array( 'Entendi', 'Got it' ),
			'consent_details'           => array( 'Ver detalhes', 'View details' ),
			'consent_save'              => array( 'Salvar minhas escolhas', 'Save my choices' ),
			'consent_close'             => array( 'Fechar', 'Close' ),
			'consent_policy'            => array( 'Política de privacidade', 'Privacy policy' ),
			'consent_prefs_title'       => array( 'Preferências de cookies', 'Cookie preferences' ),
			'consent_prefs_text'        => array( 'Cookies são pequenos arquivos guardados no seu navegador. Os necessários ficam sempre ativos, porque sem eles o site não funciona (LGPD, art. 7º, IX). Os opcionais só são ativados com o seu consentimento (LGPD, art. 7º, I, e art. 8º), e você pode retirá-lo a qualquer momento, de graça, nesta mesma tela (art. 8º, § 5º). Retirar o consentimento não afeta o que foi feito antes dele.', 'Cookies are small files stored in your browser. Necessary cookies are always on, because the site does not work without them (Brazilian LGPD, art. 7, IX). Optional cookies are only enabled with your consent (art. 7, I, and art. 8), which you can withdraw at any time, free of charge, on this same screen (art. 8, § 5). Withdrawing consent does not affect anything done before it.' ),
			'consent_always_on'         => array( 'Sempre ativos', 'Always on' ),
			'consent_cat_necessary'     => array( 'Necessários', 'Necessary' ),
			'consent_cat_necessary_text' => array( 'Essenciais para o site funcionar com segurança e para guardar a sua escolha de privacidade. Não são usados para publicidade nem para traçar o seu perfil.', 'Essential for the site to work securely and to store your privacy choice. They are never used for advertising or profiling.' ),
			'consent_cat_analytics'     => array( 'Estatística', 'Analytics' ),
			'consent_cat_analytics_text' => array( 'Ajudam a entender quantas pessoas visitam o site e quais páginas são mais acessadas, para melhorarmos o conteúdo. Só são ativados com o seu consentimento.', 'Help us understand how many people visit the site and which pages are most viewed, so we can improve the content. Only enabled with your consent.' ),
			'consent_cat_marketing'     => array( 'Marketing', 'Marketing' ),
			'consent_cat_marketing_text' => array( 'Usados por ferramentas de terceiros para medir campanhas e mostrar anúncios relevantes. Só são ativados com o seu consentimento.', 'Used by third-party tools to measure campaigns and show relevant ads. Only enabled with your consent.' ),
			'consent_see_cookies'       => array( 'Ver os cookies (%d)', 'See the cookies (%d)' ),
			'consent_third_party_note'  => array( 'Os cookies desta categoria são definidos pelos serviços de terceiros configurados no site, conforme a política de privacidade de cada um.', 'Cookies in this category are set by the third-party services configured on the site, under each one’s privacy policy.' ),
			'consent_col_name'          => array( 'Cookie', 'Cookie' ),
			'consent_col_provider'      => array( 'Quem define', 'Set by' ),
			'consent_col_purpose'       => array( 'Finalidade', 'Purpose' ),
			'consent_col_duration'      => array( 'Duração', 'Duration' ),
			'consent_first_party'       => array( 'Este site', 'This site' ),
			'consent_purpose_consent'   => array( 'Guarda a sua escolha de privacidade e o código do registro do seu consentimento.', 'Stores your privacy choice and the ID of your consent record.' ),
			'consent_purpose_wp'        => array( 'Mantêm o login e as preferências de quem acessa o painel administrativo. Não são criados para visitantes.', 'Keep the login and preferences of people using the admin dashboard. Never set for visitors.' ),
			'consent_wp_duration'       => array( 'Sessão a 1 ano', 'Session to 1 year' ),
			'consent_purpose_ga'        => array( 'Distingue visitantes por um código aleatório, para gerar estatísticas de uso.', 'Tells visitors apart with a random ID to produce usage statistics.' ),
			'consent_purpose_ga_session' => array( 'Mantém o estado da visita para as estatísticas.', 'Keeps the visit state for the statistics.' ),
			'consent_days'              => array( '%d dias', '%d days' ),
			'consent_years'             => array( '%d anos', '%d years' ),
			'consent_controller'        => array( 'Controlador dos dados', 'Data controller' ),
			'consent_contact'           => array( 'Canal de privacidade (encarregado)', 'Privacy contact (DPO)' ),
			'consent_id_label'          => array( 'Seu registro de consentimento', 'Your consent record' ),
			'consent_none'              => array( 'Nenhuma escolha registrada neste navegador.', 'No choice recorded in this browser.' ),
			'consent_saved'             => array( 'Suas escolhas foram salvas.', 'Your choices were saved.' ),
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
