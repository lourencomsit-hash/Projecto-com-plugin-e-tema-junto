<?php
/**
 * Plugin Name: Breeze Safaris SEO
 * Plugin URI:  https://breezesafaris.com
 * Description: SEO completo para breezesafaris.com — meta tags, redirects 301, sitemap XML, schema JSON-LD, auditoria e migração de dados do tema anterior.
 * Version:     3.3.0
 * Author:      Breeze Safaris
 * Author URI:  https://breezesafaris.com
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: breeze-seo
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * @package BreezeSEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Constants ──────────────────────────────────────────────────────────────────
define( 'BREEZE_SEO_VERSION', '3.3.0' );
define( 'BREEZE_SEO_FILE',    __FILE__ );
define( 'BREEZE_SEO_DIR',     plugin_dir_path( __FILE__ ) );
define( 'BREEZE_SEO_URL',     plugin_dir_url( __FILE__ ) );
define( 'BREEZE_SEO_SLUG',    'breeze-seo' );

// ── Activation / Deactivation / Uninstall ─────────────────────────────────────
register_activation_hook( __FILE__,   'bseo_activate' );
register_deactivation_hook( __FILE__, 'bseo_deactivate' );
register_uninstall_hook( __FILE__,    'bseo_uninstall' );

function bseo_activate() {
	bseo_create_tables();
	// Schedule sitemap ping
	if ( ! wp_next_scheduled( 'bseo_ping_sitemap_cron' ) ) {
		wp_schedule_event( time(), 'daily', 'bseo_ping_sitemap_cron' );
	}
	// Set default options
	$defaults = bseo_default_settings();
	$existing = get_option( 'breeze_seo_settings', array() );
	update_option( 'breeze_seo_settings', array_merge( $defaults, $existing ) );
}

function bseo_deactivate() {
	wp_clear_scheduled_hook( 'bseo_ping_sitemap_cron' );
}

function bseo_uninstall() {
	global $wpdb;
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}breeze_redirects" ); // phpcs:ignore
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}breeze_crawl_log" ); // phpcs:ignore
	delete_option( 'breeze_seo_settings' );
	delete_option( 'breeze_seo_db_version' );
	delete_option( 'bseo_last_ping_time' );
	delete_option( 'bseo_last_ping_status' );
}

// ── Default settings ───────────────────────────────────────────────────────────
function bseo_default_settings() {
	return array(
		'title_format'               => '%title% | Breeze Safaris',
		'default_description'        => 'Breeze Safaris creates tailor-made Tanzania safari holidays, Serengeti tours, Ngorongoro trips and Zanzibar extensions with trusted local experts.',
		'og_image_url'               => 'https://breezesafaris.com/wp-content/uploads/2026/01/serengeti-4-1200x800.jpg',
		'og_image_id'                => 0,
		'google_verification'        => '',
		'bing_verification'          => '',
		'robots_txt'                 => "User-agent: *\nAllow: /\n\nSitemap: %SITEMAP_URL%",
		'schema_business_name'       => 'Breeze Safaris',
		'schema_email'               => 'info@breezesafaris.com',
		'schema_phone'               => '',
		'schema_address'             => 'Tanzania',
		'schema_country'             => 'TZ',
		'schema_region'              => 'Tanzania',
		'schema_logo_url'            => '',
		'schema_logo_id'             => 0,
		'sitemap_include_posts'      => '1',
		'sitemap_include_pages'      => '1',
		'sitemap_include_categories' => '0',
		'sitemap_ping_google'        => '1',
		'sitemap_excluded_ids'       => '',
	);
}

/**
 * Get a specific setting value with default fallback.
 */
function bseo_get_setting( $key, $default = '' ) {
	$settings = get_option( 'breeze_seo_settings', array() );
	$defaults = bseo_default_settings();
	if ( isset( $settings[ $key ] ) && '' !== $settings[ $key ] ) {
		return $settings[ $key ];
	}
	if ( isset( $defaults[ $key ] ) ) {
		return $defaults[ $key ];
	}
	return $default;
}

// ── Database tables ────────────────────────────────────────────────────────────
function bseo_create_tables() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();

	$sql_redirects = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}breeze_redirects (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		url_old varchar(2048) NOT NULL,
		url_new varchar(2048) NOT NULL,
		redirect_type smallint(4) NOT NULL DEFAULT 301,
		hits bigint(20) NOT NULL DEFAULT 0,
		notes text,
		created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		KEY url_old (url_old(191))
	) $charset_collate;";

	$sql_crawl_log = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}breeze_crawl_log (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		url_requested varchar(2048) NOT NULL,
		referrer varchar(2048),
		user_agent varchar(512),
		created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		KEY created_at (created_at)
	) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql_redirects );
	dbDelta( $sql_crawl_log );
	update_option( 'breeze_seo_db_version', '3.0' );
}

// ── Load plugin files ──────────────────────────────────────────────────────────
function bseo_load() {
	require_once BREEZE_SEO_DIR . 'includes/class-redirects.php';
	require_once BREEZE_SEO_DIR . 'includes/class-meta-tags.php';
	require_once BREEZE_SEO_DIR . 'includes/class-sitemap.php';
	require_once BREEZE_SEO_DIR . 'includes/class-schema.php';
	require_once BREEZE_SEO_DIR . 'includes/class-audit.php';
	require_once BREEZE_SEO_DIR . 'includes/class-importer.php';
	require_once BREEZE_SEO_DIR . 'public/class-public.php';

	if ( is_admin() ) {
		require_once BREEZE_SEO_DIR . 'admin/class-admin.php';
		Breeze_SEO_Admin::get_instance()->init();
	}

	Breeze_SEO_Public::get_instance()->init();
}
add_action( 'plugins_loaded', 'bseo_load' );

// ── Cron hook ──────────────────────────────────────────────────────────────────
add_action( 'bseo_ping_sitemap_cron', function () {
	if ( class_exists( 'Breeze_SEO_Sitemap' ) ) {
		Breeze_SEO_Sitemap::get_instance()->ping_google();
	}
} );

// ── robots.txt filter ─────────────────────────────────────────────────────────
add_filter( 'robots_txt', 'bseo_robots_txt_content', 10, 2 );
function bseo_robots_txt_content( $output, $public ) {
	$custom = bseo_get_setting( 'robots_txt', '' );
	if ( empty( $custom ) ) {
		return $output;
	}
	$sitemap_url = home_url( '/sitemap.xml' );
	$custom      = str_replace( '%SITEMAP_URL%', $sitemap_url, $custom );
	return $custom;
}

// ── Verification meta tags in wp_head ─────────────────────────────────────────
add_action( 'wp_head', 'bseo_output_verification_tags', 2 );
function bseo_output_verification_tags() {
	$google = bseo_get_setting( 'google_verification' );
	$bing   = bseo_get_setting( 'bing_verification' );
	if ( $google ) {
		echo '<meta name="google-site-verification" content="' . esc_attr( $google ) . '">' . "\n";
	}
	if ( $bing ) {
		echo '<meta name="msvalidate.01" content="' . esc_attr( $bing ) . '">' . "\n";
	}
}
