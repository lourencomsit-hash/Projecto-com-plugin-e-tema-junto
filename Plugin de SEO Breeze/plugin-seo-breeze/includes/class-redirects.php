<?php
/**
 * Redirects 301/302 — DB-backed redirect manager.
 *
 * @package BreezeSEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Breeze_SEO_Redirects {

	private static $instance = null;

	/** @var string */
	private $table;

	/** @var string */
	private $log_table;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		global $wpdb;
		$this->table     = $wpdb->prefix . 'breeze_redirects';
		$this->log_table = $wpdb->prefix . 'breeze_crawl_log';
	}

	public function init() {
		add_action( 'template_redirect', array( $this, 'handle_redirect' ), 1 );
	}

	// ── Intercept requests ────────────────────────────────────────────────────

	public function handle_redirect() {
		if ( is_admin() ) {
			return;
		}

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
		$path        = parse_url( $request_uri, PHP_URL_PATH );

		if ( empty( $path ) ) {
			return;
		}

		// Normalise: ensure trailing slash for lookup
		$path_with_slash    = rtrim( $path, '/' ) . '/';
		$path_without_slash = rtrim( $path, '/' );

		// Check with trailing slash first, then without
		$redirect = $this->find_redirect( $path_with_slash );
		if ( ! $redirect ) {
			$redirect = $this->find_redirect( $path_without_slash );
		}
		if ( ! $redirect ) {
			// Also try exact match as stored (may have no trailing slash)
			$redirect = $this->find_redirect( $path );
		}

		if ( $redirect ) {
			$target_url = $this->build_target_url( $redirect->url_new );
			// Avoid self-redirect loops (e.g. /pt → /pt/)
			$target_path = parse_url( $target_url, PHP_URL_PATH );
			if ( $target_path === $path || $target_path === $path_with_slash || $target_path === $path_without_slash ) {
				return;
			}
			$this->increment_hits( (int) $redirect->id );
			wp_redirect( $target_url, (int) $redirect->redirect_type );
			exit;
		}

		// Log 404s to crawl log (keep log lean: only if referrer exists)
		if ( is_404() && ! empty( $_SERVER['HTTP_REFERER'] ) ) {
			$this->log_404( $path );
		}
	}

	/**
	 * Find a redirect by its old URL path.
	 */
	private function find_redirect( $path ) {
		global $wpdb;
		$path = '/' . ltrim( $path, '/' );
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->table} WHERE url_old = %s LIMIT 1",
				$path
			)
		);
	}

	/**
	 * Build the final target URL from the stored new URL.
	 * If url_new is relative (starts with /), prepend home_url.
	 */
	private function build_target_url( $url_new ) {
		if ( strpos( $url_new, 'http' ) === 0 ) {
			return $url_new;
		}
		return home_url( '/' . ltrim( $url_new, '/' ) );
	}

	/**
	 * Increment hit counter for a redirect.
	 */
	private function increment_hits( $id ) {
		global $wpdb;
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$this->table} SET hits = hits + 1 WHERE id = %d",
				$id
			)
		);
	}

	/**
	 * Log a 404 request to the crawl log.
	 */
	private function log_404( $url ) {
		global $wpdb;
		$referrer   = isset( $_SERVER['HTTP_REFERER'] )   ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) )   : '';
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

		$wpdb->insert(
			$this->log_table,
			array(
				'url_requested' => sanitize_text_field( $url ),
				'referrer'      => $referrer,
				'user_agent'    => mb_substr( $user_agent, 0, 512 ),
				'created_at'    => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s' )
		);

		// Prune log: keep max 5000 entries
		$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->log_table}" );
		if ( $count > 5000 ) {
			$wpdb->query( "DELETE FROM {$this->log_table} ORDER BY id ASC LIMIT 500" );
		}
	}

	// ── CRUD ──────────────────────────────────────────────────────────────────

	/**
	 * Add a redirect.
	 */
	public function add_redirect( $url_old, $url_new, $type = 301, $notes = '' ) {
		global $wpdb;

		$url_old = '/' . ltrim( sanitize_text_field( $url_old ), '/' );
		$url_new = sanitize_text_field( $url_new );
		$type    = in_array( (int) $type, array( 301, 302 ), true ) ? (int) $type : 301;
		$notes   = sanitize_textarea_field( $notes );

		// Check for duplicate
		$existing = $wpdb->get_var(
			$wpdb->prepare( "SELECT id FROM {$this->table} WHERE url_old = %s LIMIT 1", $url_old )
		);
		if ( $existing ) {
			return new WP_Error( 'duplicate', __( 'A redirect for this URL already exists.', 'breeze-seo' ) );
		}

		$result = $wpdb->insert(
			$this->table,
			array(
				'url_old'       => $url_old,
				'url_new'       => $url_new,
				'redirect_type' => $type,
				'notes'         => $notes,
				'created_at'    => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%d', '%s', '%s' )
		);

		if ( false === $result ) {
			return new WP_Error( 'db_error', $wpdb->last_error );
		}

		return $wpdb->insert_id;
	}

	/**
	 * Update a redirect.
	 */
	public function update_redirect( $id, $url_old, $url_new, $type = 301, $notes = '' ) {
		global $wpdb;

		$id      = absint( $id );
		$url_old = '/' . ltrim( sanitize_text_field( $url_old ), '/' );
		$url_new = sanitize_text_field( $url_new );
		$type    = in_array( (int) $type, array( 301, 302 ), true ) ? (int) $type : 301;
		$notes   = sanitize_textarea_field( $notes );

		return $wpdb->update(
			$this->table,
			array(
				'url_old'       => $url_old,
				'url_new'       => $url_new,
				'redirect_type' => $type,
				'notes'         => $notes,
			),
			array( 'id' => $id ),
			array( '%s', '%s', '%d', '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Delete a redirect by ID.
	 */
	public function delete_redirect( $id ) {
		global $wpdb;
		return $wpdb->delete(
			$this->table,
			array( 'id' => absint( $id ) ),
			array( '%d' )
		);
	}

	/**
	 * Delete multiple redirects by IDs.
	 */
	public function bulk_delete( array $ids ) {
		global $wpdb;
		$ids = array_map( 'absint', $ids );
		if ( empty( $ids ) ) {
			return 0;
		}
		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		return $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$this->table} WHERE id IN ($placeholders)",
				...$ids
			)
		);
	}

	/**
	 * Get all redirects with optional pagination.
	 */
	public function get_redirects( $page = 1, $per_page = 20, $search = '' ) {
		global $wpdb;
		$offset = ( max( 1, (int) $page ) - 1 ) * (int) $per_page;

		if ( $search ) {
			$like = '%' . $wpdb->esc_like( $search ) . '%';
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$this->table} WHERE url_old LIKE %s OR url_new LIKE %s ORDER BY id DESC LIMIT %d OFFSET %d",
					$like, $like, $per_page, $offset
				)
			);
			$total = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$this->table} WHERE url_old LIKE %s OR url_new LIKE %s",
					$like, $like
				)
			);
		} else {
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$this->table} ORDER BY id DESC LIMIT %d OFFSET %d",
					$per_page, $offset
				)
			);
			$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table}" );
		}

		return array(
			'rows'       => $rows ?? array(),
			'total'      => $total,
			'per_page'   => $per_page,
			'page'       => $page,
			'last_page'  => (int) ceil( $total / $per_page ),
		);
	}

	/**
	 * Get crawl log entries.
	 */
	public function get_crawl_log( $page = 1, $per_page = 50 ) {
		global $wpdb;
		$offset = ( max( 1, (int) $page ) - 1 ) * (int) $per_page;
		$rows   = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$this->log_table} ORDER BY id DESC LIMIT %d OFFSET %d",
				$per_page, $offset
			)
		);
		$total  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->log_table}" );

		return array(
			'rows'    => $rows ?? array(),
			'total'   => $total,
		);
	}

	/**
	 * Clear the crawl log.
	 */
	public function clear_crawl_log() {
		global $wpdb;
		return $wpdb->query( "TRUNCATE TABLE {$this->log_table}" );
	}

	// ── Import / Export ───────────────────────────────────────────────────────

	/**
	 * Import redirects from a CSV file.
	 * Expected columns: url_antigo, url_novo (+ optionally: tipo, notas)
	 */
	public function import_from_csv( $file_path ) {
		if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
			return new WP_Error( 'file_error', __( 'File not found or not readable.', 'breeze-seo' ) );
		}

		$handle = fopen( $file_path, 'r' );
		if ( ! $handle ) {
			return new WP_Error( 'file_error', __( 'Could not open file.', 'breeze-seo' ) );
		}

		$imported = 0;
		$skipped  = 0;
		$errors   = array();
		$header   = null;
		$row_num  = 0;

		while ( ( $row = fgetcsv( $handle, 4096, ',' ) ) !== false ) {
			$row_num++;

			// Detect delimiter from first line
			if ( $row_num === 1 && count( $row ) === 1 ) {
				rewind( $handle );
				$row_num = 0;
				// Try semicolon
				while ( ( $row = fgetcsv( $handle, 4096, ';' ) ) !== false ) {
					$row_num++;
					if ( $row_num === 1 ) {
						$header = array_map( 'trim', $row );
						continue;
					}
					$result = $this->process_csv_row( $header, $row );
					if ( is_wp_error( $result ) ) {
						$errors[] = "Row $row_num: " . $result->get_error_message();
						$skipped++;
					} elseif ( $result === 0 ) {
						$skipped++;
					} else {
						$imported++;
					}
				}
				fclose( $handle );
				return array( 'imported' => $imported, 'skipped' => $skipped, 'errors' => $errors );
			}

			if ( $row_num === 1 ) {
				$header = array_map( 'trim', $row );
				continue;
			}

			$result = $this->process_csv_row( $header, $row );
			if ( is_wp_error( $result ) ) {
				$errors[] = "Row $row_num: " . $result->get_error_message();
				$skipped++;
			} elseif ( $result === 0 ) {
				$skipped++;
			} else {
				$imported++;
			}
		}

		fclose( $handle );
		return array( 'imported' => $imported, 'skipped' => $skipped, 'errors' => $errors );
	}

	private function process_csv_row( $header, $row ) {
		$data = array();
		foreach ( $header as $i => $col ) {
			$data[ strtolower( trim( $col ) ) ] = isset( $row[ $i ] ) ? trim( $row[ $i ] ) : '';
		}

		$url_old = $data['url_antigo'] ?? $data['old_url'] ?? $data['from'] ?? $data['source'] ?? '';
		$url_new = $data['url_novo']   ?? $data['new_url'] ?? $data['to']   ?? $data['target'] ?? '';

		if ( empty( $url_old ) || empty( $url_new ) ) {
			return 0; // skip empty rows
		}

		$type  = isset( $data['tipo'] ) ? (int) $data['tipo'] : 301;
		$notes = $data['notas'] ?? $data['notes'] ?? '';

		return $this->add_redirect( $url_old, $url_new, $type, $notes );
	}

	/**
	 * Export all redirects as CSV download.
	 */
	public function export_csv() {
		global $wpdb;
		$rows = $wpdb->get_results( "SELECT * FROM {$this->table} ORDER BY id ASC", ARRAY_A );

		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="breeze-seo-redirects-' . date( 'Y-m-d' ) . '.csv"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$out = fopen( 'php://output', 'w' );
		fprintf( $out, chr(0xEF) . chr(0xBB) . chr(0xBF) ); // UTF-8 BOM
		fputcsv( $out, array( 'id', 'url_antigo', 'url_novo', 'tipo', 'hits', 'notas', 'criado_em' ) );

		foreach ( $rows as $row ) {
			fputcsv( $out, array(
				$row['id'],
				$row['url_old'],
				$row['url_new'],
				$row['redirect_type'],
				$row['hits'],
				$row['notes'],
				$row['created_at'],
			) );
		}

		fclose( $out );
		exit;
	}

	/**
	 * Export sample CSV template.
	 */
	public function export_sample_csv() {
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="redirects-sample.csv"' );

		$out = fopen( 'php://output', 'w' );
		fprintf( $out, chr(0xEF) . chr(0xBB) . chr(0xBF) );
		fputcsv( $out, array( 'url_antigo', 'url_novo', 'tipo', 'notas' ) );
		fputcsv( $out, array( '/old-page/', '/new-page/', '301', 'Migração de URL' ) );
		fputcsv( $out, array( '/project/serengeti/', '/serengeti-national-park/', '301', '' ) );
		fclose( $out );
		exit;
	}

	// ── Getters ───────────────────────────────────────────────────────────────

	public function get_table_name() {
		return $this->table;
	}

	public function get_total_count() {
		global $wpdb;
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table}" );
	}

	public function get_top_redirects( $limit = 10 ) {
		global $wpdb;
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$this->table} ORDER BY hits DESC LIMIT %d",
				$limit
			)
		);
	}
}
