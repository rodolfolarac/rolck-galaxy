import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import { icons } from '../../shared/icons';
import metadata from './block.json';
import './style.scss';

function Edit() {
	return (
		<div { ...useBlockProps() }>
			<ServerSideRender
				block={ metadata.name }
				EmptyResponsePlaceholder={ () => (
					<p>Cadastre as redes em Rolck Galaxy → Geral.</p>
				) }
			/>
		</div>
	);
}

registerBlockType( metadata.name, { icon: icons.social, edit: Edit } );
