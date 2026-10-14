/**
 * Painel "Detalhes do projeto": os campos que aparecem nos cartões, na ficha
 * do projeto e nos dados estruturados para o Google.
 */
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import {
	Button,
	ColorPalette,
	SelectControl,
	TextControl,
	ToggleControl,
	Flex,
	FlexBlock,
} from '@wordpress/components';
import { config } from './config';
import { useEditPost, usePost } from './use-post';

const ACCENTS = [
	{ name: 'Violeta', color: '#8b5cf6' },
	{ name: 'Ciano', color: '#22d3ee' },
	{ name: 'Azul', color: '#3b82f6' },
	{ name: 'Rosa', color: '#ec4899' },
	{ name: 'Verde', color: '#22c55e' },
	{ name: 'Âmbar', color: '#f59e0b' },
];

export default function ProjectPanel() {
	const post = usePost();
	const { setMeta } = useEditPost();

	if ( post.type !== config.projectType ) {
		return null;
	}

	const m = post.meta;
	const field = ( key, label, props = {} ) => (
		<TextControl
			__next40pxDefaultSize
			__nextHasNoMarginBottom
			label={ label }
			value={ m[ key ] || '' }
			onChange={ ( v ) => setMeta( { [ key ]: v } ) }
			{ ...props }
		/>
	);

	const highlights = Array.isArray( m.rolck_highlights )
		? m.rolck_highlights
		: [];
	const setHighlight = ( index, value ) =>
		setMeta( {
			rolck_highlights: highlights.map( ( h, i ) =>
				i === index ? value : h
			),
		} );

	return (
		<PluginDocumentSettingPanel
			name="rolck-project"
			title="Detalhes do projeto"
			className="rolck-panel rolck-panel--project"
		>
			{ field( 'rolck_tagline', 'Frase curta', {
				help: 'Aparece no cartão e embaixo do título. Até ~120 caracteres.',
			} ) }
			<SelectControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label="Status"
				value={ m.rolck_status || 'available' }
				options={ config.projectStatuses }
				onChange={ ( v ) => setMeta( { rolck_status: v } ) }
			/>
			<ToggleControl
				label="Projeto em destaque"
				help="Aparece primeiro e no bloco “Projeto em destaque”."
				checked={ !! m.rolck_featured }
				onChange={ ( v ) => setMeta( { rolck_featured: v } ) }
			/>
			{ field( 'rolck_demo_url', 'Link da demo ao vivo', {
				type: 'url',
				placeholder: 'https://',
			} ) }
			{ field( 'rolck_buy_url', 'Link de compra (página de pagamento)', {
				type: 'url',
				placeholder: 'https://',
			} ) }
			{ field( 'rolck_repo_url', 'Link do código (opcional)', {
				type: 'url',
				placeholder: 'https://github.com/…',
			} ) }
			<Flex align="flex-start">
				<FlexBlock>
					{ field( 'rolck_price', 'Preço', { placeholder: '$59' } ) }
				</FlexBlock>
				<FlexBlock>
					{ field( 'rolck_version', 'Versão', {
						placeholder: '1.0.0',
					} ) }
				</FlexBlock>
			</Flex>
			{ field( 'rolck_released', 'Data de lançamento', {
				type: 'date',
			} ) }
			{ field( 'rolck_compat', 'Compatibilidade', {
				placeholder: 'WordPress 6.6+, PHP 8.1+',
			} ) }

			<div className="rolck-panel__field">
				<span className="rolck-panel__label">Cor de destaque</span>
				<ColorPalette
					colors={ ACCENTS }
					value={ m.rolck_accent || undefined }
					onChange={ ( v ) => setMeta( { rolck_accent: v || '' } ) }
				/>
			</div>

			<div className="rolck-panel__field">
				<span className="rolck-panel__label">Destaques do projeto</span>
				{ highlights.map( ( item, index ) => (
					<div className="rolck-panel__row" key={ index }>
						<TextControl
							__next40pxDefaultSize
							__nextHasNoMarginBottom
							label={ `Destaque ${ index + 1 }` }
							hideLabelFromVision
							value={ item }
							onChange={ ( v ) => setHighlight( index, v ) }
						/>
						<Button
							icon="no-alt"
							label="Remover"
							onClick={ () =>
								setMeta( {
									rolck_highlights: highlights.filter(
										( _, i ) => i !== index
									),
								} )
							}
						/>
					</div>
				) ) }
				<Button
					variant="secondary"
					onClick={ () =>
						setMeta( { rolck_highlights: [ ...highlights, '' ] } )
					}
				>
					Adicionar destaque
				</Button>
			</div>
			<p className="rolck-panel__hint">
				Categoria e tecnologias ficam nos painéis “Categorias de
				projeto” e “Tecnologias”. A capa é a imagem destacada (1600 ×
				1000 px).
			</p>
		</PluginDocumentSettingPanel>
	);
}
