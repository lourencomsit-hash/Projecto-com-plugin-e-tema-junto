<?php
/**
 * Public-facing — bootstraps all front-end SEO classes.
 *
 * @package BreezeSEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Breeze_SEO_Public {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	public function init() {
		Breeze_SEO_Redirects::get_instance()->init();
		Breeze_SEO_Meta_Tags::get_instance()->init();
		Breeze_SEO_Sitemap::get_instance()->init();
		Breeze_SEO_Schema::get_instance()->init();
		// Register AJAX hooks available in wp-admin context
		Breeze_SEO_Audit::get_instance()->init();
		Breeze_SEO_Importer::get_instance()->init();
	}
}
