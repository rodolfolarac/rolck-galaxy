<?php
/**
 * Renomeia o administrador criado pelo Playground (admin/password) para o
 * usuário e a senha do .env.local. O WordPress não deixa trocar o login pelo
 * painel, por isso a troca é direta no banco.
 *
 * @package RolckGalaxy
 */

defined( 'ABSPATH' ) || exit;

/**
 * @param string $login Novo login.
 * @param string $pass  Nova senha.
 */
function rolck_set_admin( string $login, string $pass ): void {
	global $wpdb;
	$login = sanitize_user( $login, true );
	if ( '' === $login || '' === $pass ) {
		return;
	}
	$user = get_user_by( 'login', $login ) ?: get_user_by( 'id', 1 );
	if ( ! $user ) {
		return;
	}
	$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->users,
		array(
			'user_login'    => $login,
			'user_nicename' => sanitize_title( $login ),
			'display_name'  => $login,
		),
		array( 'ID' => $user->ID )
	);
	clean_user_cache( $user->ID );
	wp_set_password( $pass, $user->ID );
	update_user_meta( $user->ID, 'nickname', $login );
	echo "Administrador: $login\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
