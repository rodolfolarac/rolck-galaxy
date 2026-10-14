/**
 * Minifica o CSS do tema: assets/css/theme.css → assets/css/theme.min.css.
 * O tema carrega o .min (menor e embutido no HTML); com SCRIPT_DEBUG, o original.
 * Roda junto com `npm run build`.
 */
import { readFileSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import postcss from 'postcss';
import cssnano from 'cssnano';

const root = join( dirname( fileURLToPath( import.meta.url ) ), '..' );
const src = join( root, 'themes/rolck-galaxy/assets/css/theme.css' );
const dest = src.replace( /\.css$/, '.min.css' );

const { css } = await postcss( [ cssnano( { preset: 'default' } ) ] ).process(
	readFileSync( src, 'utf8' ),
	{ from: src, to: dest }
);
writeFileSync( dest, css );
console.log(
	`theme.min.css: ${ readFileSync( src ).length } → ${ css.length } bytes`
);
