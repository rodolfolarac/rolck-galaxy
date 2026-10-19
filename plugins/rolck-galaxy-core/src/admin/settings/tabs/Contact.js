import { Button, Notice } from '@wordpress/components';
import { useCallback, useEffect, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { Pair, Section, Select, Shortcuts, Text, Toggle } from '../fields';
import { useSettings } from '../context';

function Delivery() {
	const [ status, setStatus ] = useState( null );
	const [ test, setTest ] = useState( null );

	const loadStatus = useCallback( () => {
		apiFetch( { path: '/rolck/v1/mail/status' } )
			.then( setStatus )
			.catch( () => setStatus( null ) );
	}, [] );
	useEffect( loadStatus, [ loadStatus ] );

	const runTest = async () => {
		setTest( { status: 'loading' } );
		try {
			const res = await apiFetch( {
				path: '/rolck/v1/mail/test',
				method: 'POST',
			} );
			setTest( { status: 'success', text: res.message } );
		} catch ( e ) {
			setTest( { status: 'error', text: e.message } );
		}
		loadStatus();
	};

	return (
		<Section
			title="Envio de e-mail (SMTP)"
			description="Sem SMTP, o WordPress envia pelo servidor do site, que o Gmail e o Outlook costumam descartar ou mandar para o spam. Preencha com uma caixa de e-mail do seu domínio para que os avisos cheguem."
		>
			<Text
				path="contact.smtp_host"
				label="Servidor SMTP"
				placeholder="smtp.titan.email"
				help="E-mail da HostGator (Titan): smtp.titan.email. Vazio = envia pelo servidor do site."
			/>
			<Select
				path="contact.smtp_secure"
				label="Segurança"
				options={ [
					{ label: 'SSL (porta 465)', value: 'ssl' },
					{ label: 'TLS (porta 587)', value: 'tls' },
					{ label: 'Nenhuma', value: 'none' },
				] }
			/>
			<Text path="contact.smtp_port" label="Porta" type="number" />
			<Text
				path="contact.smtp_user"
				label="Usuário"
				type="email"
				placeholder="contato@seudominio.com"
				help="O endereço completo da caixa de e-mail."
			/>
			<Text
				path="contact.smtp_password"
				label="Senha da caixa de e-mail"
				type="password"
			/>
			<Text
				path="contact.from_email"
				label="Remetente"
				type="email"
				help="Vazio = o usuário do SMTP. Precisa ser um endereço do seu domínio."
			/>
			<div className="is-wide rolck-admin__auto">
				<p className="rolck-admin__help">
					Salve as configurações antes de testar. O teste vai para{ ' ' }
					{ status?.recipient || 'o e-mail das mensagens' }.
				</p>
				<div className="rolck-admin__auto-row">
					<Button
						variant="secondary"
						onClick={ runTest }
						isBusy={ test?.status === 'loading' }
					>
						Enviar e-mail de teste
					</Button>
				</div>
				{ test?.status === 'success' && (
					<Notice status="success" isDismissible={ false }>
						{ test.text }
					</Notice>
				) }
				{ test?.status === 'error' && (
					<Notice status="error" isDismissible={ false }>
						{ test.text }
					</Notice>
				) }
				{ status?.error && test?.status !== 'error' && (
					<Notice status="warning" isDismissible={ false }>
						Última falha de envio ({ status.error.time }):{ ' ' }
						{ status.error.message }
					</Notice>
				) }
			</div>
		</Section>
	);
}

export default function Contact() {
	const { boot } = useSettings();
	return (
		<>
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
							text: 'Todas as mensagens enviadas pelo site, com a coluna "Aviso por e-mail" mostrando se o aviso saiu.',
						},
					] }
				/>
			</Section>
			<Delivery />
		</>
	);
}
