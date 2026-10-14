/**
 * Movimento do Visual cósmico: os planetas com velocidade andam pelo anel e,
 * quando vão encostar em outro planeta, invertem o sentido.
 *
 * A projeção é a mesma do render.php (anel = círculo inclinado no espaço).
 * Na metade da frente o planeta passa por cima da esfera e fica um pouco
 * maior; na de trás, fica atrás dela. Para quando sai da tela e não roda
 * para quem prefere menos movimento.
 */
const GAP = 3;
const reduced = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

const start = ( svg ) => {
	const { orbits, bodies, still } = JSON.parse( svg.dataset.motion );
	const layers = {
		back: svg.querySelector( '[data-layer="back"]' ),
		front: svg.querySelector( '[data-layer="front"]' ),
	};

	const project = ( orbit, angle ) => {
		const t = ( angle * Math.PI ) / 180;
		const rot = ( orbit.rot * Math.PI ) / 180;
		const inc = ( orbit.inc * Math.PI ) / 180;
		const lx = orbit.R * Math.cos( t );
		const ly = orbit.R * Math.cos( inc ) * Math.sin( t );
		const z = Math.sin( t ) * Math.sin( inc );
		return {
			x: 260 + lx * Math.cos( rot ) - ly * Math.sin( rot ),
			y: 260 + lx * Math.sin( rot ) + ly * Math.cos( rot ),
			z,
			s: 1 + 0.2 * z,
		};
	};

	const items = bodies.map( ( body, i ) => ( {
		...body,
		el: svg.querySelector( `[data-p="${ i }"]` ),
		dir: Math.sign( body.v ),
		speed: Math.abs( body.v ),
		pos: project( orbits[ body.o ], body.t ),
	} ) );
	const fixed = still.map( ( [ x, y, r ] ) => ( {
		r,
		pos: { x, y, z: 0, s: 1 },
	} ) );
	const moving = items.filter( ( item ) => item.speed );

	// Encostaria em outro planeta (no mesmo anel ou perto na mesma profundidade)?
	const blocked = ( item, next ) =>
		[ ...items, ...fixed ].some( ( other ) => {
			if ( other === item ) {
				return false;
			}
			const sameRing = other.o === item.o;
			if ( ! sameRing && Math.abs( other.pos.z - next.z ) > 0.25 ) {
				return false;
			}
			const min = item.r * next.s + other.r * other.pos.s + GAP;
			const now = Math.hypot(
				item.pos.x - other.pos.x,
				item.pos.y - other.pos.y
			);
			const then = Math.hypot(
				next.x - other.pos.x,
				next.y - other.pos.y
			);
			return then < min && then < now;
		} );

	const draw = ( item ) => {
		const { x, y, s, z } = item.pos;
		item.el.setAttribute(
			'transform',
			`translate(${ x.toFixed( 1 ) } ${ y.toFixed( 1 ) }) scale(${ s.toFixed( 3 ) })`
		);
		const layer = z > 0 ? layers.front : layers.back;
		if ( item.el.parentNode !== layer ) {
			layer.appendChild( item.el );
		}
	};

	let last = 0;
	let frame = 0;
	const tick = ( now ) => {
		const dt = last ? Math.min( 0.05, ( now - last ) / 1000 ) : 0;
		last = now;
		moving.forEach( ( item ) => {
			const angle = item.t + item.dir * item.speed * dt;
			const next = project( orbits[ item.o ], angle );
			if ( blocked( item, next ) ) {
				item.dir = -item.dir;
				return;
			}
			item.t = angle;
			item.pos = next;
			draw( item );
		} );
		frame = window.requestAnimationFrame( tick );
	};

	// Só anima enquanto o desenho está na tela.
	new window.IntersectionObserver( ( [ entry ] ) => {
		window.cancelAnimationFrame( frame );
		last = 0;
		if ( entry.isIntersecting ) {
			frame = window.requestAnimationFrame( tick );
		}
	} ).observe( svg );
};

if ( ! reduced ) {
	document
		.querySelectorAll( '.rolck-cosmic__svg[data-motion]' )
		.forEach( start );
}
