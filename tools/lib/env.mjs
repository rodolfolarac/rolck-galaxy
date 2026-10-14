/**
 * Lê o .env.local da raiz do projeto (credenciais do WordPress local e dos testes).
 * Variáveis de ambiente de verdade têm prioridade sobre o arquivo.
 */
import { existsSync, readFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const file = join(
	dirname( fileURLToPath( import.meta.url ) ),
	'..',
	'..',
	'.env.local'
);
const values = {};
if ( existsSync( file ) ) {
	for ( const line of readFileSync( file, 'utf8' ).split( /\r?\n/ ) ) {
		const match = line.match( /^\s*([A-Z0-9_]+)\s*=\s*(.*)\s*$/ );
		if ( match ) {
			values[ match[ 1 ] ] = match[ 2 ].replace( /^(['"])(.*)\1$/, '$2' );
		}
	}
}

export const env = ( key, fallback = '' ) =>
	process.env[ key ] ?? values[ key ] ?? fallback;

export const admin = {
	user: env( 'WP_ADMIN_USER', 'admin' ),
	pass: env( 'WP_ADMIN_PASS', 'password' ),
};
