<?php
/**
 * Projetos da vitrine: tipo de conteúdo, categorias, tecnologias e campos.
 *
 * Os campos (demo, link de compra, preço, versão…) são metadados registrados na
 * REST API e editados no painel lateral "Detalhes do projeto" do editor de
 * blocos. Para criar um campo novo, acrescente-o em fields() e no painel
 * (src/editor/project-panel.js); o bloco Ficha do projeto lê daqui.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\Content;

use RolckGalaxy\Core\Settings;

defined( 'ABSPATH' ) || exit;

final class Projects {

	public const POST_TYPE = 'rolck_project';
	public const TYPE_TAX  = 'rolck_project_type';
	public const TECH_TAX  = 'rolck_tech';

	public static function register(): void {
		add_action( 'init', array( self::class, 'register_types' ), 0 );
		add_action( 'init', array( self::class, 'register_fields' ) );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( self::class, 'columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( self::class, 'column' ), 10, 2 );
	}

	/**
	 * Campos do projeto: chave → [ tipo, padrão ].
	 *
	 * @return array<string,array{0:string,1:mixed}>
	 */
	public static function fields(): array {
		return array(
			'tagline'   => array( 'string', '' ),
			'status'    => array( 'string', 'available' ),
			'price'     => array( 'string', '' ),
			'demo_url'  => array( 'string', '' ),
			'buy_url'   => array( 'string', '' ),
			'repo_url'  => array( 'string', '' ),
			'version'   => array( 'string', '' ),
			'released'  => array( 'string', '' ),
			'compat'    => array( 'string', '' ),
			'featured'  => array( 'boolean', false ),
			'accent'    => array( 'string', '' ),
			'highlights' => array( 'array', array() ),
		);
	}

	/** @return string[] Situações possíveis (a chave do texto é status_<valor>). */
	public static function statuses(): array {
		return array( 'available', 'soon', 'development' );
	}

	public static function register_types(): void {
		$slug = (string) Settings::get( 'projects.slug.' . \RolckGalaxy\Core\I18n\Languages::default(), 'projetos' );

		register_post_type(
			self::POST_TYPE,
			array(
				'labels'        => array(
					'name'               => 'Projetos',
					'singular_name'      => 'Projeto',
					'add_new'            => 'Adicionar projeto',
					'add_new_item'       => 'Adicionar novo projeto',
					'edit_item'          => 'Editar projeto',
					'new_item'           => 'Novo projeto',
					'view_item'          => 'Ver projeto',
					'view_items'         => 'Ver projetos',
					'search_items'       => 'Buscar projetos',
					'not_found'          => 'Nenhum projeto encontrado.',
					'not_found_in_trash' => 'Nenhum projeto na lixeira.',
					'all_items'          => 'Todos os projetos',
					'featured_image'     => 'Capa do projeto',
					'set_featured_image' => 'Definir capa',
					'menu_name'          => 'Projetos',
				),
				'public'        => true,
				'show_in_rest'  => true,
				'menu_icon'     => 'dashicons-portfolio',
				'menu_position' => 5,
				'has_archive'   => $slug,
				'rewrite'       => array(
					'slug'       => $slug,
					'with_front' => false,
				),
				'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'revisions', 'page-attributes' ),
				'template'      => array(
					array( 'core/paragraph', array( 'placeholder' => 'Conte o que o projeto resolve, para quem ele é e o que vem no pacote…' ) ),
					array( 'core/gallery', array( 'linkTo' => 'none' ) ),
				),
			)
		);

		register_taxonomy(
			self::TYPE_TAX,
			self::POST_TYPE,
			array(
				'labels'            => array(
					'name'          => 'Categorias de projeto',
					'singular_name' => 'Categoria de projeto',
					'add_new_item'  => 'Adicionar categoria',
					'edit_item'     => 'Editar categoria',
					'search_items'  => 'Buscar categorias',
					'all_items'     => 'Todas as categorias',
					'menu_name'     => 'Categorias',
				),
				'hierarchical'      => true,
				'public'            => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => array(
					'slug'       => (string) Settings::get( 'projects.type_slug.' . \RolckGalaxy\Core\I18n\Languages::default(), 'categoria-projeto' ),
					'with_front' => false,
				),
			)
		);

		register_taxonomy(
			self::TECH_TAX,
			self::POST_TYPE,
			array(
				'labels'            => array(
					'name'          => 'Tecnologias',
					'singular_name' => 'Tecnologia',
					'add_new_item'  => 'Adicionar tecnologia',
					'search_items'  => 'Buscar tecnologias',
					'menu_name'     => 'Tecnologias',
					'separate_items_with_commas' => 'Separe com vírgulas',
				),
				'hierarchical'      => false,
				'public'            => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => array(
					'slug'       => 'tech',
					'with_front' => false,
				),
			)
		);
	}

	public static function register_fields(): void {
		foreach ( self::fields() as $key => [ $type, $default ] ) {
			$args = array(
				'type'          => $type,
				'single'        => true,
				'default'       => $default,
				'show_in_rest'  => true,
				'auth_callback' => static fn(): bool => current_user_can( 'edit_posts' ),
			);
			if ( 'array' === $type ) {
				$args['show_in_rest'] = array(
					'schema' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
				);
				$args['sanitize_callback'] = static fn( $value ) => array_values( array_filter( array_map( 'sanitize_text_field', (array) $value ) ) );
			} elseif ( 'string' === $type ) {
				$args['sanitize_callback'] = str_ends_with( $key, '_url' ) ? 'esc_url_raw' : ( 'accent' === $key ? 'sanitize_hex_color' : 'sanitize_text_field' );
			}
			register_post_meta( self::POST_TYPE, "rolck_$key", $args );
		}
	}

	/**
	 * Todos os campos de um projeto, já com os padrões.
	 *
	 * @param int $post_id Projeto.
	 * @return array<string,mixed>
	 */
	public static function data( int $post_id ): array {
		$out = array();
		foreach ( self::fields() as $key => [ , $default ] ) {
			$value       = get_post_meta( $post_id, "rolck_$key", true );
			$out[ $key ] = ( '' === $value || null === $value ) ? $default : $value;
		}
		if ( ! in_array( $out['status'], self::statuses(), true ) ) {
			$out['status'] = 'available';
		}
		return $out;
	}

	/**
	 * @param array<string,string> $columns Colunas da lista.
	 */
	public static function columns( array $columns ): array {
		$new = array();
		foreach ( $columns as $key => $label ) {
			if ( 'title' === $key ) {
				$new['rolck_thumb'] = 'Capa';
			}
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['rolck_status'] = 'Status';
			}
		}
		return $new;
	}

	/**
	 * @param string $column  Coluna.
	 * @param int    $post_id Projeto.
	 */
	public static function column( string $column, int $post_id ): void {
		$data = self::data( $post_id );
		match ( $column ) {
			'rolck_thumb'  => print( get_the_post_thumbnail( $post_id, array( 64, 48 ), array( 'style' => 'border-radius:6px;object-fit:cover' ) ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'rolck_status' => print( esc_html( \RolckGalaxy\Core\I18n\Strings::get( 'status_' . $data['status'], 'pt' ) ) . ( $data['featured'] ? ' · ★' : '' ) ),
			default        => null,
		};
	}
}
