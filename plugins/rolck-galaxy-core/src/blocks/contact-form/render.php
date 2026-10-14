<?php
/**
 * Formulário de contato.
 *
 * Funciona como formulário HTML comum; com JavaScript, a Interactivity API
 * envia pela REST API (rolck/v1/contact) sem recarregar a página.
 *
 * @package RolckGalaxy
 *
 * @var array $attributes Atributos do bloco.
 */

use RolckGalaxy\Core\I18n\Languages;
use RolckGalaxy\Core\I18n\Strings;

defined( 'ABSPATH' ) || exit;

$rolck_t = Strings::many( array( 'contact_name', 'contact_email', 'contact_subject', 'contact_message', 'contact_send', 'contact_sending', 'contact_invalid', 'contact_error' ) );

wp_interactivity_state(
	'rolck/contact-form',
	array(
		'endpoint' => rest_url( 'rolck/v1/contact' ),
		'lang'     => Languages::current(),
		'started'  => time(),
		'texts'    => $rolck_t,
		// Estado derivado também no servidor, para o HTML já sair com o texto do botão.
		'buttonText' => static fn(): string => $rolck_t['contact_send'],
	)
);

$rolck_uid = wp_unique_id( 'rolck-contact-' );
?>
<form
	<?php echo get_block_wrapper_attributes( array( 'class' => 'rolck-contact' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	method="post"
	action="<?php echo esc_url( rest_url( 'rolck/v1/contact' ) ); ?>"
	novalidate
	data-wp-interactive="rolck/contact-form"
	<?php echo wp_interactivity_data_wp_context( array( 'status' => 'idle', 'feedback' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	data-wp-on--submit="actions.submit"
>
	<div class="rolck-contact__row">
		<p class="rolck-contact__field">
			<label for="<?php echo esc_attr( $rolck_uid ); ?>-name"><?php echo esc_html( $rolck_t['contact_name'] ); ?></label>
			<input id="<?php echo esc_attr( $rolck_uid ); ?>-name" name="name" type="text" autocomplete="name" required maxlength="120">
		</p>
		<p class="rolck-contact__field">
			<label for="<?php echo esc_attr( $rolck_uid ); ?>-email"><?php echo esc_html( $rolck_t['contact_email'] ); ?></label>
			<input id="<?php echo esc_attr( $rolck_uid ); ?>-email" name="email" type="email" autocomplete="email" required maxlength="160">
		</p>
	</div>
	<?php if ( ! empty( $attributes['showSubject'] ) ) : ?>
		<p class="rolck-contact__field">
			<label for="<?php echo esc_attr( $rolck_uid ); ?>-subject"><?php echo esc_html( $rolck_t['contact_subject'] ); ?></label>
			<input id="<?php echo esc_attr( $rolck_uid ); ?>-subject" name="subject" type="text" maxlength="160">
		</p>
	<?php endif; ?>
	<p class="rolck-contact__field">
		<label for="<?php echo esc_attr( $rolck_uid ); ?>-message"><?php echo esc_html( $rolck_t['contact_message'] ); ?></label>
		<textarea id="<?php echo esc_attr( $rolck_uid ); ?>-message" name="message" rows="6" required maxlength="5000"></textarea>
	</p>

	<?php // Campo-isca: invisível para pessoas, preenchido por robôs. ?>
	<p class="rolck-contact__trap" aria-hidden="true">
		<label for="<?php echo esc_attr( $rolck_uid ); ?>-website">Website</label>
		<input id="<?php echo esc_attr( $rolck_uid ); ?>-website" name="website" type="text" tabindex="-1" autocomplete="off">
	</p>

	<div class="rolck-contact__footer">
		<button class="rolck-btn rolck-btn--primary" type="submit" data-wp-bind--disabled="state.isSending">
			<span data-wp-text="state.buttonText"><?php echo esc_html( $rolck_t['contact_send'] ); ?></span>
		</button>
		<p class="rolck-contact__feedback" role="status" aria-live="polite" data-wp-class--is-error="state.isError" data-wp-class--is-success="state.isSuccess" data-wp-text="context.feedback"></p>
	</div>
</form>
