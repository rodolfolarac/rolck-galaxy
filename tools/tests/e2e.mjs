/**
 * Testes de ponta a ponta do site (precisa do `npm run dev` rodando).
 *
 *   npm run test:e2e
 *
 * Cobre: rotas nos dois idiomas, SEO no <head> (canonical, hreflang, OG,
 * JSON-LD), seletor de idioma, sitemap, filtro da vitrine, formulário de
 * contato, painel de configurações, criação de tradução pelo editor,
 * validade dos blocos no editor e rolagem horizontal em celulares.
 */
import { launch } from '../lib/browser.mjs';
import { admin as credentials } from '../lib/env.mjs';

const BASE = process.env.BASE || 'http://127.0.0.1:9500';
let passed = 0;
let failed = 0;

function check( name, condition, detail = '' ) {
	if ( condition ) {
		passed++;
		console.log( `  ✓ ${ name }` );
	} else {
		failed++;
		console.log( `  ✗ ${ name }${ detail ? ` → ${ detail }` : '' }` );
	}
}

const get = async ( path ) => {
	const response = await fetch( BASE + path, { redirect: 'manual' } );
	return {
		status: response.status,
		location: response.headers.get( 'location' ),
		html: await response.text(),
	};
};
const attr = ( html, regex ) => ( html.match( regex ) || [] )[ 1 ] || '';
const jsonLd = ( html ) => {
	try {
		return JSON.parse(
			attr(
				html,
				/<script type="application\/ld\+json">([\s\S]*?)<\/script>/
			)
		);
	} catch {
		return null;
	}
};

// O Playground responde a 1ª requisição depois de subir com um redirecionamento de login automático.
await fetch( BASE + '/', { redirect: 'manual' } ).catch( () => {} );

// ── 1. Rotas ────────────────────────────────────────────────────────────
console.log( '\nRotas' );
const routes = {
	'/': 200,
	'/en/': 200,
	'/projetos/': 200,
	'/en/projects/': 200,
	'/projetos/galaxy-academy/': 200,
	'/en/projects/galaxy-academy/': 200,
	'/categoria-projeto/web-apps/': 200,
	'/en/project-category/web-apps/': 200,
	'/servicos/': 200,
	'/en/services/': 200,
	'/sobre/': 200,
	'/en/about/': 200,
	'/contato/': 200,
	'/en/contact/': 200,
	'/blog/': 200,
	'/en/blog/': 200,
	'/site-wordpress-sem-plugins/': 200,
	'/en/wordpress-site-no-plugins/': 200,
	'/?s=galaxy': 200,
	'/en/?s=galaxy': 200,
	'/pagina-que-nao-existe/': 404,
	'/en/page-that-does-not-exist/': 404,
	'/wp-sitemap.xml': 200,
};
for ( const [ path, expected ] of Object.entries( routes ) ) {
	const { status } = await get( path );
	check(
		`${ path } responde ${ expected }`,
		status === expected,
		`recebeu ${ status }`
	);
}

const wrong = await get( '/en/sobre/' );
check(
	'conteúdo em português aberto com /en/ redireciona (301) para a URL certa',
	wrong.status === 301 && wrong.location?.endsWith( '/sobre/' ),
	`${ wrong.status } ${ wrong.location }`
);

