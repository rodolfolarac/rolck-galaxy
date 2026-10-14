/**
 * Filtro da grade de projetos (Interactivity API, nativa do WordPress).
 *
 * O contexto da grade guarda a categoria ativa; cada cartão guarda as
 * próprias categorias. Sem JavaScript, os filtros continuam funcionando como
 * links para as páginas de categoria.
 */
import { store, getContext } from '@wordpress/interactivity';

store( 'rolck/project-grid', {
	state: {
		get isVisible() {
			const { active, cats } = getContext();
			return active === 'all' || cats.includes( active );
		},
		get isCurrent() {
			const { active, slug } = getContext();
			return active === slug ? 'true' : null;
		},
	},
	actions: {
		filter( event ) {
			// Ctrl/Cmd + clique continua abrindo a página da categoria.
			if (
				event.metaKey ||
				event.ctrlKey ||
				event.shiftKey ||
				event.button === 1
			) {
				return;
			}
			event.preventDefault();
			const context = getContext();
			context.active = context.slug;
		},
	},
} );
