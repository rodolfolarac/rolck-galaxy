<?php
/**
 * Coluna "Idioma" e filtro por idioma nas listas de posts, páginas e projetos.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\Admin;

use RolckGalaxy\Core\I18n\Languages;
use RolckGalaxy\Core\I18n\Translations;
use WP_Query;

defined( 'ABSPATH' ) || exit;

final class ListColumns {

	public static function register(): void {
		add_action( 'admin_init', array( self::class, 'hooks' ) );
		add_action( 'restrict_manage_posts', array( self::class, 'filter_dropdown' ) );
		add_action( 'pre_get_posts', array( self::class, 'filter_query' ) );
	}

	public static function hooks(): void {
		foreach ( Translations::post_types() as $type ) {
			add_filter( "manage_{$type}_posts_columns", array( self::class, 'columns' ) );
			add_action( "manage_{$type}_posts_custom_column", array( self::class, 'column' ), 10, 2 );
		}
	}

	/**
	 * @param array<string,string> $columns Colunas.
	 */
	public static function columns( array $columns ): array {
		$out = array();
		foreach ( $columns as $key => $label ) {
			$out[ $key ] = $label;
			if ( 'title' === $key ) {
				$out['rolck_language'] = 'Idioma';
			}
		}
		return $out;
	}

	/**
	 * @param string $column  Coluna.
	 * @param int    $post_id Post.
	 */
	public static function column( string $column, int $post_id ): void {
		if ( 'rolck_language' !== $column ) {
			return;
		}
		$parts = array();
		foreach ( Translations::group_of( $post_id ) as $code => $id ) {
			$info    = Languages::info( $code );
			$parts[] = $id === $post_id
				? sprintf( '<strong title="%s">%s</strong>', esc_attr( $info['label'] ), esc_html( $info['short'] ) )
				: sprintf( '<a href="%s" title="%s">%s</a>', esc_url( (string) get_edit_post_link( $id ) ), esc_attr( 'Editar versão em ' . $info['label'] ), esc_html( $info['short'] ) );
		}
		echo wp_kses_post( implode( ' · ', $parts ) );
	}

	public static function filter_dropdown( string $post_type ): void {
		if ( ! in_array( $post_type, Translations::post_types(), true ) ) {
			return;
		}
		$selected = isset( $_GET['rolck_lang'] ) ? sanitize_key( wp_unslash( $_GET['rolck_lang'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		echo '<select name="rolck_lang"><option value="">Todos os idiomas</option>';
		foreach ( Languages::codes() as $code ) {
			printf( '<option value="%s"%s>%s</option>', esc_attr( $code ), selected( $selected, $code, false ), esc_html( Languages::info( $code )['label'] ) );
		}
		echo '</select>';
	}

	public static function filter_query( WP_Query $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() || empty( $_GET['rolck_lang'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		$lang = sanitize_key( wp_unslash( $_GET['rolck_lang'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( Languages::is_valid( $lang ) ) {
			$tax   = (array) $query->get( 'tax_query' );
			$tax[] = Translations::tax_clause( $lang );
			$query->set( 'tax_query', $tax );
		}
	}
}
