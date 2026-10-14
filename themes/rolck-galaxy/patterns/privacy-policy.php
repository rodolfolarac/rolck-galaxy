<?php
/**
 * Title: Política de privacidade (LGPD)
 * Slug: rolck-galaxy/privacy-policy
 * Categories: rolck-galaxy, text
 * Keywords: privacidade, lgpd, cookies, política, termos
 * Description: Política de privacidade baseada na LGPD (Lei 13.709/2018), no guia de cookies da ANPD e no Marco Civil da Internet, com a tabela de cookies atualizada automaticamente. Revise com a sua assessoria jurídica.
 * Inserter: true
 *
 * O texto descreve o que o site faz de fato: formulário de contato, limite
 * anti-spam por IP, registro de consentimento e cookies. Os trechos sobre o
 * Google Analytics só entram se ele estiver ligado em Integrações no momento
 * em que o padrão for inserido.
 *
 * @package RolckGalaxy
 */

$t       = 'rolck_theme_t';
$company = function_exists( 'rolck_setting' ) ? (string) rolck_setting( 'general.company_name', get_bloginfo( 'name' ) ) : get_bloginfo( 'name' );
$email   = function_exists( 'rolck_setting' ) ? (string) ( rolck_setting( 'privacy.dpo_email', '' ) ?: rolck_setting( 'general.email', '' ) ) : get_option( 'admin_email' );
$dpo     = function_exists( 'rolck_setting' ) ? (string) rolck_setting( 'privacy.dpo_name', '' ) : '';
$ga      = function_exists( 'rolck_setting' ) && preg_match( '/^G-[A-Z0-9]+$/', (string) rolck_setting( 'integrations.ga4_id', '' ) );
$months  = function_exists( 'rolck_setting' ) ? max( 1, (int) rolck_setting( 'privacy.retention_months', 60 ) ) : 60;
$site    = wp_parse_url( home_url(), PHP_URL_HOST );
$mail    = sprintf( '<a href="mailto:%1$s">%1$s</a>', esc_html( antispambot( $email ) ) );

$p  = static fn( string $html ): string => "<!-- wp:paragraph -->\n<p>" . $html . "</p>\n<!-- /wp:paragraph -->\n\n";
$h2 = static fn( string $text ): string => "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">" . esc_html( $text ) . "</h2>\n<!-- /wp:heading -->\n\n";
$h3 = static fn( string $text ): string => "<!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">" . esc_html( $text ) . "</h3>\n<!-- /wp:heading -->\n\n";
$ul = static function ( array $items ): string {
	$lis = array_map( static fn( string $li ): string => "<!-- wp:list-item -->\n<li>" . $li . "</li>\n<!-- /wp:list-item -->", $items );
	return "<!-- wp:list -->\n<ul class=\"wp-block-list\">" . implode( "\n\n", $lis ) . "</ul>\n<!-- /wp:list -->\n\n";
};

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- textos fixos deste arquivo; valores das configurações escapados acima.
echo $p(
	'<em>' . esc_html( $t( 'Última atualização: ', 'Last updated: ' ) . wp_date( $t( 'd/m/Y', 'F j, Y' ) ) ) . '</em>'
);

echo $p(
	sprintf(
		esc_html( $t(
			'Esta política explica, de forma simples, quais dados pessoais o site %1$s trata, para quê, com base em qual hipótese legal, por quanto tempo e como você exerce os seus direitos, conforme a Lei Geral de Proteção de Dados Pessoais (LGPD, Lei 13.709/2018), o Marco Civil da Internet (Lei 12.965/2014) e o Guia Orientativo de Cookies da Autoridade Nacional de Proteção de Dados (ANPD).',
			'This policy explains, in plain words, which personal data the website %1$s processes, why, on which legal basis, for how long and how you can exercise your rights, under Brazil’s General Data Protection Law (LGPD, Law 13,709/2018), the Brazilian Internet Civil Framework (Law 12,965/2014) and the cookie guidance of the Brazilian Data Protection Authority (ANPD).'
		) ),
		'<strong>' . esc_html( $site ) . '</strong>'
	)
);

