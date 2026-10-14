/**
 * Atalhos para ler e gravar dados do post aberto no editor.
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';

export function usePost() {
	return useSelect( ( select ) => {
		const editor = select( editorStore );
		return {
			id: editor.getCurrentPostId(),
			type: editor.getCurrentPostType(),
			title: editor.getEditedPostAttribute( 'title' ) || '',
			excerpt: editor.getEditedPostAttribute( 'excerpt' ) || '',
			link: editor.getPermalink() || '',
			meta: editor.getEditedPostAttribute( 'meta' ) || {},
			lang: editor.getEditedPostAttribute( 'rolck_lang' ),
			isNew: editor.isEditedPostNew(),
		};
	}, [] );
}

export function useEditPost() {
	const { editPost } = useDispatch( editorStore );
	return {
		setMeta: ( patch ) => editPost( { meta: patch } ),
		setField: ( key, value ) => editPost( { [ key ]: value } ),
	};
}
