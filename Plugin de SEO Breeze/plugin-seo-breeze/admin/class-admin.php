<?php
/**
 * Admin panel — menu pages, settings, AJAX handlers, asset enqueueing.
 *
 * @package BreezeSEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Breeze_SEO_Admin {

	private static $instance = null;

	/** Slug of the top-level menu page. */
	const MENU_SLUG = 'breeze-seo';

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	public function init() {
		add_action( 'admin_menu',             array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init',             array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts',  array( $this, 'enqueue_assets' ) );

		// Redirect AJAX
		add_action( 'wp_ajax_bseo_add_redirect',          array( $this, 'ajax_add_redirect' ) );
		add_action( 'wp_ajax_bseo_update_redirect',       array( $this, 'ajax_update_redirect' ) );
		add_action( 'wp_ajax_bseo_delete_redirect',       array( $this, 'ajax_delete_redirect' ) );
		add_action( 'wp_ajax_bseo_bulk_delete_redirects', array( $this, 'ajax_bulk_delete_redirects' ) );
		add_action( 'wp_ajax_bseo_export_redirects_csv',  array( $this, 'ajax_export_redirects_csv' ) );
		add_action( 'wp_ajax_bseo_export_sample_csv',     array( $this, 'ajax_export_sample_csv' ) );
		add_action( 'wp_ajax_bseo_import_redirects_csv',  array( $this, 'ajax_import_redirects_csv' ) );
		add_action( 'wp_ajax_bseo_clear_crawl_log',       array( $this, 'ajax_clear_crawl_log' ) );
		add_action( 'wp_ajax_bseo_ping_sitemap_now',      array( $this, 'ajax_ping_sitemap' ) );
		add_action( 'wp_ajax_bseo_bulk_set_og_image',     array( $this, 'ajax_bulk_set_og_image' ) );
	}

	// ── Menu ─────────────────────────────────────────────────────────────────

	public function add_admin_menu() {
		add_menu_page(
			'Breeze SEO',
			'Breeze SEO',
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'page_dashboard' ),
			'dashicons-search',
			80
		);

		add_submenu_page( self::MENU_SLUG, __( 'Dashboard',        'breeze-seo' ), __( 'Dashboard',        'breeze-seo' ), 'manage_options', self::MENU_SLUG,            array( $this, 'page_dashboard' ) );
		add_submenu_page( self::MENU_SLUG, __( 'Settings',         'breeze-seo' ), __( 'Settings',         'breeze-seo' ), 'manage_options', 'bseo-settings',           array( $this, 'page_settings' ) );
		add_submenu_page( self::MENU_SLUG, __( 'Redirects',        'breeze-seo' ), __( 'Redirects',        'breeze-seo' ), 'manage_options', 'bseo-redirects',          array( $this, 'page_redirects' ) );
		add_submenu_page( self::MENU_SLUG, __( 'SEO Audit',        'breeze-seo' ), __( 'SEO Audit',        'breeze-seo' ), 'manage_options', 'bseo-audit',              array( $this, 'page_audit' ) );
		add_submenu_page( self::MENU_SLUG, __( 'Import / Export',  'breeze-seo' ), __( 'Import / Export',  'breeze-seo' ), 'manage_options', 'bseo-import',             array( $this, 'page_import' ) );
	}

	// ── Settings API ─────────────────────────────────────────────────────────

	public function register_settings() {
		register_setting(
			'breeze_seo_settings_group',
			'breeze_seo_settings',
			array( 'sanitize_callback' => array( $this, 'sanitize_settings' ) )
		);
	}

	public function sanitize_settings( $input ) {
		if ( ! is_array( $input ) ) {
			return array();
		}

		$clean = array();

		// Text fields
		$text_fields = array(
			'title_format', 'google_verification', 'bing_verification',
			'schema_business_name', 'schema_email', 'schema_phone',
			'schema_address', 'schema_country', 'schema_region',
			'sitemap_excluded_ids',
		);
		foreach ( $text_fields as $f ) {
			$clean[ $f ] = isset( $input[ $f ] ) ? sanitize_text_field( $input[ $f ] ) : '';
		}

		// Textarea fields
		$textarea_fields = array( 'default_description', 'robots_txt' );
		foreach ( $textarea_fields as $f ) {
			$clean[ $f ] = isset( $input[ $f ] ) ? sanitize_textarea_field( $input[ $f ] ) : '';
		}

		// URL fields
		$url_fields = array( 'og_image_url', 'schema_logo_url' );
		foreach ( $url_fields as $f ) {
			$clean[ $f ] = isset( $input[ $f ] ) ? esc_url_raw( $input[ $f ] ) : '';
		}

		// Integer fields
		$int_fields = array( 'og_image_id', 'schema_logo_id' );
		foreach ( $int_fields as $f ) {
			$clean[ $f ] = isset( $input[ $f ] ) ? absint( $input[ $f ] ) : 0;
		}

		// Checkbox fields
		$checkbox_fields = array(
			'sitemap_include_posts', 'sitemap_include_pages',
			'sitemap_include_categories', 'sitemap_ping_google',
		);
		foreach ( $checkbox_fields as $f ) {
			$clean[ $f ] = isset( $input[ $f ] ) && $input[ $f ] === '1' ? '1' : '0';
		}

		return $clean;
	}

	// ── Asset enqueueing ─────────────────────────────────────────────────────

	public function enqueue_assets( $hook ) {
		// Admin panel pages
		$admin_pages = array(
			'toplevel_page_breeze-seo',
			'breeze-seo_page_bseo-settings',
			'breeze-seo_page_bseo-redirects',
			'breeze-seo_page_bseo-audit',
			'breeze-seo_page_bseo-import',
		);

		// Post editor (meta box)
		$editor_pages = array( 'post.php', 'post-new.php' );

		$is_admin_page  = in_array( $hook, $admin_pages, true );
		$is_editor_page = in_array( $hook, $editor_pages, true );

		if ( ! $is_admin_page && ! $is_editor_page ) {
			return;
		}

		wp_enqueue_style(
			'bseo-admin',
			BREEZE_SEO_URL . 'admin/assets/css/admin.css',
			array(),
			BREEZE_SEO_VERSION
		);

		if ( $is_editor_page || $is_admin_page ) {
			wp_enqueue_media();
		}

		wp_enqueue_script(
			'bseo-admin',
			BREEZE_SEO_URL . 'admin/assets/js/admin.js',
			array( 'jquery' ),
			BREEZE_SEO_VERSION,
			true
		);

		wp_localize_script(
			'bseo-admin',
			'bseoAdmin',
			array(
				'ajaxurl'      => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( 'bseo_admin_nonce' ),
				'importNonce'  => wp_create_nonce( 'bseo_importer_nonce' ),
				'auditNonce'   => wp_create_nonce( 'bseo_export_csv_nonce' ),
				'isEditorPage' => $is_editor_page ? 1 : 0,
				'strings'      => array(
					'confirm_delete'       => __( 'Delete this redirect?', 'breeze-seo' ),
					'confirm_bulk_delete'  => __( 'Delete selected redirects?', 'breeze-seo' ),
					'confirm_clear_log'    => __( 'Clear all crawl log entries?', 'breeze-seo' ),
					'saved'                => __( 'Saved.', 'breeze-seo' ),
					'error'                => __( 'An error occurred. Please try again.', 'breeze-seo' ),
					'choose_image'         => __( 'Choose Image', 'breeze-seo' ),
					'use_image'            => __( 'Use this image', 'breeze-seo' ),
				),
			)
		);
	}

	// ── Page renderers ───────────────────────────────────────────────────────

	public function page_dashboard() {
		include BREEZE_SEO_DIR . 'admin/views/dashboard.php';
	}

	public function page_settings() {
		include BREEZE_SEO_DIR . 'admin/views/settings.php';
	}

	public function page_redirects() {
		include BREEZE_SEO_DIR . 'admin/views/redirects.php';
	}

	public function page_audit() {
		include BREEZE_SEO_DIR . 'admin/views/audit.php';
	}

	public function page_import() {
		include BREEZE_SEO_DIR . 'admin/views/importer.php';
	}

	// ── AJAX: Redirects ───────────────────────────────────────────────────────

	private function check_admin_nonce() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'breeze-seo' ) ), 403 );
		}
		check_ajax_referer( 'bseo_admin_nonce', 'nonce' );
	}

	public function ajax_add_redirect() {
		$this->check_admin_nonce();

		$url_old = isset( $_POST['url_old'] ) ? wp_unslash( $_POST['url_old'] ) : '';
		$url_new = isset( $_POST['url_new'] ) ? wp_unslash( $_POST['url_new'] ) : '';
		$type    = isset( $_POST['redirect_type'] ) ? absint( $_POST['redirect_type'] ) : 301;
		$notes   = isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '';

		if ( empty( $url_old ) || empty( $url_new ) ) {
			wp_send_json_error( array( 'message' => __( 'Old URL and New URL are required.', 'breeze-seo' ) ) );
		}

		$result = Breeze_SEO_Redirects::get_instance()->add_redirect( $url_old, $url_new, $type, $notes );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( array( 'id' => $result, 'message' => __( 'Redirect added.', 'breeze-seo' ) ) );
	}

	public function ajax_update_redirect() {
		$this->check_admin_nonce();

		$id      = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$url_old = isset( $_POST['url_old'] ) ? wp_unslash( $_POST['url_old'] ) : '';
		$url_new = isset( $_POST['url_new'] ) ? wp_unslash( $_POST['url_new'] ) : '';
		$type    = isset( $_POST['redirect_type'] ) ? absint( $_POST['redirect_type'] ) : 301;
		$notes   = isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '';

		if ( ! $id || empty( $url_old ) || empty( $url_new ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid data.', 'breeze-seo' ) ) );
		}

		$result = Breeze_SEO_Redirects::get_instance()->update_redirect( $id, $url_old, $url_new, $type, $notes );
		wp_send_json_success( array( 'message' => __( 'Redirect updated.', 'breeze-seo' ) ) );
	}

	public function ajax_delete_redirect() {
		$this->check_admin_nonce();

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid ID.', 'breeze-seo' ) ) );
		}

		Breeze_SEO_Redirects::get_instance()->delete_redirect( $id );
		wp_send_json_success( array( 'message' => __( 'Deleted.', 'breeze-seo' ) ) );
	}

	public function ajax_bulk_delete_redirects() {
		$this->check_admin_nonce();

		$ids = isset( $_POST['ids'] ) ? array_map( 'absint', (array) $_POST['ids'] ) : array();
		if ( empty( $ids ) ) {
			wp_send_json_error( array( 'message' => __( 'No IDs provided.', 'breeze-seo' ) ) );
		}

		$count = Breeze_SEO_Redirects::get_instance()->bulk_delete( $ids );
		/* translators: %d: number of deleted redirects */
		wp_send_json_success( array( 'message' => sprintf( __( '%d redirect(s) deleted.', 'breeze-seo' ), (int) $count ) ) );
	}

	public function ajax_export_redirects_csv() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'breeze-seo' ) );
		}
		check_ajax_referer( 'bseo_admin_nonce', 'nonce' );
		Breeze_SEO_Redirects::get_instance()->export_csv();
		exit;
	}

	public function ajax_export_sample_csv() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'breeze-seo' ) );
		}
		check_ajax_referer( 'bseo_admin_nonce', 'nonce' );
		Breeze_SEO_Redirects::get_instance()->export_sample_csv();
		exit;
	}

	public function ajax_import_redirects_csv() {
		$this->check_admin_nonce();

		if ( empty( $_FILES['csv_file']['tmp_name'] ) || ! is_uploaded_file( $_FILES['csv_file']['tmp_name'] ) ) {
			wp_send_json_error( array( 'message' => __( 'No file uploaded.', 'breeze-seo' ) ) );
		}

		$result = Breeze_SEO_Redirects::get_instance()->import_from_csv( $_FILES['csv_file']['tmp_name'] );
		wp_send_json_success( $result );
	}

	public function ajax_clear_crawl_log() {
		$this->check_admin_nonce();
		Breeze_SEO_Redirects::get_instance()->clear_crawl_log();
		wp_send_json_success( array( 'message' => __( 'Crawl log cleared.', 'breeze-seo' ) ) );
	}

	public function ajax_ping_sitemap() {
		$this->check_admin_nonce();
		$response = Breeze_SEO_Sitemap::get_instance()->ping_google();
		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'message' => $response->get_error_message() ) );
		}
		$code = wp_remote_retrieve_response_code( $response );
		/* translators: %s: HTTP status code */
		wp_send_json_success( array( 'message' => sprintf( __( 'Ping sent. Google responded with HTTP %s.', 'breeze-seo' ), $code ) ) );
	}

	public function ajax_bulk_set_og_image() {
		$this->check_admin_nonce();

		$og_url = bseo_get_setting( 'og_image_url' );
		if ( empty( $og_url ) ) {
			wp_send_json_error( array( 'message' => __( 'No default OG image configured in Settings.', 'breeze-seo' ) ) );
		}

		$posts = get_posts( array(
			'post_type'      => array( 'page', 'post' ),
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		) );

		$updated = 0;
		$skipped = 0;
		foreach ( $posts as $post_id ) {
			if ( ! empty( get_post_meta( $post_id, '_bseo_og_image', true ) ) ) {
				$skipped++;
				continue;
			}
			update_post_meta( $post_id, '_bseo_og_image', esc_url_raw( $og_url ) );
			$updated++;
		}

		wp_send_json_success( array(
			'message' => sprintf(
				/* translators: 1: updated count, 2: skipped count */
				__( 'Done. Updated: %1$d page(s), Skipped (already had OG image): %2$d.', 'breeze-seo' ),
				$updated,
				$skipped
			),
		) );
	}

	// ── Helpers ───────────────────────────────────────────────────────────────

	/**
	 * Output an admin notice (call before page output).
	 */
	public static function admin_notice( $message, $type = 'success' ) {
		printf(
			'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
			esc_attr( $type ),
			esc_html( $message )
		);
	}

	/**
	 * Get the URL for a sub-page of our plugin admin.
	 */
	public static function page_url( $slug ) {
		return admin_url( 'admin.php?page=' . rawurlencode( $slug ) );
	}
}
