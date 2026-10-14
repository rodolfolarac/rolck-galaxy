<?php
/**
 * Idioma de cada conteúdo e o vínculo entre as versões traduzidas.
 *
 * - O idioma de um post fica na taxonomia interna rolck_language (consultas
 *   rápidas com tax_query). Post sem idioma conta como idioma padrão.
 * - Versões de um mesmo conteúdo compartilham o meta _rolck_group.
 * - Listagens (blog, busca, arquivos, bloco Query Loop) mostram só o idioma atual.
 * - O link de cada post usa o idioma do próprio post.
 * - No editor, o painel "Idioma e tradução" usa as rotas REST daqui.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\I18n;

use WP_Post;
use WP_Query;
use WP_REST_Request;
use WP_Error;

defined( 'ABSPATH' ) || exit;

final class Translations {

	public const TAXONOMY = 'rolck_language';
	public const GROUP    = '_rolck_group';

	public static function register(): void {
		add_action( 'init', array( self::class, 'register_taxonomy' ), 5 );
		add_action( 'init', array( self::class, 'register_fields' ), 20 );
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
		add_action( 'pre_get_posts', array( self::class, 'filter_main_query' ) );
		add_filter( 'query_loop_block_query_vars', array( self::class, 'filter_block_query' ) );
		add_filter( 'widget_posts_args', array( self::class, 'with_language' ) );

		foreach ( array( 'post_link', 'page_link', 'post_type_link' ) as $hook ) {
			add_filter( $hook, array( self::class, 'permalink' ), 20, 2 );
		}

		if ( ! is_admin() ) {
			add_filter( 'option_page_on_front', array( self::class, 'translate_page_option' ) );
			add_filter( 'option_page_for_posts', array( self::class, 'translate_page_option' ) );
			add_filter( 'option_wp_page_for_privacy_policy', array( self::class, 'translate_page_option' ) );
			add_action( 'template_redirect', array( self::class, 'redirect_wrong_language' ), 1 );
		}

		add_action( 'save_post', array( self::class, 'ensure_language' ), 10, 2 );
		add_filter( 'wp_unique_post_slug', array( self::class, 'shared_slug' ), 10, 6 );
		if ( ! is_admin() ) {
			add_filter( 'request', array( self::class, 'resolve_shared_slug' ) );
		}
	}

	/** @return string[] Tipos de conteúdo que têm versões por idioma. */
	public static function post_types(): array {
		return (array) apply_filters( 'rolck_translatable_post_types', array( 'post', 'page', 'rolck_project', 'wp_block' ) );
	}

	public static function register_taxonomy(): void {
		register_taxonomy(
			self::TAXONOMY,
			self::post_types(),
			array(
				'public'             => false,
				'show_ui'            => false,
				'show_in_rest'       => false,
				'hierarchical'       => false,
				'rewrite'            => false,
				'query_var'          => false,
				'show_in_nav_menus'  => false,
				'show_admin_column'  => false,
			)
		);
		foreach ( Languages::codes() as $code ) {
			if ( ! term_exists( $code, self::TAXONOMY ) ) {
				wp_insert_term( $code, self::TAXONOMY, array( 'slug' => $code ) );
			}
		}
	}

	// ── idioma e grupo de um post ───────────────────────────────────────────

	/**
	 * @param int|WP_Post $post Post.
	 */
	public static function language_of( int|WP_Post $post ): string {
		$id    = $post instanceof WP_Post ? $post->ID : $post;
		$terms = get_the_terms( $id, self::TAXONOMY );
		$code  = is_array( $terms ) && $terms ? $terms[0]->slug : '';
		return Languages::is_valid( $code ) ? $code : Languages::default();
	}

	/**
	 * @param int    $post_id Post.
	 * @param string $lang    Idioma.
	 */
	public static function set_language( int $post_id, string $lang ): void {
		if ( Languages::is_valid( $lang ) ) {
			wp_set_object_terms( $post_id, $lang, self::TAXONOMY );
		}
	}

	/**
	 * Versões do mesmo conteúdo, por idioma (inclui o próprio post).
	 *
	 * @param int $post_id Post.
	 * @return array<string,int> Idioma → ID.
	 */
	public static function group_of( int $post_id ): array {
		static $cache = array();
		if ( isset( $cache[ $post_id ] ) ) {
			return $cache[ $post_id ];
		}

		$out   = array( self::language_of( $post_id ) => $post_id );
		$group = (string) get_post_meta( $post_id, self::GROUP, true );
		if ( '' !== $group ) {
			$ids = get_posts(
				array(
					'post_type'        => 'any',
					'post_status'      => array( 'publish', 'draft', 'pending', 'private', 'future' ),
					'meta_key'         => self::GROUP, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'meta_value'       => $group, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
					'fields'           => 'ids',
					'posts_per_page'   => 20,
					'suppress_filters' => true,
					'no_found_rows'    => true,
					'lang'             => 'all',
				)
			);
			foreach ( $ids as $id ) {
				$out[ self::language_of( (int) $id ) ] ??= (int) $id;
			}
		}
		return $cache[ $post_id ] = $out;
	}

	/**
	 * ID da versão de um post em outro idioma, ou 0.
	 *
	 * @param int    $post_id Post.
	 * @param string $lang    Idioma desejado.
	 * @param bool   $public  Só versões publicadas.
	 */
	public static function translation_of( int $post_id, string $lang, bool $public = true ): int {
		$id = self::group_of( $post_id )[ $lang ] ?? 0;
		if ( $id && $public && 'publish' !== get_post_status( $id ) ) {
			return 0;
		}
		return $id;
	}

	/**
	 * Liga dois posts como versões um do outro.
	 *
	 * @param int $a Post.
	 * @param int $b Post.
	 */
	public static function link( int $a, int $b ): void {
		$group = (string) get_post_meta( $a, self::GROUP, true );
		if ( '' === $group ) {
			$group = (string) get_post_meta( $b, self::GROUP, true );
		}
		if ( '' === $group ) {
			$group = wp_generate_uuid4();
		}
		update_post_meta( $a, self::GROUP, $group );
		update_post_meta( $b, self::GROUP, $group );
	}

	/** Todo post salvo sem idioma recebe o idioma padrão. */
	public static function ensure_language( int $post_id, WP_Post $post ): void {
		if ( wp_is_post_revision( $post_id ) || ! in_array( $post->post_type, self::post_types(), true ) ) {
			return;
		}
		if ( ! has_term( '', self::TAXONOMY, $post_id ) ) {
			self::set_language( $post_id, Languages::default() );
		}
	}

	// ── slugs iguais em idiomas diferentes ─────────────────────────────────

	/**
	 * Permite o mesmo slug em idiomas diferentes (/projetos/x e /en/projects/x),
	 * em vez do "x-2" que o WordPress criaria.
	 *
	 * @param string $slug          Slug decidido pelo WordPress.
	 * @param int    $post_id       Post.
	 * @param string $status        Status.
	 * @param string $type          Tipo.
	 * @param int    $parent        Pai.
	 * @param string $original_slug Slug pedido.
	 */
	public static function shared_slug( string $slug, int $post_id, string $status, string $type, int $parent, string $original_slug ): string {
		if ( $slug === $original_slug || ! in_array( $type, self::post_types(), true ) ) {
			return $slug;
		}
		$lang = self::language_of( $post_id );
		foreach ( self::posts_with_slug( $original_slug, $type, $post_id, $parent ) as $other ) {
			if ( self::language_of( $other ) === $lang ) {
				return $slug;
			}
		}
		return $original_slug;
	}

	/**
	 * @param string $slug    Slug.
	 * @param string $type    Tipo.
	 * @param int    $exclude Post a ignorar.
	 * @param int    $parent  Pai (páginas).
	 * @return int[]
	 */
	private static function posts_with_slug( string $slug, string $type, int $exclude = 0, ?int $parent = null ): array {
		global $wpdb;
		$sql = $wpdb->prepare(
			"SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type = %s AND ID != %d AND post_status NOT IN ( 'trash', 'auto-draft', 'inherit' )",
			$slug,
			$type,
			$exclude
		);
		if ( null !== $parent && is_post_type_hierarchical( $type ) ) {
			$sql .= $wpdb->prepare( ' AND post_parent = %d', $parent );
		}
		return array_map( 'intval', $wpdb->get_col( $sql ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared -- montado com prepare acima.
	}

	/**
	 * Quando dois conteúdos têm o mesmo slug, abre o do idioma da URL.
	 *
	 * @param array $vars Variáveis da requisição.
	 */
	public static function resolve_shared_slug( array $vars ): array {
		$lang = Languages::current();

		if ( ! empty( $vars['pagename'] ) ) {
			$path = trim( (string) $vars['pagename'], '/' );
			$ids  = array_filter(
				self::posts_with_slug( basename( $path ), 'page' ),
				static fn( int $id ): bool => trim( get_page_uri( $id ), '/' ) === $path
			);
			if ( count( $ids ) > 1 ) {
				foreach ( $ids as $id ) {
					if ( self::language_of( $id ) === $lang ) {
						unset( $vars['pagename'] );
						$vars['page_id'] = $id;
						break;
					}
				}
			}
			return $vars;
		}

		if ( ! empty( $vars['name'] ) ) {
			$type = (string) ( $vars['post_type'] ?? 'post' );
			if ( ! in_array( $type, self::post_types(), true ) ) {
				return $vars;
			}
			$ids = self::posts_with_slug( (string) $vars['name'], $type );
			if ( count( $ids ) > 1 ) {
				foreach ( $ids as $id ) {
					if ( self::language_of( $id ) === $lang ) {
						unset( $vars['name'], $vars[ $type ] );
						$vars['p']         = $id;
						$vars['post_type'] = $type;
						break;
					}
				}
			}
		}
		return $vars;
	}

	// ── consultas ───────────────────────────────────────────────────────────

	/**
	 * Cláusula tax_query que filtra pelo idioma (posts sem idioma contam como padrão).
	 *
	 * @param string|null $lang Idioma (padrão: o atual).
	 */
	public static function tax_clause( ?string $lang = null ): array {
		$lang = $lang ?? Languages::current();
		if ( Languages::is_default( $lang ) ) {
			$others = array_values( array_diff( Languages::codes(), array( $lang ) ) );
			return array(
				'taxonomy' => self::TAXONOMY,
				'field'    => 'slug',
				'terms'    => $others,
				'operator' => 'NOT IN',
			);
		}
		return array(
			'taxonomy' => self::TAXONOMY,
			'field'    => 'slug',
			'terms'    => array( $lang ),
		);
	}

	/**
	 * Acrescenta o filtro de idioma a argumentos de WP_Query.
	 *
	 * @param array $args Argumentos.
	 */
	public static function with_language( array $args ): array {
		if ( 'all' === ( $args['lang'] ?? '' ) ) {
			return $args;
		}
		$args['tax_query']   = (array) ( $args['tax_query'] ?? array() ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		$args['tax_query'][] = self::tax_clause( $args['lang'] ?? null );
		return $args;
	}

	public static function filter_main_query( WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() || $query->is_singular() || $query->get( 'lang' ) === 'all' ) {
			return;
		}
		$types = (array) ( $query->get( 'post_type' ) ?: 'post' );
		if ( array_intersect( $types, self::post_types() ) || $query->is_search() || $query->is_tax() || $query->is_category() || $query->is_tag() ) {
			$tax   = (array) $query->get( 'tax_query' );
			$tax[] = self::tax_clause();
			$query->set( 'tax_query', $tax );
		}
	}

	/**
	 * @param array $vars Argumentos do bloco Query Loop.
	 */
	public static function filter_block_query( array $vars ): array {
		return is_admin() || wp_is_serving_rest_request() ? $vars : self::with_language( $vars );
	}

	// ── URLs ────────────────────────────────────────────────────────────────

	/**
	 * O link de um post leva o prefixo do idioma do post, não da página atual.
	 *
	 * @param string      $url  Link montado pelo WordPress.
	 * @param int|WP_Post $post Post.
	 */
	public static function permalink( string $url, int|WP_Post $post ): string {
		$post = get_post( $post );
		if ( ! $post || ! in_array( $post->post_type, self::post_types(), true ) ) {
			return $url;
		}
		$lang = self::language_of( $post );

		// As páginas iniciais de cada idioma apontam direto para / e /en/.
		if ( 'page' === $post->post_type ) {
			$front = (int) get_option( 'page_on_front' );
			if ( $front && in_array( $post->ID, self::raw_group( $front ), true ) ) {
				return Languages::home_url( $lang );
			}
		}
		return Languages::convert_url( $url, $lang );
	}

	/**
	 * Em /en/, a página inicial, a do blog e a de privacidade viram as versões em inglês.
	 *
	 * @param mixed $id ID salvo nas Configurações → Leitura.
	 */
	public static function translate_page_option( mixed $id ): mixed {
		static $running = false, $cache = array();
		// A consulta das traduções lê esta mesma opção (WP_Query → is_home); a trava evita o laço.
		if ( ! $id || $running || Languages::is_default() ) {
			return $id;
		}
		$key = Languages::current() . ':' . $id;
		if ( ! isset( $cache[ $key ] ) ) {
			$running       = true;
			$cache[ $key ] = self::translation_of( (int) $id, Languages::current() ) ?: $id;
			$running       = false;
		}
		return $cache[ $key ];
	}

	/** Conteúdo aberto com o prefixo do idioma errado vai para a URL certa (301). */
	public static function redirect_wrong_language(): void {
		if ( ! is_singular( self::post_types() ) || is_preview() ) {
			return;
		}
		$post = get_queried_object();
		if ( $post instanceof WP_Post && self::language_of( $post ) !== Languages::current() ) {
			wp_safe_redirect( get_permalink( $post ), 301 );
			exit;
		}
	}

	/**
	 * Grupo sem passar pelo filtro de página inicial (evita recursão).
	 *
	 * @param int $post_id Post.
	 * @return int[]
	 */
	private static function raw_group( int $post_id ): array {
		return array_values( self::group_of( $post_id ) );
	}

	// ── REST: campos e rotas usados pelo painel do editor ───────────────────

	public static function register_fields(): void {
		register_rest_field(
			self::post_types(),
			'rolck_lang',
			array(
				'get_callback'    => static fn( array $post ): string => self::language_of( (int) $post['id'] ),
				'update_callback' => static function ( $value, WP_Post $post ): void {
					self::set_language( $post->ID, (string) $value );
				},
				'schema'          => array(
					'type' => 'string',
					'enum' => Languages::codes(),
				),
			)
		);
	}

	public static function register_routes(): void {
		$can_edit = static fn( WP_REST_Request $r ): bool => current_user_can( 'edit_post', (int) $r['id'] );

		register_rest_route(
			'rolck/v1',
			'/translations/(?P<id>\d+)',
			array(
				array(
					'methods'             => 'GET',
					'permission_callback' => $can_edit,
					'callback'            => array( self::class, 'rest_list' ),
				),
				array(
					'methods'             => 'POST',
					'permission_callback' => $can_edit,
					'callback'            => array( self::class, 'rest_create' ),
					'args'                => array(
						'lang' => array(
							'required' => true,
							'enum'     => Languages::codes(),
						),
					),
				),
				array(
					'methods'             => 'PUT',
					'permission_callback' => $can_edit,
					'callback'            => array( self::class, 'rest_link' ),
					'args'                => array(
						'target' => array(
							'required' => true,
							'type'     => 'integer',
						),
					),
				),
				array(
					'methods'             => 'DELETE',
					'permission_callback' => $can_edit,
					'callback'            => array( self::class, 'rest_unlink' ),
				),
			)
		);

		register_rest_route(
			'rolck/v1',
			'/translatable',
			array(
				'methods'             => 'GET',
				'permission_callback' => static fn(): bool => current_user_can( 'edit_posts' ),
				'callback'            => array( self::class, 'rest_search' ),
				'args'                => array(
					'type'   => array(
						'required' => true,
						'type'     => 'string',
					),
					'lang'   => array(
						'required' => true,
						'enum'     => Languages::codes(),
					),
					'search' => array( 'type' => 'string' ),
				),
			)
		);
	}

	/** Versões existentes de um post, para o painel do editor. */
	public static function rest_list( WP_REST_Request $request ): array {
		$id  = (int) $request['id'];
		$out = array();
		foreach ( Languages::codes() as $code ) {
			$tid          = self::group_of( $id )[ $code ] ?? 0;
			$out[ $code ] = $tid ? self::describe( $tid ) : null;
		}
		return array(
			'lang'         => self::language_of( $id ),
			'translations' => $out,
			'autoEnabled'  => AutoTranslate\Translator::enabled(),
		);
	}

	/** Cria a versão em outro idioma como rascunho, copiando conteúdo, imagem, termos e metadados. */
	public static function rest_create( WP_REST_Request $request ): array|WP_Error {
		$source = get_post( (int) $request['id'] );
		$lang   = (string) $request['lang'];
		if ( ! $source ) {
			return new WP_Error( 'rolck_not_found', 'Conteúdo não encontrado.', array( 'status' => 404 ) );
		}
		if ( self::translation_of( $source->ID, $lang, false ) ) {
			return new WP_Error( 'rolck_exists', 'Já existe uma versão neste idioma.', array( 'status' => 409 ) );
		}

		// Com a tradução automática ligada, a versão já nasce traduzida.
		if ( AutoTranslate\Translator::enabled() && Languages::is_default( self::language_of( $source ) ) ) {
			$id = AutoTranslate\Sync::translate_post( $source->ID, $lang, true );
			if ( is_wp_error( $id ) ) {
				$id->add_data( array( 'status' => 502 ) );
				return $id;
			}
			return self::describe( $id );
		}

		$new_id = wp_insert_post(
			array(
				'post_type'    => $source->post_type,
				'post_status'  => 'draft',
				'post_title'   => $source->post_title,
				'post_content' => $source->post_content,
				'post_excerpt' => $source->post_excerpt,
				'post_parent'  => self::translation_of( (int) $source->post_parent, $lang, false ) ?: 0,
				'menu_order'   => $source->menu_order,
				'post_author'  => get_current_user_id(),
			),
			true
		);
		if ( is_wp_error( $new_id ) ) {
			return $new_id;
		}

		self::set_language( $new_id, $lang );
		self::link( $source->ID, $new_id );

		foreach ( get_post_meta( $source->ID ) as $key => $values ) {
			if ( self::GROUP === $key || str_starts_with( $key, '_edit_' ) || str_starts_with( $key, '_wp_old' ) ) {
				continue;
			}
			foreach ( $values as $value ) {
				add_post_meta( $new_id, $key, maybe_unserialize( $value ) );
			}
		}
		foreach ( get_object_taxonomies( $source->post_type ) as $taxonomy ) {
			if ( self::TAXONOMY !== $taxonomy ) {
				wp_set_object_terms( $new_id, wp_get_object_terms( $source->ID, $taxonomy, array( 'fields' => 'ids' ) ), $taxonomy );
			}
		}

		return self::describe( $new_id );
	}

	/** Liga o post a uma versão já existente em outro idioma. */
	public static function rest_link( WP_REST_Request $request ): array|WP_Error {
		$id     = (int) $request['id'];
		$target = (int) $request['target'];
		if ( ! get_post( $target ) || ! current_user_can( 'edit_post', $target ) ) {
			return new WP_Error( 'rolck_not_found', 'Conteúdo não encontrado.', array( 'status' => 404 ) );
		}
		if ( self::language_of( $id ) === self::language_of( $target ) ) {
			return new WP_Error( 'rolck_same_language', 'Os dois conteúdos estão no mesmo idioma.', array( 'status' => 400 ) );
		}
		// O alvo sai do grupo antigo antes de entrar no novo.
		delete_post_meta( $target, self::GROUP );
		self::link( $id, $target );
		return self::rest_list( $request );
	}

	/** Desfaz o vínculo deste post com as outras versões. */
	public static function rest_unlink( WP_REST_Request $request ): array {
		delete_post_meta( (int) $request['id'], self::GROUP );
		return self::rest_list( $request );
	}

	/** Busca conteúdos de um idioma que ainda não têm versão ligada, para vincular. */
	public static function rest_search( WP_REST_Request $request ): array {
		$query = new WP_Query(
			self::with_language(
				array(
					'post_type'      => sanitize_key( $request['type'] ),
					'post_status'    => array( 'publish', 'draft', 'pending', 'future', 'private' ),
					's'              => (string) $request['search'],
					'posts_per_page' => 20,
					'no_found_rows'  => true,
					'lang'           => (string) $request['lang'],
				)
			)
		);
		return array_map( array( self::class, 'describe' ), wp_list_pluck( $query->posts, 'ID' ) );
	}

	/**
	 * @param int $id Post.
	 */
	private static function describe( int $id ): array {
		return array(
			'id'     => $id,
			'title'  => html_entity_decode( get_the_title( $id ) ?: '(sem título)', ENT_QUOTES ),
			'status' => get_post_status( $id ),
			'edit'   => get_edit_post_link( $id, 'raw' ),
			'link'   => get_permalink( $id ),
			'lang'   => self::language_of( $id ),
			'auto'   => (bool) get_post_meta( $id, AutoTranslate\Sync::AUTO, true ),
			'manual' => (bool) get_post_meta( $id, AutoTranslate\Sync::MANUAL, true ),
		);
	}
}
