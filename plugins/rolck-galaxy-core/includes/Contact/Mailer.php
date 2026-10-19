<?php
/**
 * Envio de e-mail por SMTP, sem plugin.
 *
 * Por padrão o WordPress envia pelo próprio servidor do site, que costuma não
 * estar autorizado no SPF do domínio: Gmail e Outlook descartam ou jogam no
 * spam. Com o SMTP preenchido em Rolck Galaxy → Contato, todo wp_mail() sai
 * pela caixa de e-mail do domínio (ex.: Titan, da HostGator), já autorizada.
 *
 * Também guarda a última falha de envio e oferece um e-mail de teste no painel.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\Contact;

use PHPMailer\PHPMailer\PHPMailer;
use RolckGalaxy\Core\Settings;
use WP_Error;

defined( 'ABSPATH' ) || exit;

final class Mailer {

	/** Última falha de envio: [ 'time' => int, 'message' => string ]. */
	private const LAST_ERROR = 'rolck_mail_last_error';

	/** Mensagem da falha mais recente nesta requisição. */
	private static string $error = '';

	public static function register(): void {
		add_action( 'phpmailer_init', array( self::class, 'configure' ) );
		add_filter( 'wp_mail_from', array( self::class, 'from' ) );
		add_filter( 'wp_mail_from_name', array( self::class, 'from_name' ) );
		add_action( 'wp_mail_failed', array( self::class, 'failed' ) );
		add_action( 'rest_api_init', array( self::class, 'routes' ) );
	}

	public static function smtp_enabled(): bool {
		return '' !== (string) Settings::get( 'contact.smtp_host' ) && '' !== (string) Settings::get( 'contact.smtp_user' );
	}

	public static function configure( PHPMailer $mailer ): void {
		if ( ! self::smtp_enabled() ) {
			return;
		}
		$secure = (string) Settings::get( 'contact.smtp_secure', 'ssl' );

		$mailer->isSMTP();
		$mailer->Host       = (string) Settings::get( 'contact.smtp_host' );
		$mailer->Port       = (int) Settings::get( 'contact.smtp_port', 465 ) ?: ( 'tls' === $secure ? 587 : 465 );
		$mailer->SMTPAuth   = true;
		$mailer->Username   = (string) Settings::get( 'contact.smtp_user' );
		$mailer->Password   = (string) Settings::get( 'contact.smtp_password' );
		$mailer->SMTPSecure = in_array( $secure, array( 'ssl', 'tls' ), true ) ? $secure : '';
		$mailer->SMTPAutoTLS = 'none' !== $secure;
		$mailer->Timeout    = 15;
		// Envelope igual ao remetente: o SPF e o DKIM do domínio passam a valer.
		$mailer->Sender = $mailer->From;
	}

	/** Remetente: o do painel; com SMTP e sem remetente, o próprio usuário do SMTP. */
	public static function from( string $email ): string {
		$from = (string) Settings::get( 'contact.from_email' );
		if ( '' === $from && self::smtp_enabled() && is_email( (string) Settings::get( 'contact.smtp_user' ) ) ) {
			$from = (string) Settings::get( 'contact.smtp_user' );
		}
		return is_email( $from ) ? $from : $email;
	}

	public static function from_name( string $name ): string {
		return 'WordPress' === $name ? wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) : $name;
	}

	public static function failed( WP_Error $error ): void {
		self::$error = $error->get_error_message();
		update_option(
			self::LAST_ERROR,
			array(
				'time'    => time(),
				'message' => self::$error,
			),
			false
		);
	}

	/** Falha do último wp_mail() desta requisição ('' se não houve). */
	public static function last_error(): string {
		return self::$error;
	}

	public static function routes(): void {
		$admin = static fn(): bool => current_user_can( 'manage_options' );

		register_rest_route(
			'rolck/v1',
			'/mail/test',
			array(
				'methods'             => 'POST',
				'permission_callback' => $admin,
				'callback'            => array( self::class, 'rest_test' ),
			)
		);
		register_rest_route(
			'rolck/v1',
			'/mail/status',
			array(
				'methods'             => 'GET',
				'permission_callback' => $admin,
				'callback'            => static fn(): array => self::status(),
			)
		);
	}

	/** Destinatário das mensagens do formulário. */
	public static function recipient(): string {
		return (string) ( Settings::get( 'contact.recipient' ) ?: Settings::get( 'general.email' ) ?: get_option( 'admin_email' ) );
	}

	public static function rest_test(): array|WP_Error {
		$to = self::recipient();
		self::$error = '';
		$sent = wp_mail(
			$to,
			trim( Settings::get( 'contact.subject_prefix', '' ) . ' E-mail de teste' ),
			sprintf(
				"Este é um e-mail de teste do formulário de contato de %s.\n\nSe ele chegou, as mensagens do site vão chegar neste endereço.\n\nEnviado por: %s",
				home_url( '/' ),
				self::smtp_enabled() ? 'SMTP ' . Settings::get( 'contact.smtp_host' ) : 'servidor do site (sem SMTP)'
			)
		);
		if ( ! $sent ) {
			return new WP_Error( 'rolck_mail', 'O e-mail não saiu: ' . ( self::$error ?: 'erro desconhecido.' ), array( 'status' => 500 ) );
		}
		delete_option( self::LAST_ERROR );
		return array( 'message' => sprintf( 'E-mail de teste enviado para %s. Confira a caixa de entrada e o spam.', $to ) );
	}

	/** @return array{smtp:bool,recipient:string,error:?array} */
	public static function status(): array {
		$error = get_option( self::LAST_ERROR );
		return array(
			'smtp'      => self::smtp_enabled(),
			'recipient' => self::recipient(),
			'error'     => is_array( $error ) ? array(
				'time'    => wp_date( 'd/m/Y H:i', (int) $error['time'] ),
				'message' => (string) $error['message'],
			) : null,
		);
	}
}
