<?php
/**
 * Admin view: Redirects
 *
 * @package BreezeSEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$redir   = Breeze_SEO_Redirects::get_instance();
$per_page = 25;
$page     = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
$search   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
$data     = $redir->get_redirects( $page, $per_page, $search );
$rows     = $data['rows'];
$total    = $data['total'];
$last_page = $data['last_page'];

// Crawl log (last 10)
$log = $redir->get_crawl_log( 1, 10 );
?>
<div class="wrap bseo-wrap">
	<h1 class="bseo-page-title">
		<span class="dashicons dashicons-randomize"></span>
		<?php esc_html_e( 'Redirects 301/302', 'breeze-seo' ); ?>
	</h1>

	<!-- ── Add / Edit form ──────────────────────────────────────────────────── -->
	<div class="bseo-card" id="bseo-redirect-form-card">
		<h2 id="bseo-redirect-form-title"><?php esc_html_e( 'Add New Redirect', 'breeze-seo' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><label for="redir-url-old"><?php esc_html_e( 'Old URL (from)', 'breeze-seo' ); ?></label></th>
				<td>
					<input type="text" id="redir-url-old" class="large-text" placeholder="/old-page/">
					<p class="description"><?php esc_html_e( 'Relative path, e.g. /old-page/ — leading slash is added automatically.', 'breeze-seo' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="redir-url-new"><?php esc_html_e( 'New URL (to)', 'breeze-seo' ); ?></label></th>
				<td>
					<input type="text" id="redir-url-new" class="large-text" placeholder="/new-page/ or https://...">
				</td>
			</tr>
			<tr>
				<th><label for="redir-type"><?php esc_html_e( 'Type', 'breeze-seo' ); ?></label></th>
				<td>
					<select id="redir-type">
						<option value="301">301 — Permanent</option>
						<option value="302">302 — Temporary</option>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="redir-notes"><?php esc_html_e( 'Notes', 'breeze-seo' ); ?></label></th>
				<td><input type="text" id="redir-notes" class="large-text" placeholder="<?php esc_attr_e( 'Optional description', 'breeze-seo' ); ?>"></td>
			</tr>
		</table>
		<input type="hidden" id="redir-edit-id" value="">
		<button type="button" id="bseo-save-redirect" class="button button-primary">
			<?php esc_html_e( 'Save Redirect', 'breeze-seo' ); ?>
		</button>
		<button type="button" id="bseo-cancel-edit" class="button" style="display:none;">
			<?php esc_html_e( 'Cancel Edit', 'breeze-seo' ); ?>
		</button>
		<span id="bseo-redirect-result" class="bseo-ajax-result"></span>
	</div>

	<!-- ── CSV Import / Export ──────────────────────────────────────────────── -->
	<div class="bseo-card">
		<h2><?php esc_html_e( 'CSV Import / Export', 'breeze-seo' ); ?></h2>
		<div class="bseo-inline-actions">
			<button type="button" id="bseo-export-redirects" class="button">
				<?php esc_html_e( 'Export All as CSV', 'breeze-seo' ); ?>
			</button>
			<button type="button" id="bseo-export-sample" class="button">
				<?php esc_html_e( 'Download Sample CSV', 'breeze-seo' ); ?>
			</button>
		</div>
		<div class="bseo-csv-import-form" style="margin-top:12px;">
			<label><strong><?php esc_html_e( 'Import from CSV:', 'breeze-seo' ); ?></strong></label><br>
			<input type="file" id="bseo-csv-file" accept=".csv" style="margin-top:6px;">
			<button type="button" id="bseo-import-csv" class="button" style="margin-left:8px;">
				<?php esc_html_e( 'Import', 'breeze-seo' ); ?>
			</button>
			<span id="bseo-csv-result" class="bseo-ajax-result"></span>
			<p class="description"><?php esc_html_e( 'Columns: url_antigo, url_novo (+ optional: tipo, notas). Duplicates are skipped.', 'breeze-seo' ); ?></p>
		</div>
	</div>

	<!-- ── Redirects table ──────────────────────────────────────────────────── -->
	<div class="bseo-card">
		<div class="bseo-table-header">
			<h2>
				<?php
				/* translators: %d: total redirect count */
				printf( esc_html__( 'All Redirects (%d)', 'breeze-seo' ), (int) $total );
				?>
			</h2>
			<form method="get" class="bseo-search-form">
				<input type="hidden" name="page" value="bseo-redirects">
				<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search URLs…', 'breeze-seo' ); ?>">
				<?php submit_button( __( 'Search', 'breeze-seo' ), 'secondary', '', false ); ?>
				<?php if ( $search ) : ?>
					<a href="<?php echo esc_url( Breeze_SEO_Admin::page_url( 'bseo-redirects' ) ); ?>" class="button"><?php esc_html_e( 'Clear', 'breeze-seo' ); ?></a>
				<?php endif; ?>
			</form>
		</div>

		<?php if ( empty( $rows ) ) : ?>
			<p><?php esc_html_e( 'No redirects found.', 'breeze-seo' ); ?></p>
		<?php else : ?>
			<div class="bseo-bulk-bar">
				<button type="button" id="bseo-select-all" class="button"><?php esc_html_e( 'Select All', 'breeze-seo' ); ?></button>
				<button type="button" id="bseo-bulk-delete" class="button button-link-delete"><?php esc_html_e( 'Delete Selected', 'breeze-seo' ); ?></button>
				<span id="bseo-bulk-result" class="bseo-ajax-result"></span>
			</div>
			<table class="wp-list-table widefat fixed striped bseo-redirects-table">
				<thead>
					<tr>
						<th class="check-column"><input type="checkbox" id="bseo-check-all"></th>
						<th><?php esc_html_e( 'Old URL', 'breeze-seo' ); ?></th>
						<th><?php esc_html_e( 'New URL', 'breeze-seo' ); ?></th>
						<th width="60"><?php esc_html_e( 'Type', 'breeze-seo' ); ?></th>
						<th width="60"><?php esc_html_e( 'Hits', 'breeze-seo' ); ?></th>
						<th><?php esc_html_e( 'Notes', 'breeze-seo' ); ?></th>
						<th width="120"><?php esc_html_e( 'Actions', 'breeze-seo' ); ?></th>
					</tr>
				</thead>
				<tbody id="bseo-redirects-tbody">
				<?php foreach ( $rows as $row ) : ?>
					<tr id="redir-row-<?php echo esc_attr( $row->id ); ?>"
						data-id="<?php echo esc_attr( $row->id ); ?>"
						data-old="<?php echo esc_attr( $row->url_old ); ?>"
						data-new="<?php echo esc_attr( $row->url_new ); ?>"
						data-type="<?php echo esc_attr( $row->redirect_type ); ?>"
						data-notes="<?php echo esc_attr( $row->notes ); ?>">
						<td class="check-column"><input type="checkbox" class="bseo-row-check" value="<?php echo esc_attr( $row->id ); ?>"></td>
						<td><code><?php echo esc_html( $row->url_old ); ?></code></td>
						<td><code><?php echo esc_html( $row->url_new ); ?></code></td>
						<td><span class="bseo-badge bseo-badge-<?php echo esc_attr( $row->redirect_type ); ?>"><?php echo esc_html( $row->redirect_type ); ?></span></td>
						<td><?php echo esc_html( number_format( (int) $row->hits ) ); ?></td>
						<td><?php echo esc_html( $row->notes ); ?></td>
						<td class="bseo-row-actions">
							<button type="button" class="button button-small bseo-edit-redirect">
								<?php esc_html_e( 'Edit', 'breeze-seo' ); ?>
							</button>
							<button type="button" class="button button-small button-link-delete bseo-delete-redirect">
								<?php esc_html_e( 'Delete', 'breeze-seo' ); ?>
							</button>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<!-- Pagination -->
			<?php if ( $last_page > 1 ) : ?>
			<div class="tablenav bottom">
				<div class="tablenav-pages">
					<?php
					$base_url = Breeze_SEO_Admin::page_url( 'bseo-redirects' );
					if ( $search ) $base_url = add_query_arg( 's', urlencode( $search ), $base_url );
					if ( $page > 1 ) :
					?>
						<a href="<?php echo esc_url( add_query_arg( 'paged', $page - 1, $base_url ) ); ?>" class="button">&laquo; <?php esc_html_e( 'Previous', 'breeze-seo' ); ?></a>
					<?php endif; ?>
					<span class="bseo-page-info">
						<?php
						/* translators: 1: current page, 2: last page */
						printf( esc_html__( 'Page %1$d of %2$d', 'breeze-seo' ), (int) $page, (int) $last_page );
						?>
					</span>
					<?php if ( $page < $last_page ) : ?>
						<a href="<?php echo esc_url( add_query_arg( 'paged', $page + 1, $base_url ) ); ?>" class="button"><?php esc_html_e( 'Next', 'breeze-seo' ); ?> &raquo;</a>
					<?php endif; ?>
				</div>
			</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>

	<!-- ── Crawl log ────────────────────────────────────────────────────────── -->
	<div class="bseo-card">
		<div class="bseo-table-header">
			<h2><?php esc_html_e( '404 Crawl Log (last 10)', 'breeze-seo' ); ?></h2>
			<button type="button" id="bseo-clear-crawl-log" class="button button-link-delete">
				<?php esc_html_e( 'Clear Log', 'breeze-seo' ); ?>
			</button>
			<span id="bseo-clear-log-result" class="bseo-ajax-result"></span>
		</div>
		<?php if ( empty( $log['rows'] ) ) : ?>
			<p><?php esc_html_e( 'No 404 errors logged yet.', 'breeze-seo' ); ?></p>
		<?php else : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'URL', 'breeze-seo' ); ?></th>
						<th><?php esc_html_e( 'Referrer', 'breeze-seo' ); ?></th>
						<th><?php esc_html_e( 'Date', 'breeze-seo' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $log['rows'] as $entry ) : ?>
					<tr>
						<td><code><?php echo esc_html( $entry->url_requested ); ?></code></td>
						<td><?php echo esc_html( $entry->referrer ); ?></td>
						<td><?php echo esc_html( $entry->created_at ); ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p class="description">
				<?php
				/* translators: %d: total log entries */
				printf( esc_html__( 'Total log entries: %d (max 5000)', 'breeze-seo' ), (int) $log['total'] );
				?>
			</p>
		<?php endif; ?>
	</div>
</div>
