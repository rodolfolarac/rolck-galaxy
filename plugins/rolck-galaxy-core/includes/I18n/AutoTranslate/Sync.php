<?php
/**
 * Mantém as versões nos outros idiomas atualizadas sozinhas.
 *
 * Com a tradução automática ligada, você escreve só no idioma principal:
 * - Publicou ou atualizou uma página, post ou projeto → a versão em inglês é
 *   criada/atualizada (título, conteúdo, resumo, SEO, campos do projeto, slug,
 *   imagem, categorias e links internos).
 * - Editou o cabeçalho/rodapé no Editor do site → "Cabeçalho (inglês)" acompanha.
 * - Criou uma categoria ou tecnologia → o nome em inglês é preenchido.
 * - Mudou um texto em português no painel → o inglês acompanha.
 *
 * Se alguém editar a versão traduzida à mão, ela passa a ser "manual" e não é
 * mais sobrescrita (dá para voltar ao automático no painel do editor).
 *
 * O trabalho roda depois que a resposta é enviada (PHP-FPM), então salvar no
 * editor continua instantâneo.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\I18n\AutoTranslate;

use RolckGalaxy\Core\I18n\Languages;
use RolckGalaxy\Core\I18n\TermTranslations;
use RolckGalaxy\Core\I18n\Translations;
use RolckGalaxy\Core\Settings;
use WP_Error;
use WP_Post;
use WP_REST_Request;

defined( 'ABSPATH' ) || exit;

final class Sync {

	public const AUTO    = '_rolck_auto';
	public const MANUAL  = '_rolck_manual';
	public const SOURCE  = '_rolck_auto_hash';
	public const WRITTEN = '_rolck_auto_written';
	public const ERROR   = 'rolck_translate_last_error';

	/** Campos traduzidos além de título, conteúdo e resumo. */
	private const TEXT_META = array( '_rolck_seo_title', '_rolck_seo_description', 'rolck_tagline', 'rolck_compat' );
	private const LIST_META = array( 'rolck_highlights' );

	/** Metadados que nunca são copiados entre versões. */
	private const SKIP_META = array( Translations::GROUP, self::AUTO, self::MANUAL, self::SOURCE, self::WRITTEN, '_edit_lock', '_edit_last', '_wp_old_slug', '_wp_old_date', '_wp_trash_meta_status', '_wp_trash_meta_time', '_encloseme', '_pingme' );

	/** Durante a escrita das traduções, os próprios ganchos de salvar são ignorados. */
	private static bool $running = false;

	/** @var array<int,bool> Conteúdos a traduzir no fim da requisição. */
	private static array $queue = array();

	public static function register(): void {
		add_action( 'wp_after_insert_post', array( self::class, 'on_save' ), 20, 2 );
		add_action( 'wp_trash_post', array( self::class, 'on_trash' ) );
		add_filter( 'pre_update_option_' . Settings::OPTION, array( self::class, 'on_settings' ), 10, 2 );
		add_action( 'init', array( self::class, 'term_hooks' ), 30 );
		add_action( 'rest_api_init', array( self::class, 'routes' ) );
	}

	private static function on(): bool {
		return Translator::enabled();
	}

	/** @return string[] Idiomas de destino (todos menos o principal). */
	private static function targets(): array {
		return array_values( array_filter( Languages::codes(), static fn( $c ) => ! Languages::is_default( $c ) ) );
	}

	// ── conteúdo ────────────────────────────────────────────────────────────

	public static function on_save( int $post_id, WP_Post $post ): void {
		if ( self::$running || wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}
		if ( 'wp_template_part' === $post->post_type ) {
			self::on_part_save( $post );
			return;
		}
		if ( ! in_array( $post->post_type, Translations::post_types(), true ) || 'wp_block' === $post->post_type ) {
			return;
		}

		if ( ! Languages::is_default( Translations::language_of( $post ) ) ) {
			self::detect_manual_edit( $post );
			return;
		}
		if ( self::on() && in_array( $post->post_status, array( 'publish', 'future', 'private' ), true ) ) {
			self::enqueue( $post_id );
		}
	}

	/** Versão traduzida editada por uma pessoa: para de ser sobrescrita. */
	private static function detect_manual_edit( WP_Post $post ): void {
		if ( get_post_meta( $post->ID, self::AUTO, true ) && get_post_meta( $post->ID, self::WRITTEN, true ) !== self::post_hash( $post ) ) {
			update_post_meta( $post->ID, self::MANUAL, 1 );
		}
	}

	/** Original foi para a lixeira → as traduções automáticas também. */
	public static function on_trash( int $post_id ): void {
		if ( self::$running || ! Languages::is_default( Translations::language_of( $post_id ) ) ) {
			return;
		}
		self::$running = true;
		foreach ( self::targets() as $lang ) {
			$target = Translations::translation_of( $post_id, $lang, false );
			if ( $target && get_post_meta( $target, self::AUTO, true ) && ! get_post_meta( $target, self::MANUAL, true ) ) {
				wp_trash_post( $target );
			}
		}
		self::$running = false;
	}

	private static function enqueue( int $post_id ): void {
		if ( ! self::$queue ) {
			add_action( 'shutdown', array( self::class, 'run_queue' ) );
		}
		self::$queue[ $post_id ] = true;
	}

	public static function run_queue(): void {
		if ( function_exists( 'fastcgi_finish_request' ) ) {
			fastcgi_finish_request(); // Responde ao navegador antes de traduzir.
		}
		foreach ( array_keys( self::$queue ) as $post_id ) {
			foreach ( self::targets() as $lang ) {
				self::translate_post( $post_id, $lang );
			}
		}
		self::$queue = array();
	}

	/**
	 * Cria ou atualiza a versão de um conteúdo em outro idioma.
	 *
	 * @param int    $source_id Conteúdo no idioma principal.
	 * @param string $lang      Idioma de destino.
	 * @param bool   $force     Traduz mesmo se nada mudou ou se a versão era manual.
	 * @return int|WP_Error ID da versão traduzida (0 se não precisou).
	 */
	public static function translate_post( int $source_id, string $lang, bool $force = false ): int|WP_Error {
		$source = get_post( $source_id );
		if ( ! $source ) {
			return new WP_Error( 'rolck_not_found', 'Conteúdo não encontrado.' );
		}
		$target = Translations::translation_of( $source_id, $lang, false );
		if ( $target && ! $force && get_post_meta( $target, self::MANUAL, true ) ) {
			return 0;
		}

		$fields = self::source_fields( $source );
		$hash   = md5( (string) wp_json_encode( $fields ) );
		if ( $target && ! $force && get_post_meta( $target, self::SOURCE, true ) === $hash ) {
			return 0;
		}

		// Textos curtos numa chamada só; o conteúdo em blocos à parte.
		$short = array( 'title' => $fields['title'], 'excerpt' => $fields['excerpt'] );
		foreach ( $fields['meta'] as $key => $value ) {
			if ( is_array( $value ) ) {
				foreach ( $value as $i => $item ) {
					$short[ "$key|$i" ] = $item;
				}
			} else {
				$short[ $key ] = $value;
			}
		}
		$texts = Translator::translate_many( array_filter( $short, static fn( $v ) => '' !== (string) $v ), $lang );
		if ( is_wp_error( $texts ) ) {
			return self::fail( $texts );
		}
		$content = ContentTranslator::translate( $fields['content'], $lang );
		if ( is_wp_error( $content ) ) {
			return self::fail( $content );
		}

		self::$running = true;
		$data          = array(
			'post_title'   => (string) ( $texts['title'] ?? $fields['title'] ),
			'post_content' => $content,
			'post_excerpt' => (string) ( $texts['excerpt'] ?? '' ),
			'menu_order'   => $source->menu_order,
			'post_parent'  => $source->post_parent ? ( Translations::translation_of( $source->post_parent, $lang, false ) ?: 0 ) : 0,
		);

		if ( ! $target ) {
			$target = wp_insert_post( $data + array( 'post_type' => $source->post_type, 'post_status' => 'draft', 'post_author' => $source->post_author ), true );
			if ( is_wp_error( $target ) ) {
				self::$running = false;
				return self::fail( $target );
			}
			Translations::set_language( $target, $lang );
			Translations::link( $source_id, $target );
			$status = Settings::get( 'translation.auto_publish', true ) ? $source->post_status : 'draft';
			wp_update_post(
				array(
					'ID'          => $target,
					'post_status' => $status,
					'post_name'   => sanitize_title( $data['post_title'] ) ?: $source->post_name,
					'post_date'   => $source->post_date,
				)
			);
		} else {
			wp_update_post( array( 'ID' => $target ) + $data );
		}

		self::copy_meta_and_terms( $source, $target );
		foreach ( $fields['meta'] as $key => $value ) {
			if ( is_array( $value ) ) {
				$list = array();
				foreach ( array_keys( $value ) as $i ) {
					$list[] = (string) ( $texts[ "$key|$i" ] ?? $value[ $i ] );
				}
				update_post_meta( $target, $key, $list );
			} else {
				update_post_meta( $target, $key, (string) ( $texts[ $key ] ?? $value ) );
			}
		}

		update_post_meta( $target, self::AUTO, 1 );
		delete_post_meta( $target, self::MANUAL );
		update_post_meta( $target, self::SOURCE, $hash );
		update_post_meta( $target, self::WRITTEN, self::post_hash( get_post( $target ) ) );
		self::$running = false;
		delete_option( self::ERROR );

		return (int) $target;
	}

	/** @return array{title:string,content:string,excerpt:string,meta:array} */
	private static function source_fields( WP_Post $post ): array {
		$meta = array();
		foreach ( self::TEXT_META as $key ) {
			$value = (string) get_post_meta( $post->ID, $key, true );
			if ( '' !== $value ) {
				$meta[ $key ] = $value;
			}
		}
		foreach ( self::LIST_META as $key ) {
			$value = get_post_meta( $post->ID, $key, true );
			if ( is_array( $value ) && $value ) {
				$meta[ $key ] = array_values( array_map( 'strval', $value ) );
			}
		}
		return array(
			'title'   => $post->post_title,
			'content' => $post->post_content,
			'excerpt' => $post->post_excerpt,
			'meta'    => $meta,
		);
	}

	/** Imagem, modelo, campos que não mudam com o idioma (demo, preço…) e termos. */
	private static function copy_meta_and_terms( WP_Post $source, int $target ): void {
		$skip = array_merge( self::SKIP_META, self::TEXT_META, self::LIST_META );
		foreach ( get_post_meta( $source->ID ) as $key => $values ) {
			if ( in_array( $key, $skip, true ) ) {
				continue;
			}
			delete_post_meta( $target, $key );
			foreach ( $values as $value ) {
				add_post_meta( $target, $key, maybe_unserialize( $value ) );
			}
		}
		foreach ( get_object_taxonomies( $source->post_type ) as $taxonomy ) {
			if ( Translations::TAXONOMY !== $taxonomy ) {
				wp_set_object_terms( $target, wp_get_object_terms( $source->ID, $taxonomy, array( 'fields' => 'ids' ) ), $taxonomy );
			}
		}
	}

	private static function post_hash( ?WP_Post $post ): string {
		return $post ? md5( $post->post_title . '|' . $post->post_content . '|' . $post->post_excerpt ) : '';
	}

	private static function fail( WP_Error $error ): WP_Error {
		update_option( self::ERROR, array( 'message' => $error->get_error_message(), 'time' => time() ), false );
		return $error;
	}

	// ── cabeçalho e rodapé (partes de modelo) ───────────────────────────────

	private static function on_part_save( WP_Post $post ): void {
		$suffix = '/-(' . implode( '|', array_map( 'preg_quote', self::targets() ) ) . ')$/';
		if ( preg_match( $suffix, $post->post_name ) ) {
			self::detect_manual_edit( $post );
			return;
		}
		if ( ! self::on() ) {
			return;
		}
		$terms = wp_get_post_terms( $post->ID, 'wp_theme', array( 'fields' => 'names' ) );
		$theme = is_array( $terms ) && $terms ? (string) $terms[0] : get_stylesheet();
		foreach ( self::targets() as $lang ) {
			self::translate_part( $post, $theme, $lang );
		}
	}

	private static function translate_part( WP_Post $post, string $theme, string $lang ): void {
		$slug     = $post->post_name . '-' . $lang;
		$existing = get_block_template( "$theme//$slug", 'wp_template_part' );
		if ( ! $existing || ( $existing->wp_id && get_post_meta( $existing->wp_id, self::MANUAL, true ) ) ) {
			return; // Só sincroniza partes que têm versão no outro idioma.
		}
		$content = ContentTranslator::translate( $post->post_content, $lang );
		if ( is_wp_error( $content ) ) {
			self::fail( $content );
			return;
		}

		self::$running = true;
		if ( $existing->wp_id ) {
			$id = $existing->wp_id;
			wp_update_post( array( 'ID' => $id, 'post_content' => $content ) );
		} else {
			$id = wp_insert_post(
				array(
					'post_type'    => 'wp_template_part',
					'post_status'  => 'publish',
					'post_name'    => $slug,
					'post_title'   => $existing->title,
					'post_content' => $content,
				)
			);
			if ( $id && ! is_wp_error( $id ) ) {
				wp_set_object_terms( $id, $theme, 'wp_theme' );
				wp_set_object_terms( $id, $existing->area, 'wp_template_part_area' );
			}
		}
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, self::AUTO, 1 );
			update_post_meta( $id, self::WRITTEN, self::post_hash( get_post( $id ) ) );
		}
		self::$running = false;
	}

	// ── categorias e tecnologias ────────────────────────────────────────────

	public static function term_hooks(): void {
		foreach ( TermTranslations::taxonomies() as $taxonomy ) {
			// Prioridade 20: depois de o formulário gravar os campos "Nome em English".
			add_action( "created_{$taxonomy}", array( self::class, 'on_term' ), 20 );
			add_action( "edited_{$taxonomy}", array( self::class, 'on_term' ), 20 );
		}
	}

	public static function on_term( int $term_id ): void {
		if ( ! self::on() ) {
			return;
		}
		$term = get_term( $term_id );
		if ( ! $term || is_wp_error( $term ) ) {
			return;
		}
		foreach ( self::targets() as $lang ) {
			foreach ( array( 'name', 'description' ) as $field ) {
				$key     = "rolck_{$field}_{$lang}";
				$current = (string) get_term_meta( $term_id, $key, true );
				$auto    = (string) get_term_meta( $term_id, "_rolck_auto_{$field}_{$lang}", true );
				$source  = (string) $term->{$field};
				// Só preenche se estiver vazio ou se o valor atual foi gerado automaticamente.
				if ( '' === $source || ( '' !== $current && $current !== $auto ) ) {
					continue;
				}
				$translated = Translator::translate( $source, $lang );
				if ( is_wp_error( $translated ) ) {
					self::fail( $translated );
					return;
				}
				$translated = wp_strip_all_tags( $translated );
				update_term_meta( $term_id, $key, $translated );
				update_term_meta( $term_id, "_rolck_auto_{$field}_{$lang}", $translated );
			}
		}
	}

	// ── textos do painel ────────────────────────────────────────────────────

	/**
	 * Texto em português mudou e o inglês não foi mexido → traduz o inglês.
	 *
	 * @param mixed $new Valor novo.
	 * @param mixed $old Valor antigo.
	 */
	public static function on_settings( mixed $new, mixed $old ): mixed {
		if ( ! is_array( $new ) || ! is_array( $old ) ) {
			return $new;
		}
		// Vale a partir do salvamento seguinte ao que ligou o serviço.
		if ( ! self::on() ) {
			return $new;
		}
		$default = Languages::default();
		$jobs    = array();
		self::find_pairs( $new, $old, array(), $jobs, $default );
		if ( ! $jobs ) {
			return $new;
		}
		foreach ( self::targets() as $lang ) {
			$sources    = array_map( static fn( $job ) => $job['text'], $jobs );
			$translated = Translator::translate_many( $sources, $lang );
			if ( is_wp_error( $translated ) ) {
				self::fail( $translated );
				return $new;
			}
			foreach ( $jobs as $i => $job ) {
				$ref = &$new;
				foreach ( $job['path'] as $key ) {
					$ref = &$ref[ $key ];
				}
				if ( ( $ref[ $lang ] ?? '' ) === ( $job['old'][ $lang ] ?? '' ) ) {
					$ref[ $lang ] = wp_strip_all_tags( $translated[ $i ] );
				}
				unset( $ref );
			}
		}
		return $new;
	}

	/**
	 * @param array  $new     Valores novos.
	 * @param array  $old     Valores antigos.
	 * @param array  $path    Caminho atual.
	 * @param array  $jobs    Saída.
	 * @param string $default Idioma principal.
	 */
	private static function find_pairs( array $new, array $old, array $path, array &$jobs, string $default ): void {
		foreach ( $new as $key => $value ) {
			if ( ! is_array( $value ) ) {
				continue;
			}
			$before = is_array( $old[ $key ] ?? null ) ? $old[ $key ] : array();
			if ( array_key_exists( $default, $value ) && ! array_diff( array_keys( $value ), Languages::codes() ) ) {
				$changed = (string) $value[ $default ] !== (string) ( $before[ $default ] ?? '' );
				if ( $changed && '' !== trim( (string) $value[ $default ] ) ) {
					$jobs[] = array(
						'path' => array_merge( $path, array( $key ) ),
						'text' => (string) $value[ $default ],
						'old'  => $before,
					);
				}
				continue;
			}
			self::find_pairs( $value, $before, array_merge( $path, array( $key ) ), $jobs, $default );
		}
	}

	// ── REST: painel do editor e aba Idiomas ────────────────────────────────

	public static function routes(): void {
		$admin = static fn(): bool => current_user_can( 'manage_options' );

		register_rest_route(
			'rolck/v1',
			'/translations/(?P<id>\d+)/auto',
			array(
				'methods'             => 'POST',
				'permission_callback' => static fn( WP_REST_Request $r ): bool => current_user_can( 'edit_post', (int) $r['id'] ),
				'callback'            => array( self::class, 'rest_auto' ),
				'args'                => array(
					'action' => array(
						'required' => true,
						'enum'     => array( 'sync', 'stop', 'resume' ),
					),
					'lang'   => array( 'enum' => Languages::codes() ),
				),
			)
		);
		register_rest_route(
			'rolck/v1',
			'/auto/test',
			array(
				'methods'             => 'POST',
				'permission_callback' => $admin,
				'callback'            => static function (): array|WP_Error {
					$result = Translator::translate( 'Olá! A Rolck Galaxy cria temas WordPress rápidos e fáceis de editar.', 'en' );
					return is_wp_error( $result ) ? $result : array( 'result' => $result );
				},
			)
		);
		register_rest_route(
			'rolck/v1',
			'/auto/pending',
			array(
				array(
					'methods'             => 'GET',
					'permission_callback' => $admin,
					'callback'            => static fn(): array => self::pending_report(),
				),
				array(
					'methods'             => 'POST',
					'permission_callback' => $admin,
					'callback'            => array( self::class, 'rest_run_pending' ),
				),
			)
		);
	}

	/** Parar, retomar ou forçar a tradução automática de uma versão. */
	public static function rest_auto( WP_REST_Request $request ): array|WP_Error {
		$id     = (int) $request['id'];
		$action = (string) $request['action'];
		$source = Languages::is_default( Translations::language_of( $id ) ) ? $id : ( Translations::group_of( $id )[ Languages::default() ] ?? 0 );
		$langs  = $request['lang'] ? array( (string) $request['lang'] ) : self::targets();
		if ( ! Languages::is_default( Translations::language_of( $id ) ) ) {
			$langs = array( Translations::language_of( $id ) );
		}

		foreach ( $langs as $lang ) {
			$target = Translations::translation_of( $source, $lang, false );
			if ( 'stop' === $action && $target ) {
				update_post_meta( $target, self::MANUAL, 1 );
				continue;
			}
			if ( ! $source ) {
				return new WP_Error( 'rolck_no_source', 'Esta página não está ligada a uma versão no idioma principal.', array( 'status' => 400 ) );
			}
			$result = self::translate_post( $source, $lang, true );
			if ( is_wp_error( $result ) ) {
				$result->add_data( array( 'status' => 502 ) );
				return $result;
			}
		}
		return Translations::rest_list( $request );
	}

	/** @return array{count:int,error:array|null} Conteúdos publicados sem tradução atualizada. */
	public static function pending_report(): array {
		return array(
			'count' => count( self::pending_ids() ),
			'error' => get_option( self::ERROR ) ?: null,
		);
	}

	/** @return int[] */
	private static function pending_ids(): array {
		$ids     = get_posts(
			Translations::with_language(
				array(
					'post_type'      => array_diff( Translations::post_types(), array( 'wp_block' ) ),
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'lang'           => Languages::default(),
				)
			)
		);
		$pending = array();
		foreach ( $ids as $id ) {
			foreach ( self::targets() as $lang ) {
				if ( ! Translations::translation_of( (int) $id, $lang, false ) ) {
					$pending[] = (int) $id;
					break;
				}
			}
		}
		return $pending;
	}

	/** Traduz alguns conteúdos pendentes por chamada (a tela chama de novo até zerar). */
	public static function rest_run_pending(): array|WP_Error {
		foreach ( array_slice( self::pending_ids(), 0, 3 ) as $id ) {
			foreach ( self::targets() as $lang ) {
				$result = self::translate_post( $id, $lang );
				if ( is_wp_error( $result ) ) {
					$result->add_data( array( 'status' => 502 ) );
					return $result;
				}
			}
		}
		return self::pending_report();
	}
}
