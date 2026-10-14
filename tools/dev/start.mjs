/**
 * Rolck Galaxy — WordPress local sem PHP, MySQL nem Docker (WordPress Playground).
 *
 *   npm run dev              sobe o site (a primeira vez instala tudo e cria a demo)
 *   npm run dev -- --reset   apaga o site local e começa do zero
 *   PORT=8080 npm run dev    outra porta
 *
 * O site fica em .wp-dev/. Tema, plugin e tools/ são montados direto das
 * pastas do projeto: editou, recarregou.
 */
import { spawn } from 'node:child_process';
import { existsSync, mkdirSync, rmSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { admin } from '../lib/env.mjs';

const b64 = ( text ) => Buffer.from( text ).toString( 'base64' );
const root = join( dirname( fileURLToPath( import.meta.url ) ), '..', '..' );
const PORT = Number( process.env.PORT || 9500 );
const BASE = `http://127.0.0.1:${ PORT }`;
const DATA = join( root, '.wp-dev' );
const BLUEPRINT = join( root, '.wp-dev-blueprint.json' );
const cli = join( root, 'node_modules', '.bin', 'wp-playground-cli' );

if ( ! existsSync( cli ) ) {
	console.error( 'Faltam as dependências. Rode "npm install" primeiro.' );
	process.exit( 1 );
}
if (
	! existsSync(
		join( root, 'plugins/rolck-galaxy-core/build/blocks-manifest.php' )
	)
) {
	console.error(
		'Os blocos ainda não foram compilados. Rode "npm run build" primeiro.'
	);
	process.exit( 1 );
}

if ( process.argv.includes( '--reset' ) ) {
	rmSync( DATA, { recursive: true, force: true } );
	console.log( 'Site local apagado.' );
}

const firstRun = ! existsSync( join( DATA, 'wp-config.php' ) );
mkdirSync( DATA, { recursive: true } );

const steps = firstRun
	? [
			{ step: 'setSiteLanguage', language: 'pt_BR' },
			{
				step: 'activatePlugin',
				pluginPath: 'rolck-galaxy-core/rolck-galaxy-core.php',
			},
			{ step: 'activateTheme', themeFolderName: 'rolck-galaxy' },
			{
				step: 'runPHP',
				code: "<?php require '/wordpress/wp-load.php'; require '/tools/seed.php';",
			},
			// Usuário e senha do admin vêm do .env.local (o Playground cria admin/password).
			{
				step: 'runPHP',
				code: `<?php require '/wordpress/wp-load.php'; require '/tools/dev/set-admin.php'; rolck_set_admin( base64_decode( '${ b64( admin.user ) }' ), base64_decode( '${ b64( admin.pass ) }' ) );`,
			},
		]
	: [];
writeFileSync(
	BLUEPRINT,
	JSON.stringify(
		{
			landingPage: '/',
			preferredVersions: { php: '8.3', wp: 'latest' },
			steps,
		},
		null,
		2
	)
);

const args = [
	'server',
	'--port',
	String( PORT ),
	'--php',
	'8.3',
	'--define-bool',
	'WP_DEBUG',
	'true',
	'--define-bool',
	'WP_DEBUG_LOG',
	'true',
	'--define-bool',
	'WP_DEBUG_DISPLAY',
	'false',
	// Libera o "Teste local (sem API)" na tradução automática (só no ambiente local).
	'--define-bool',
	'ROLCK_FAKE_TRANSLATOR',
	'true',
	'--wordpress-install-mode',
	firstRun ? 'download-and-install' : 'install-from-existing-files-if-needed',
	'--mount-before-install',
	`${ DATA }:/wordpress`,
	'--mount',
	`${ join( root, 'themes/rolck-galaxy' ) }:/wordpress/wp-content/themes/rolck-galaxy`,
	'--mount',
	`${ join( root, 'plugins/rolck-galaxy-core' ) }:/wordpress/wp-content/plugins/rolck-galaxy-core`,
	'--mount',
	`${ join( root, 'tools' ) }:/tools`,
	'--blueprint',
	BLUEPRINT,
];

console.log(
	firstRun
		? 'Primeira vez: instalando o WordPress e criando a demonstração (1–3 minutos)…'
		: 'Subindo o site local…'
);
const server = spawn( cli, args, {
	cwd: root,
	stdio: [ 'ignore', 'pipe', 'pipe' ],
} );

let ready = false;
const onOutput = ( chunk ) => {
	const text = chunk.toString();
	if (
		process.env.VERBOSE ||
		( ! ready && /error|fatal|warning/i.test( text ) )
	) {
		process.stdout.write( text );
	}
	if ( ! ready && /Ready!/.test( text ) ) {
		ready = true;
		console.log( `
✦ Rolck Galaxy no ar

  Site (português)  ${ BASE }/
  Site (inglês)     ${ BASE }/en/
  Painel            ${ BASE }/wp-admin/   usuário: ${ admin.user }   (senha no .env.local)
  Configurações     ${ BASE }/wp-admin/admin.php?page=rolck-galaxy

  Ctrl+C para parar. Para recomeçar do zero: npm run dev -- --reset
` );
	}
};
server.stdout.on( 'data', onOutput );
server.stderr.on( 'data', onOutput );
server.on( 'exit', ( code ) => process.exit( code ?? 0 ) );
process.on( 'SIGINT', () => server.kill( 'SIGINT' ) );
