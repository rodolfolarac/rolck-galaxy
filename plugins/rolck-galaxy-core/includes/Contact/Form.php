<?php
/**
 * Formulário de contato nativo: rota REST, proteção contra spam e caixa de
 * mensagens no painel (Rolck Galaxy → Mensagens).
 *
 * Proteção sem captcha (que pesaria no PageSpeed): campo-isca invisível,
 * tempo mínimo de preenchimento e limite de envios por IP. A mensagem é
 * salva antes do e-mail sair, então nenhum contato se perde se o servidor
 * de e-mail falhar.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\Contact;

use RolckGalaxy\Core\I18n\Languages;
use RolckGalaxy\Core\I18n\Strings;
use RolckGalaxy\Core\Settings;
use WP_Error;
use WP_REST_Request;

defined( 'ABSPATH' ) || exit;

final class Form {

	public const POST_TYPE = 'rolck_message';

	/** Envios aceitos por IP a cada 10 minutos. */
	private const LIMIT = 5;

	/** Segundos mínimos entre abrir a página e enviar (robôs enviam na hora). */
	private const MIN_SECONDS = 3;

	public static function register(): void {
		add_action( 'init', array( self::class, 'register_type' ) );
		add_action( 'rest_api_init', array( self::class, 'routes' ) );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( self::class, 'columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( self::class, 'column' ), 10, 2 );
		add_action( 'add_meta_boxes_' . self::POST_TYPE, array( self::class, 'meta_box' ) );
	}

	public static function register_type(): void {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'          => array(
					'name'          => 'Mensagens',
					'singular_name' => 'Mensagem',
					'all_items'     => 'Mensagens',
					'edit_item'     => 'Mensagem recebida',
					'search_items'  => 'Buscar mensagens',
					'not_found'     => 'Nenhuma mensagem ainda.',
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => 'rolck-galaxy',
				'show_in_rest'    => false,
				'supports'        => array( 'title' ),
				'capability_type' => 'post',
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'    => true,
			)
		);
	}

	public static function routes(): void {
		register_rest_route(
			'rolck/v1',
			'/contact',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => array( self::class, 'submit' ),
				'args'                => array(
					'name'    => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'email'   => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_email',
					),
					'subject' => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'message' => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_textarea_field',
					),
					'website' => array( 'type' => 'string' ),
					'started' => array( 'type' => 'integer' ),
					'lang'    => array( 'type' => 'string' ),
					'page'    => array(
						'type'              => 'string',
						'sanitize_callback' => 'esc_url_raw',
					),
				),
			)
		);
	}

	public static function submit( WP_REST_Request $request ): array|WP_Error {
		$lang = Languages::is_valid( (string) $request['lang'] ) ? (string) $request['lang'] : Languages::default();
		$fail = static fn( string $key, int $status ) => new WP_Error( 'rolck_contact', Strings::get( $key, $lang ), array( 'status' => $status ) );

		// Robô: preencheu o campo-isca ou enviou rápido demais. Responde "ok" para não ensinar o robô.
		// "started" vem do servidor quando a página é gerada (não do relógio do visitante).
		$elapsed = time() - (int) ( $request['started'] ?? 0 );
		if ( '' !== (string) $request['website'] || $elapsed < self::MIN_SECONDS ) {
			return array( 'message' => rolck_l( Settings::get( 'contact.success' ) ) );
		}

		$name    = trim( (string) $request['name'] );
		$email   = (string) $request['email'];
		$message = trim( (string) $request['message'] );
		if ( '' === $name || ! is_email( $email ) || mb_strlen( $message ) < 5 ) {
			return $fail( 'contact_invalid', 400 );
		}

		$key   = 'rolck_contact_' . md5( self::ip() );
		$count = (int) get_transient( $key );
		if ( $count >= self::LIMIT ) {
			return $fail( 'contact_too_many', 429 );
		}
		set_transient( $key, $count + 1, 10 * MINUTE_IN_SECONDS );

		$subject = trim( (string) $request['subject'] ) ?: mb_substr( $message, 0, 60 );

		if ( Settings::get( 'contact.store_messages', true ) ) {
			$id = wp_insert_post(
				array(
					'post_type'    => self::POST_TYPE,
					'post_status'  => 'private',
					'post_title'   => sprintf( '%s — %s', $name, $subject ),
					'post_content' => $message,
				)
			);
			if ( $id && ! is_wp_error( $id ) ) {
				update_post_meta( $id, 'rolck_email', $email );
				update_post_meta( $id, 'rolck_lang', $lang );
				update_post_meta( $id, 'rolck_page', (string) $request['page'] );
			}
		}

		$to   = Settings::get( 'contact.recipient' ) ?: Settings::get( 'general.email' ) ?: get_option( 'admin_email' );
		$body = sprintf( "Nome: %s\nE-mail: %s\nIdioma: %s\nPágina: %s\n\n%s", $name, $email, strtoupper( $lang ), (string) $request['page'], $message );
		$sent = wp_mail(
			(string) $to,
			trim( Settings::get( 'contact.subject_prefix', '' ) . ' ' . $subject ),
			$body,
			array( 'Reply-To: ' . $name . ' <' . $email . '>' )
		);

		if ( ! $sent && ! Settings::get( 'contact.store_messages', true ) ) {
			return $fail( 'contact_error', 500 );
		}
		return array( 'message' => Languages::pick( Settings::get( 'contact.success' ), $lang ) );
	}

	private static function ip(): string {
		return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';
	}

	/**
	 * @param array<string,string> $columns Colunas.
	 */
	public static function columns( array $columns ): array {
		return array(
			'cb'          => $columns['cb'] ?? '',
			'title'       => 'Contato',
			'rolck_email' => 'E-mail',
			'rolck_lang'  => 'Idioma',
			'date'        => 'Recebida em',
		);
	}

	/**
	 * @param string $column  Coluna.
	 * @param int    $post_id Mensagem.
	 */
	public static function column( string $column, int $post_id ): void {
		$value = (string) get_post_meta( $post_id, $column, true );
		if ( 'rolck_email' === $column ) {
			printf( '<a href="mailto:%1$s">%1$s</a>', esc_attr( $value ) );
		} elseif ( 'rolck_lang' === $column ) {
			echo esc_html( strtoupper( $value ) );
		}
	}

	public static function meta_box(): void {
		remove_meta_box( 'submitdiv', self::POST_TYPE, 'side' );
		add_meta_box(
			'rolck_message',
			'Mensagem',
			static function ( \WP_Post $post ): void {
				$email = (string) get_post_meta( $post->ID, 'rolck_email', true );
				printf(
					'<p><strong>E-mail:</strong> <a href="mailto:%1$s">%1$s</a></p><p><strong>Página:</strong> %2$s</p><div style="white-space:pre-wrap;font-size:14px;line-height:1.6;background:#f6f7f7;padding:16px;border-radius:6px">%3$s</div><p><a class="button button-primary" href="mailto:%1$s">Responder por e-mail</a></p>',
					esc_attr( $email ),
					esc_html( (string) get_post_meta( $post->ID, 'rolck_page', true ) ),
					esc_html( $post->post_content )
				);
			},
			self::POST_TYPE,
			'normal',
			'high'
		);
	}
}