// ── 2. SEO no <head> ────────────────────────────────────────────────────
console.log( '\nSEO' );
for ( const [ path, lang, other ] of [
	[ '/projetos/galaxy-academy/', 'pt-BR', '/en/projects/galaxy-academy/' ],
	[ '/en/projects/galaxy-academy/', 'en', '/projetos/galaxy-academy/' ],
] ) {
	const { html } = await get( path );
	check(
		`${ path }: <html lang="${ lang }">`,
		html.includes( `<html lang="${ lang }"` )
	);
	check(
		`${ path }: canonical aponta para ela mesma`,
		attr( html, /<link rel="canonical" href="([^"]+)"/ ) === BASE + path &&
			( html.match( /rel="canonical"/g ) || [] ).length === 1
	);
	check(
		`${ path }: hreflang para a outra versão`,
		html.includes( `href="${ BASE + other }"` ) &&
			html.includes( 'hreflang="x-default"' )
	);
	check(
		`${ path }: título e meta description`,
		/<title>[^<]{10,}<\/title>/.test( html ) &&
			/<meta name="description" content="[^"]{40,}"/.test( html )
	);
	check(
		`${ path }: Open Graph com imagem`,
		html.includes( 'property="og:image"' ) &&
			html.includes( 'property="og:locale"' )
	);
	const ld = jsonLd( html );
	const types = ( ld?.[ '@graph' ] || [] ).map( ( n ) => n[ '@type' ] );
	check(
		`${ path }: JSON-LD válido com SoftwareApplication e BreadcrumbList`,
		types.includes( 'SoftwareApplication' ) &&
			types.includes( 'BreadcrumbList' ) &&
			types.includes( 'Organization' ),
		types.join( ',' )
	);
	check(
		`${ path }: exatamente um <h1>`,
		( html.match( /<h1[\s>]/g ) || [] ).length === 1
	);
}
{
	const { html } = await get( '/' );
	check(
		'home: título da home vem do painel',
		/<title>Criação de sites WordPress, temas e plugins · Rolck Galaxy<\/title>/.test(
			html
		)
	);
	check(
		'home: sem script de emoji e sem gerador do WordPress',
		! html.includes( 'wp-emoji' ) && ! html.includes( 'name="generator"' )
	);
	check(
		'home: nenhum CSS bloqueando a renderização',
		! html.includes( "rel='stylesheet'" )
	);
	check(
		'home: fonte pré-carregada',
		html.includes( 'rel="preload"' ) && html.includes( 'inter-latin.woff2' )
	);
	const search = await get( '/?s=galaxy' );
	check(
		'busca interna: noindex',
		/<meta name='robots' content='[^']*noindex/.test( search.html )
	);
	const sitemap = await get( '/wp-sitemap-posts-rolck_project-1.xml' );
	check(
		'sitemap tem projetos nos dois idiomas',
		sitemap.html.includes( '/projetos/galaxy-academy/' ) &&
			sitemap.html.includes( '/en/projects/galaxy-academy/' )
	);
	const users = await get( '/wp-sitemap-users-1.xml' );
	check( 'sitemap não expõe usuários', users.status === 404 );

	const graph = ( page ) =>
		( jsonLd( page )?.[ '@graph' ] || [] ).reduce(
			( all, node ) => ( { ...all, [ node[ '@type' ] ]: node } ),
			{}
		);
	const home = graph( html );
	check(
		'home: perguntas frequentes viram FAQPage com pergunta e resposta',
		home.FAQPage?.mainEntity?.length >= 3 &&
			home.FAQPage.mainEntity.every(
				( q ) => q.name && q.acceptedAnswer?.text
			),
		JSON.stringify( home.FAQPage?.mainEntity?.[ 0 ] || {} )
	);
	check(
		'home: empresa informa as tecnologias que domina (knowsAbout)',
		home.Organization?.knowsAbout?.includes( 'WordPress' )
	);

	const services = await get( '/servicos/' );
	check(
		'serviços: título de SEO com o que as pessoas pesquisam',
		services.html.includes(
			'<title>Criação de sites WordPress e plugins sob medida · Rolck Galaxy</title>'
		)
	);
	const cards = await get( '/en/projects/galaxy-cards/' );
	check(
		'Galaxy Cards: título de SEO em inglês e categoria Educação no schema',
		cards.html.includes(
			'<title>Galaxy Cards: English vocabulary flashcards · Rolck Galaxy</title>'
		) &&
			graph( cards.html ).SoftwareApplication?.applicationCategory ===
				'EducationalApplication'
	);
	check(
		'capas dos projetos com texto alternativo da biblioteca',
		/<img [^>]*class="rolck-card__image[^"]*"[^>]*alt="[^"]+"/.test(
			html
		) || /<img [^>]*alt="[^"]+"[^>]*class="rolck-card__image/.test( html )
	);

	const post = await get( '/site-wordpress-sem-plugins/' );
	const author = graph( post.html ).BlogPosting?.author || {};
	check(
		'post: o login do autor não aparece nos dados estruturados',
		author[ '@id' ]?.endsWith( '#organization' ) ||
			( author.name && author.name !== 'admin' ),
		JSON.stringify( author )
	);

	const tech = await get( '/tech/react/' );
	check(
		'páginas de tecnologia: noindex e fora do sitemap',
		/<meta name='robots' content='[^']*noindex/.test( tech.html ) &&
			! ( await get( '/wp-sitemap.xml' ) ).html.includes(
				'taxonomies-rolck_tech'
			)
	);
	const extra = await get( '/wp-sitemap-rolck-archives-1.xml' );
	const terms = await get( '/wp-sitemap-rolck-terms-1.xml' );
	check(
		'sitemap inclui a vitrine e as categorias em inglês',
		extra.html.includes( `${ BASE }/projetos/</loc>` ) &&
			extra.html.includes( `${ BASE }/en/projects/</loc>` ) &&
			terms.html.includes( '/en/project-category/web-apps/' ),
		extra.status + ' ' + terms.status
	);
	check(
		'sem link para o feed de comentários (comentários fechados)',
		! html.includes( 'comments/feed' )
	);
}

// ── 3. Navegador ────────────────────────────────────────────────────────
const browser = await launch();
const errors = [];
const page = await browser.newPage( {
	viewport: { width: 1280, height: 900 },
} );
page.on( 'pageerror', ( e ) => errors.push( e.message ) );
page.on( 'console', ( m ) => m.type() === 'error' && errors.push( m.text() ) );

