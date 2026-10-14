<?php
/**
 * Dados estruturados (JSON-LD) num único @graph: Organization, WebSite,
 * WebPage, BreadcrumbList, BlogPosting, SoftwareApplication (projetos) e
 * ItemList (vitrine).
 *
 * Para acrescentar um tipo, crie um método que devolva o nó e chame-o em graph().
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core\Seo;

use RolckGalaxy\Core\Content\Projects;
use RolckGalaxy\Core\I18n\Languages;
use RolckGalaxy\Core\Settings;
use WP_Post;

defined( 'ABSPATH' ) || exit;

final class Schema {

	public static function register(): void {
		if ( ! is_admin() ) {
			add_action( 'wp_head', array( self::class, 'output' ), 20 );
		}
	}

	public static function output(): void {
		if ( is_404() ) {
			return;
		}
		$graph = array_values( array_filter( self::graph() ) );
		printf(
			"<script type=\"application/ld+json\">%s</script>\n",
			wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
		);
	}

	private static function home(): string {
		return Languages::home_url( Languages::default() );
	}

	private static function id( string $fragment ): string {
		return self::home() . '#' . $fragment;
	}

	private static function lang(): string {
		return Languages::info( Languages::current() )['hreflang'];
	}

	/** @return array<int,array|null> */
	private static function graph(): array {
		$url  = Head::canonical() ?: Languages::home_url( Languages::current() );
		$post = Meta::current_post();

		$nodes = array( self::organization(), self::website(), self::webpage( $url, $post ) );

		if ( ! is_front_page() ) {
			$nodes[] = self::breadcrumbs( $url );
		}
		if ( is_singular( 'post' ) && $post ) {
			$nodes[] = self::article( $url, $post );
		}
		if ( is_singular( Projects::POST_TYPE ) && $post ) {
			$nodes[] = self::project( $url, $post );
		}
		if ( is_post_type_archive( Projects::POST_TYPE ) || is_tax( Projects::TYPE_TAX ) ) {
			$nodes[] = self::project_list( $url );
		}
		return (array) apply_filters( 'rolck_schema_graph', $nodes );
	}

	private static function organization(): array {
		$logo_id = (int) ( Settings::get( 'general.logo_id' ) ?: get_theme_mod( 'custom_logo' ) );
		$logo    = $logo_id ? wp_get_attachment_image_src( $logo_id, 'full' ) : false;
		$social  = array_values( array_filter( (array) Settings::get( 'general.social', array() ) ) );
		$email   = (string) Settings::get( 'general.email' );

		return array_filter(
			array(
				'@type'        => (string) Settings::get( 'seo.organization_type', 'Organization' ),
				'@id'          => self::id( 'organization' ),
				'name'         => (string) Settings::get( 'general.company_name' ),
				'url'          => self::home(),
				'description'  => rolck_l( Settings::get( 'general.tagline' ) ),
				'email'        => $email,
				'foundingDate' => (string) ( Settings::get( 'general.founded_year' ) ?: '' ),
				'sameAs'       => $social,
				'logo'         => $logo ? array(
					'@type'  => 'ImageObject',
					'@id'    => self::id( 'logo' ),
					'url'    => $logo[0],
					'width'  => $logo[1],
					'height' => $logo[2],
				) : null,
				'contactPoint' => $email ? array(
					'@type'             => 'ContactPoint',
					'contactType'       => 'sales',
					'email'             => $email,
					'availableLanguage' => array( 'Portuguese', 'English' ),
				) : null,
			)
		);
	}

	private static function website(): array {
		return array(
			'@type'           => 'WebSite',
			'@id'             => self::id( 'website' ),
			'url'             => self::home(),
			'name'            => (string) Settings::get( 'general.company_name' ),
			'inLanguage'      => array_map( static fn( $c ) => Languages::info( $c )['hreflang'], Languages::codes() ),
			'publisher'       => array( '@id' => self::id( 'organization' ) ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => array(
					'@type'       => 'EntryPoint',
					'urlTemplate' => Languages::home_url( Languages::current() ) . '?s={search_term_string}',
				),
				'query-input' => 'required name=search_term_string',
			),
		);
	}

	private static function webpage( string $url, ?WP_Post $post ): array {
		$image = Meta::image();
		$type  = match ( true ) {
			is_search()                                                                   => 'SearchResultsPage',
			is_home(), is_archive()                                                       => 'CollectionPage',
			is_page() && str_contains( (string) get_page_template_slug(), 'contact' )     => 'ContactPage',
			default                                                                       => 'WebPage',
		};
		return array_filter(
			array(
				'@type'              => $type,
				'@id'                => $url . '#webpage',
				'url'                => $url,
				'name'               => Meta::title(),
				'description'        => Meta::description(),
				'inLanguage'         => self::lang(),
				'isPartOf'           => array( '@id' => self::id( 'website' ) ),
				'about'              => is_front_page() ? array( '@id' => self::id( 'organization' ) ) : null,
				'breadcrumb'         => is_front_page() ? null : array( '@id' => $url . '#breadcrumb' ),
				'primaryImageOfPage' => $image ? array(
					'@type' => 'ImageObject',
					'url'   => $image['url'],
				) : null,
				'datePublished'      => $post ? get_the_date( 'c', $post ) : null,
				'dateModified'       => $post ? get_the_modified_date( 'c', $post ) : null,
			)
		);
	}

	private static function breadcrumbs( string $url ): array {
		$items = array();
		foreach ( Breadcrumbs::trail() as $i => $step ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'name'     => wp_strip_all_tags( $step['name'] ),
				'item'     => $step['url'],
			);
		}
		return array(
			'@type'           => 'BreadcrumbList',
			'@id'             => $url . '#breadcrumb',
			'itemListElement' => $items,
		);
	}

	private static function article( string $url, WP_Post $post ): array {
		$image = Meta::image();
		return array_filter(
			array(
				'@type'            => 'BlogPosting',
				'@id'              => $url . '#article',
				'headline'         => get_the_title( $post ),
				'description'      => Meta::description(),
				'inLanguage'       => self::lang(),
				'mainEntityOfPage' => array( '@id' => $url . '#webpage' ),
				'datePublished'    => get_the_date( 'c', $post ),
				'dateModified'     => get_the_modified_date( 'c', $post ),
				'image'            => $image ? $image['url'] : null,
				'wordCount'        => str_word_count( wp_strip_all_tags( $post->post_content ) ),
				'author'           => array(
					'@type' => 'Person',
					'name'  => get_the_author_meta( 'display_name', (int) $post->post_author ),
				),
				'publisher'        => array( '@id' => self::id( 'organization' ) ),
			)
		);
	}

	private static function project( string $url, WP_Post $post ): array {
		$data  = Projects::data( $post->ID );
		$image = Meta::image();
		$types = get_the_terms( $post, Projects::TYPE_TAX );
		$tech  = get_the_terms( $post, Projects::TECH_TAX );

		$offer = null;
		$price = self::price( (string) $data['price'] );
		if ( null !== $price ) {
			$offer = array_filter(
				array(
					'@type'         => 'Offer',
					'price'         => $price['amount'],
					'priceCurrency' => $price['currency'],
					'availability'  => 'available' === $data['status'] ? 'https://schema.org/InStock' : 'https://schema.org/PreOrder',
					'url'           => $data['buy_url'] ?: $url,
				)
			);
		}

		return array_filter(
			array(
				'@type'               => 'SoftwareApplication',
				'@id'                 => $url . '#software',
				'name'                => get_the_title( $post ),
				'description'         => Meta::description(),
				'url'                 => $url,
				'image'               => $image ? $image['url'] : null,
				'inLanguage'          => self::lang(),
				'applicationCategory' => 'DeveloperApplication',
				'applicationSubCategory' => is_array( $types ) && $types ? $types[0]->name : null,
				'operatingSystem'     => $data['compat'] ?: 'Web',
				'softwareVersion'     => $data['version'] ?: null,
				'datePublished'       => $data['released'] ?: null,
				'keywords'            => is_array( $tech ) ? implode( ', ', wp_list_pluck( $tech, 'name' ) ) : null,
				'featureList'         => $data['highlights'] ?: null,
				'author'              => array( '@id' => self::id( 'organization' ) ),
				'offers'              => $offer,
				'sameAs'              => array_values( array_filter( array( $data['demo_url'], $data['buy_url'], $data['repo_url'] ) ) ) ?: null,
			)
		);
	}

	private static function project_list( string $url ): ?array {
		global $wp_query;
		$items = array();
		foreach ( (array) $wp_query->posts as $i => $post ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'url'      => get_permalink( $post ),
				'name'     => get_the_title( $post ),
			);
		}
		return $items ? array(
			'@type'           => 'ItemList',
			'@id'             => $url . '#projects',
			'itemListElement' => $items,
		) : null;
	}

	/**
	 * Lê "$59", "R$ 199,90" ou "59 USD" como valor + moeda. Texto livre ("Grátis") vira 0.
	 *
	 * @param string $text Preço digitado no projeto.
	 * @return array{amount:string,currency:string}|null
	 */
	private static function price( string $text ): ?array {
		$text = trim( $text );
		if ( '' === $text ) {
			return null;
		}
		if ( preg_match( '/^(gr[aá]tis|free|gratuito)$/iu', $text ) ) {
			return array(
				'amount'   => '0',
				'currency' => 'USD',
			);
		}
		if ( ! preg_match( '/(\d+(?:[.,]\d{1,2})?)/', $text, $m ) ) {
			return null;
		}
		$currency = match ( true ) {
			str_contains( $text, 'R$' ) || stripos( $text, 'BRL' ) !== false => 'BRL',
			str_contains( $text, '€' ) || stripos( $text, 'EUR' ) !== false  => 'EUR',
			default                                                          => 'USD',
		};
		return array(
			'amount'   => str_replace( ',', '.', $m[1] ),
			'currency' => $currency,
		);
	}
}