echo $h2( $t( '1. Quem é o responsável pelos seus dados', '1. Who is responsible for your data' ) );
echo $p(
	sprintf(
		esc_html( $t(
			'O controlador dos dados é %1$s. Para qualquer assunto de privacidade, inclusive para exercer os seus direitos, escreva para %2$s.',
			'The data controller is %1$s. For any privacy matter, including exercising your rights, write to %2$s.'
		) ),
		'<strong>' . esc_html( $company ) . '</strong>',
		$mail
	) . ( $dpo ? ' ' . esc_html( $t( 'Encarregado pelo tratamento de dados (DPO): ', 'Data protection officer (DPO): ' ) . $dpo ) . '.' : '' )
);

echo $h2( $t( '2. Quais dados tratamos e por quê', '2. What data we process and why' ) );

echo $h3( $t( 'Formulário de contato', 'Contact form' ) );
echo $ul(
	array(
		esc_html( $t( 'Dados: nome, e-mail, assunto e mensagem que você escreve, além do idioma e da página de onde a mensagem foi enviada.', 'Data: the name, email, subject and message you write, plus the language and the page the message was sent from.' ) ),
		esc_html( $t( 'Finalidade: responder ao seu contato e, se for o caso, preparar uma proposta.', 'Purpose: to reply to you and, where applicable, prepare a proposal.' ) ),
		esc_html( $t( 'Base legal: procedimentos preliminares a um contrato, a seu pedido (LGPD, art. 7º, V), e o nosso legítimo interesse em responder a quem nos procura (art. 7º, IX).', 'Legal basis: steps taken at your request before entering into a contract (LGPD, art. 7, V) and our legitimate interest in replying to people who contact us (art. 7, IX).' ) ),
		esc_html( $t( 'Prazo: pelo tempo necessário para atender ao contato e à relação comercial que dele resultar. Você pode pedir a exclusão a qualquer momento.', 'Retention: for as long as needed to handle your message and any business relationship that follows. You can ask for deletion at any time.' ) ),
	)
);

echo $h3( $t( 'Proteção contra spam', 'Spam protection' ) );
echo $p( esc_html( $t( 'Ao enviar o formulário, o endereço IP é usado de forma transitória, por até 10 minutos, apenas para limitar envios automáticos em sequência. Base legal: legítimo interesse na segurança do site (art. 7º, IX).', 'When you submit the form, your IP address is used temporarily, for up to 10 minutes, only to limit automated submissions in a row. Legal basis: legitimate interest in keeping the site secure (art. 7, IX).' ) ) );

echo $h3( $t( 'Registro do seu consentimento sobre cookies', 'Record of your cookie consent' ) );
echo $p(
	sprintf(
		esc_html( $t(
			'Quando você responde ao aviso de cookies, guardamos um código aleatório, a data e a hora, a versão do aviso, as categorias oferecidas e as escolhidas, o idioma, a página e o navegador usado. O endereço IP não é guardado: fica apenas um código derivado dele (hash), que não permite descobrir o IP. Finalidade: comprovar o consentimento, como exige a LGPD (art. 8º, § 2º). Base legal: cumprimento de obrigação legal (art. 7º, II) e exercício regular de direitos (art. 7º, VI). Prazo: %d meses.',
			'When you answer the cookie notice, we store a random ID, the date and time, the notice version, the categories offered and chosen, the language, the page and the browser used. Your IP address is not stored: only a code derived from it (hash) is kept, which cannot be turned back into the IP. Purpose: to prove consent, as required by the LGPD (art. 8, § 2). Legal basis: compliance with a legal obligation (art. 7, II) and the regular exercise of rights (art. 7, VI). Retention: %d months.'
		) ),
		$months
	)
);

