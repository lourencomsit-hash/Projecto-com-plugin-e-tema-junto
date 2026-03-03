<?php
/**
 * SEO Audit — Scores, missing data, duplicates, image alt check.
 *
 * @package BreezeSEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Breeze_SEO_Audit {

	private static $instance = null;
	private $post_types      = array( 'page', 'post' );

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	public function init() {
		add_action( 'wp_ajax_bseo_export_audit_csv', array( $this, 'ajax_export_csv' ) );
	}

	// ── SEO Scores ────────────────────────────────────────────────────────────

	/**
	 * Returns array of all published pages/posts with SEO score data.
	 */
	public function get_pages_seo_scores() {
		$posts = get_posts( array(
			'post_type'      => $this->post_types,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'post_type',
			'order'          => 'ASC',
		) );

		$results = array();

		foreach ( $posts as $post ) {
			$bseo_title   = (string) get_post_meta( $post->ID, '_bseo_title', true );
			$bseo_desc    = (string) get_post_meta( $post->ID, '_bseo_description', true );
			$bseo_og_img  = (string) get_post_meta( $post->ID, '_bseo_og_image', true );
			$bseo_noindex = get_post_meta( $post->ID, '_bseo_noindex', true ) === '1';

			$effective_title = $bseo_title !== '' ? $bseo_title : $post->post_title;
			$title_len       = mb_strlen( $effective_title );
			$desc_len        = mb_strlen( $bseo_desc );

			$checks = array(
				'title_length'       => ( $title_len >= 30 && $title_len <= 60 ),
				'description_set'    => ( $bseo_desc !== '' ),
				'description_length' => ( $desc_len >= 100 && $desc_len <= 160 ),
				'not_noindex'        => ! $bseo_noindex,
				'has_og_image'       => ( $bseo_og_img !== '' || has_post_thumbnail( $post->ID ) ),
			);

			$score = count( array_filter( $checks ) ) * 20;

			$results[] = array(
				'post_id'         => $post->ID,
				'title'           => $post->post_title,
				'url'             => get_permalink( $post->ID ),
				'edit_url'        => get_edit_post_link( $post->ID ),
				'seo_title'       => $bseo_title,
				'seo_description' => $bseo_desc,
				'score'           => $score,
				'checks'          => $checks,
				'noindex'         => $bseo_noindex,
				'title_len'       => $title_len,
				'desc_len'        => $desc_len,
			);
		}

		return $results;
	}

	// ── Missing description ───────────────────────────────────────────────────

	public function get_pages_missing_description() {
		return get_posts( array(
			'post_type'      => $this->post_types,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => array( 'post_type' => 'ASC', 'post_title' => 'ASC' ),
			'meta_query'     => array(
				'relation' => 'OR',
				array( 'key' => '_bseo_description', 'compare' => 'NOT EXISTS' ),
				array( 'key' => '_bseo_description', 'value' => '', 'compare' => '=' ),
			),
		) );
	}

	// ── Duplicate titles ──────────────────────────────────────────────────────

	public function get_duplicate_titles() {
		$posts  = get_posts( array(
			'post_type'      => $this->post_types,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		) );

		$groups = array();
		foreach ( $posts as $post ) {
			$bseo_title      = (string) get_post_meta( $post->ID, '_bseo_title', true );
			$effective_title = $bseo_title !== '' ? $bseo_title : $post->post_title;
			if ( $effective_title === '' ) continue;

			if ( ! isset( $groups[ $effective_title ] ) ) {
				$groups[ $effective_title ] = array( 'title' => $effective_title, 'count' => 0, 'posts' => array() );
			}
			$groups[ $effective_title ]['count']++;
			$groups[ $effective_title ]['posts'][] = $post;
		}

		return array_values( array_filter( $groups, fn( $g ) => $g['count'] > 1 ) );
	}

	// ── Images missing alt ────────────────────────────────────────────────────

	public function get_images_missing_alt() {
		$posts   = get_posts( array(
			'post_type'      => $this->post_types,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		) );
		$results = array();

		foreach ( $posts as $post ) {
			if ( empty( $post->post_content ) ) continue;

			if ( ! preg_match_all( '/<img\s[^>]*>/i', $post->post_content, $matches ) ) continue;

			foreach ( $matches[0] as $img_tag ) {
				$alt_missing = ( false === stripos( $img_tag, 'alt=' ) );
				$alt_empty   = (bool) preg_match( '/alt\s*=\s*(["\'])\s*\1/i', $img_tag );

				if ( ! $alt_missing && ! $alt_empty ) continue;

				$src = '';
				if ( preg_match( '/src\s*=\s*(["\'])([^"\']+)\1/i', $img_tag, $sm ) ) {
					$src = $sm[2];
				}

				$results[] = array(
					'post_id'   => $post->ID,
					'title'     => $post->post_title,
					'url'       => get_permalink( $post->ID ),
					'edit_url'  => get_edit_post_link( $post->ID ),
					'image_src' => $src,
				);
			}
		}

		return $results;
	}

	// ── Summary stats ─────────────────────────────────────────────────────────

	public function get_summary_stats() {
		$scores = $this->get_pages_seo_scores();
		$total  = count( $scores );

		$with_description = 0;
		$with_title       = 0;
		$with_og_image    = 0;
		$noindex_count    = 0;
		$score_sum        = 0;

		foreach ( $scores as $item ) {
			if ( $item['seo_description'] !== '' ) $with_description++;
			if ( $item['seo_title'] !== '' )       $with_title++;
			if ( $item['checks']['has_og_image'] )  $with_og_image++;
			if ( $item['noindex'] )                 $noindex_count++;
			$score_sum += $item['score'];
		}

		return array(
			'total_pages'      => $total,
			'with_description' => $with_description,
			'with_title'       => $with_title,
			'with_og_image'    => $with_og_image,
			'avg_score'        => $total > 0 ? round( $score_sum / $total, 1 ) : 0.0,
			'noindex_count'    => $noindex_count,
		);
	}

	// ── CSV export ────────────────────────────────────────────────────────────

	public function export_csv() {
		$scores   = $this->get_pages_seo_scores();
		$filename = 'breeze-seo-audit-' . gmdate( 'Y-m-d' ) . '.csv';

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $filename ) . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$out = fopen( 'php://output', 'w' );
		fputs( $out, "\xEF\xBB\xBF" ); // UTF-8 BOM
		fputcsv( $out, array( 'post_id', 'title', 'url', 'seo_title', 'seo_description', 'score', 'noindex' ) );

		foreach ( $scores as $item ) {
			fputcsv( $out, array(
				$item['post_id'],
				$item['title'],
				$item['url'],
				$item['seo_title'],
				$item['seo_description'],
				$item['score'],
				$item['noindex'] ? '1' : '0',
			) );
		}

		fclose( $out );
	}

	// ── AJAX ──────────────────────────────────────────────────────────────────

	public function ajax_export_csv() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'breeze-seo' ), 403 );
		}
		check_ajax_referer( 'bseo_export_csv_nonce', 'nonce' );
		$this->export_csv();
		exit;
	}
}
