/**
 * Painel "SEO": título e descrição para o Google, imagem de compartilhamento,
 * opção de não indexar e uma prévia de como o resultado aparece na busca.
 */
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import {
	Button,
	TextareaControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { config } from './config';
import { useEditPost, usePost } from './use-post';

const LIMITS = { title: 60, description: 160 };

function Counter( { value, max } ) {
	const length = value.length;
	let tone = 'is-short';
	if ( length === 0 ) {
		tone = '';
	} else if ( length > max ) {
		tone = 'is-over';
	} else if ( length >= max * 0.7 ) {
		tone = 'is-good';
	}
	return (
		<span className={ `rolck-panel__counter ${ tone }` }>
			{ length } / { max }
		</span>
	);
}

export default function SeoPanel() {
	const post = usePost();
	const { setMeta } = useEditPost();

	const imageId = post.meta._rolck_seo_image || 0;
	const image = useSelect(
		( select ) =>
			imageId
				? select( coreStore ).getEntityRecord(
						'root',
						'media',
						imageId
					)
				: null,
		[ imageId ]
	);

	if (
		! config.translatableTypes.includes( post.type ) ||
		post.type === 'wp_block' ||
		post.meta._rolck_seo_title === undefined
	) {
		return null;
	}

	const title = post.meta._rolck_seo_title || '';
	const description = post.meta._rolck_seo_description || '';
	const previewTitle =
		title || `${ post.title } ${ config.separator } ${ config.siteName }`;
	const previewDescription =
		description ||
		post.excerpt ||
		'Sem descrição: o site usa o resumo ou o começo do texto.';

	return (
		<PluginDocumentSettingPanel
			name="rolck-seo"
			title="SEO"
			className="rolck-panel"
		>
			<div className="rolck-serp" aria-hidden="true">
				<span className="rolck-serp__url">
					{ post.link.replace( /^https?:\/\//, '' ) }
				</span>
				<span className="rolck-serp__title">{ previewTitle }</span>
				<span className="rolck-serp__text">{ previewDescription }</span>
			</div>

			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label={
					<>
						Título para o Google{ ' ' }
						<Counter value={ title } max={ LIMITS.title } />
					</>
				}
				help="Vazio = título da página + nome do site."
				value={ title }
				onChange={ ( v ) => setMeta( { _rolck_seo_title: v } ) }
			/>
			<TextareaControl
				__nextHasNoMarginBottom
				label={
					<>
						Descrição{ ' ' }
						<Counter
							value={ description }
							max={ LIMITS.description }
						/>
					</>
				}
				help="Entre 120 e 160 caracteres, com a palavra-chave principal. Vazio = resumo do conteúdo."
				value={ description }
				onChange={ ( v ) => setMeta( { _rolck_seo_description: v } ) }
			/>

			<div className="rolck-panel__field">
				<span className="rolck-panel__label">
					Imagem ao compartilhar
				</span>
				{ image?.source_url && (
					<img
						className="rolck-panel__thumb"
						src={ image.source_url }
						alt=""
					/>
				) }
				<MediaUploadCheck>
					<MediaUpload
						allowedTypes={ [ 'image' ] }
						value={ imageId }
						onSelect={ ( media ) =>
							setMeta( { _rolck_seo_image: media.id } )
						}
						render={ ( { open } ) => (
							<Button variant="secondary" onClick={ open }>
								{ imageId
									? 'Trocar imagem'
									: 'Escolher imagem' }
							</Button>
						) }
					/>
				</MediaUploadCheck>
				{ !! imageId && (
					<Button
						variant="link"
						isDestructive
						onClick={ () => setMeta( { _rolck_seo_image: 0 } ) }
					>
						Remover
					</Button>
				) }
				<p className="rolck-panel__hint">
					1200 × 630 px. Vazio = imagem destacada ou a imagem padrão
					do painel.
				</p>
			</div>

			<ToggleControl
				label="Esconder dos buscadores (noindex)"
				help="A página continua no ar, mas sai do Google e do sitemap."
				checked={ !! post.meta._rolck_seo_noindex }
				onChange={ ( v ) => setMeta( { _rolck_seo_noindex: v } ) }
			/>
		</PluginDocumentSettingPanel>
	);
}
