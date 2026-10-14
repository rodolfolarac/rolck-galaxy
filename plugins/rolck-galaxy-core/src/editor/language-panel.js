/**
 * Painel "Idioma e tradução": idioma do conteúdo, versões ligadas e o estado
 * da tradução automática (automática / editada à mão), com os botões de
 * traduzir agora, parar e voltar ao automático. Sem tradução automática,
 * cria uma cópia para traduzir à mão ou liga a um conteúdo existente.
 */
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import {
	Button,
	ComboboxControl,
	ExternalLink,
	Notice,
	SelectControl,
	Spinner,
} from '@wordpress/components';
import { useCallback, useEffect, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { config, languageLabel } from './config';
import { useEditPost, usePost } from './use-post';

const STATUS = {
	publish: 'publicado',
	draft: 'rascunho',
	pending: 'pendente',
	future: 'agendado',
	private: 'privado',
};

export default function LanguagePanel() {
	const post = usePost();
	const { setField } = useEditPost();
	const [ data, setData ] = useState( null );
	const [ busy, setBusy ] = useState( '' );
	const [ error, setError ] = useState( '' );

	const translatable =
		config.translatableTypes.includes( post.type ) &&
		post.type !== 'wp_block';

	const load = useCallback( () => {
		if ( ! post.id || post.isNew ) {
			return;
		}
		apiFetch( { path: `/rolck/v1/translations/${ post.id }` } )
			.then( setData )
			.catch( ( e ) => setError( e.message ) );
	}, [ post.id, post.isNew ] );

	useEffect( load, [ load ] );

	if ( ! translatable ) {
		return null;
	}

	const current = post.lang || config.defaultLanguage;
	const isDefault = current === config.defaultLanguage;
	const others = config.languages.filter( ( l ) => l.code !== current );
	const self = data?.translations?.[ current ];
	const auto = data?.autoEnabled;

	const run = async ( key, request ) => {
		setBusy( key );
		setError( '' );
		try {
			const result = await request();
			if ( result?.translations ) {
				setData( result );
			} else {
				load();
			}
			return result;
		} catch ( e ) {
			setError( e.message );
		} finally {
			setBusy( '' );
		}
	};

	const autoAction = ( action, lang ) =>
		run( `${ action }-${ lang || '' }`, () =>
			apiFetch( {
				path: `/rolck/v1/translations/${ post.id }/auto`,
				method: 'POST',
				data: { action, lang },
			} )
		);

	const create = ( code ) =>
		run( `create-${ code }`, async () => {
			const created = await apiFetch( {
				path: `/rolck/v1/translations/${ post.id }`,
				method: 'POST',
				data: { lang: code },
			} );
			// Sem tradução automática, abre a cópia para traduzir à mão.
			if ( ! auto ) {
				window.open( created.edit, '_blank', 'noopener' );
			}
		} );

	return (
		<PluginDocumentSettingPanel
			name="rolck-language"
			title="Idioma e tradução"
			className="rolck-panel"
		>
			<SelectControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label="Idioma deste conteúdo"
				value={ current }
				options={ config.languages.map( ( l ) => ( {
					label: l.label,
					value: l.code,
				} ) ) }
				onChange={ ( v ) => setField( 'rolck_lang', v ) }
			/>

			{ post.isNew && (
				<p className="rolck-panel__hint">
					{ auto
						? 'Ao publicar, a versão em inglês é criada sozinha.'
						: 'Salve o rascunho para criar ou ligar a tradução.' }
				</p>
			) }
			{ error && (
				<Notice status="error" onRemove={ () => setError( '' ) }>
					{ error }
				</Notice>
			) }
			{ ! post.isNew && ! data && ! error && <Spinner /> }

			{ /* Versão traduzida aberta no editor. */ }
			{ data && ! isDefault && self?.auto && ! self?.manual && (
				<Notice status="info" isDismissible={ false }>
					Traduzida automaticamente de{ ' ' }
					{ languageLabel( config.defaultLanguage ) }. Ela é
					atualizada sempre que o original muda. Se você editar este
					texto, ela passa a ser manual.
				</Notice>
			) }
			{ data && ! isDefault && self?.manual && (
				<Notice status="warning" isDismissible={ false }>
					Esta versão foi editada à mão e não é mais atualizada
					sozinha.
				</Notice>
			) }
			{ data && ! isDefault && auto && (
				<div className="rolck-panel__actions">
					{ self?.manual ? (
						<Button
							variant="secondary"
							isBusy={ busy === 'resume-' }
							disabled={ !! busy }
							onClick={ () => autoAction( 'resume' ) }
						>
							Voltar ao automático (traduzir de novo)
						</Button>
					) : (
						<Button
							variant="tertiary"
							isBusy={ busy === 'stop-' }
							disabled={ !! busy }
							onClick={ () => autoAction( 'stop' ) }
						>
							Parar as atualizações automáticas
						</Button>
					) }
				</div>
			) }

			{ data &&
				others.map( ( lang ) => {
					const t = data.translations?.[ lang.code ];
					return (
						<div className="rolck-panel__lang" key={ lang.code }>
							<strong>{ lang.label }</strong>
							{ t ? (
								<>
									<p>
										<a href={ t.edit }>{ t.title }</a>{ ' ' }
										<span className="rolck-panel__muted">
											({ STATUS[ t.status ] || t.status }
											{ t.auto &&
												! t.manual &&
												' · automática' }
											{ t.manual && ' · editada à mão' })
										</span>
										<br />
										{ t.status === 'publish' && (
											<ExternalLink href={ t.link }>
												Ver no site
											</ExternalLink>
										) }
									</p>
									{ isDefault && auto && (
										<Button
											variant="secondary"
											isBusy={
												busy === `sync-${ lang.code }`
											}
											disabled={ !! busy }
											onClick={ () =>
												autoAction( 'sync', lang.code )
											}
										>
											{ t.manual
												? 'Substituir pela tradução automática'
												: 'Traduzir de novo agora' }
										</Button>
									) }
								</>
							) : (
								<>
									<p className="rolck-panel__muted">
										{ auto && isDefault
											? 'Ainda sem versão. Ela é criada sozinha quando você publica.'
											: 'Ainda sem versão neste idioma.' }
									</p>
									<Button
										variant="secondary"
										isBusy={
											busy === `create-${ lang.code }`
										}
										disabled={ !! busy || post.isNew }
										onClick={ () => create( lang.code ) }
									>
										{ auto && isDefault
											? `Traduzir para ${ lang.label } agora`
											: `Criar versão em ${ lang.label }` }
									</Button>
									<LinkExisting
										post={ post }
										lang={ lang.code }
										disabled={ !! busy }
										onLink={ ( target ) =>
											run( `link-${ lang.code }`, () =>
												apiFetch( {
													path: `/rolck/v1/translations/${ post.id }`,
													method: 'PUT',
													data: { target },
												} )
											)
										}
									/>
								</>
							) }
						</div>
					);
				} ) }

			{ data &&
				Object.values( data.translations || {} ).filter( Boolean )
					.length > 1 && (
					<Button
						variant="link"
						isDestructive
						disabled={ !! busy }
						onClick={ () =>
							run( 'unlink', () =>
								apiFetch( {
									path: `/rolck/v1/translations/${ post.id }`,
									method: 'DELETE',
								} )
							)
						}
					>
						Desligar das outras versões
					</Button>
				) }
			{ ! auto && (
				<p className="rolck-panel__hint">
					Dica: ligue a tradução automática em Rolck Galaxy →
					Configurações → Idiomas para não precisar traduzir à mão.
				</p>
			) }
		</PluginDocumentSettingPanel>
	);
}

function LinkExisting( { post, lang, onLink, disabled } ) {
	const [ search, setSearch ] = useState( '' );
	const [ options, setOptions ] = useState( [] );

	useEffect( () => {
		const timer = setTimeout( () => {
			apiFetch( {
				path: `/rolck/v1/translatable?type=${ post.type }&lang=${ lang }&search=${ encodeURIComponent( search ) }`,
			} )
				.then( ( rows ) =>
					setOptions(
						rows.map( ( r ) => ( { value: r.id, label: r.title } ) )
					)
				)
				.catch( () => setOptions( [] ) );
		}, 300 );
		return () => clearTimeout( timer );
	}, [ search, post.type, lang ] );

	return (
		<ComboboxControl
			__next40pxDefaultSize
			__nextHasNoMarginBottom
			className="rolck-panel__link"
			label="…ou ligar a um conteúdo existente"
			value={ null }
			options={ options }
			disabled={ disabled }
			onFilterValueChange={ setSearch }
			onChange={ ( v ) => v && onLink( Number( v ) ) }
		/>
	);
}