console.log( '\nIdiomas' );
await page.goto( BASE + '/projetos/galaxy-academy/' );
const pill = () =>
	page.locator( '.rolck-header .rolck-lang__list' ).evaluate( ( list ) => ( {
		ready: list.classList.contains( 'is-ready' ),
		x: list.style.getPropertyValue( '--rolck-lang-x' ),
	} ) );
const before = await pill();
await page.locator( '.rolck-header .rolck-lang__link[hreflang="en"]' ).click();
const during = await pill();
check(
	'pílula do seletor desliza até o idioma clicado antes de trocar a página',
	before.ready && during.x && during.x !== before.x,
	JSON.stringify( { before, during } )
);
await page.waitForURL( BASE + '/en/projects/galaxy-academy/' );
check(
	'seletor PT → EN abre a versão traduzida do projeto',
	page.url() === BASE + '/en/projects/galaxy-academy/',
	page.url()
);
check(
	'menu e textos da página em inglês',
	( await page.locator( '.rolck-header' ).innerText() ).includes(
		'Projects'
	) &&
		( await page.locator( '.rolck-details' ).first().innerText() ).includes(
			'Coming soon'
		)
);
check(
	'categoria traduzida no breadcrumb',
	( await page.locator( '.rolck-breadcrumbs' ).first().innerText() ).includes(
		'WordPress themes'
	)
);
await page
	.locator( '.rolck-header .rolck-lang__link[hreflang="pt-BR"]' )
	.click();
await page.waitForURL( BASE + '/projetos/galaxy-academy/' );
check(
	'seletor EN → PT volta para a versão em português',
	page.url() === BASE + '/projetos/galaxy-academy/',
	page.url()
);

check(
	'busca usa os textos do painel no idioma da página',
	/wp-block-search__button[^>]*>\s*Buscar\s*</.test(
		( await get( '/?s=zzzxx' ) ).html
	) &&
		/wp-block-search__button[^>]*>\s*Search\s*</.test(
			( await get( '/en/?s=zzzxx' ) ).html
		)
);

check(
	'título da vitrine em inglês na aba do navegador',
	( await get( '/en/projects/' ) ).html.includes(
		'<title>WordPress themes and web apps with live demos · Rolck Galaxy</title>'
	)
);

console.log( '\nVitrine' );
await page.goto( BASE + '/projetos/' );
const cards = page.locator( '.rolck-projects__grid .rolck-card' );
const total = await cards.count();
await page
	.locator( '.rolck-filters__item', { hasText: 'Temas WordPress' } )
	.click();
await page.waitForTimeout( 300 );
const visible = await page
	.locator( '.rolck-projects__item:not([hidden])' )
	.count();
check(
	'filtro por categoria esconde os outros projetos sem recarregar',
	total === 3 && visible === 1 && page.url() === BASE + '/projetos/',
	`${ visible }/${ total } ${ page.url() }`
);
check(
	'filtro ativo marcado com aria-current',
	(
		await page
			.locator( '.rolck-filters__item[aria-current="true"]' )
			.innerText()
	).includes( 'Temas WordPress' )
);
await page.locator( '.rolck-filters__item', { hasText: 'Todos' } ).click();
check(
	'"Todos" mostra todos de novo',
	( await page.locator( '.rolck-projects__item:not([hidden])' ).count() ) ===
		total
);

check(
	'cartões em itens de lista válidos (<article> sem role="listitem")',
	( await page
		.locator(
			'.rolck-projects__item[role="listitem"] > article.rolck-card'
		)
		.count() ) === total &&
		( await page.locator( 'article[role]' ).count() ) === 0
);

console.log( '\nFormulário de contato' );
await page.goto( BASE + '/contato/' );
// Campos dentro do cartão, com respiro e sem encostar uns nos outros.
const formLayout = await page.evaluate( () => {
	const card = document
		.querySelector( '.rolck-contact' )
		.closest( '.is-style-glass' )
		.getBoundingClientRect();
	const boxes = [
		...document.querySelectorAll(
			'.rolck-contact input:not([name="website"]), .rolck-contact textarea'
		),
	].map( ( el ) => el.getBoundingClientRect() );
	const inset = Math.min(
		...boxes.map( ( b ) =>
			Math.min( b.left - card.left, card.right - b.right )
		)
	);
	const overlap = boxes.some( ( a, i ) =>
		boxes.some(
			( b, j ) =>
				i < j &&
				a.left < b.right &&
				b.left < a.right &&
				a.top < b.bottom &&
				b.top < a.bottom
		)
	);
	return { inset: Math.round( inset ), overlap };
} );
check(
	'campos do formulário com margem interna e sem sobreposição',
	formLayout.inset >= 16 && ! formLayout.overlap,
	JSON.stringify( formLayout )
);
await page.locator( '.rolck-contact button[type="submit"]' ).click();
check(
	'envio vazio mostra erro sem sair da página',
	( await page.locator( '.rolck-contact__feedback' ).innerText() ).includes(
		'Preencha'
	)
);
await page.waitForTimeout( 3200 );
await page.fill( '.rolck-contact input[name="name"]', 'Teste Automatizado' );
await page.fill( '.rolck-contact input[name="email"]', 'teste@example.com' );
await page.fill(
	'.rolck-contact textarea[name="message"]',
	'Mensagem enviada pelo teste automatizado.'
);
await page.locator( '.rolck-contact button[type="submit"]' ).click();
await page.waitForSelector(
	'.rolck-contact__feedback.is-success, .rolck-contact__feedback.is-error',
	{ timeout: 15000 }
);
check(
	'envio válido mostra a mensagem de sucesso do painel',
	( await page.locator( '.rolck-contact__feedback' ).innerText() ).includes(
		'Mensagem enviada'
	)
);

