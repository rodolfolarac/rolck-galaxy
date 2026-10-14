import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	TextareaControl,
	TextControl,
} from '@wordpress/components';
import { icons } from '../../shared/icons';
import metadata from './block.json';
import './style.scss';

function Edit( { attributes: { items, speed, label }, setAttributes } ) {
	return (
		<div { ...useBlockProps( { className: 'rolck-marquee' } ) }>
			<InspectorControls>
				<PanelBody title="Tecnologias">
					<TextareaControl
						__nextHasNoMarginBottom
						label="Uma por linha"
						rows={ 10 }
						value={ items.join( '\n' ) }
						onChange={ ( v ) =>
							setAttributes( {
								items: v
									.split( '\n' )
									.map( ( s ) => s.trim() )
									.filter( Boolean ),
							} )
						}
					/>
					<RangeControl
						label="Duração de uma volta (segundos)"
						help="Maior = mais devagar."
						value={ speed }
						min={ 10 }
						max={ 120 }
						onChange={ ( v ) => setAttributes( { speed: v } ) }
					/>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label="Nome da lista (leitores de tela)"
						value={ label }
						onChange={ ( v ) => setAttributes( { label: v } ) }
					/>
				</PanelBody>
			</InspectorControls>
			<ul className="rolck-marquee__track rolck-marquee__track--static">
				{ items.map( ( item ) => (
					<li key={ item }>{ item }</li>
				) ) }
			</ul>
		</div>
	);
}

registerBlockType( metadata.name, { icon: icons.marquee, edit: Edit } );
