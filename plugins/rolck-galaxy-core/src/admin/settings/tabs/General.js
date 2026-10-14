import { Media, Pair, Section, Text } from '../fields';

const NETWORKS = [
	[ 'github', 'GitHub' ],
	[ 'linkedin', 'LinkedIn' ],
	[ 'instagram', 'Instagram' ],
	[ 'youtube', 'YouTube' ],
	[ 'x', 'X (Twitter)' ],
	[ 'dribbble', 'Dribbble' ],
];

export default function General() {
	return (
		<>
			<Section
				title="Empresa"
				description="Usado no rodapé, no copyright, nos dados estruturados do Google e nos e-mails."
			>
				<Text path="general.company_name" label="Nome da empresa" />
				<Text
					path="general.founded_year"
					label="Ano de fundação"
					type="number"
					help="O copyright mostra “2026–ano atual”."
				/>
				<Pair
					path="general.tagline"
					label="Slogan"
					long
					rows={ 2 }
					help="Também é a descrição padrão do site quando uma página não tem uma."
				/>
				<Media
					path="general.logo_id"
					label="Logo para o Google"
					help="PNG ou SVG quadrado, mínimo 112 × 112 px. O logo do cabeçalho é o bloco Logo do site, no Editor do site."
				/>
			</Section>

			<Section
				title="Contato"
				description="Aparece no bloco “Dados de contato” e no rodapé."
			>
				<Text path="general.email" label="E-mail" type="email" />
				<Text
					path="general.phone"
					label="Telefone"
					placeholder="+55 11 99999-9999"
				/>
				<Text
					path="general.whatsapp"
					label="WhatsApp (com DDI e DDD)"
					placeholder="5511999999999"
				/>
				<Pair path="general.address" label="Cidade / endereço" />
			</Section>

			<Section
				title="Redes sociais"
				description="Só as redes preenchidas aparecem no site. Os links também vão para o Google (sameAs), o que ajuda a ligar a marca aos perfis."
			>
				{ NETWORKS.map( ( [ key, label ] ) => (
					<Text
						key={ key }
						path={ `general.social.${ key }` }
						label={ label }
						type="url"
						placeholder="https://"
					/>
				) ) }
			</Section>
		</>
	);
}