console.log( '\nPainel' );
const admin = await browser.newPage( {
	viewport: { width: 1440, height: 1000 },
} );
admin.on( 'pageerror', ( e ) => errors.push( 'admin: ' + e.message ) );
await admin.goto( BASE + '/wp-login.php' );
await admin.fill( '#user_login', credentials.user );
await admin.fill( '#user_pass', credentials.pass );
await admin.click( '#wp-submit' );
await admin.waitForLoadState( 'networkidle' );

await admin.goto( BASE + '/wp-admin/edit.php?post_type=rolck_message' );
check(
	'mensagem do formulário guardada em Rolck Galaxy → Mensagens',
	( await admin.locator( '#the-list' ).innerText() ).includes(
		'Teste Automatizado'
	)
);

await admin.goto( BASE + '/wp-admin/admin.php?page=rolck-galaxy#texts' );
await admin.waitForSelector( '.rolck-admin__string' );
await admin.locator( '.rolck-admin__search input' ).fill( 'Todos' );
await admin.waitForTimeout( 300 );
const allRow = admin
	.locator( '.rolck-admin__string' )
	.filter( { has: admin.locator( 'legend', { hasText: /^Todos$/ } ) } )
	.first();
const field = allRow.locator( 'input' ).nth( 1 );
await field.fill( 'Everything' );
await admin.getByRole( 'button', { name: 'Salvar alterações' } ).click();
await admin.waitForSelector( '.components-notice.is-success' );
check(
	'texto editado em "Textos do site" aparece no site em inglês',
	( await get( '/en/projects/' ) ).html.includes( 'Everything' )
);
await allRow.getByRole( 'button', { name: 'Restaurar o padrão' } ).click();
await admin.getByRole( 'button', { name: 'Salvar alterações' } ).click();
await admin.waitForSelector( '.components-notice.is-success' );
check(
	'"Restaurar o padrão" volta o texto original',
	( await get( '/en/projects/' ) ).html.includes( '>\n\t\t\t\tAll' ) ||
		/class="rolck-filters__item"[^>]*>\s*All/.test(
			( await get( '/en/projects/' ) ).html
		)
);

// Tradução pelo editor: cria uma página só em português e gera a versão em inglês pelo painel.
const created = await admin
	.evaluate( async () => {
		const headers = {
			'Content-Type': 'application/json',
			'X-WP-Nonce': window.wpApiSettings?.nonce,
		};
		const pageRes = await fetch( '/wp-json/wp/v2/pages', {
			method: 'POST',
			headers,
			body: JSON.stringify( {
				title: 'Página de teste',
				status: 'publish',
				content:
					'<!-- wp:paragraph --><p>Olá</p><!-- /wp:paragraph -->',
			} ),
		} );
		return pageRes.json();
	} )
	.catch( () => null );
