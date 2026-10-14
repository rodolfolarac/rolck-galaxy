/**
 * Painéis do editor de blocos. Cada painel é um arquivo; para criar outro,
 * escreva o componente e registre aqui.
 */
import { registerPlugin } from '@wordpress/plugins';
import LanguagePanel from './language-panel';
import SeoPanel from './seo-panel';
import ProjectPanel from './project-panel';
import './editor.scss';

registerPlugin( 'rolck-language', { render: LanguagePanel } );
registerPlugin( 'rolck-seo', { render: SeoPanel } );
registerPlugin( 'rolck-project', { render: ProjectPanel } );