echo $h3( $t( 'Registros de acesso', 'Access logs' ) );
echo $p( esc_html( $t( 'Como todo site, o servidor registra o endereço IP, a data e a hora de cada acesso. Esses registros são mantidos sob sigilo, em ambiente controlado, pelo prazo de 6 meses exigido pelo Marco Civil da Internet (art. 15), e só são fornecidos mediante ordem judicial. Base legal: cumprimento de obrigação legal (LGPD, art. 7º, II).', 'Like any website, the server logs the IP address, date and time of each visit. These logs are kept confidential, in a controlled environment, for the 6 months required by the Brazilian Internet Civil Framework (art. 15), and are only disclosed under a court order. Legal basis: compliance with a legal obligation (LGPD, art. 7, II).' ) ) );

if ( $ga ) {
	echo $h3( $t( 'Estatísticas de visita (Google Analytics)', 'Visit statistics (Google Analytics)' ) );
	echo $p( esc_html( $t( 'Somente se você aceitar a categoria Estatística, o Google Analytics registra de forma pseudonimizada as páginas visitadas, o tempo de visita, o tipo de dispositivo e a região aproximada, para entendermos como o site é usado. Base legal: consentimento (art. 7º, I), que pode ser retirado a qualquer momento. Esses dados são tratados pelo Google, que pode armazená-los fora do Brasil.', 'Only if you accept the Analytics category, Google Analytics records, in pseudonymized form, the pages visited, visit duration, device type and approximate region, so we understand how the site is used. Legal basis: consent (art. 7, I), which you can withdraw at any time. This data is processed by Google, which may store it outside Brazil.' ) ) );
}

echo $h2( $t( '3. Cookies', '3. Cookies' ) );
echo $p( esc_html( $t( 'Cookies são pequenos arquivos guardados no seu navegador. Os necessários ficam sempre ativos, porque sem eles o site não funciona (legítimo interesse, art. 7º, IX). Os opcionais só são ativados se você aceitar, e você pode mudar a sua escolha quando quiser pelo link "Preferências de cookies", no rodapé de todas as páginas. A lista abaixo é atualizada automaticamente.', 'Cookies are small files stored in your browser. Necessary cookies are always on, because the site does not work without them (legitimate interest, art. 7, IX). Optional cookies are only enabled if you accept them, and you can change your choice at any time through the "Cookie preferences" link in the footer of every page. The list below updates automatically.' ) ) );
echo "<!-- wp:rolck/cookie-table /-->\n\n";

echo $h2( $t( '4. Com quem compartilhamos', '4. Who we share data with' ) );
echo $p( esc_html( $t( 'Não vendemos nem alugamos dados pessoais. Eles podem ser tratados, em nosso nome e só para as finalidades acima, pelos fornecedores de hospedagem do site e de envio de e-mail' . ( $ga ? ' e pelo Google (estatísticas, se você aceitar)' : '' ) . '. Também podemos fornecê-los a autoridades públicas quando houver obrigação legal ou ordem judicial.', 'We do not sell or rent personal data. It may be processed on our behalf, and only for the purposes above, by the site’s hosting and email delivery providers' . ( $ga ? ' and by Google (statistics, if you accept)' : '' ) . '. We may also disclose it to public authorities when required by law or court order.' ) ) );

echo $h2( $t( '5. Transferência internacional', '5. International transfers' ) );
echo $p( esc_html( $t( 'Alguns desses fornecedores podem armazenar dados em servidores fora do Brasil. Nesses casos, a transferência segue as hipóteses do art. 33 da LGPD.', 'Some of these providers may store data on servers outside Brazil. In those cases, the transfer follows the cases allowed by article 33 of the LGPD.' ) ) );

