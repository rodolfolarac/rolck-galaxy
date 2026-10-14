import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	ComboboxControl,
	PanelBody,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { useState } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';
import { icons } from '../../shared/icons';
import metadata from './block.json';
import './style.scss';

function Edit( { attributes: a, setAttributes } ) {
	const [ search, setSearch ] = useState( '' );
	const projects = useSelect(
		( select ) =>
			select( coreStore ).getEntityRecords( 'postType', 'rolck_project', {
				per_page: 20,
				search: search || undefined,
				status: 'publish',
				_fields: 'id,title',
			} ) || [],
		[ search ]
	);

	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody title="Projeto">
					<ComboboxControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label="Qual projeto"
						help="Vazio = o projeto em destaque mais recente."
						value={ a.projectId || null }
						options={ projects.map( ( p ) => ( {
							value: p.id,
							label: p.title.rendered,
						} ) ) }
						onFilterValueChange={ setSearch }
						onChange={ ( v ) =>
							setAttributes( { projectId: Number( v ) || 0 } )
						}
					/>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label="Chamada acima do título"
						help="Ex.: Lançamento, Mais vendido. Vazio = categoria do projeto."
						value={ a.eyebrow }
						onChange={ ( eyebrow ) => setAttributes( { eyebrow } ) }
					/>
					<ToggleControl
						label="Imagem à direita"
						checked={ a.reverse }
						onChange={ ( reverse ) => setAttributes( { reverse } ) }
					/>
					<ToggleControl
						label="Carregar a imagem com prioridade"
						help="Ligue se este bloco for o primeiro da página (melhora o LCP)."
						checked={ a.eager }
						onChange={ ( eager ) => setAttributes( { eager } ) }
					/>
				</PanelBody>
			</InspectorControls>
			<ServerSideRender
				block={ metadata.name }
				attributes={ a }
				skipBlockSupportAttributes
			/>
		</div>
	);
}

registerBlockType( metadata.name, { icon: icons.spotlight, edit: Edit } );
