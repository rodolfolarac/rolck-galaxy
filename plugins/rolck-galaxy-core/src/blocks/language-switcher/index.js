import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { icons } from '../../shared/icons';
import metadata from './block.json';
import './style.scss';

function Edit( { attributes, setAttributes } ) {
	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody title="Aparência">
					<ToggleControl
						label="Mostrar ícone de globo"
						checked={ attributes.showIcon }
						onChange={ ( showIcon ) =>
							setAttributes( { showIcon } )
						}
					/>
					<ToggleControl
						label="Nome completo do idioma"
						help="Desligado mostra só PT | EN."
						checked={ attributes.fullName }
						onChange={ ( fullName ) =>
							setAttributes( { fullName } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<ServerSideRender
				block={ metadata.name }
				attributes={ attributes }
				skipBlockSupportAttributes
			/>
		</div>
	);
}

registerBlockType( metadata.name, { icon: icons.language, edit: Edit } );
