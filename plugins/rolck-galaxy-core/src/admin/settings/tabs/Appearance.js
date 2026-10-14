import { Section, Shortcuts } from '../fields';
import { useSettings } from '../context';

export default function Appearance() {
	const { boot } = useSettings();
	const { links } = boot;
	return (
		<>
			<Section
				title="Onde editar o conteúdo"
				description="Nenhum texto ou imagem do site fica preso no código. Escreva só em português: com a tradução automática ligada (aba Idiomas), o inglês acompanha."
			>
				<Shortcuts
					items={ [
						{
							href: links.pages,
							title: 'Páginas',
							text: 'Início, Serviços, Sobre, Contato…: cada título, parágrafo, botão, imagem, número e pergunta do FAQ é editado clicando nele no editor de blocos.',
						},
						{
							href: links.projects,
							title: 'Projetos',
							text: 'Nome, resumo, capa, descrição, preço, versão, destaques, demo e link de compra de cada produto.',
						},
						{
							href: links.posts,
							title: 'Posts do blog',
							text: 'Artigos, categorias e imagens de destaque.',
						},
						{
							href: links.cta,
							title: 'Chamada final',
							text: 'O bloco “Vamos conversar” do fim dos projetos, posts e vitrine.',
						},
						{
							href: links.media,
							title: 'Imagens',
							text: 'Biblioteca de mídia. O logo e a imagem de compartilhamento ficam nas abas Geral e SEO.',
						},
						{
							href: links.texts,
							title: 'Textos do site',
							text: 'Botões, rótulos, títulos da vitrine, do blog, da busca, da página 404 e mensagens do formulário (aba Textos do site).',
						},
					] }
				/>
			</Section>
			<Section
				title="Visual do site"
				description="Cores, fontes, espaçamentos, cabeçalho, rodapé e menus usam o Editor do site, a ferramenta visual do próprio WordPress. Assim tudo continua nativo, rápido e editável sem código."
			>
				<Shortcuts
					items={ [
						{
							href: links.styles,
							title: 'Cores e fontes',
							text: 'Paleta, gradientes, tipografia e tamanhos de todo o site (Estilos).',
						},
						{
							href: links.parts,
							title: 'Cabeçalho e rodapé',
							text: 'Cada idioma tem o seu: “Cabeçalho” e “Cabeçalho (inglês)”, “Rodapé” e “Rodapé (inglês)”.',
						},
						{
							href: links.navigation,
							title: 'Menus',
							text: 'Itens do menu principal e do rodapé de cada idioma.',
						},
						{
							href: links.siteEditor,
							title: 'Modelos',
							text: 'Layout das páginas, posts, projeto, vitrine, busca e 404.',
						},
						{
							href: links.reading,
							title: 'Página inicial e blog',
							text: 'Escolha as páginas em Configurações → Leitura (as versões em inglês entram sozinhas).',
						},
					] }
				/>
			</Section>
		</>
	);
}
