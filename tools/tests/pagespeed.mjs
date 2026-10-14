/**
 * Lighthouse (o mesmo motor do PageSpeed Insights) nas páginas principais,
 * em modo celular. Precisa do site local rodando (npm run dev).
 *
 *   npm run test:pagespeed               todas as páginas
 *   npm run test:pagespeed -- /en/       uma página
 *   DESKTOP=1 npm run test:pagespeed     modo computador
 *   BASE=https://rolckgalaxy.com npm run test:pagespeed   site no ar
 *
 * Atenção: localmente o PHP roda em WebAssembly e responde bem mais devagar
 * que um servidor de verdade; o tempo do servidor (TTFB) puxa a nota de
 * performance para baixo aqui. As outras notas não dependem disso.
 */
import { spawnSync } from 'node:child_process';
import {
	existsSync,
	mkdirSync,
	mkdtempSync,
	readFileSync,
	rmSync,
} from 'node:fs';
import { homedir, tmpdir } from 'node:os';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join( dirname( fileURLToPath( import.meta.url ) ), '..', '..' );
const BASE = process.env.BASE || 'http://127.0.0.1:9500';
const out = join(
	root,
	'tools/tests/results/lighthouse',
	new URL( BASE ).hostname.replace( /\W+/g, '-' )
);
mkdirSync( out, { recursive: true } );

const chrome = join(
	homedir(),
	'.cache/ms-playwright/chromium-1187/chrome-linux/chrome'
);
const libs = join(
	homedir(),
	'.cache/chromium-libs/root/usr/lib/x86_64-linux-gnu'
);
const env = {
	...process.env,
	CHROME_PATH: existsSync( chrome ) ? chrome : process.env.CHROME_PATH,
};
if ( existsSync( libs ) ) {
	env.LD_LIBRARY_PATH = [ libs, env.LD_LIBRARY_PATH ]
		.filter( Boolean )
		.join( ':' );
}

const pages = process.argv.slice( 2 ).length
	? process.argv.slice( 2 )
	: [
			'/',
			'/en/',
			'/servicos/',
			'/en/services/',
			'/sobre/',
			'/en/about/',
			'/contato/',
			'/en/contact/',
			'/projetos/',
			'/en/projects/',
			'/projetos/galaxy-academy/',
			'/en/projects/galaxy-academy/',
			'/projetos/galaxy-cards/',
			'/projetos/galaxy-health/',
			'/blog/',
			'/en/blog/',
			'/site-wordpress-sem-plugins/',
			'/en/wordpress-site-no-plugins/',
			'/politica-de-privacidade/',
			'/en/privacy-policy/',
		];
const desktop = !! process.env.DESKTOP;
const rows = [];
const work = mkdtempSync( join( tmpdir(), 'rolck-lighthouse-' ) );

for ( const path of pages ) {
	const file = join(
		out,
		( path.replace( /\W+/g, '-' ).replace( /^-|-$/g, '' ) || 'home' ) +
			( desktop ? '-desktop' : '' ) +
			'.json'
	);
	const run = spawnSync(
		join( root, 'node_modules/.bin/lighthouse' ),
		[
			BASE + path,
			'--quiet',
			'--output=json',
			`--output-path=${ file }`,
			'--chrome-flags=--headless=new --no-sandbox',
			'--only-categories=performance,accessibility,best-practices,seo',
			...( desktop ? [ '--preset=desktop' ] : [] ),
		],
		// No WSL o chrome-launcher cria a pasta temporária com nome de caminho do
		// Windows na pasta atual; rodando numa pasta temporária, nada sobra no projeto.
		{ env, encoding: 'utf8', cwd: work }
	);
	if ( run.status !== 0 ) {
		console.error( path, run.stderr.slice( -500 ) );
		continue;
	}
	const report = JSON.parse( readFileSync( file, 'utf8' ) );
	const score = ( k ) => Math.round( report.categories[ k ].score * 100 );
	const audit = ( k ) => report.audits[ k ]?.displayValue || '';
	rows.push( {
		página: path,
		perf: score( 'performance' ),
		a11y: score( 'accessibility' ),
		boas: score( 'best-practices' ),
		seo: score( 'seo' ),
		LCP: audit( 'largest-contentful-paint' ),
		CLS: audit( 'cumulative-layout-shift' ),
		TBT: audit( 'total-blocking-time' ),
		TTFB: audit( 'server-response-time' ).replace(
			'Root document took ',
			''
		),
	} );
	const failed = Object.values( report.audits ).filter(
		( a ) =>
			a.score !== null &&
			a.score < 0.9 &&
			a.scoreDisplayMode !== 'informative' &&
			a.scoreDisplayMode !== 'manual' &&
			a.scoreDisplayMode !== 'notApplicable'
	);
	if ( failed.length ) {
		console.log(
			`${ path } — a melhorar: ${ failed.map( ( a ) => a.id ).join( ', ' ) }`
		);
	}
}
console.table( rows );
rmSync( work, { recursive: true, force: true } );
