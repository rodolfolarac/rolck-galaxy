/**
 * Aviso de cookies (LGPD): mostra o aviso, guarda a escolha, registra a
 * prova no servidor e ativa só os scripts das categorias aceitas.
 *
 * A escolha fica no cookie rolck_consent:
 *   { v: versão do aviso, id: código, t: data (ms), c: aceitas, o: oferecidas }
 * O aviso volta quando a versão muda, a validade vence ou o site passa a
 * usar uma categoria que ainda não foi oferecida a este visitante.
 *
 * Qualquer link para "#cookies" ou elemento com data-rolck-consent-open
 * reabre as preferências (o rodapé tem um). Também dá para usar
 * window.rolckConsent.open() e window.rolckConsent.get().
 */
import './style.scss';

const COOKIE = 'rolck_consent';
const configEl = document.getElementById( 'rolck-consent-config' );
const config = configEl ? JSON.parse( configEl.textContent ) : null;
const banner = document.getElementById( 'rolck-consent' );
const dialog = document.getElementById( 'rolck-consent-prefs' );
const status = document.querySelector( '.rolck-consent-status' );

const read = () => {
	const raw = document.cookie
		.split( '; ' )
		.find( ( part ) => part.startsWith( COOKIE + '=' ) );
	if ( ! raw ) {
		return null;
	}
	try {
		return JSON.parse(
			decodeURIComponent( raw.slice( COOKIE.length + 1 ) )
		);
	} catch {
		return null;
	}
};

const isValid = ( saved ) =>
	saved &&
	saved.v === config.version &&
	Date.now() - saved.t < config.days * 864e5 &&
	config.categories.every( ( cat ) => ( saved.o || [] ).includes( cat ) );

// Código aleatório no formato UUID v4 (o mesmo validado pelo servidor).
const uuid = () => {
	if ( window.crypto.randomUUID ) {
		return window.crypto.randomUUID();
	}
	const bytes = window.crypto.getRandomValues( new Uint8Array( 16 ) );
	bytes[ 6 ] = ( bytes[ 6 ] % 16 ) + 64;
	bytes[ 8 ] = ( bytes[ 8 ] % 64 ) + 128;
	const hex = [ ...bytes ].map( ( b ) =>
		b.toString( 16 ).padStart( 2, '0' )
	);
	return [ 0, 4, 6, 8, 10 ]
		.map( ( from, i, all ) => hex.slice( from, all[ i + 1 ] ).join( '' ) )
		.join( '-' );
};

// Ativa os scripts guardados como texto/<template> das categorias aceitas.
const activate = ( granted ) => {
	document.querySelectorAll( '[data-rolck-consent]' ).forEach( ( el ) => {
		if (
			el.dataset.done ||
			! granted.includes( el.dataset.rolckConsent )
		) {
			return;
		}
		el.dataset.done = '1';
		const revive = ( old ) => {
			const fresh = document.createElement( 'script' );
			[ ...old.attributes ].forEach( ( attr ) => {
				if (
					! [ 'type', 'data-rolck-consent', 'data-done' ].includes(
						attr.name
					)
				) {
					fresh.setAttribute( attr.name, attr.value );
				}
			} );
			fresh.text = old.text;
			return fresh;
		};
		if ( el.tagName === 'TEMPLATE' ) {
			const content = el.content.cloneNode( true );
			content
				.querySelectorAll( 'script' )
				.forEach( ( old ) => old.replaceWith( revive( old ) ) );
			el.after( content );
		} else {
			el.after( revive( el ) );
		}
	} );
};

// Apaga os cookies do Google Analytics quando a estatística é recusada depois.
const clearAnalytics = () => {
	const host = window.location.hostname.split( '.' );
	const domains = host.map( ( _, i ) => '.' + host.slice( i ).join( '.' ) );
	document.cookie.split( '; ' ).forEach( ( part ) => {
		const name = part.split( '=' )[ 0 ];
		if ( /^_ga/.test( name ) ) {
			[ '', ...domains ].forEach( ( domain ) => {
				document.cookie = `${ name }=; Max-Age=0; path=/${
					domain ? '; domain=' + domain : ''
				}`;
			} );
		}
	} );
};

