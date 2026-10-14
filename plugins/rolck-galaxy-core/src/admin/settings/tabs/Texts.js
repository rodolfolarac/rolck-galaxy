import { Button, SearchControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { Pair, Section } from '../fields';
import { useSettings } from '../context';

export default function Texts() {
	const { boot, get, getDefault, set } = useSettings();
	const [ search, setSearch ] = useState( '' );
	const term = search.trim().toLowerCase();

	const matches = ( key ) =>
		! term ||
		[
			get( `strings.${ key }.pt` ),
			get( `strings.${ key }.en` ),
			key,
		].some( ( v ) =>
			String( v || '' )
				.toLowerCase()
				.includes( term )
		);

	return (
		<>
			<p className="rolck-admin__intro">
				Botões, rótulos e mensagens fixas do site, nos dois idiomas. Com
				a tradução automática ligada (aba Idiomas), basta mudar o
				português: o inglês acompanha, a menos que você tenha escrito o
				inglês à mão. O texto de cada página você edita no próprio
				editor de blocos.
			</p>
			<SearchControl
				__nextHasNoMarginBottom
				value={ search }
				onChange={ setSearch }
				placeholder="Buscar texto…"
				className="rolck-admin__search"
			/>
			{ Object.entries( boot.stringGroups ).map( ( [ group, keys ] ) => {
				const visible = keys.filter( matches );
				if ( ! visible.length ) {
					return null;
				}
				return (
					<Section key={ group } title={ group }>
						{ visible.map( ( key ) => {
							const changed = [ 'pt', 'en' ].some(
								( c ) =>
									get( `strings.${ key }.${ c }` ) !==
									getDefault( `strings.${ key }.${ c }` )
							);
							return (
								<div
									className="rolck-admin__string is-wide"
									key={ key }
								>
									<Pair
										path={ `strings.${ key }` }
										label={ getDefault(
											`strings.${ key }.pt`
										) }
									/>
									{ changed && (
										<Button
											variant="link"
											onClick={ () =>
												set(
													`strings.${ key }`,
													getDefault(
														`strings.${ key }`
													)
												)
											}
										>
											Restaurar o padrão
										</Button>
									) }
								</div>
							);
						} ) }
					</Section>
				);
			} ) }
		</>
	);
}