if ( created?.id ) {
	await admin.goto(
		`${ BASE }/wp-admin/post.php?post=${ created.id }&action=edit`
	);
	await admin.waitForSelector(
		'iframe[name="editor-canvas"], .editor-styles-wrapper',
		{ timeout: 60000 }
	);
	await admin.waitForTimeout( 1500 );
	// Fecha o guia de boas-vindas (aparece na 1ª vez) e abre a barra lateral do documento.
	await admin.evaluate( () => {
		window.wp.data
			.dispatch( 'core/preferences' )
			.set( 'core/edit-post', 'welcomeGuide', false );
		window.wp.data
			.dispatch( 'core/edit-post' )
			.openGeneralSidebar( 'edit-post/document' );
	} );
	await admin.keyboard.press( 'Escape' );
	const createButton = admin.getByRole( 'button', {
		name: 'Criar versão em English',
	} );
	if ( ! ( await createButton.isVisible().catch( () => false ) ) ) {
		await admin
			.getByRole( 'button', { name: 'Idioma e tradução' } )
			.click();
	}
	const [ popup ] = await Promise.all( [
		admin.waitForEvent( 'popup', { timeout: 20000 } ).catch( () => null ),
		createButton.click(),
	] );
	check(
		'botão "Criar versão em English" abre o rascunho traduzido',
		!! popup && popup.url().includes( 'post.php' ),
		popup?.url()
	);
	const list = await admin.evaluate(
		async ( id ) =>
			(
				await fetch( `/wp-json/rolck/v1/translations/${ id }`, {
					headers: { 'X-WP-Nonce': window.wpApiSettings?.nonce },
				} )
			).json(),
		created.id
	);
	check(
		'versões ficam ligadas (pt ↔ en)',
		list?.translations?.en?.status === 'draft',
		JSON.stringify( list )
	);
	await admin.evaluate( async ( ids ) => {
		for ( const id of ids ) {
			await fetch( `/wp-json/wp/v2/pages/${ id }?force=true`, {
				method: 'DELETE',
				headers: { 'X-WP-Nonce': window.wpApiSettings?.nonce },
			} );
		}
	}, [ created.id, list?.translations?.en?.id ].filter( Boolean ) );
	popup?.close();
} else {
	check( 'criação de página de teste pela REST API', false );
}

// ── Tradução automática (tradutor de teste: prefixa "[EN] ", sem API) ──
console.log( '\nTradução automática' );
const api = ( path, method = 'GET', body ) =>
	admin.evaluate(
		async ( [ p, m, b ] ) => {
			const res = await fetch( '/wp-json' + p, {
				method: m,
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': window.wpApiSettings?.nonce,
				},
				body: b ? JSON.stringify( b ) : undefined,
			} );
			return {
				status: res.status,
				body: await res.json().catch( () => null ),
			};
		},
		[ path, method, body ]
	);
const saveSettings = async ( change ) => {
	const current = ( await api( '/wp/v2/settings' ) ).body.rolck_settings;
	change( current );
	return api( '/wp/v2/settings', 'POST', { rolck_settings: current } );
};

await saveSettings( ( s ) => {
	s.translation.provider = 'fake';
} );

// Botão "Testar a tradução" na aba Idiomas.
await admin.goto( BASE + '/wp-admin/admin.php?page=rolck-galaxy#languages' );
await admin.getByRole( 'button', { name: 'Testar a tradução' } ).click();
await admin.waitForSelector( '.rolck-admin__auto .components-notice' );
check(
	'botão "Testar a tradução" mostra o texto traduzido',
	(
		await admin
			.locator( '.rolck-admin__auto .components-notice' )
			.first()
			.innerText()
	).includes( '[EN] Olá' )
);

// Publicar em português cria a versão em inglês sozinha.
const pt = await api( '/wp/v2/pages', 'POST', {
	title: 'Página automática',
	status: 'publish',
	excerpt: 'Resumo da página',
	content:
		'<!-- wp:paragraph --><p>Conheça a <a href="/sobre/">Rolck Galaxy</a> e os <a href="/projetos/">projetos</a>.</p><!-- /wp:paragraph -->',
	meta: { _rolck_seo_description: 'Descrição para o Google' },
} );
const ptId = pt.body?.id;
let group = ( await api( `/rolck/v1/translations/${ ptId }` ) ).body;
const en = group?.translations?.en;
check(
	'publicar em português cria a versão em inglês publicada e automática',
	en?.status === 'publish' && en?.auto && ! en?.manual,
	JSON.stringify( en )
);
const enPage = en ? await get( new URL( en.link ).pathname ) : { html: '' };
check(
	'título, texto e slug traduzidos',
	en?.title.startsWith( '[EN]' ) &&
		enPage.html.includes( '[EN] Conheça' ) &&
		en.link.includes( '/en/en-pagina-automatica/' ),
	en?.link
);
check(
	'links internos apontam para as versões em inglês',
	enPage.html.includes( 'href="/en/about/"' ) &&
		enPage.html.includes( 'href="/en/projects/"' )
);
check(
	'nome da marca não é traduzido (glossário)',
	enPage.html.includes( '>Rolck Galaxy</a>' )
);
check(
	'descrição de SEO traduzida',
	enPage.html.includes( 'content="[EN] Descrição para o Google"' )
);

// Atualizar o original atualiza a tradução.
await api( `/wp/v2/pages/${ ptId }`, 'POST', {
	content:
		'<!-- wp:paragraph --><p>Texto novo do original.</p><!-- /wp:paragraph -->',
} );
check(
	'atualizar o original atualiza a versão em inglês',
	( await get( new URL( en.link ).pathname ) ).html.includes(
		'[EN] Texto novo do original.'
	)
);