const showRecord = ( saved ) => {
	const el = dialog?.querySelector( '[data-consent-record]' );
	if ( ! el ) {
		return;
	}
	el.textContent = saved
		? `${ saved.id } · ${ new Date( saved.t ).toLocaleString(
				document.documentElement.lang
			) }`
		: el.dataset.empty;
};

const announce = () => {
	if ( status ) {
		status.textContent = status.dataset.saved;
		setTimeout( () => ( status.textContent = '' ), 4000 );
	}
};

const save = ( granted, action ) => {
	const previous = read();
	const saved = {
		v: config.version,
		id: previous?.id || uuid(),
		t: Date.now(),
		c: granted,
		o: config.categories,
	};
	const secure = window.location.protocol === 'https:' ? '; Secure' : '';
	document.cookie = `${ COOKIE }=${ encodeURIComponent(
		JSON.stringify( saved )
	) }; Max-Age=${ config.days * 86400 }; path=/; SameSite=Lax${ secure }`;

	if ( config.log ) {
		window
			.fetch( config.endpoint, {
				method: 'POST',
				keepalive: true,
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify( {
					id: saved.id,
					action,
					granted,
					offered: config.categories,
					version: config.version,
					lang: config.lang,
					page: window.location.href.split( /[?#]/ )[ 0 ],
				} ),
			} )
			.catch( () => {} );
	}

	banner.hidden = true;
	if ( dialog?.open ) {
		dialog.close();
	}
	showRecord( saved );
	announce();

	// Retirar um consentimento dado antes: apaga e recarrega sem os scripts.
	const revoked = ( previous?.c || [] ).filter(
		( cat ) => ! granted.includes( cat )
	);
	if ( revoked.length ) {
		if ( revoked.includes( 'analytics' ) ) {
			clearAnalytics();
		}
		window.location.reload();
		return;
	}
	activate( granted );
};

const open = () => {
	if ( ! dialog ) {
		return;
	}
	const saved = read();
	const granted = isValid( saved ) ? saved.c : [];
	dialog
		.querySelectorAll( '.rolck-consent-cat__switch' )
		.forEach(
			( input ) => ( input.checked = granted.includes( input.name ) )
		);
	showRecord( isValid( saved ) ? saved : null );
	dialog.showModal();
};

const handle = ( action ) => {
	if ( action === 'open' ) {
		open();
	} else if ( action === 'close' ) {
		dialog.close();
	} else if ( action === 'accept' ) {
		save(
			config.categories,
			config.categories.length ? 'accept_all' : 'acknowledge'
		);
	} else if ( action === 'reject' ) {
		save( [], 'reject_all' );
	} else if ( action === 'save' ) {
		const granted = [
			...dialog.querySelectorAll( '.rolck-consent-cat__switch:checked' ),
		].map( ( input ) => input.name );
		let kind = 'custom';
		if ( ! granted.length ) {
			kind = 'reject_all';
		} else if ( granted.length === config.categories.length ) {
			kind = 'accept_all';
		}
		save( granted, kind );
	}
};

if ( config && banner ) {
	const saved = read();
	// O aviso já vem visível no HTML; o script logo depois dele o esconde
	// para quem já escolheu. Aqui só ativamos o que foi aceito.
	banner.hidden = isValid( saved );
	if ( isValid( saved ) ) {
		activate( saved.c );
	}

	document.addEventListener( 'click', ( event ) => {
		const button = event.target.closest( '[data-consent]' );
		if (
			button &&
			( banner.contains( button ) || dialog?.contains( button ) )
		) {
			handle( button.dataset.consent );
			return;
		}
		const opener = event.target.closest(
			'a[href$="#cookies"], [data-rolck-consent-open]'
		);
		if ( opener ) {
			event.preventDefault();
			open();
		}
	} );

	// Clique no fundo escuro fecha as preferências.
	dialog?.addEventListener( 'click', ( event ) => {
		if ( event.target === dialog ) {
			dialog.close();
		}
	} );

	window.rolckConsent = {
		open,
		get: () => ( isValid( read() ) ? read().c : null ),
	};
}
