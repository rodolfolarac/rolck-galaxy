import { Button, Notice } from '@wordpress/components';
import { Section, Shortcuts, Text, Toggle } from '../fields';
import { useSettings } from '../context';

export default function Privacy() {
	const { boot, get, set } = useSettings();

	// Nova versão do aviso: todo visitante vê o aviso de novo e escolhe outra vez.
	const bump = () =>
		set(
			'privacy.version',
			String( ( Number( get( 'privacy.version' ) ) || 1 ) + 1 )
		);

	return (
		<>
			<p className="rolck-admin__intro">
				Aviso de cookies e registro de consentimento conforme a LGPD
				(Lei 13.709/2018) e o guia de cookies da ANPD. Cookies opcionais
				só são ativados depois do aceite, &quot;Rejeitar&quot; tem o
				mesmo destaque de &quot;Aceitar&quot; e o visitante pode mudar
				de ideia a qualquer momento pelo link &quot;Preferências de
				cookies&quot; do rodapé. As categorias aparecem sozinhas
				conforme o que estiver ligado em Integrações.
			</p>
			{ ! boot.privacyPolicy && (
				<Notice status="warning" isDismissible={ false }>
					Nenhuma página de política de privacidade escolhida: o aviso
					fica sem o link para ela. Escolha a página em{ ' ' }
					<a href={ boot.links.privacyPage }>
						Configurações → Privacidade
					</a>
					.
				</Notice>
			) }
			<Section title="Aviso de cookies">
				<Toggle
					path="privacy.enabled"
					label="Mostrar o aviso de cookies"
					help="Desligado, o Google Analytics e o código extra do <head> voltam a carregar sem pedir consentimento. Mantenha ligado."
				/>
				<Text
					path="privacy.days"
					type="number"
					label="Validade da escolha (dias)"
					help="Depois disso o aviso aparece de novo. 180 dias (6 meses) é o mais usado."
				/>
				<div className="is-wide">
					<p>
						Versão atual do aviso:{ ' ' }
						<strong>{ get( 'privacy.version' ) }</strong>
					</p>
					<Button variant="secondary" onClick={ bump }>
						Pedir o consentimento de novo a todos
					</Button>
					<p className="description">
						Use quando mudar a política de privacidade ou passar a
						usar cookies novos. Vale depois de salvar.
					</p>
				</div>
			</Section>
			<Section
				title="Registro de consentimentos"
				description="Cabe ao site provar que o visitante consentiu (LGPD, art. 8º, § 2º). Cada escolha é guardada com data, versão do aviso e categorias, sem guardar o IP (só um código derivado dele)."
			>
				<Toggle
					path="privacy.log"
					label="Registrar cada escolha"
					help="Recomendado ligado."
				/>
				<Text
					path="privacy.retention_months"
					type="number"
					label="Guardar os registros por (meses)"
					help="60 meses (5 anos) cobre o prazo para ações de consumidor (CDC, art. 27). Depois disso são apagados automaticamente."
				/>
				<Shortcuts
					items={ [
						{
							href: boot.links.consents,
							title: 'Ver e exportar registros',
							text: 'Busca pelo código do visitante e exportação em CSV.',
						},
					] }
				/>
			</Section>
			<Section
				title="Quem responde pelos dados"
				description="Aparece no painel de preferências. A LGPD pede a identidade e o contato do encarregado (art. 41); pequenas empresas podem informar só um canal de contato."
			>
				<Text
					path="privacy.dpo_name"
					label="Encarregado (DPO)"
					placeholder="Opcional"
				/>
				<Text
					path="privacy.dpo_email"
					type="email"
					label="E-mail de privacidade"
					help="Vazio usa o e-mail da aba Geral."
				/>
			</Section>
			<Section title="Textos e política">
				<Shortcuts
					items={ [
						{
							href: boot.links.privacyTexts,
							title: 'Textos do aviso',
							text: 'Grupo "Privacidade (LGPD)" em Textos do site, nos dois idiomas.',
						},
						{
							href: boot.links.privacyPage,
							title: 'Página da política de privacidade',
							text: 'Escolha a página em Configurações → Privacidade do WordPress. O aviso aponta para ela.',
						},
					] }
				/>
			</Section>
		</>
	);
}
