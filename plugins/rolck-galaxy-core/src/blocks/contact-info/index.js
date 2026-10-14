import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	CheckboxControl,
	PanelBody,
	SelectControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { icons } from '../../shared/icons';
import metadata from './block.json';
import './style.scss';

const FIELDS = {
	email: 'E-mail',
	whatsapp: 'WhatsApp',
	phone: 'Telefone',
	address: 'Endereço',
};

function Edit( { attributes, setAttributes } ) {
	const toggle = ( key, on ) =>
		setAttributes( {
			fields: on
				? [ ...attributes.fields, key ]
				: attributes.fields.filter( ( f ) => f !== key ),
		} );

	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody title="Dados exibidos">
					{ Object.entries( FIELDS ).map( ( [ key, label ] ) => (
						<CheckboxControl
							__nextHasNoMarginBottom
							key={ key }
							label={ label }
							checked={ attributes.fields.includes( key ) }
							onChange={ ( on ) => toggle( key, on ) }
						/>
					) ) }
					<SelectControl
						label="Disposição"
						value={ attributes.layout }
						options={ [
							{ label: 'Um embaixo do outro', value: 'stack' },
							{ label: 'Lado a lado', value: 'inline' },
						] }
						onChange={ ( layout ) => setAttributes( { layout } ) }
					/>
				</PanelBody>
			</InspectorControls>
			<ServerSideRender
				block={ metadata.name }
				attributes={ attributes }
				skipBlockSupportAttributes
				EmptyResponsePlaceholder={ () => (
					<p>
						Cadastre e-mail, telefone e endereço em Rolck Galaxy →
						Geral.
					</p>
				) }
			/>
		</div>
	);
}

registerBlockType( metadata.name, { icon: icons.info, edit: Edit } );
