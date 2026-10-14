import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { icons } from '../../shared/icons';
import metadata from './block.json';

function Edit( { attributes, setAttributes } ) {
	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody title="Títulos">
					<SelectControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label="Nível do título de cada categoria"
						value={ attributes.headingLevel }
						options={ [ 'h2', 'h3', 'h4' ].map( ( value ) => ( {
							label: value.toUpperCase(),
							value,
						} ) ) }
						onChange={ ( headingLevel ) =>
							setAttributes( { headingLevel } )
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

registerBlockType( metadata.name, { icon: icons.cookie, edit: Edit } );
