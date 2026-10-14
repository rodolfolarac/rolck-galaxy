/**
 * Screenshot de uma página em partes (para revisar o visual).
 *   node tools/tests/screenshot.mjs /en/ nome 390 844 1600
 * Gera tools/tests/results/nome-0.png, nome-1.png…
 */
import { launch } from '../lib/browser.mjs';
const [ , , path = '/', name = 'home', w = '1440', h = '900', seg = '1800' ] =
	process.argv;
const b = await launch();
const p = await b.newPage( { viewport: { width: +w, height: +h } } );
const errors = [];
p.on( 'console', ( m ) => m.type() === 'error' && errors.push( m.text() ) );
p.on( 'pageerror', ( e ) => errors.push( e.message ) );
await p.goto( 'http://127.0.0.1:9500' + path, { waitUntil: 'networkidle' } );
await p.waitForTimeout( 400 );
const total = await p.evaluate( () => document.documentElement.scrollHeight );
let i = 0;
for ( let y = 0; y < total; y += +seg, i++ ) {
	await p.screenshot( {
		path: `tools/tests/results/${ name }-${ i }.png`,
		fullPage: true,
		clip: { x: 0, y, width: +w, height: Math.min( +seg, total - y ) },
	} );
}
console.log(
	name,
	'height',
	total,
	'parts',
	i,
	'errors:',
	JSON.stringify( errors )
);
await b.close();
