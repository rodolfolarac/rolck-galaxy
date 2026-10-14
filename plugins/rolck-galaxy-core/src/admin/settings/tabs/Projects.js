import { Pair, Section, Shortcuts } from '../fields';
import { useSettings } from '../context';

export default function Projects() {
	const { boot, get } = useSettings();
	return (
		<>
			<Section
				title="Endereços da vitrine"
				description="Use letras minúsculas, sem acento e com hífens. As URLs são regravadas sozinhas ao salvar."
			>
				<Pair
					path="projects.slug"
					label="Endereço da vitrine e dos projetos"
					help={ `Ex.: ${ boot.siteUrl }${ get( 'projects.slug.pt' ) }/galaxy-academy/ e ${ boot.siteUrl }en/${ get( 'projects.slug.en' ) }/galaxy-academy/` }
				/>
				<Pair
					path="projects.type_slug"
					label="Endereço das categorias de projeto"
				/>
			</Section>
			<Section title="Atalhos">
				<Shortcuts
					items={ [
						{
							href: boot.links.projects,
							title: 'Todos os projetos',
							text: 'Cadastrar, ordenar (campo Ordem) e marcar destaques.',
						},
						{
							href: `${ boot.links.projects.replace( 'edit.php', 'edit-tags.php' ) }&taxonomy=rolck_project_type`,
							title: 'Categorias de projeto',
							text: 'Temas WordPress, Plugins, Templates… com nome em inglês.',
						},
						{
							href: `${ boot.links.projects.replace( 'edit.php', 'edit-tags.php' ) }&taxonomy=rolck_tech`,
							title: 'Tecnologias',
							text: 'React, PHP, Gutenberg… aparecem nos cartões e na ficha.',
						},
					] }
				/>
			</Section>
		</>
	);
}
