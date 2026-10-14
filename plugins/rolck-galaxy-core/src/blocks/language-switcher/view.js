/**
 * Seletor de idioma com a pílula deslizando até o idioma clicado.
 *
 * Sem JavaScript, os links funcionam normalmente e o idioma atual aparece
 * com fundo em gradiente. Com JavaScript, esse fundo vira uma pílula única
 * que desliza; a troca de página espera a pílula chegar.
 */
import { store, getElement } from '@wordpress/interactivity';

const reduced = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
const DURATION = 320;

// Põe a pílula sobre o link (posição e largura em variáveis CSS).
const moveTo = ( list, link ) => {
	if ( ! link ) {
		return;
	}
	list.style.setProperty( '--rolck-lang-x', `${ link.offsetLeft }px` );
	list.style.setProperty( '--rolck-lang-w', `${ link.offsetWidth }px` );
};

const current = ( list ) =>
	list.querySelector( '.rolck-lang__link[aria-current="true"]' );

store( 'rolck/language-switcher', {
	actions: {
		go( event ) {
			// Ctrl/Cmd + clique continua abrindo em outra aba.
			if (
				event.metaKey ||
				event.ctrlKey ||
				event.shiftKey ||
				event.button === 1
			) {
				return;
			}
			const { ref } = getElement();
			const list = ref.closest( '.rolck-lang__list' );
			const previous = current( list );
			event.preventDefault();
			if ( previous === ref ) {
				return;
			}
			previous?.removeAttribute( 'aria-current' );
			ref.setAttribute( 'aria-current', 'true' );
			moveTo( list, ref );
			setTimeout(
				() => window.location.assign( ref.href ),
				reduced ? 0 : DURATION
			);
		},
	},
	callbacks: {
		init() {
			const { ref: list } = getElement();
			const place = () => moveTo( list, current( list ) );
			place();
			// Liga a transição só depois da primeira posição (sem deslizar ao carregar).
			requestAnimationFrame( () => list.classList.add( 'is-ready' ) );

			// Fontes carregando ou nome completo mudando de largura.
			const observer = new window.ResizeObserver( place );
			observer.observe( list );

			// Voltar pelo navegador restaura a página do cache: realinha a pílula.
			const restore = ( event ) => {
				if ( ! event.persisted ) {
					return;
				}
				list.querySelectorAll( '.rolck-lang__link' ).forEach(
					( link ) => {
						if ( link.dataset.current === 'true' ) {
							link.setAttribute( 'aria-current', 'true' );
						} else {
							link.removeAttribute( 'aria-current' );
						}
					}
				);
				place();
			};
			window.addEventListener( 'pageshow', restore );

			return () => {
				observer.disconnect();
				window.removeEventListener( 'pageshow', restore );
			};
		},
	},
} );
