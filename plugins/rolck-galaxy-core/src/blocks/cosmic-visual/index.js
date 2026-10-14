import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { icons } from '../../shared/icons';
import metadata from './block.json';
import './style.scss';

function Edit( { attributes, setAttributes } ) {
	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody title="Conteúdo">
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label="Texto no centro"
						help="Até 3 caracteres (iniciais da marca)."
						maxLength={ 3 }
						value={ attributes.center }
						onChange={ ( center ) => setAttributes( { center } ) }
					/>
					<TextareaControl
						__nextHasNoMarginBottom
						label="Etiquetas flutuantes (uma por linha, até 4)"
						value={ attributes.chips.join( '\n' ) }
						onChange={ ( v ) =>
							setAttributes( {
								chips: v
									.split( '\n' )
									.map( ( s ) => s.trim() )
									.filter( Boolean )
									.slice( 0, 4 ),
							} )
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

registerBlockType( metadata.name, { icon: icons.spotlight, edit: Edit } );
