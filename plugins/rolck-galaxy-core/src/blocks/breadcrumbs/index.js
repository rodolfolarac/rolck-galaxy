import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { icons } from '../../shared/icons';
import metadata from './block.json';
import './style.scss';

function Edit() {
	return (
		<nav { ...useBlockProps( { className: 'rolck-breadcrumbs' } ) }>
			<ol>
				<li>
					<a href="#home">Início</a>
				</li>
				<li>
					<a href="#section">Seção</a>
				</li>
				<li aria-current="page">Página atual</li>
			</ol>
		</nav>
	);
}

registerBlockType( metadata.name, { icon: icons.breadcrumbs, edit: Edit } );
