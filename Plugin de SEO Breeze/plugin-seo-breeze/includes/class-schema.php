<?php
/**
 * Schema / JSON-LD Structured Data.
 *
 * Outputs schema.org markup in <head>. Supports:
 * Organization, WebSite, TravelAgency, TouristDestination, TouristTrip,
 * WebPage, ContactPage, AboutPage, BreadcrumbList.
 *
 * @package BreezeSEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Breeze_SEO_Schema {

	private static $instance = null;
	private $settings        = array();
	private $defaults        = array(
		'schema_business_name' => 'Breeze Safaris',
		'schema_email'         => 'info@breezesafaris.com',
		'schema_phone'         => '',
		'schema_address'       => 'Tanzania',
		'schema_country'       => 'TZ',
		'schema_logo_url'      => '',
	);

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->settings = (array) get_option( 'breeze_seo_settings', array() );
	}

	public function init() {
		// Priority 5: after meta tags (priority 1), before most other head output
		add_action( 'wp_head', array( $this, 'output_schema' ), 5 );
	}

	// ── Output ────────────────────────────────────────────────────────────────

	public function output_schema() {
		$schema = $this->build_schema();
		if ( empty( $schema ) ) {
			return;
		}

		$json = wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
		if ( false === $json ) {
			return;
		}

		echo '<script type="application/ld+json">' . "\n" . $json . "\n</script>\n";
	}

	// ── Schema builder ────────────────────────────────────────────────────────

	public function build_schema() {
		global $post;

		$business_name = $this->cfg( 'schema_business_name' );
		$site_url      = trailingslashit( home_url() );
		$is_pt         = $this->detect_pt( $post );
		$in_language   = $is_pt ? 'pt-PT' : 'en';

		// Always-present: Organization + WebSite
		$org_node = array(
			'@type' => 'Organization',
			'@id'   => $site_url . '#organization',
			'name'  => $business_name,
			'url'   => $site_url,
			'email' => $this->cfg( 'schema_email' ),
		);
		$logo_url = $this->cfg( 'schema_logo_url' );
		if ( $logo_url ) {
			$org_node['logo'] = array( '@type' => 'ImageObject', 'url' => $logo_url );
		}

		$website_node = array(
			'@type'      => 'WebSite',
			'@id'        => $site_url . '#website',
			'name'       => $business_name,
			'url'        => $site_url,
			'inLanguage' => $in_language,
			'publisher'  => array( '@id' => $site_url . '#organization' ),
		);

		$graph = array( $org_node, $website_node );

		// Page-specific nodes
		if ( is_front_page() ) {
			$graph = array_merge( $graph, $this->homepage_nodes( $site_url, $business_name, $in_language, $is_pt, $post ) );
		} elseif ( $this->is_destination() ) {
			$graph = array_merge( $graph, $this->destination_nodes( $site_url, $in_language, $is_pt, $post ) );
		} elseif ( $this->is_package() ) {
			$graph = array_merge( $graph, $this->package_nodes( $site_url, $business_name, $in_language, $is_pt, $post ) );
		} elseif ( is_page( array( 'contact', 'contacto' ) ) ) {
			$graph = array_merge( $graph, $this->contact_nodes( $site_url, $in_language, $is_pt, $post ) );
		} elseif ( is_page( array( 'about', 'sobre-nos' ) ) ) {
			$graph = array_merge( $graph, $this->about_nodes( $site_url, $in_language, $is_pt, $post ) );
		} elseif ( is_singular() && $post ) {
			$graph[] = $this->webpage_node( $post, $site_url, $in_language, 'WebPage' );
			$graph[] = $this->breadcrumb_node( $post, $site_url, $is_pt );
		}

		return array( '@context' => 'https://schema.org', '@graph' => $graph );
	}

	// ── Page type builders ────────────────────────────────────────────────────

	private function homepage_nodes( $site_url, $business_name, $in_language, $is_pt, $post ) {
		$nodes    = array();
		$desc     = $this->get_meta_description( $post );
		$phone    = $this->cfg( 'schema_phone' );

		$business = array(
			'@type'       => 'TravelAgency',
			'@id'         => $site_url . '#business',
			'name'        => $business_name,
			'url'         => $site_url,
			'email'       => $this->cfg( 'schema_email' ),
			'areaServed'  => array( 'Tanzania', 'Zanzibar', 'Serengeti', 'Ngorongoro', 'Tarangire' ),
			'address'     => array(
				'@type'           => 'PostalAddress',
				'addressLocality' => $this->cfg( 'schema_address' ),
				'addressCountry'  => $this->cfg( 'schema_country' ),
			),
		);
		if ( $desc )  $business['description'] = $desc;
		if ( $phone ) $business['telephone']   = $phone;

		$logo_url = $this->cfg( 'schema_logo_url' );
		if ( $logo_url ) {
			$business['logo'] = array( '@type' => 'ImageObject', 'url' => $logo_url );
		}

		$nodes[] = $business;
		if ( $post ) {
			$nodes[] = $this->webpage_node( $post, $site_url, $in_language, 'WebPage' );
		}

		return $nodes;
	}

	private function destination_nodes( $site_url, $in_language, $is_pt, $post ) {
		if ( ! $post ) return array();

		$desc  = $this->get_meta_description( $post );
		$image = $this->get_og_image( $post );
		$nodes = array();

		$dest = array(
			'@type'            => 'TouristDestination',
			'name'             => get_the_title( $post ),
			'url'              => get_permalink( $post ),
			'containedInPlace' => array(
				'@type'          => 'Country',
				'name'           => $this->cfg( 'schema_address' ),
				'addressCountry' => $this->cfg( 'schema_country' ),
			),
		);
		if ( $desc )  $dest['description'] = $desc;
		if ( $image ) $dest['image']       = array( '@type' => 'ImageObject', 'url' => $image );

		$nodes[] = $dest;
		$nodes[] = $this->webpage_node( $post, $site_url, $in_language, 'WebPage' );
		$nodes[] = $this->breadcrumb_node( $post, $site_url, $is_pt );

		return $nodes;
	}

	private function package_nodes( $site_url, $business_name, $in_language, $is_pt, $post ) {
		if ( ! $post ) return array();

		$desc  = $this->get_meta_description( $post );
		$image = $this->get_og_image( $post );
		$nodes = array();

		$trip = array(
			'@type'       => 'TouristTrip',
			'name'        => get_the_title( $post ),
			'url'         => get_permalink( $post ),
			'provider'    => array( '@type' => 'TravelAgency', 'name' => $business_name, 'url' => $site_url ),
			'touristType' => array( 'Wildlife Safari', 'Adventure Travel' ),
		);
		if ( $desc )  $trip['description'] = $desc;
		if ( $image ) $trip['image']       = array( '@type' => 'ImageObject', 'url' => $image );

		$nodes[] = $trip;
		$nodes[] = $this->webpage_node( $post, $site_url, $in_language, 'WebPage' );
		$nodes[] = $this->breadcrumb_node( $post, $site_url, $is_pt );

		return $nodes;
	}

	private function contact_nodes( $site_url, $in_language, $is_pt, $post ) {
		if ( ! $post ) return array();
		return array(
			$this->webpage_node( $post, $site_url, $in_language, 'ContactPage' ),
			$this->breadcrumb_node( $post, $site_url, $is_pt ),
		);
	}

	private function about_nodes( $site_url, $in_language, $is_pt, $post ) {
		if ( ! $post ) return array();
		return array(
			$this->webpage_node( $post, $site_url, $in_language, 'AboutPage' ),
			$this->breadcrumb_node( $post, $site_url, $is_pt ),
		);
	}

	// ── Shared node builders ──────────────────────────────────────────────────

	private function webpage_node( $post, $site_url, $in_language, $type = 'WebPage' ) {
		$permalink = get_permalink( $post );
		$node      = array(
			'@type'      => $type,
			'@id'        => $permalink . '#webpage',
			'url'        => $permalink,
			'name'       => get_the_title( $post ),
			'inLanguage' => $in_language,
			'isPartOf'   => array( '@id' => $site_url . '#website' ),
		);
		$desc = $this->get_meta_description( $post );
		if ( $desc ) $node['description'] = $desc;
		return $node;
	}

	private function breadcrumb_node( $post, $site_url, $is_pt ) {
		$items = array();
		$pos   = 1;

		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $pos++,
			'name'     => $is_pt ? 'Início' : 'Home',
			'item'     => $site_url,
		);

		if ( $post->post_parent ) {
			$parent  = get_post( $post->post_parent );
			if ( $parent ) {
				$items[] = array(
					'@type'    => 'ListItem',
					'position' => $pos++,
					'name'     => get_the_title( $parent ),
					'item'     => get_permalink( $parent ),
				);
			}
		}

		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $pos,
			'name'     => get_the_title( $post ),
			'item'     => get_permalink( $post ),
		);

		return array( '@type' => 'BreadcrumbList', 'itemListElement' => $items );
	}

	// ── Page type detection ───────────────────────────────────────────────────

	private function is_destination() {
		return is_page() && 'page-templates/destination-page.php' === get_page_template_slug();
	}

	private function is_package() {
		if ( ! is_page() ) return false;
		global $post;
		if ( ! $post || ! $post->post_parent ) return false;
		$parent = get_post( $post->post_parent );
		return $parent && in_array( $parent->post_name, array( 'packages', 'pacotes' ), true );
	}

	// ── Helpers ───────────────────────────────────────────────────────────────

	private function cfg( $key ) {
		if ( isset( $this->settings[ $key ] ) && $this->settings[ $key ] !== '' ) {
			return $this->settings[ $key ];
		}
		return $this->defaults[ $key ] ?? '';
	}

	private function get_meta_description( $post ) {
		if ( ! $post ) return '';
		$desc = get_post_meta( $post->ID, '_bseo_description', true );
		if ( $desc ) return $desc;
		if ( $post->post_excerpt ) return wp_strip_all_tags( $post->post_excerpt );
		return '';
	}

	private function get_og_image( $post ) {
		if ( ! $post ) return '';
		$img = get_post_meta( $post->ID, '_bseo_og_image', true );
		if ( $img ) return $img;
		return $this->settings['og_image_url'] ?? '';
	}

	private function detect_pt( $post ) {
		if ( ! $post ) return false;
		if ( 'pt' === get_post_meta( $post->ID, '_breeze_lang', true ) ) return true;
		$path = wp_parse_url( get_permalink( $post ), PHP_URL_PATH );
		return $path && strpos( $path, '/pt/' ) === 0;
	}

	// ── Admin: field definitions ──────────────────────────────────────────────

	public function get_schema_settings_fields() {
		return array(
			array( 'key' => 'schema_business_name', 'label' => 'Business Name',              'type' => 'text',  'default' => 'Breeze Safaris' ),
			array( 'key' => 'schema_email',          'label' => 'Contact Email',              'type' => 'email', 'default' => 'info@breezesafaris.com' ),
			array( 'key' => 'schema_phone',          'label' => 'Phone Number (+intl format)','type' => 'text',  'default' => '' ),
			array( 'key' => 'schema_address',        'label' => 'City / Region',              'type' => 'text',  'default' => 'Tanzania' ),
			array( 'key' => 'schema_country',        'label' => 'Country Code (ISO 3166)',    'type' => 'text',  'default' => 'TZ' ),
			array( 'key' => 'schema_logo_url',       'label' => 'Logo URL',                   'type' => 'url',   'default' => '' ),
		);
	}
}