// Editar a versão em inglês à mão: ela deixa de ser sobrescrita.
await api( `/wp/v2/pages/${ en.id }`, 'POST', {
	content:
		'<!-- wp:paragraph --><p>Hand-written English.</p><!-- /wp:paragraph -->',
} );
await api( `/wp/v2/pages/${ ptId }`, 'POST', {
	content:
		'<!-- wp:paragraph --><p>Mais uma mudança.</p><!-- /wp:paragraph -->',
} );
group = ( await api( `/rolck/v1/translations/${ ptId }` ) ).body;
check(
	'versão editada à mão vira manual e não é sobrescrita',
	group.translations.en.manual &&
		( await get( new URL( en.link ).pathname ) ).html.includes(
			'Hand-written English.'
		)
);
await api( `/rolck/v1/translations/${ en.id }/auto`, 'POST', {
	action: 'resume',
} );
check(
	'"Voltar ao automático" traduz de novo a partir do original',
	( await get( new URL( en.link ).pathname ) ).html.includes(
		'[EN] Mais uma mudança.'
	)
);

// Categoria nova ganha o nome em inglês.
const term = await api( '/wp/v2/rolck_project_type', 'POST', {
	name: 'Plugins premium',
} );
const termData = ( await api( `/wp/v2/rolck_project_type/${ term.body?.id }` ) )
	.body;
check(
	'categoria nova ganha o nome em inglês sozinha',
	termData?.meta?.rolck_name_en === '[EN] Plugins premium',
	JSON.stringify( termData?.meta )
);

// Texto do painel: mudou o português, o inglês acompanha.
await saveSettings( ( s ) => {
	s.strings.filter_all.pt = 'Tudo';
} );
let settings = ( await api( '/wp/v2/settings' ) ).body.rolck_settings;
check(
	'texto do painel alterado em português é traduzido para o inglês',
	settings.strings.filter_all.en === '[EN] Tudo',
	settings.strings.filter_all.en
);

// Cabeçalho editado no Editor do site: o cabeçalho em inglês acompanha.
const header = (
	await api( '/wp/v2/template-parts/rolck-galaxy//header?context=edit' )
).body;
await api( '/wp/v2/template-parts/rolck-galaxy//header', 'POST', {
	content: header.content.raw.replace( 'Fale conosco', 'Fale com a gente' ),
} );
check(
	'cabeçalho editado em português atualiza o cabeçalho em inglês',
	( await get( '/en/' ) ).html.includes( '[EN] Fale com a gente' )
);

// Chamada final (parte de modelo) editada em português: o inglês acompanha.
const cta = (
	await api( '/wp/v2/template-parts/rolck-galaxy//cta?context=edit' )
).body;
await api( '/wp/v2/template-parts/rolck-galaxy//cta', 'POST', {
	content: cta.content.raw.replace( 'Vamos conversar', 'Bora conversar' ),
} );
const ctaPt = ( await get( '/projetos/galaxy-academy/' ) ).html;
const ctaEn = ( await get( '/en/projects/galaxy-academy/' ) ).html;
check(
	'chamada final editada em português atualiza a versão em inglês',
	ctaPt.includes( 'Bora conversar' ) &&
		ctaEn.includes( '[EN] Bora conversar' ),
	JSON.stringify( {
		pt: ( ctaPt.match( /[^>]{0,20}conversar[^<]{0,20}/g ) || [] ).slice(
			0,
			3
		),
		en: (
			ctaEn.match( /[^>]{0,30}(talk|conversar)[^<]{0,20}/gi ) || []
		).slice( 0, 3 ),
	} )
);

// Limpeza: volta tudo ao estado de antes do teste.
await api( '/wp/v2/template-parts/rolck-galaxy//header', 'DELETE' );
await api( '/wp/v2/template-parts/rolck-galaxy//header-en', 'DELETE' );
await api( '/wp/v2/template-parts/rolck-galaxy//cta', 'DELETE' );
await api( '/wp/v2/template-parts/rolck-galaxy//cta-en', 'DELETE' );
await api( `/wp/v2/pages/${ en.id }?force=true`, 'DELETE' );
await api( `/wp/v2/pages/${ ptId }?force=true`, 'DELETE' );
await api(
	`/wp/v2/rolck_project_type/${ term.body?.id }?force=true`,
	'DELETE'
);
await saveSettings( ( s ) => {
	s.translation.provider = '';
	s.strings.filter_all = { pt: 'Todos', en: 'All' };
} );
settings = ( await api( '/wp/v2/settings' ) ).body.rolck_settings;
check(
	'limpeza do teste (cabeçalho, páginas, categoria e textos restaurados)',
	settings.strings.filter_all.en === 'All' &&
		( await get( '/en/' ) ).html.includes( 'Get in touch' )
);

