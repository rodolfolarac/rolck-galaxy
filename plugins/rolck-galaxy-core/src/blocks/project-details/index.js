import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, Notice } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { icons } from '../../shared/icons';
import metadata from './block.json';
import './style.scss';

function Edit( { attributes, setAttributes, context } ) {
	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody title="O que mostrar">
					<SelectControl
						label="Partes"
						value={ attributes.show }
						options={ [
							{ label: 'Tudo', value: 'all' },
							{
								label: 'Só status, preço e botões',
								value: 'actions',
							},
							{ label: 'Só a ficha técnica', value: 'specs' },
							{ label: 'Só os destaques', value: 'highlights' },
						] }
						onChange={ ( show ) => setAttributes( { show } ) }
					/>
				</PanelBody>
			</InspectorControls>
			{ context.postType === 'rolck_project' && context.postId ? (
				<ServerSideRender
					block={ metadata.name }
					attributes={ attributes }
					urlQueryArgs={ { post_id: context.postId } }
					skipBlockSupportAttributes
				/>
			) : (
				<Notice status="info" isDismissible={ false }>
					A ficha mostra os dados do projeto aberto. Use este bloco no
					modelo “Projeto” ou dentro de um projeto.
				</Notice>
			) }
		</div>
	);
}

registerBlockType( metadata.name, { icon: icons.details, edit: Edit } );
