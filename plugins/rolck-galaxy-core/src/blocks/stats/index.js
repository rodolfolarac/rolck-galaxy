import { registerBlockType } from '@wordpress/blocks';
import {
	InspectorControls,
	RichText,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	Button,
	PanelBody,
	TextControl,
	ToggleControl,
	Flex,
	FlexBlock,
} from '@wordpress/components';
import { icons } from '../../shared/icons';
import metadata from './block.json';
import './style.scss';

const EMPTY = { value: '0', prefix: '', suffix: '', label: '' };

function Edit( { attributes: { items, animate }, setAttributes } ) {
	const update = ( index, patch ) =>
		setAttributes( {
			items: items.map( ( item, i ) =>
				i === index ? { ...item, ...patch } : item
			),
		} );
	const remove = ( index ) =>
		setAttributes( { items: items.filter( ( _, i ) => i !== index ) } );
	const add = () => setAttributes( { items: [ ...items, { ...EMPTY } ] } );

	return (
		<div { ...useBlockProps( { className: 'rolck-stats' } ) }>
			<InspectorControls>
				<PanelBody title="Números">
					<ToggleControl
						label="Contagem animada"
						checked={ animate }
						onChange={ ( v ) => setAttributes( { animate: v } ) }
					/>
					{ items.map( ( item, index ) => (
						<div key={ index } className="rolck-stats__panel-item">
							<Flex>
								<FlexBlock>
									<TextControl
										__next40pxDefaultSize
										__nextHasNoMarginBottom
										label="Antes"
										value={ item.prefix }
										onChange={ ( prefix ) =>
											update( index, { prefix } )
										}
									/>
								</FlexBlock>
								<FlexBlock>
									<TextControl
										__next40pxDefaultSize
										__nextHasNoMarginBottom
										label="Número"
										value={ item.value }
										onChange={ ( value ) =>
											update( index, { value } )
										}
									/>
								</FlexBlock>
								<FlexBlock>
									<TextControl
										__next40pxDefaultSize
										__nextHasNoMarginBottom
										label="Depois"
										value={ item.suffix }
										onChange={ ( suffix ) =>
											update( index, { suffix } )
										}
									/>
								</FlexBlock>
							</Flex>
							<Button
								isDestructive
								variant="link"
								onClick={ () => remove( index ) }
							>
								Remover este número
							</Button>
						</div>
					) ) }
					<Button variant="secondary" onClick={ add }>
						Adicionar número
					</Button>
				</PanelBody>
			</InspectorControls>
			<dl className="rolck-stats__list">
				{ items.map( ( item, index ) => (
					<div className="rolck-stats__item" key={ index }>
						<dt className="rolck-stats__label">
							<RichText
								tagName="span"
								value={ item.label }
								allowedFormats={ [] }
								placeholder="Descrição"
								onChange={ ( label ) =>
									update( index, { label } )
								}
							/>
						</dt>
						<dd className="rolck-stats__value">
							{ item.prefix }
							{ item.value }
							{ item.suffix }
						</dd>
					</div>
				) ) }
			</dl>
		</div>
	);
}

registerBlockType( metadata.name, { icon: icons.stats, edit: Edit } );
