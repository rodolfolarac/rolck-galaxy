/** Dados enviados pelo PHP (Admin\EditorAssets). */
export const config = window.rolckEditor || {
	languages: [],
	defaultLanguage: 'pt',
	translatableTypes: [],
	projectType: 'rolck_project',
	projectStatuses: [],
	appCategories: [],
	siteName: '',
	separator: '·',
	homeUrl: '/',
};

export const languageLabel = ( code ) =>
	config.languages.find( ( l ) => l.code === code )?.label || code;
