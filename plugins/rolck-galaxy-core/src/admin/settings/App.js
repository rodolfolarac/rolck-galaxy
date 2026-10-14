/**
 * Carrega as configurações pela REST API nativa (/wp/v2/settings), guarda as
 * alterações num rascunho e grava tudo de uma vez no botão "Salvar".
 *
 * Para criar uma aba: escreva o componente em tabs/ e acrescente em TABS.
 */
import { Button, Notice, Spinner, TabPanel } from '@wordpress/components';
import { useCallback, useEffect, useMemo, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { SettingsContext } from './context';
import General from './tabs/General';
import Languages from './tabs/Languages';
import Seo from './tabs/Seo';
import Projects from './tabs/Projects';
import Contact from './tabs/Contact';
import Texts from './tabs/Texts';
import Performance from './tabs/Performance';
import Integrations from './tabs/Integrations';
import Appearance from './tabs/Appearance';

const boot = window.rolckSettings;

const TABS = [
	{ name: 'general', title: 'Geral', Component: General },
	{ name: 'appearance', title: 'Aparência', Component: Appearance },
	{ name: 'languages', title: 'Idiomas', Component: Languages },
	{ name: 'seo', title: 'SEO', Component: Seo },
	{ name: 'projects', title: 'Projetos', Component: Projects },
	{ name: 'contact', title: 'Contato', Component: Contact },
	{ name: 'texts', title: 'Textos do site', Component: Texts },
	{ name: 'performance', title: 'Desempenho', Component: Performance },
	{ name: 'integrations', title: 'Integrações', Component: Integrations },
];

/**
 * Lê um caminho com pontos ('seo.home_title.pt').
 *
 * @param {Object} obj  Objeto das configurações.
 * @param {string} path Caminho.
 * @return {string|number|boolean|Object} Valor encontrado.
 */
const read = ( obj, path ) =>
	path.split( '.' ).reduce( ( value, key ) => value?.[ key ], obj );

/**
 * Grava um caminho com pontos sem alterar o objeto original.
 *
 * @param {Object}                       obj   Objeto das configurações.
 * @param {string[]}                     keys  Caminho já dividido em chaves.
 * @param {string|number|boolean|Object} value Novo valor.
 * @return {Object} Cópia atualizada.
 */
const write = ( obj, [ key, ...rest ], value ) => ( {
	...obj,
	[ key ]: rest.length ? write( obj?.[ key ] || {}, rest, value ) : value,
} );

export default function App() {
	const [ saved, setSaved ] = useState( null );
	const [ draft, setDraft ] = useState( null );
	const [ status, setStatus ] = useState( 'loading' );
	const [ notice, setNotice ] = useState( null );

	useEffect( () => {
		apiFetch( { path: '/wp/v2/settings' } )
			.then( ( data ) => {
				setSaved( data[ boot.option ] );
				setDraft( data[ boot.option ] );
				setStatus( 'ready' );
			} )
			.catch( ( error ) => {
				setNotice( { status: 'error', text: error.message } );
				setStatus( 'error' );
			} );
	}, [] );

	const dirty = useMemo(
		() => JSON.stringify( saved ) !== JSON.stringify( draft ),
		[ saved, draft ]
	);

	// Avisa antes de sair da página com alterações não salvas.
	useEffect( () => {
		const warn = ( event ) => {
			if ( dirty ) {
				event.preventDefault();
				event.returnValue = '';
			}
		};
		window.addEventListener( 'beforeunload', warn );
		return () => window.removeEventListener( 'beforeunload', warn );
	}, [ dirty ] );

	const save = useCallback( async () => {
		setStatus( 'saving' );
		setNotice( null );
		try {
			const data = await apiFetch( {
				path: '/wp/v2/settings',
				method: 'POST',
				data: { [ boot.option ]: draft },
			} );
			setSaved( data[ boot.option ] );
			setDraft( data[ boot.option ] );
			setNotice( {
				status: 'success',
				text: 'Configurações salvas. O site já está usando os novos valores.',
			} );
		} catch ( error ) {
			setNotice( { status: 'error', text: error.message } );
		} finally {
			setStatus( 'ready' );
		}
	}, [ draft ] );

	// Ctrl/Cmd + S salva.
	useEffect( () => {
		const onKey = ( event ) => {
			if ( ( event.ctrlKey || event.metaKey ) && event.key === 's' ) {
				event.preventDefault();
				if ( dirty ) {
					save();
				}
			}
		};
		window.addEventListener( 'keydown', onKey );
		return () => window.removeEventListener( 'keydown', onKey );
	}, [ dirty, save ] );

	const context = useMemo(
		() => ( {
			boot,
			get: ( path ) => read( draft, path ),
			getDefault: ( path ) => read( boot.defaults, path ),
			set: ( path, value ) =>
				setDraft( ( current ) =>
					write( current, path.split( '.' ), value )
				),
		} ),
		[ draft ]
	);

	if ( status === 'loading' ) {
		return <Spinner />;
	}
	if ( ! draft ) {
		return (
			notice && (
				<Notice status="error" isDismissible={ false }>
					{ notice.text }
				</Notice>
			)
		);
	}

	const initialTab = TABS.some(
		( t ) => t.name === window.location.hash.slice( 1 )
	)
		? window.location.hash.slice( 1 )
		: 'general';

	return (
		<SettingsContext.Provider value={ context }>
			<header className="rolck-admin__header">
				<div>
					<h1>Rolck Galaxy</h1>
					<p>
						Tudo o que o site usa e não está no editor de páginas
						fica aqui. Cores, fontes, cabeçalho e rodapé ficam na
						aba Aparência.
					</p>
				</div>
				<div className="rolck-admin__actions">
					<a
						className="components-button is-tertiary"
						href={ boot.siteUrl }
						target="_blank"
						rel="noreferrer"
					>
						Ver site ↗
					</a>
					<Button
						variant="primary"
						onClick={ save }
						isBusy={ status === 'saving' }
						disabled={ ! dirty || status === 'saving' }
					>
						{ dirty ? 'Salvar alterações' : 'Tudo salvo' }
					</Button>
				</div>
			</header>

			{ notice && (
				<Notice
					status={ notice.status }
					onRemove={ () => setNotice( null ) }
					className="rolck-admin__notice"
				>
					{ notice.text }
				</Notice>
			) }

			<TabPanel
				className="rolck-admin__tabs"
				tabs={ TABS.map( ( { name, title } ) => ( { name, title } ) ) }
				initialTabName={ initialTab }
				onSelect={ ( name ) =>
					window.history.replaceState( null, '', `#${ name }` )
				}
			>
				{ ( tab ) => {
					const { Component } = TABS.find(
						( t ) => t.name === tab.name
					);
					return (
						<div className="rolck-admin__panel">
							<Component />
						</div>
					);
				} }
			</TabPanel>
		</SettingsContext.Provider>
	);
}
