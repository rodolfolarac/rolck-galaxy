/**
 * Configuração padrão do @wordpress/scripts (a mesma do create-block) com quatro
 * entradas extras: a tela de configurações do painel, os painéis do editor, o
 * CSS compartilhado dos blocos (estilo "rolck-ui") e o aviso de cookies (LGPD).
 * Os blocos em src/blocks/* são encontrados sozinhos pelo block.json.
 */
const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

const [ scriptConfig, moduleConfig ] = Array.isArray( defaultConfig ) ? defaultConfig : [ defaultConfig ];
const src = ( file ) => path.resolve( __dirname, 'plugins/rolck-galaxy-core/src', file );

module.exports = [
	{
		...scriptConfig,
		entry: () => ( {
			...( typeof scriptConfig.entry === 'function' ? scriptConfig.entry() : scriptConfig.entry ),
			'admin/settings': src( 'admin/settings/index.js' ),
			'editor/index': src( 'editor/index.js' ),
			'shared/ui': src( 'shared/ui.js' ),
			'consent/index': src( 'consent/index.js' ),
		} ),
	},
	moduleConfig,
].filter( Boolean );