// ── Privacidade (LGPD) ──
console.log( '\nAviso de cookies (LGPD)' );
await saveSettings( ( s ) => {
	s.integrations.ga4_id = '';
} );
const visitor = async () => {
	const ctx = await browser.newContext( {
		viewport: { width: 390, height: 844 },
	} );
	return { ctx, page: await ctx.newPage() };
};
const isOpen = ( p ) =>
	p.evaluate( () => document.getElementById( 'rolck-consent-prefs' ).open );

let v = await visitor();
await v.page.goto( BASE + '/' );
check(
	'aviso aparece na primeira visita, já na primeira pintura (sem esperar JavaScript)',
	( await v.page.isVisible( '#rolck-consent' ) ) &&
		! ( await get( '/' ) ).html.match( /id="rolck-consent"[^>]*hidden/ )
);
check(
	'sem integrações, o aviso diz que só há cookies necessários e não mostra categorias opcionais',
	( await v.page.locator( '#rolck-consent' ).innerText() ).includes(
		'somente cookies necessários'
	) && ( await v.page.locator( '.rolck-consent-cat__switch' ).count() ) === 0
);
const [ logged ] = await Promise.all( [
	v.page.waitForResponse( ( r ) => r.url().includes( 'rolck/v1/consent' ) ),
	v.page.click( '#rolck-consent [data-consent="accept"]' ),
] );
const firstId = JSON.parse(
	decodeURIComponent(
		( await v.ctx.cookies() ).find( ( c ) => c.name === 'rolck_consent' )
			?.value || '{}'
	)
).id;
check(
	'"Entendi" guarda a escolha e registra a prova no servidor',
	logged.status() === 201 && /^[0-9a-f-]{36}$/.test( firstId || '' ),
	String( logged.status() )
);
await v.page.goto( BASE + '/en/' );
check(
	'quem já escolheu não vê o aviso de novo (nem no outro idioma)',
	! ( await v.page.isVisible( '#rolck-consent' ) )
);
await v.page.locator( 'a[href$="#cookies"]' ).first().click();
check(
	'"Cookie preferences" no rodapé reabre as preferências com o código do registro',
	( await isOpen( v.page ) ) &&
		(
			await v.page.locator( '[data-consent-record]' ).innerText()
		).includes( firstId )
);
await v.ctx.close();

// Com Google Analytics ligado, ele só roda depois do aceite.
await saveSettings( ( s ) => {
	s.integrations.ga4_id = 'G-TESTE123';
} );
v = await visitor();
await v.page.goto( BASE + '/sobre/' );
const gaState = () =>
	v.page.evaluate( () => ( {
		inert: !! document.querySelector(
			'script[type="text/plain"][data-rolck-consent="analytics"]'
		),
		running: Array.isArray( window.dataLayer ),
	} ) );
let ga = await gaState();
check(
	'com Analytics ligado: script fica inativo antes do consentimento',
	ga.inert && ! ga.running,
	JSON.stringify( ga )
);
check(
	'"Rejeitar opcionais" e "Aceitar todos" lado a lado, com o mesmo estilo',
	await v.page.evaluate( () => {
		const [ a, b ] = [ 'reject', 'accept' ].map( ( k ) =>
			window.getComputedStyle(
				document.querySelector(
					`#rolck-consent [data-consent="${ k }"]`
				)
			)
		);
		return a.backgroundImage === b.backgroundImage && a.color === b.color;
	} )
);
await v.page.click( '#rolck-consent [data-consent="reject"]' );
ga = await gaState();
check( 'rejeitando, o Analytics continua sem rodar', ! ga.running );
await v.page.locator( 'a[href$="#cookies"]' ).first().click();
await v.page.locator( '.rolck-consent-cat__switch[name="analytics"]' ).check();
await v.page.click( '#rolck-consent-prefs [data-consent="save"]' );
ga = await gaState();
check(
	'aceitando Estatística nas preferências, o Analytics passa a rodar sem recarregar',
	ga.running
);
const consentId = JSON.parse(
	decodeURIComponent(
		( await v.ctx.cookies() ).find( ( c ) => c.name === 'rolck_consent' )
			?.value || '{}'
	)
).id;
await v.page.locator( 'a[href$="#cookies"]' ).first().click();
await v.page
	.locator( '.rolck-consent-cat__switch[name="analytics"]' )
	.uncheck();
await Promise.all( [
	v.page.waitForEvent( 'load' ),
	v.page.click( '#rolck-consent-prefs [data-consent="save"]' ),
] );
ga = await gaState();
check(
	'retirar o consentimento recarrega a página sem o Analytics',
	! ga.running && ga.inert
);
await v.ctx.close();

await saveSettings( ( s ) => {
	s.integrations.ga4_id = '';
} );
await admin.goto(
	`${ BASE }/wp-admin/admin.php?page=rolck-consents&consent=${ consentId }`
);
const logText = await admin
	.locator( '.wp-list-table, table.widefat' )
	.innerText();
