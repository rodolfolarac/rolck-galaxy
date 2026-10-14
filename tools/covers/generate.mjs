/**
 * Gera as imagens da demonstração a partir de HTML (ilustrações originais, GPL):
 * logo em PNG, ícone do site, imagem padrão de compartilhamento e capas dos
 * projetos que não têm captura de tela real.
 *
 *   npm run covers
 */
import { readFileSync, copyFileSync, existsSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { launch } from '../lib/browser.mjs';

const root = join( dirname( fileURLToPath( import.meta.url ) ), '..', '..' );
const out = join( root, 'tools/demo/images' );
const logo = readFileSync(
	join( root, 'themes/rolck-galaxy/assets/images/logo.svg' ),
	'utf8'
);
const font = `@font-face{font-family:Inter;src:url(data:font/woff2;base64,${ readFileSync( join( root, 'themes/rolck-galaxy/assets/fonts/inter-latin.woff2' ) ).toString( 'base64' ) }) format("woff2");font-weight:100 900}`;

const base = `${ font }
*{box-sizing:border-box;margin:0}
body{font-family:Inter,sans-serif;color:#f5f5fa;background:#0a0a1a;-webkit-font-smoothing:antialiased}
.stage{position:relative;overflow:hidden;display:grid;place-items:center;background:
radial-gradient(60% 80% at 0% 0%,rgba(124,58,237,.55),transparent 60%),
radial-gradient(50% 70% at 100% 100%,rgba(14,116,144,.45),transparent 60%),#0a0a1a}
.stars{position:absolute;inset:0;background-image:radial-gradient(1.5px 1.5px at 20% 30%,#fff8,transparent),radial-gradient(1px 1px at 70% 20%,#fff6,transparent),radial-gradient(1.5px 1.5px at 85% 70%,#fff7,transparent),radial-gradient(1px 1px at 35% 80%,#fff5,transparent),radial-gradient(1px 1px at 55% 50%,#fff4,transparent)}
.glass{background:linear-gradient(180deg,rgba(29,29,54,.85),rgba(20,20,42,.92));border:1px solid #2b2b4a;border-radius:28px;box-shadow:0 40px 90px -30px rgba(0,0,0,.7)}
.pill{display:inline-flex;align-items:center;gap:10px;padding:10px 18px;border-radius:999px;font-weight:600}
`;

const jobs = [
	{
		file: 'logo.png',
		width: 512,
		height: 512,
		html: `<div style="width:512px;height:512px">${ logo.replace( 'width="64" height="64"', 'width="512" height="512"' ) }</div>`,
		transparent: true,
	},
	{
		file: 'og-default.png',
		width: 1200,
		height: 630,
		html: `<div class="stage" style="width:1200px;height:630px"><div class="stars"></div>
		<div style="position:relative;display:flex;align-items:center;gap:56px;padding:0 90px">
			<div style="width:220px;height:220px;flex:none">${ logo.replace( 'width="64" height="64"', 'width="220" height="220"' ) }</div>
			<div>
				<div style="font-size:30px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:#22d3ee">Temas WordPress · Plugins</div>
				<div style="font-size:92px;font-weight:800;letter-spacing:-.04em;line-height:1;margin:14px 0 22px">Rolck Galaxy</div>
				<div style="font-size:34px;color:#c4c4dc;line-height:1.35;max-width:720px">Temas, plugins e produtos digitais de alta performance</div>
			</div>
		</div></div>`,
	},
	{
		file: 'cover-galaxy-cards.png',
		width: 1600,
		height: 1000,
		html: `<div class="stage" style="width:1600px;height:1000px"><div class="stars"></div>
		<div style="position:relative;width:1080px">
			<div style="display:flex;align-items:center;gap:22px;margin-bottom:34px;color:#a3a3bd;font-size:26px">
				<span style="color:#22d3ee;font-weight:600">04:12</span>
				<div style="flex:1;display:flex;gap:6px">${ Array.from( { length: 20 }, ( _, i ) => `<span style="flex:1;height:10px;border-radius:9px;background:${ i < 13 ? '#22d3ee' : '#ffffff1f' }"></span>` ).join( '' ) }</div>
				<span>13 de 20</span>
			</div>
			<div class="glass" style="padding:90px 80px;text-align:center">
				<div style="font-size:30px;color:#a3a3bd;letter-spacing:.12em;text-transform:uppercase">inglês</div>
				<div style="font-size:150px;font-weight:700;letter-spacing:-.04em;margin:18px 0">reliable</div>
				<div style="font-size:44px;color:#22d3ee;font-weight:600">confiável</div>
				<div style="font-size:34px;color:#c4c4dc;margin-top:34px;font-style:italic">“She is a reliable friend.”</div>
			</div>
			<div style="display:grid;grid-template-columns:1fr 1fr;gap:26px;margin-top:34px">
				<div style="padding:32px;border-radius:24px;border:2px solid rgba(245,158,11,.4);background:rgba(245,158,11,.1);font-size:36px;color:#fbbf24;font-weight:600">Difícil · volta neste baralho</div>
				<div style="padding:32px;border-radius:24px;border:2px solid rgba(34,211,238,.4);background:rgba(34,211,238,.1);font-size:36px;color:#22d3ee;font-weight:600">✓ Fácil · volta em 15 dias</div>
			</div>
		</div></div>`,
	},
	{
		file: 'cover-galaxy-health.png',
		width: 1600,
		height: 1000,
		html: `<div class="stage" style="width:1600px;height:1000px"><div class="stars"></div>
		<div style="position:relative;display:grid;grid-template-columns:1.25fr 1fr;gap:34px;width:1300px">
			<div class="glass" style="padding:56px">
				<div style="font-size:28px;color:#22c55e;font-weight:600;letter-spacing:.1em;text-transform:uppercase">Treino de hoje</div>
				<div style="font-size:72px;font-weight:800;letter-spacing:-.03em;margin:10px 0 36px">Costas e ombros</div>
				${ [
					[ 'Remada com superband', '3 × 12', 'Verde + Azul' ],
					[ 'Face pull', '3 × 15', 'Amarelo' ],
					[ 'Desenvolvimento', '4 × 10', 'Azul' ],
				]
					.map(
						( [ n, s, c ] ) => `
				<div style="display:flex;align-items:center;justify-content:space-between;padding:26px 0;border-top:1px solid #2b2b4a;font-size:34px">
					<span style="font-weight:600">${ n }</span><span style="color:#a3a3bd">${ s } · ${ c }</span>
				</div>`
					)
					.join( '' ) }
			</div>
			<div style="display:grid;gap:34px">
				<div class="glass" style="padding:48px;text-align:center">
					<div style="font-size:28px;color:#a3a3bd">Descanso</div>
					<div style="font-size:120px;font-weight:800;color:#22c55e;letter-spacing:-.04em">0:45</div>
				</div>
				<div class="glass" style="padding:48px">
					<div style="font-size:28px;color:#a3a3bd;margin-bottom:22px">Alongamentos de hoje</div>
					<div style="display:flex;gap:14px">${ [ 1, 1, 1, 0, 0 ].map( ( d ) => `<span style="flex:1;height:70px;border-radius:18px;background:${ d ? 'linear-gradient(135deg,#22c55e,#0e7490)' : '#ffffff14' }"></span>` ).join( '' ) }</div>
				</div>
			</div>
		</div></div>`,
	},
];

const browser = await launch();
for ( const job of jobs ) {
	const page = await browser.newPage( {
		viewport: { width: job.width, height: job.height },
	} );
	await page.setContent(
		`<!doctype html><html><head><style>${ base } body{background:${ job.transparent ? 'transparent' : '#0a0a1a' }}</style></head><body>${ job.html }</body></html>`
	);
	await page.evaluate( () => document.fonts.ready );
	await page.screenshot( {
		path: join( out, job.file ),
		omitBackground: !! job.transparent,
	} );
	await page.close();
	console.log( '✓', job.file );
}
await browser.close();

// Captura real do Galaxy Academy (tema do próprio Rodolfo), se o projeto estiver ao lado.
const academy = join(
	root,
	'..',
	'galaxy-academy/themes/galaxy-academy/screenshot.png'
);
if ( existsSync( academy ) ) {
	copyFileSync( academy, join( out, 'cover-galaxy-academy.png' ) );
	console.log( '✓ cover-galaxy-academy.png (screenshot real do tema)' );
}
