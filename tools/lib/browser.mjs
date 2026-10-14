/**
 * Abre o Chromium do Playwright. Em máquinas sem as bibliotecas do sistema
 * (WSL sem sudo), usa as extraídas em ~/.cache/chromium-libs
 * (apt-get download libnss3 libnspr4 libasound2t64 + dpkg-deb -x).
 */
import { chromium } from 'playwright-core';
import { existsSync } from 'node:fs';
import { homedir } from 'node:os';
import { join } from 'node:path';

const libs = join(
	homedir(),
	'.cache/chromium-libs/root/usr/lib/x86_64-linux-gnu'
);

export function launch( options = {} ) {
	const env = { ...process.env };
	if ( existsSync( libs ) ) {
		env.LD_LIBRARY_PATH = [ libs, env.LD_LIBRARY_PATH ]
			.filter( Boolean )
			.join( ':' );
	}
	return chromium.launch( { env, ...options } );
}
