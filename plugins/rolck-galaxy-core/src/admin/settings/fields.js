/**
 * Campos reutilizáveis da tela de configurações. Todos recebem `path` (o
 * caminho da chave em rolck_settings) e leem/gravam pelo contexto.
 */
import {
	BaseControl,
	Button,
	Card,
	CardBody,
	CardHeader,
	SelectControl,
	TextControl,
	TextareaControl,
	ToggleControl,
} from '@wordpress/components';
import { MediaUpload } from '@wordpress/media-utils';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { useSettings } from './context';

export function Section( { title, description, children } ) {
	return (
		<Card className="rolck-admin__card">
			<CardHeader>
				<div>
					<h2>{ title }</h2>
					{ description && (
						<p className="rolck-admin__description">
							{ description }
						</p>
					) }
				</div>
			</CardHeader>
			<CardBody className="rolck-admin__fields">{ children }</CardBody>
		</Card>
	);
}

export function Text( {
	path,
	label,
	help,
	type = 'text',
	placeholder,
	wide,
} ) {
	const { get, set } = useSettings();
	return (
		<div className={ wide ? 'is-wide' : undefined }>
			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label={ label }
				help={ help }
				type={ type }
				placeholder={ placeholder }
				value={ get( path ) ?? '' }
				onChange={ ( v ) =>
					set( path, type === 'number' ? Number( v ) || 0 : v )
				}
			/>
		</div>
	);
}

export function LongText( { path, label, help, rows = 3, code } ) {
	const { get, set } = useSettings();
	return (
		<div className="is-wide">
			<TextareaControl
				__nextHasNoMarginBottom
				className={ code ? 'rolck-admin__code' : undefined }
				label={ label }
				help={ help }
				rows={ rows }
				value={ get( path ) ?? '' }
				onChange={ ( v ) => set( path, v ) }
			/>
		</div>
	);
}

/**
 * Um campo por idioma, lado a lado (path aponta para o par { pt, en }).
 *
 * @param {Object}  props       Propriedades.
 * @param {string}  props.path  Caminho do par na configuração.
 * @param {string}  props.label Rótulo do grupo.
 * @param {string}  props.help  Texto de ajuda.
 * @param {boolean} props.long  Usa caixa de texto de várias linhas.
 * @param {number}  props.rows  Linhas da caixa de texto.
 * @param {number}  props.max   Limite recomendado de caracteres (mostra contador).
 */
export function Pair( { path, label, help, long, rows = 3, max } ) {
	const { get, set, getDefault, boot } = useSettings();
	const Control = long ? TextareaControl : TextControl;
	return (
		<fieldset className="rolck-admin__pair is-wide">
			<legend>{ label }</legend>
			<div className="rolck-admin__pair-grid">
				{ boot.languages.map( ( lang ) => {
					const value = get( `${ path }.${ lang.code }` ) ?? '';
					return (
						<Control
							key={ lang.code }
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={
								<>
									<span className="rolck-admin__lang">
										{ lang.short }
									</span>{ ' ' }
									{ lang.label }
									{ max && (
										<span
											className={ `rolck-admin__count${ value.length > max ? ' is-over' : '' }` }
										>
											{ value.length }/{ max }
										</span>
									) }
								</>
							}
							rows={ rows }
							placeholder={ getDefault(
								`${ path }.${ lang.code }`
							) }
							value={ value }
							onChange={ ( v ) =>
								set( `${ path }.${ lang.code }`, v )
							}
						/>
					);
				} ) }
			</div>
			{ help && <p className="rolck-admin__help">{ help }</p> }
		</fieldset>
	);
}

export function Toggle( { path, label, help } ) {
	const { get, set } = useSettings();
	return (
		<div className="is-wide">
			<ToggleControl
				__nextHasNoMarginBottom
				label={ label }
				help={ help }
				checked={ !! get( path ) }
				onChange={ ( v ) => set( path, v ) }
			/>
		</div>
	);
}

export function Select( { path, label, help, options } ) {
	const { get, set } = useSettings();
	return (
		<div>
			<SelectControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label={ label }
				help={ help }
				value={ get( path ) }
				options={ options }
				onChange={ ( v ) => set( path, v ) }
			/>
		</div>
	);
}

export function Media( { path, label, help } ) {
	const { get, set } = useSettings();
	const id = get( path ) || 0;
	const media = useSelect(
		( select ) =>
			id
				? select( coreStore ).getEntityRecord( 'root', 'media', id )
				: null,
		[ id ]
	);

	return (
		<BaseControl
			__nextHasNoMarginBottom
			id={ `rolck-media-${ path }` }
			label={ label }
			help={ help }
			className="rolck-admin__media"
		>
			<div className="rolck-admin__media-row">
				{ media?.source_url ? (
					<img src={ media.source_url } alt="" />
				) : (
					<span className="rolck-admin__media-empty">
						Nenhuma imagem
					</span>
				) }
				<MediaUpload
					allowedTypes={ [ 'image' ] }
					value={ id }
					onSelect={ ( m ) => set( path, m.id ) }
					render={ ( { open } ) => (
						<Button variant="secondary" onClick={ open }>
							{ id ? 'Trocar' : 'Escolher imagem' }
						</Button>
					) }
				/>
				{ !! id && (
					<Button
						variant="link"
						isDestructive
						onClick={ () => set( path, 0 ) }
					>
						Remover
					</Button>
				) }
			</div>
		</BaseControl>
	);
}

/**
 * Lista de atalhos para outras telas do painel.
 *
 * @param {Object}   props       Propriedades.
 * @param {Object[]} props.items Atalhos: { href, title, text, external }.
 */
export function Shortcuts( { items } ) {
	return (
		<ul className="rolck-admin__shortcuts is-wide">
			{ items.map( ( { href, title, text, external } ) => (
				<li key={ href }>
					<a
						href={ href }
						target={ external ? '_blank' : undefined }
						rel={ external ? 'noreferrer' : undefined }
					>
						<strong>
							{ title } { external ? '↗' : '→' }
						</strong>
						<span>{ text }</span>
					</a>
				</li>
			) ) }
		</ul>
	);
}
