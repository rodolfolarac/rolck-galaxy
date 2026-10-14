/**
 * Envio do formulário de contato sem recarregar a página (Interactivity API).
 */
import { store, getContext, getElement } from '@wordpress/interactivity';

const { state } = store( 'rolck/contact-form', {
	state: {
		get isSending() {
			return getContext().status === 'sending';
		},
		get isError() {
			return getContext().status === 'error';
		},
		get isSuccess() {
			return getContext().status === 'success';
		},
		get buttonText() {
			return state.isSending
				? state.texts.contact_sending
				: state.texts.contact_send;
		},
	},
	actions: {
		*submit( event ) {
			event.preventDefault();
			const context = getContext();
			const form = getElement().ref;
			const data = Object.fromEntries( new FormData( form ) );

			if (
				! data.name?.trim() ||
				! form.email.checkValidity() ||
				! data.email ||
				( data.message || '' ).trim().length < 5
			) {
				context.status = 'error';
				context.feedback = state.texts.contact_invalid;
				return;
			}

			context.status = 'sending';
			context.feedback = '';

			try {
				const response = yield fetch( state.endpoint, {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify( {
						...data,
						lang: state.lang,
						started: state.started,
						page: window.location.href,
					} ),
				} );
				const body = yield response.json();
				if ( ! response.ok ) {
					throw new Error(
						body?.message || state.texts.contact_error
					);
				}
				context.status = 'success';
				context.feedback = body.message;
				form.reset();
			} catch ( error ) {
				context.status = 'error';
				context.feedback = error.message || state.texts.contact_error;
			}
		},
	},
} );
