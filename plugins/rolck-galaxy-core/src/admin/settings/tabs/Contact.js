import { Pair, Section, Shortcuts, Text, Toggle } from '../fields';
import { useSettings } from '../context';

export default function Contact() {
	const { boot } = useSettings();
	return (
		<Section
			title="Formulário de contato"
			description="Proteção contra spam sem captcha (não pesa no PageSpeed): campo-isca, tempo mínimo e limite de envios por IP."
		>
			<Text
				path="contact.recipient"
				label="Enviar mensagens para"
				type="email"
				help="Vazio = e-mail da aba Geral."
			/>
			<Text
				path="contact.subject_prefix"
				label="Prefixo do assunto do e-mail"
			/>
			<Pair
				path="contact.success"
				label="Mensagem depois do envio"
				long
				rows={ 2 }
			/>
			<Toggle
				path="contact.store_messages"
				label="Guardar cópia das mensagens no painel"
				help="Recomendado: se o servidor de e-mail falhar, nenhum contato se perde."
			/>
			<Shortcuts
				items={ [
					{
						href: boot.links.messages,
						title: 'Mensagens recebidas',
						text: 'Todas as mensagens enviadas pelo site.',
					},
				] }
			/>
		</Section>
	);
}
