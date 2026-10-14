import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { icons } from '../../shared/icons';
import metadata from './block.json';

function Edit( { attributes, setAttributes } ) {
	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody title="Texto">
					<ToggleControl
						label="Mostrar “Feito com WordPress…”"
						checked={ attributes.showMadeWith }
						onChange={ ( showMadeWith ) =>
							setAttributes( { showMadeWith } )
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

registerBlockType( metadata.name, { icon: icons.copyright, edit: Edit } );
