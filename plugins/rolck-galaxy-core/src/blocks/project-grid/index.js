import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	SelectControl,
	ToggleControl,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import ServerSideRender from '@wordpress/server-side-render';
import { icons } from '../../shared/icons';
import metadata from './block.json';
import './style.scss';

function Edit( { attributes: a, setAttributes } ) {
	const terms = useSelect(
		( select ) =>
			select( coreStore ).getEntityRecords(
				'taxonomy',
				'rolck_project_type',
				{ per_page: 100 }
			) || [],
		[]
	);

	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody title="Quais projetos">
					<ToggleControl
						label="Usar a listagem da página"
						help="Ligue nos modelos de arquivo de projetos e de categoria: a grade mostra os projetos daquela página, com paginação."
						checked={ a.inheritQuery }
						onChange={ ( inheritQuery ) =>
							setAttributes( { inheritQuery } )
						}
					/>
					{ ! a.inheritQuery && (
						<>
							<RangeControl
								label="Quantidade (0 = todos)"
								value={ a.count }
								min={ 0 }
								max={ 48 }
								onChange={ ( count ) =>
									setAttributes( { count } )
								}
							/>
							<SelectControl
								label="Categoria"
								value={ a.category }
								options={ [
									{ label: 'Todas', value: 0 },
									...terms.map( ( t ) => ( {
										label: t.name,
										value: t.id,
									} ) ),
								] }
								onChange={ ( v ) =>
									setAttributes( { category: Number( v ) } )
								}
							/>
							<ToggleControl
								label="Só projetos em destaque"
								checked={ a.featuredOnly }
								onChange={ ( featuredOnly ) =>
									setAttributes( { featuredOnly } )
								}
							/>
							<ToggleControl
								label="Esconder o projeto da página atual"
								help="Útil no bloco “Outros projetos” do modelo de projeto."
								checked={ a.excludeCurrent }
								onChange={ ( excludeCurrent ) =>
									setAttributes( { excludeCurrent } )
								}
							/>
						</>
					) }
				</PanelBody>
				<PanelBody title="Aparência">
					<RangeControl
						label="Colunas no desktop"
						value={ a.columns }
						min={ 1 }
						max={ 4 }
						onChange={ ( columns ) => setAttributes( { columns } ) }
					/>
					<ToggleControl
						label="Mostrar filtro por categoria"
						checked={ a.showFilters }
						onChange={ ( showFilters ) =>
							setAttributes( { showFilters } )
						}
					/>
					<ToggleControl
						label="Mostrar resumo"
						checked={ a.showExcerpt }
						onChange={ ( showExcerpt ) =>
							setAttributes( { showExcerpt } )
						}
					/>
					<SelectControl
						label="Nível do título dos cartões"
						value={ a.headingLevel }
						options={ [ 'h2', 'h3', 'h4' ].map( ( v ) => ( {
							label: v.toUpperCase(),
							value: v,
						} ) ) }
						onChange={ ( headingLevel ) =>
							setAttributes( { headingLevel } )
						}
					/>
					<RangeControl
						label="Imagens carregadas com prioridade"
						help="Use 1 a 3 quando a grade aparece no topo da página (melhora o LCP). Abaixo da dobra, deixe 0."
						value={ a.eagerImages }
						min={ 0 }
						max={ 4 }
						onChange={ ( eagerImages ) =>
							setAttributes( { eagerImages } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<ServerSideRender
				block={ metadata.name }
				attributes={ a }
				skipBlockSupportAttributes
			/>
		</div>
	);
}

registerBlockType( metadata.name, { icon: icons.grid, edit: Edit } );
