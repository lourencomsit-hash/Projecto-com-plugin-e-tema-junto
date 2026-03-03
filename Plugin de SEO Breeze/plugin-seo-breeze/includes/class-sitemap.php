<?php
/**
 * Sitemap XML — Geração automática de sitemaps EN e PT.
 *
 * Intercepts /sitemap.xml, /sitemap-en.xml, /sitemap-pt.xml at init
 * priority 1, before WordPress rewrite rules are evaluated.
 * No flush_rewrite_rules() call needed.
 *
 * @package BreezeSEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Breeze_SEO_Sitemap {

	private static $instance = null;
	private $settings        = array();

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
		add_action( 'init', array( $this, 'intercept_sitemap_uri' ), 1 );
		add_action( 'bseo_ping_sitemap_cron', array( $this, 'ping_google' ) );
	}

	// ── URI intercept ─────────────────────────────────────────────────────────
	// Runs at init priority 1, before WordPress rewrite rules are evaluated.
	// More reliable than rewrite rules which require a DB flush to take effect.

	public function intercept_sitemap_uri() {
		if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return;
		}

		$request_path = parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ); // phpcs:ignore
		if ( ! $request_path ) {
			return;
		}

		// Strip subdirectory prefix (for WP installs not at domain root)
		$home_path = rtrim( parse_url( get_option( 'home' ), PHP_URL_PATH ), '/' );
		if ( $home_path && strpos( $request_path, $home_path ) === 0 ) {
			$request_path = substr( $request_path, strlen( $home_path ) );
		}
		$request_path = trim( $request_path, '/' );

		if ( 'sitemap.xml' === $request_path ) {
			$type = 'index';
		} elseif ( 'sitemap-en.xml' === $request_path ) {
			$type = 'en';
		} elseif ( 'sitemap-pt.xml' === $request_path ) {
			$type = 'pt';
		} else {
			return;
		}

		// Clear any output buffers started before this point
		while ( ob_get_level() ) {
			ob_end_clean();
		}

		header( 'Content-Type: application/xml; charset=UTF-8' );
		header( 'X-Robots-Tag: noindex' );
		header( 'Cache-Control: no-cache, must-revalidate' );

		switch ( $type ) {
			case 'index':
				echo $this->build_sitemap_index(); // phpcs:ignore
				break;
			case 'en':
				echo $this->build_language_sitemap( 'en' ); // phpcs:ignore
				break;
			case 'pt':
				echo $this->build_language_sitemap( 'pt' ); // phpcs:ignore
				break;
		}

		exit;
	}

	// ── Sitemap index ─────────────────────────────────────────────────────────

	private function build_sitemap_index() {
		$lastmod = gmdate( 'Y-m-d' );
		$base    = trailingslashit( home_url() );

		$xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
		$xml .= "\t<sitemap>\n";
		$xml .= "\t\t<loc>" . esc_url( $base . 'sitemap-en.xml' ) . "</loc>\n";
		$xml .= "\t\t<lastmod>" . esc_html( $lastmod ) . "</lastmod>\n";
		$xml .= "\t</sitemap>\n";
		$xml .= "\t<sitemap>\n";
		$xml .= "\t\t<loc>" . esc_url( $base . 'sitemap-pt.xml' ) . "</loc>\n";
		$xml .= "\t\t<lastmod>" . esc_html( $lastmod ) . "</lastmod>\n";
		$xml .= "\t</sitemap>\n";
		$xml .= '</sitemapindex>';

		return $xml;
	}

	// ── Language sitemaps ─────────────────────────────────────────────────────

	private function build_language_sitemap( $lang ) {
		$pages = $this->get_sitemap_pages( $lang );

		$xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
		foreach ( $pages as $page_data ) {
			$xml .= $this->build_url_element( $page_data );
		}
		$xml .= '</urlset>';

		return $xml;
	}

	private function get_sitemap_pages( $lang ) {
		$excluded_ids  = $this->get_excluded_ids();
		$include_pages = $this->settings['sitemap_include_pages'] ?? '1';
		$results       = array();

		if ( '1' === $include_pages ) {
			$all_pages = get_pages( array(
				'post_status' => 'publish',
				'sort_column' => 'menu_order',
				'number'      => 0,
			) );

			foreach ( $all_pages as $page ) {
				$page_lang = (string) get_post_meta( $page->ID, '_breeze_lang', true );

				if ( 'en' === $lang && 'pt' === $page_lang ) {
					continue;
				}
				if ( 'pt' === $lang && 'pt' !== $page_lang ) {
					continue;
				}
				if ( in_array( $page->ID, $excluded_ids, true ) ) {
					continue;
				}
				if ( '1' === get_post_meta( $page->ID, '_bseo_noindex', true ) ) {
					continue;
				}
				if ( in_array( $page->post_name, array( 'privacy-policy', 'politica-de-privacidade' ), true ) ) {
					continue;
				}

				$results[] = $this->prepare_url_data( $page );
			}
		}

		// Optionally include posts (EN only)
		$include_posts = $this->settings['sitemap_include_posts'] ?? '0';
		if ( '1' === $include_posts && 'en' === $lang ) {
			$posts = get_posts( array(
				'post_type'   => 'post',
				'post_status' => 'publish',
				'numberposts' => -1,
			) );
			foreach ( $posts as $post ) {
				if ( in_array( $post->ID, $excluded_ids, true ) ) {
					continue;
				}
				if ( '1' === get_post_meta( $post->ID, '_bseo_noindex', true ) ) {
					continue;
				}
				$results[] = $this->prepare_url_data( $post );
			}
		}

		return $results;
	}

	private function prepare_url_data( $post ) {
		$modified = $post->post_modified_gmt !== '0000-00-00 00:00:00'
			? gmdate( 'Y-m-d', strtotime( $post->post_modified_gmt ) )
			: gmdate( 'Y-m-d' );

		$priority   = $this->get_priority( $post );
		$changefreq = $this->get_changefreq( $priority );

		return array(
			'loc'        => get_permalink( $post ),
			'lastmod'    => $modified,
			'changefreq' => $changefreq,
			'priority'   => $priority,
		);
	}

	private function build_url_element( $data ) {
		$xml  = "\t<url>\n";
		$xml .= "\t\t<loc>" . esc_url( $data['loc'] ) . "</loc>\n";
		$xml .= "\t\t<lastmod>" . esc_html( $data['lastmod'] ) . "</lastmod>\n";
		$xml .= "\t\t<changefreq>" . esc_html( $data['changefreq'] ) . "</changefreq>\n";
		$xml .= "\t\t<priority>" . esc_html( $data['priority'] ) . "</priority>\n";
		$xml .= "\t</url>\n";
		return $xml;
	}

	// ── Priority logic ────────────────────────────────────────────────────────

	private function get_priority( $post ) {
		if ( (int) $post->ID === (int) get_option( 'page_on_front' ) ) {
			return '1.0';
		}
		$template = get_page_template_slug( $post->ID );
		if ( 'page-templates/destination-page.php' === $template ) {
			return '0.9';
		}
		if ( $post->post_parent ) {
			$parent = get_post( $post->post_parent );
			if ( $parent && in_array( $parent->post_name, array( 'packages', 'pacotes' ), true ) ) {
				return '0.8';
			}
		}
		return '0.7';
	}

	private function get_changefreq( $priority ) {
		switch ( $priority ) {
			case '1.0': return 'daily';
			case '0.9':
			case '0.8': return 'weekly';
			default:    return 'monthly';
		}
	}

	// ── Helpers ───────────────────────────────────────────────────────────────

	private function get_excluded_ids() {
		$raw = $this->settings['sitemap_excluded_ids'] ?? '';
		if ( empty( $raw ) ) {
			return array();
		}
		return array_filter( array_map( 'absint', explode( ',', $raw ) ) );
	}

	public function get_sitemap_url() {
		return home_url( '/sitemap.xml' );
	}

	// ── Google Ping ───────────────────────────────────────────────────────────

	public function ping_google() {
		$ping_enabled = $this->settings['sitemap_ping_google'] ?? '1';
		if ( '1' !== $ping_enabled ) {
			return false;
		}

		$url      = 'https://www.google.com/ping?sitemap=' . urlencode( $this->get_sitemap_url() );
		$response = wp_remote_get( $url, array(
			'timeout'    => 10,
			'user-agent' => 'Breeze Safaris SEO/' . BREEZE_SEO_VERSION . '; ' . home_url(),
		) );

		update_option( 'bseo_last_ping_time', current_time( 'mysql' ) );
		update_option( 'bseo_last_ping_status', is_wp_error( $response ) ? 'error' : wp_remote_retrieve_response_code( $response ) );

		return $response;
	}
}
