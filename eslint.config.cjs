/**
 * Lint de JavaScript no padrão oficial do WordPress (mesma base do wp-scripts).
 *
 * - Pacotes @wordpress/* vêm do próprio WordPress (externos no build), por isso
 *   não precisam estar instalados para o lint.
 * - Scripts de terminal em tools/ podem usar console e dependências de desenvolvimento.
 */
const wpConfig = require( '@wordpress/scripts/config/eslint.config.cjs' );

module.exports = [
	...wpConfig,
	{
		rules: {
			'import/no-unresolved': [ 'error', { ignore: [ '^@wordpress/' ] } ],
		},
	},
	{
		files: [ 'plugins/*/src/**/*.js' ],
		rules: {
			// @wordpress/* são fornecidos pelo WordPress em tempo de execução.
			'import/no-extraneous-dependencies': 'off',
		},
	},
	{
		files: [ 'plugins/*/src/**/view.js' ],
		languageOptions: { globals: { IntersectionObserver: 'readonly', requestAnimationFrame: 'readonly' } },
	},
	{
		files: [ 'tools/**/*.mjs', '*.config.js', '*.config.cjs' ],
		languageOptions: { globals: { process: 'readonly', console: 'readonly' } },
		rules: {
			'no-console': 'off',
			'import/no-extraneous-dependencies': 'off',
		},
	},
];
