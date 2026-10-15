<?php
/**
 * Atualizações de conteúdo que acompanham uma versão do código.
 *
 * O deploy só envia código; o conteúdo do site no ar fica no banco. Quando uma
 * versão precisa preencher algo no banco (um link novo, por exemplo), o passo
 * entra aqui e roda uma única vez depois do deploy. Cada passo só preenche
 * campo vazio, então nunca sobrescreve o que foi editado no painel.
 *
 * Para criar um passo: acrescente-o em steps() com a próxima chave numérica.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\Content;

defined( 'ABSPATH' ) || exit;

final class Updates {

	/** Último passo aplicado neste site. */
	private const OPTION = 'rolck_content_updates';

	public static function register(): void {
		add_action( 'init', array( self::class, 'run' ), 20 );
	}

	/**
	 * Passos, na ordem em que rodam.
	 *
	 * @return array<int,callable>
	 */
	private static function steps(): array {
		return array(
			// Demo pública do Galaxy Cards (versão de teste, dados no navegador do visitante).
			1 => static fn() => self::fill_project_field( 'galaxy-cards', 'demo_url', 'https://galaxy-cards.vercel.app/?demo' ),
		);
	}

	public static function run(): void {
		$done  = (int) get_option( self::OPTION, 0 );
		$steps = self::steps();
		$last  = max( array_keys( $steps ) );
		if ( $done >= $last ) {
			return;
		}
		foreach ( $steps as $number => $step ) {
			if ( $number > $done ) {
				$step();
				$done = $number;
			}
		}
		update_option( self::OPTION, $done, true );
	}

	/** Preenche um campo do projeto (em todos os idiomas) quando ele está vazio. */
	private static function fill_project_field( string $slug, string $field, string $value ): void {
		$ids = get_posts(
			array(
				'post_type'        => Projects::POST_TYPE,
				'name'             => $slug,
				'post_status'      => 'any',
				'posts_per_page'   => -1,
				'fields'           => 'ids',
				'suppress_filters' => true,
			)
		);
		foreach ( $ids as $id ) {
			if ( '' === (string) get_post_meta( $id, 'rolck_' . $field, true ) ) {
				update_post_meta( $id, 'rolck_' . $field, $value );
			}
		}
	}
}
