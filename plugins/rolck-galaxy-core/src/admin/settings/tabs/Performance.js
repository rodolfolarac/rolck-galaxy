import { Section, Select, Toggle } from '../fields';

export default function Performance() {
	return (
		<>
			<Section
				title="Enxugar o WordPress"
				description="Remove recursos padrão que este site não usa. Tudo recomendado ligado."
			>
				<Toggle
					path="performance.disable_emojis"
					label="Remover script de emojis"
					help="O navegador já desenha emojis; economiza um script em toda página."
				/>
				<Toggle
					path="performance.disable_embeds"
					label="Remover oEmbed do WordPress"
					help="Outros sites deixam de incorporar suas páginas como cartão; vídeos do YouTube continuam funcionando."
				/>
				<Toggle
					path="performance.clean_head"
					label="Limpar o <head>"
					help="Tira links de RSD, Windows Live Writer, versão do WordPress e shortlink."
				/>
				<Toggle
					path="performance.disable_xmlrpc"
					label="Desligar XML-RPC"
					help="Fecha uma porta muito usada em ataques de força bruta."
				/>
				<Toggle
					path="performance.disable_dashicons"
					label="Não carregar Dashicons para visitantes"
				/>
				<Toggle
					path="performance.inline_css"
					label="Embutir o CSS no HTML"
					help="Os estilos do tema e dos blocos vão dentro da página em vez de arquivos separados. Remove o CSS que bloqueia a renderização (recomendado)."
				/>
				<Toggle
					path="performance.webp_uploads"
					label="Gerar imagens em WebP"
					help="Os tamanhos criados a partir de JPEG e PNG enviados saem em WebP, 25–35% menores. Vale para imagens enviadas daqui em diante."
				/>
			</Section>
			<Section
				title="Navegação instantânea"
				description="O navegador prepara a próxima página quando o visitante passa o mouse no link (Speculation Rules, nativo do WordPress)."
			>
				<Select
					path="performance.speculation"
					label="Modo"
					options={ [
						{
							label: 'Pré-renderizar (mais rápido)',
							value: 'prerender',
						},
						{
							label: 'Pré-carregar (mais leve)',
							value: 'prefetch',
						},
						{ label: 'Desligado', value: 'off' },
					] }
				/>
			</Section>
		</>
	);
}
