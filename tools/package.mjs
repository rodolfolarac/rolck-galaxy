/**
 * Gera os zips para instalar o site em qualquer WordPress:
 *   dist/rolck-galaxy.zip        tema
 *   dist/rolck-galaxy-core.zip   plugin (já compilado, sem src/)
 *   dist/rolck-galaxy-setup.zip  conteúdo inicial (uso único, sem precisar de SSH)
 *
 *   npm run package
 *
 * Instale pelo painel (Plugins → Adicionar → Enviar e Aparência → Temas →
 * Enviar), ative os três e clique em Ferramentas → Conteúdo Rolck Galaxy →
 * Importar conteúdo. Com WP-CLI dá para usar `wp eval-file tools/seed.php`.
 */
import { execFileSync, execSync } from 'node:child_process';
import { cpSync, existsSync, mkdirSync, rmSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join( dirname( fileURLToPath( import.meta.url ) ), '..' );
const dist = join( root, 'dist' );
rmSync( dist, { recursive: true, force: true } );
mkdirSync( dist );

execSync( 'npm run build', { cwd: root, stdio: 'inherit' } );

// Zip com o zipfile do Python (vem em qualquer Linux/WSL; o comando `zip` nem sempre).
const zip = ( folder, name, exclude = [] ) => {
	const script = `
import os, sys, zipfile
base, name, out, skip = sys.argv[1], sys.argv[2], sys.argv[3], sys.argv[4:]
with zipfile.ZipFile(out, 'w', zipfile.ZIP_DEFLATED) as z:
    for d, dirs, files in os.walk(os.path.join(base, name)):
        rel = os.path.relpath(d, base)
        if any(rel == os.path.join(name, s) or rel.startswith(os.path.join(name, s) + os.sep) for s in skip):
            continue
        for f in files:
            if f != '.DS_Store':
                z.write(os.path.join(d, f), os.path.join(rel, f))
`;
	execFileSync( 'python3', [
		'-c',
		script,
		join( root, folder ),
		name,
		join( dist, name + '.zip' ),
		...exclude,
	] );
	console.log( `✓ dist/${ name }.zip` );
};

if (
	! existsSync(
		join( root, 'plugins/rolck-galaxy-core/build/blocks-manifest.php' )
	)
) {
	throw new Error( 'Build do plugin não encontrado.' );
}
zip( 'themes', 'rolck-galaxy' );
zip( 'plugins', 'rolck-galaxy-core', [ 'src' ] );

// Conteúdo inicial: instalador de uso único + seed + imagens da demo.
const stage = join( dist, '.setup' );
const setup = join( stage, 'rolck-galaxy-setup' );
mkdirSync( join( setup, 'demo' ), { recursive: true } );
cpSync(
	join( root, 'tools/setup/rolck-galaxy-setup.php' ),
	join( setup, 'rolck-galaxy-setup.php' )
);
cpSync( join( root, 'tools/seed.php' ), join( setup, 'seed.php' ) );
cpSync( join( root, 'tools/demo/images' ), join( setup, 'demo/images' ), {
	recursive: true,
} );
zip( 'dist/.setup', 'rolck-galaxy-setup' );
rmSync( stage, { recursive: true, force: true } );
