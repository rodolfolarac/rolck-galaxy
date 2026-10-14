/**
 * Contagem animada dos números quando eles entram na tela.
 * Números já visíveis no carregamento não são animados (evita "piscar").
 */
import { store, getElement } from '@wordpress/interactivity';

const reduced = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

store( 'rolck/stats', {
	callbacks: {
		countUp() {
			const { ref } = getElement();
			const target = Number(
				String( ref.dataset.target ).replace( /[.,]/g, '' )
			);
			if (
				reduced ||
				! target ||
				! ( 'IntersectionObserver' in window )
			) {
				return;
			}
			const final = ref.textContent;
			let first = true;

			const observer = new IntersectionObserver( ( [ entry ] ) => {
				if ( first ) {
					first = false;
					if ( entry.isIntersecting ) {
						observer.disconnect();
						return;
					}
				}
				if ( ! entry.isIntersecting ) {
					return;
				}
				observer.disconnect();
				// Trava a largura final para o texto não "pular" enquanto conta.
				ref.style.minWidth = `${ ref.offsetWidth }px`;
				const start = performance.now();
				const duration = 1400;
				const tick = ( now ) => {
					const progress = Math.min( 1, ( now - start ) / duration );
					const eased = 1 - Math.pow( 1 - progress, 3 );
					ref.textContent =
						progress < 1
							? Math.round( target * eased ).toLocaleString(
									document.documentElement.lang
								)
							: final;
					if ( progress < 1 ) {
						requestAnimationFrame( tick );
					}
				};
				requestAnimationFrame( tick );
			} );
			observer.observe( ref );
			return () => observer.disconnect();
		},
	},
} );