echo $h2( $t( '6. Como protegemos os dados', '6. How we protect data' ) );
echo $p( esc_html( $t( 'O site usa conexão criptografada (HTTPS), o acesso ao painel administrativo é restrito a pessoas autorizadas e o registro de consentimento não guarda o seu IP. Nenhum sistema é totalmente imune a incidentes; se ocorrer algum que possa trazer risco ou dano relevante, comunicaremos você e a ANPD, como manda a LGPD (art. 48).', 'The site uses an encrypted connection (HTTPS), access to the admin dashboard is restricted to authorized people and the consent record does not store your IP. No system is completely immune to incidents; if one occurs that may cause relevant risk or harm, we will notify you and the ANPD, as the LGPD requires (art. 48).' ) ) );

echo $h2( $t( '7. Seus direitos', '7. Your rights' ) );
echo $p( esc_html( $t( 'A LGPD (art. 18) garante a você, a qualquer momento e de graça:', 'The LGPD (art. 18) gives you, at any time and free of charge, the right to:' ) ) );
echo $ul(
	array(
		esc_html( $t( 'confirmar se tratamos dados seus e ter acesso a eles;', 'confirm whether we process your data and access it;' ) ),
		esc_html( $t( 'corrigir dados incompletos, inexatos ou desatualizados;', 'correct incomplete, inaccurate or outdated data;' ) ),
		esc_html( $t( 'pedir a anonimização, o bloqueio ou a eliminação de dados desnecessários, excessivos ou tratados em desacordo com a lei;', 'ask for anonymization, blocking or deletion of unnecessary or excessive data, or data processed unlawfully;' ) ),
		esc_html( $t( 'pedir a portabilidade dos dados a outro fornecedor;', 'ask for your data to be ported to another provider;' ) ),
		esc_html( $t( 'pedir a eliminação dos dados tratados com base no seu consentimento;', 'ask for deletion of data processed based on your consent;' ) ),
		esc_html( $t( 'saber com quem compartilhamos os seus dados;', 'know who we share your data with;' ) ),
		esc_html( $t( 'saber que pode não dar o consentimento e quais são as consequências;', 'know that you may refuse consent and what the consequences are;' ) ),
		esc_html( $t( 'retirar o consentimento, pelo link "Preferências de cookies" ou por e-mail;', 'withdraw consent, through the "Cookie preferences" link or by email;' ) ),
		esc_html( $t( 'se opor a um tratamento feito sem consentimento, se ele descumprir a lei;', 'object to processing carried out without consent if it breaches the law;' ) ),
		esc_html( $t( 'apresentar reclamação à ANPD (gov.br/anpd).', 'file a complaint with the ANPD (gov.br/anpd).' ) ),
	)
);
echo $p( sprintf( esc_html( $t( 'Para exercer qualquer um deles, escreva para %s. Podemos pedir informações para confirmar a sua identidade antes de atender, para proteger os seus dados. Responderemos nos prazos previstos na LGPD.', 'To exercise any of them, write to %s. We may ask for information to confirm your identity first, to protect your data. We will reply within the deadlines set by the LGPD.' ) ), $mail ) );

echo $h2( $t( '8. Crianças e adolescentes', '8. Children and teenagers' ) );
echo $p( esc_html( $t( 'O site não é direcionado a menores de 18 anos e não coletamos intencionalmente dados de crianças. Se você souber de algum caso, avise pelo e-mail acima para que os dados sejam eliminados.', 'The site is not aimed at people under 18 and we do not knowingly collect data from children. If you become aware of any such case, let us know by email so the data can be deleted.' ) ) );

echo $h2( $t( '9. Mudanças nesta política', '9. Changes to this policy' ) );
echo $p( esc_html( $t( 'Podemos atualizar esta política para refletir mudanças no site ou na lei. A data no início indica a versão em vigor. Quando a mudança afetar o uso de cookies, o aviso aparecerá de novo para você escolher outra vez.', 'We may update this policy to reflect changes to the site or the law. The date at the top shows the version in force. When a change affects cookies, the notice will appear again so you can choose again.' ) ) );
// phpcs:enable
