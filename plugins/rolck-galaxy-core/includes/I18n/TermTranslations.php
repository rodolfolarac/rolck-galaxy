<?php
/**
 * Nome e descrição dos termos (categorias de projeto, tecnologias, categorias
 * do blog) nos outros idiomas.
 *
 * O termo é um só — mesma URL e mesmo vínculo com os posts —, e ganha campos
 * "Nome em English" e "Descrição em English" na tela de edição. No site, o
 * nome exibido segue o idioma da página.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\I18n;

use WP_Term;

defined( 'ABSPATH' ) || exit;

final class TermTranslations {

	/** @return string[] Taxonomias com nomes traduzíveis. */
	public static function taxonomies(): array {
		return (array) apply_filters( 'rolck_translatable_taxonomies', array( 'category', 'post_tag', 'rolck_project_type', 'rolck_tech' ) );
	}

	public static function register(): void {
		add_action( 'init', array( self::class, 'register_meta' ), 20 );
		add_action( 'admin_init', array( self::class, 'admin_hooks' ) );
		if ( ! is_admin() ) {
			add_filter( 'get_term', array( self::class, 'translate' ) );
			add_filter( 'get_terms', array( self::class, 'translate_many' ) );
			add_filter( 'get_the_terms', array( self::class, 'translate_many' ) );
		}
	}

	/** @return string[] Idiomas que precisam de campos (todos menos o padrão). */
	private static function extra_languages(): array {
		return array_values( array_filter( Languages::codes(), static fn( $c ) => ! Languages::is_default( $c ) ) );
	}

	public static function register_meta(): void {
		foreach ( self::taxonomies() as $taxonomy ) {
			foreach ( self::extra_languages() as $code ) {
				foreach ( array( 'name', 'description' ) as $field ) {
					register_term_meta(
						$taxonomy,
						"rolck_{$field}_{$code}",
						array(
							'type'              => 'string',
							'single'            => true,
							'show_in_rest'      => true,
							'sanitize_callback' => 'name' === $field ? 'sanitize_text_field' : 'sanitize_textarea_field',
						)
					);
				}
			}
		}
	}

	public static function admin_hooks(): void {
		foreach ( self::taxonomies() as $taxonomy ) {
			add_action( "{$taxonomy}_add_form_fields", array( self::class, 'add_fields' ) );
			add_action( "{$taxonomy}_edit_form_fields", array( self::class, 'edit_fields' ) );
			add_action( "created_{$taxonomy}", array( self::class, 'save' ) );
			add_action( "edited_{$taxonomy}", array( self::class, 'save' ) );
		}
	}

	public static function add_fields(): void {
		wp_nonce_field( 'rolck_term', 'rolck_term_nonce' );
		foreach ( self::extra_languages() as $code ) {
			$label = Languages::info( $code )['label'];
			printf(
				'<div class="form-field"><label for="rolck_name_%1$s">%2$s</label><input type="text" id="rolck_name_%1$s" name="rolck_name_%1$s"></div>',
				esc_attr( $code ),
				esc_html( sprintf( 'Nome em %s', $label ) )
			);
			printf(
				'<div class="form-field"><label for="rolck_description_%1$s">%2$s</label><textarea id="rolck_description_%1$s" name="rolck_description_%1$s" rows="4"></textarea></div>',
				esc_attr( $code ),
				esc_html( sprintf( 'Descrição em %s', $label ) )
			);
		}
	}

	public static function edit_fields( WP_Term $term ): void {
		wp_nonce_field( 'rolck_term', 'rolck_term_nonce' );
		foreach ( self::extra_languages() as $code ) {
			$label = Languages::info( $code )['label'];
			printf(
				'<tr class="form-field"><th scope="row"><label for="rolck_name_%1$s">%2$s</label></th><td><input type="text" id="rolck_name_%1$s" name="rolck_name_%1$s" value="%3$s"></td></tr>',
				esc_attr( $code ),
				esc_html( sprintf( 'Nome em %s', $label ) ),
				esc_attr( (string) get_term_meta( $term->term_id, "rolck_name_$code", true ) )
			);
			printf(
				'<tr class="form-field"><th scope="row"><label for="rolck_description_%1$s">%2$s</label></th><td><textarea id="rolck_description_%1$s" name="rolck_description_%1$s" rows="4">%3$s</textarea></td></tr>',
				esc_attr( $code ),
				esc_html( sprintf( 'Descrição em %s', $label ) ),
				esc_textarea( (string) get_term_meta( $term->term_id, "rolck_description_$code", true ) )
			);
		}
	}

	public static function save( int $term_id ): void {
		if ( ! isset( $_POST['rolck_term_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['rolck_term_nonce'] ) ), 'rolck_term' ) ) {
			return;
		}
		foreach ( self::extra_languages() as $code ) {
			if ( isset( $_POST[ "rolck_name_$code" ] ) ) {
				update_term_meta( $term_id, "rolck_name_$code", sanitize_text_field( wp_unslash( $_POST[ "rolck_name_$code" ] ) ) );
			}
			if ( isset( $_POST[ "rolck_description_$code" ] ) ) {
				update_term_meta( $term_id, "rolck_description_$code", sanitize_textarea_field( wp_unslash( $_POST[ "rolck_description_$code" ] ) ) );
			}
		}
	}

	/**
	 * @param mixed $term Termo.
	 */
	public static function translate( mixed $term ): mixed {
		if ( ! $term instanceof WP_Term || Languages::is_default() || ! in_array( $term->taxonomy, self::taxonomies(), true ) ) {
			return $term;
		}
		$code = Languages::current();
		$name = (string) get_term_meta( $term->term_id, "rolck_name_$code", true );
		$desc = (string) get_term_meta( $term->term_id, "rolck_description_$code", true );
		if ( '' !== $name ) {
			$term->name = $name;
		}
		if ( '' !== $desc ) {
			$term->description = $desc;
		}
		return $term;
	}

	/**
	 * @param mixed $terms Lista de termos.
	 */
	public static function translate_many( mixed $terms ): mixed {
		return is_array( $terms ) ? array_map( array( self::class, 'translate' ), $terms ) : $terms;
	}
}
