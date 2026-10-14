/**
 * Cópia de segurança do código-fonte do projeto:
 *   backups/rolck-galaxy-codigo-AAAA-MM-DD.zip
 *
 *   npm run backup
 *
 * Fica de fora o que se gera de novo (node_modules, build, dist, site local)
 * e o .env.local, que tem a senha. Para restaurar: descompacte, `npm install`
 * e `npm run build`.
 */
import { execFileSync } from 'node:child_process';
import { mkdirSync } from 'node:fs';
import { basename, dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join( dirname( fileURLToPath( import.meta.url ) ), '..' );
const folder = join( root, 'backups' );
mkdirSync( folder, { recursive: true } );
const out = join(
	folder,
	`rolck-galaxy-codigo-${ new Date().toISOString().slice( 0, 10 ) }.zip`
);

const skip = [
	'node_modules',
	'.wp-dev',
	'dist',
	'backups',
	'plugins/rolck-galaxy-core/build',
	'tools/tests/results',
	'.git',
];
const skipFiles = [ '.env.local', '.wp-dev-blueprint.json', '.DS_Store' ];

const script = `
import os, sys, zipfile
root, out, name = sys.argv[1], sys.argv[2], sys.argv[3]
skip = sys.argv[4].split('|')
skip_files = sys.argv[5].split('|')
with zipfile.ZipFile(out, 'w', zipfile.ZIP_DEFLATED) as z:
    for d, dirs, files in os.walk(root):
        rel = os.path.relpath(d, root)
        dirs[:] = [x for x in dirs if os.path.normpath(os.path.join(rel, x)) not in skip and '__pycache__' != x]
        for f in files:
            if f in skip_files or f.endswith('.log') or ':Zone.Identifier' in f:
                continue
            z.write(os.path.join(d, f), os.path.join(name, os.path.normpath(os.path.join(rel, f))))
`;
execFileSync( 'python3', [
	'-c',
	script,
	root,
	out,
	basename( root ),
	skip.join( '|' ),
	skipFiles.join( '|' ),
] );
console.log( `✓ ${ out.replace( root + '/', '' ) }` );