check(
	'registro de consentimentos no painel mostra as três escolhas do visitante',
	logText.includes( 'Rejeitou os opcionais' ) &&
		logText.includes( 'Aceitou todos' ) &&
		( logText.match( new RegExp( consentId, 'g' ) ) || [] ).length === 3,
	logText.slice( 0, 300 )
);
await admin.goto( BASE + '/wp-admin/admin.php?page=rolck-galaxy' );
const patterns = await api( '/wp/v2/block-patterns/patterns' );
check(
	'padrão "Política de privacidade (LGPD)" disponível, com a tabela de cookies automática',
	( patterns.body || [] ).some(
		( p ) =>
			p.name === 'rolck-galaxy/privacy-policy' &&
			p.content.includes( 'wp:rolck/cookie-table' )
	)
);

// Blocos válidos no editor (nenhum aviso de "conteúdo inesperado").
const pages = await admin.evaluate( async () =>
	(
		await fetch( '/wp-json/wp/v2/pages?per_page=50&_fields=id,slug', {
			headers: { 'X-WP-Nonce': window.wpApiSettings?.nonce },
		} )
	).json()
);
let invalid = 0;
for ( const p of pages ) {
	await admin.goto(
		`${ BASE }/wp-admin/post.php?post=${ p.id }&action=edit`
	);
	await admin.waitForSelector(
		'iframe[name="editor-canvas"], .editor-styles-wrapper',
		{ timeout: 60000 }
	);
	await admin.waitForTimeout( 1500 );
	const frame = admin.frame( { name: 'editor-canvas' } ) || admin.mainFrame();
	invalid += await frame.locator( '.block-editor-warning' ).count();
}
check(
	`${ pages.length } páginas abrem no editor sem nenhum bloco inválido`,
	invalid === 0,
	`${ invalid } inválidos`
);

// Modelos e partes válidos no Editor do site.
const templates = [
	[ 'wp_template_part', 'header' ],
	[ 'wp_template_part', 'header-en' ],
	[ 'wp_template_part', 'footer' ],
	[ 'wp_template_part', 'footer-en' ],
	[ 'wp_template_part', 'cta' ],
	[ 'wp_template_part', 'cta-en' ],
	[ 'wp_template', 'front-page' ],
	[ 'wp_template', 'page' ],
	[ 'wp_template', 'page-landing' ],
	[ 'wp_template', 'single' ],
	[ 'wp_template', 'home' ],
	[ 'wp_template', 'archive' ],
	[ 'wp_template', 'search' ],
	[ 'wp_template', '404' ],
	[ 'wp_template', 'single-rolck_project' ],
	[ 'wp_template', 'archive-rolck_project' ],
	[ 'wp_template', 'taxonomy-rolck_project_type' ],
];
let badTemplates = 0;
for ( const [ type, slug ] of templates ) {
	await admin.goto(
		`${ BASE }/wp-admin/site-editor.php?p=${ encodeURIComponent( `/${ type }/rolck-galaxy//${ slug }` ) }&canvas=edit`
	);
	await admin.waitForSelector( 'iframe[name="editor-canvas"]', {
		timeout: 60000,
	} );
	await admin.waitForTimeout( 2500 );
	badTemplates += await admin
		.frame( { name: 'editor-canvas' } )
		.locator( '.block-editor-warning' )
		.count();
}
check(
	`${ templates.length } modelos e partes abrem no Editor do site sem bloco inválido`,
	badTemplates === 0,
	`${ badTemplates } inválidos`
);

// ── 4. Layout em celulares e tablets ────────────────────────────────────
console.log( '\nLayout (sem rolagem horizontal)' );
const widths = [ 320, 360, 390, 414, 768, 1024, 1366, 1920 ];
const layoutPages = [
	'/',
	'/en/',
	'/projetos/',
	'/projetos/galaxy-academy/',
	'/blog/',
	'/contato/',
	'/site-wordpress-sem-plugins/',
];
const overflow = [];
for ( const width of widths ) {
	const view = await browser.newPage( { viewport: { width, height: 900 } } );
	for ( const path of layoutPages ) {
		await view.goto( BASE + path, { waitUntil: 'domcontentloaded' } );
		const extra = await view.evaluate(
			() => document.documentElement.scrollWidth - window.innerWidth
		);
		if ( extra > 1 ) {
			overflow.push( `${ path } @${ width }px (+${ extra }px)` );
		}
	}
	await view.close();
}
check(
	`${ widths.length } larguras × ${ layoutPages.length } páginas sem rolagem horizontal`,
	overflow.length === 0,
	overflow.join( ', ' )
);

check(
	'nenhum erro de JavaScript no site e no painel',
	errors.length === 0,
	[ ...new Set( errors ) ].slice( 0, 5 ).join( ' | ' )
);

await browser.close();
console.log( `\n${ passed } passaram, ${ failed } falharam\n` );
process.exit( failed ? 1 : 0 );
