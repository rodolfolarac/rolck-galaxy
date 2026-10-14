import {
	Media,
	Pair,
	Section,
	Select,
	Shortcuts,
	Text,
	Toggle,
} from '../fields';
import { useSettings } from '../context';

export default function Seo() {
	const { boot } = useSettings();
	return (
		<>
			<Section
				title="Página inicial"
				description="Título e descrição que o Google mostra para a home de cada idioma."
			>
				<Pair path="seo.home_title" label="Título da home" max={ 60 } />
				<Pair
					path="seo.home_description"
					label="Descrição da home"
					long
					rows={ 3 }
					max={ 160 }
				/>
			</Section>

			<Section
				title="Padrões"
				description="Usados em todas as páginas que não têm valores próprios no painel SEO do editor."
			>
				<Text
					path="seo.separator"
					label="Separador do título"
					help="Ex.: Projetos · Rolck Galaxy"
				/>
				<Select
					path="seo.organization_type"
					label="Tipo da empresa (schema.org)"
					options={ [
						{ label: 'Organização', value: 'Organization' },
						{
							label: 'Serviço profissional',
							value: 'ProfessionalService',
						},
						{ label: 'Empresa local', value: 'LocalBusiness' },
						{ label: 'Corporação', value: 'Corporation' },
					] }
				/>
				<Media
					path="seo.default_image_id"
					label="Imagem padrão de compartilhamento"
					help="1200 × 630 px. Aparece no WhatsApp, LinkedIn e X quando a página não tem imagem própria."
				/>
				<Text
					path="seo.twitter_handle"
					label="Usuário no X"
					placeholder="@rolckgalaxy"
				/>
			</Section>

			<Section title="Indexação">
				<Toggle
					path="seo.noindex_search"
					label="Não indexar resultados de busca interna"
					help="Recomendado: evita páginas fracas e duplicadas no Google."
				/>
				<Toggle
					path="seo.noindex_paged"
					label="Não indexar páginas 2, 3… das listagens"
					help="Normalmente deixe desligado; o canonical já resolve."
				/>
			</Section>

			<Section
				title="Verificação e ferramentas"
				description='Cole só o código (o valor de content="…").'
			>
				<Text
					path="seo.google_verification"
					label="Google Search Console"
				/>
				<Text
					path="seo.bing_verification"
					label="Bing Webmaster Tools"
				/>
				<Shortcuts
					items={ [
						{
							href: boot.links.sitemap,
							title: 'Sitemap',
							text: 'Envie este endereço no Search Console e no Bing.',
							external: true,
						},
						{
							href: boot.links.pagespeed,
							title: 'Testar no PageSpeed Insights',
							text: 'Mede performance, acessibilidade, boas práticas e SEO.',
							external: true,
						},
					] }
				/>
			</Section>
		</>
	);
}
