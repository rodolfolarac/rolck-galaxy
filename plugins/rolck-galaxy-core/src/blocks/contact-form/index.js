import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, ExternalLink } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { icons } from '../../shared/icons';
import metadata from './block.json';
import './style.scss';

function Edit( { attributes, setAttributes } ) {
	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody title="Formulário">
					<ToggleControl
						label="Campo de assunto"
						checked={ attributes.showSubject }
						onChange={ ( showSubject ) =>
							setAttributes( { showSubject } )
						}
					/>
					<p>
						E-mail de destino, mensagem de sucesso e textos dos
						campos ficam no painel.{ ' ' }
						<ExternalLink href="admin.php?page=rolck-galaxy#contact">
							Abrir configurações
						</ExternalLink>
					</p>
				</PanelBody>
			</InspectorControls>
			<div inert="">
				<ServerSideRender
					block={ metadata.name }
					attributes={ attributes }
					skipBlockSupportAttributes
				/>
			</div>
		</div>
	);
}

registerBlockType( metadata.name, { icon: icons.mail, edit: Edit } );
