import { Button, Notice } from '@wordpress/components';
import { useCallback, useEffect, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { LongText, Section, Select, Text, Toggle } from '../fields';
import { useSettings } from '../context';

const PROVIDERS = {
	google: {
		key: 'translation.google_key',
		label: 'Chave da API do Google Cloud',
		help: 'console.cloud.google.com → ative a "Cloud Translation API" → Credenciais → Criar chave de API. Cerca de US$ 20 por milhão de caracteres (os primeiros 500 mil por mês são gratuitos).',
	},
	deepl: {
		key: 'translation.deepl_key',
		label: 'Chave da API do DeepL',
		help: 'deepl.com/pro-api → plano gratuito (500 mil caracteres por mês) ou pago. Chaves do plano gratuito terminam em ":fx".',
	},
	claude: {
		key: 'translation.claude_key',
		label: 'Chave da API da Anthropic',
		help: 'console.anthropic.com → API Keys. Usa o modelo Claude Opus 5.5: traduz o sentido dos textos de venda, não palavra por palavra. Cobrado por uso.',
	},
};

function AutoTranslation() {
	const { get, boot } = useSettings();
	const provider = get( 'translation.provider' );
	const [ test, setTest ] = useState( null );
	const [ pending, setPending ] = useState( null );
	const [ running, setRunning ] = useState( false );
	const [ error, setError ] = useState( '' );

	const loadPending = useCallback( () => {
		apiFetch( { path: '/rolck/v1/auto/pending' } )
			.then( setPending )
			.catch( () => setPending( null ) );
	}, [] );
	useEffect( loadPending, [ loadPending ] );

	const runTest = async () => {
		setTest( { status: 'loading' } );
		try {
			const res = await apiFetch( {
				path: '/rolck/v1/auto/test',
				method: 'POST',
			} );
			setTest( { status: 'success', text: res.result } );
		} catch ( e ) {
			setTest( { status: 'error', text: e.message } );
		}
	};

	const runPending = async () => {
		setRunning( true );
		setError( '' );
		try {
			let report = await apiFetch( {
				path: '/rolck/v1/auto/pending',
				method: 'POST',
			} );
			setPending( report );
			while ( report.count > 0 ) {
				report = await apiFetch( {
					path: '/rolck/v1/auto/pending',
					method: 'POST',
				} );
				setPending( report );
			}
		} catch ( e ) {
			setError( e.message );
		} finally {
			setRunning( false );
		}
	};

	const other = boot.languages
		.filter( ( l ) => l.code !== get( 'languages.default' ) )
		.map( ( l ) => l.label )
		.join( ', ' );

	return (
		<Section
			title="Tradução automática"
			description={ `Você escreve só em ${ boot.languages.find( ( l ) => l.code === get( 'languages.default' ) )?.label }. Ao publicar ou atualizar, a versão em ${ other } é criada e atualizada sozinha: páginas, posts, projetos, SEO, cabeçalho, rodapé, categorias e os textos deste painel.` }
		>
			<Select
				path="translation.provider"
				label="Serviço de tradução"
				options={ [
					{ label: 'Desligado (traduzo à mão)', value: '' },
					{ label: 'Google Cloud Translation', value: 'google' },
					{ label: 'DeepL', value: 'deepl' },
					{ label: 'Claude (Anthropic)', value: 'claude' },
					...( boot.fakeTranslator
						? [ { label: 'Teste local (sem API)', value: 'fake' } ]
						: [] ),
				] }
			/>
			{ PROVIDERS[ provider ] && (
				<Text
					path={ PROVIDERS[ provider ].key }
					label={ PROVIDERS[ provider ].label }
					help={ PROVIDERS[ provider ].help }
					type="password"
				/>
			) }
			<Toggle
				path="translation.auto_publish"
				label="Publicar a tradução junto com o original"
				help="Desligado, a versão traduzida fica como rascunho para você revisar antes."
			/>
			<LongText
				path="translation.glossary"
				label="Nunca traduzir (separe por vírgula)"
				help="Nomes de marca e produtos que devem ficar iguais nos dois idiomas."
				rows={ 2 }
			/>
			{ provider && (
				<div className="is-wide rolck-admin__auto">
					<p className="rolck-admin__help">
						Salve as configurações antes de testar.
					</p>
					<div className="rolck-admin__auto-row">
						<Button
							variant="secondary"
							onClick={ runTest }
							isBusy={ test?.status === 'loading' }
						>
							Testar a tradução
						</Button>
						{ pending && (
							<Button
								variant="primary"
								onClick={ runPending }
								isBusy={ running }
								disabled={ running || ! pending.count }
							>
								{ pending.count
									? `Traduzir os ${ pending.count } conteúdos que ainda não têm versão`
									: 'Todo o conteúdo publicado já tem tradução' }
							</Button>
						) }
					</div>
					{ test?.status === 'success' && (
						<Notice status="success" isDismissible={ false }>
							{ test.text }
						</Notice>
					) }
					{ test?.status === 'error' && (
						<Notice status="error" isDismissible={ false }>
							{ test.text }
						</Notice>
					) }
					{ error && (
						<Notice
							status="error"
							onRemove={ () => setError( '' ) }
						>
							{ error }
						</Notice>
					) }
					{ pending?.error && (
						<Notice status="warning" isDismissible={ false }>
							Última falha de tradução: { pending.error.message }
						</Notice>
					) }
				</div>
			) }
		</Section>
	);
}

export default function Languages() {
	const { boot, get } = useSettings();
	const def = get( 'languages.default' );
	const other = boot.languages.find( ( l ) => l.code !== def );

	return (
		<>
			<AutoTranslation />

			<Section
				title="Idioma principal"
				description="O idioma principal fica na raiz do site; os outros ganham um prefixo na URL."
			>
				<Select
					path="languages.default"
					label="Idioma principal"
					options={ boot.languages.map( ( l ) => ( {
						label: l.label,
						value: l.code,
					} ) ) }
				/>
				<div className="is-wide">
					<p className="rolck-admin__example">
						{ boot.siteUrl } →{ ' ' }
						{
							boot.languages.find( ( l ) => l.code === def )
								?.label
						}
						<br />
						{ boot.siteUrl }
						{ other?.code }/ → { other?.label }
					</p>
					<Notice status="warning" isDismissible={ false }>
						Trocar o idioma principal muda todas as URLs do site.
						Faça isso só antes de publicar.
					</Notice>
				</div>
			</Section>

			<Section
				title="Como funciona"
				description="Sem plugins: cada página, post e projeto tem uma versão por idioma, ligadas entre si."
			>
				<ul className="rolck-admin__steps is-wide">
					<li>
						<strong>Com a tradução automática ligada:</strong>{ ' ' }
						publique em português e pronto. A versão em inglês usa o
						mesmo layout, imagens e campos, com texto, SEO, slug e
						links internos traduzidos.
					</li>
					<li>
						<strong>Quer ajustar uma tradução?</strong> Edite a
						versão em inglês normalmente. A partir daí ela vira
						&quot;manual&quot; e não é mais sobrescrita. No painel{ ' ' }
						<strong>Idioma e tradução</strong> dá para voltar ao
						automático.
					</li>
					<li>
						<strong>Sem tradução automática:</strong> o botão
						&quot;Criar versão&quot; copia a página para você
						traduzir à mão.
					</li>
					<li>
						O seletor PT | EN, as tags hreflang e o sitemap apontam
						uma versão para a outra.
					</li>
				</ul>
			</Section>
		</>
	);
}
