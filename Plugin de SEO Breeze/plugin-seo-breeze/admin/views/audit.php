<?php
/**
 * Admin view: SEO Audit
 *
 * @package BreezeSEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$audit  = Breeze_SEO_Audit::get_instance();
$scores = $audit->get_pages_seo_scores();
$stats  = $audit->get_summary_stats();
$dupes  = $audit->get_duplicate_titles();

// Filter
$filter = isset( $_GET['filter'] ) ? sanitize_key( $_GET['filter'] ) : 'all';

if ( 'missing_desc' === $filter ) {
	$scores = array_filter( $scores, fn( $s ) => ! $s['checks']['description_set'] );
} elseif ( 'noindex' === $filter ) {
	$scores = array_filter( $scores, fn( $s ) => $s['noindex'] );
} elseif ( 'low_score' === $filter ) {
	$scores = array_filter( $scores, fn( $s ) => $s['score'] < 60 );
}
?>
<div class="wrap bseo-wrap">
	<h1 class="bseo-page-title">
		<span class="dashicons dashicons-visibility"></span>
		<?php esc_html_e( 'SEO Audit', 'breeze-seo' ); ?>
	</h1>

	<!-- Stats summary -->
	<div class="bseo-stats-grid bseo-stats-compact">
		<div class="bseo-stat-card">
			<div class="bseo-stat-number"><?php echo esc_html( $stats['total_pages'] ); ?></div>
			<div class="bseo-stat-label"><?php esc_html_e( 'Total', 'breeze-seo' ); ?></div>
		</div>
		<div class="bseo-stat-card">
			<div class="bseo-stat-number"><?php echo esc_html( $stats['with_title'] ); ?></div>
			<div class="bseo-stat-label"><?php esc_html_e( 'With Title', 'breeze-seo' ); ?></div>
		</div>
		<div class="bseo-stat-card">
			<div class="bseo-stat-number"><?php echo esc_html( $stats['with_description'] ); ?></div>
			<div class="bseo-stat-label"><?php esc_html_e( 'With Description', 'breeze-seo' ); ?></div>
		</div>
		<div class="bseo-stat-card">
			<div class="bseo-stat-number"><?php echo esc_html( $stats['with_og_image'] ); ?></div>
			<div class="bseo-stat-label"><?php esc_html_e( 'With OG Image', 'breeze-seo' ); ?></div>
		</div>
		<div class="bseo-stat-card">
			<div class="bseo-stat-number"><?php echo esc_html( number_format( $stats['avg_score'], 1 ) ); ?>/100</div>
			<div class="bseo-stat-label"><?php esc_html_e( 'Avg Score', 'breeze-seo' ); ?></div>
		</div>
		<div class="bseo-stat-card">
			<div class="bseo-stat-number"><?php echo esc_html( count( $dupes ) ); ?></div>
			<div class="bseo-stat-label"><?php esc_html_e( 'Duplicate Titles', 'breeze-seo' ); ?></div>
		</div>
	</div>

	<!-- Duplicate titles warning -->
	<?php if ( ! empty( $dupes ) ) : ?>
	<div class="notice notice-warning inline" style="margin:0 0 16px;padding:10px 12px;">
		<strong><?php esc_html_e( 'Duplicate SEO titles detected:', 'breeze-seo' ); ?></strong>
		<ul style="margin:6px 0 0 16px;list-style:disc;">
		<?php foreach ( $dupes as $dup ) : ?>
			<li>
				"<?php echo esc_html( $dup['title'] ); ?>" &mdash;
				<?php
				$post_links = array_map(
					fn( $p ) => '<a href="' . esc_url( get_edit_post_link( $p->ID ) ) . '">' . esc_html( get_the_title( $p->ID ) ) . '</a>',
					$dup['posts']
				);
				echo implode( ', ', $post_links ); // phpcs:ignore
				?>
			</li>
		<?php endforeach; ?>
		</ul>
	</div>
	<?php endif; ?>

	<!-- Filter tabs -->
	<div class="bseo-card">
		<div class="bseo-table-header">
			<h2><?php esc_html_e( 'Pages & Posts', 'breeze-seo' ); ?></h2>
			<div class="bseo-filter-tabs">
				<a href="<?php echo esc_url( add_query_arg( 'filter', 'all', Breeze_SEO_Admin::page_url( 'bseo-audit' ) ) ); ?>"
					class="button <?php echo $filter === 'all' ? 'button-primary' : ''; ?>">
					<?php esc_html_e( 'All', 'breeze-seo' ); ?>
				</a>
				<a href="<?php echo esc_url( add_query_arg( 'filter', 'low_score', Breeze_SEO_Admin::page_url( 'bseo-audit' ) ) ); ?>"
					class="button <?php echo $filter === 'low_score' ? 'button-primary' : ''; ?>">
					<?php esc_html_e( 'Low Score (<60)', 'breeze-seo' ); ?>
				</a>
				<a href="<?php echo esc_url( add_query_arg( 'filter', 'missing_desc', Breeze_SEO_Admin::page_url( 'bseo-audit' ) ) ); ?>"
					class="button <?php echo $filter === 'missing_desc' ? 'button-primary' : ''; ?>">
					<?php esc_html_e( 'Missing Description', 'breeze-seo' ); ?>
				</a>
				<a href="<?php echo esc_url( add_query_arg( 'filter', 'noindex', Breeze_SEO_Admin::page_url( 'bseo-audit' ) ) ); ?>"
					class="button <?php echo $filter === 'noindex' ? 'button-primary' : ''; ?>">
					<?php esc_html_e( 'Noindex', 'breeze-seo' ); ?>
				</a>
			</div>
			<button type="button" id="bseo-export-audit-csv" class="button">
				<?php esc_html_e( 'Export CSV', 'breeze-seo' ); ?>
			</button>
		</div>

		<table class="wp-list-table widefat fixed striped bseo-audit-table">
			<thead>
				<tr>
					<th width="30%"><?php esc_html_e( 'Page / Post', 'breeze-seo' ); ?></th>
					<th width="15%"><?php esc_html_e( 'Score', 'breeze-seo' ); ?></th>
					<th width="12%"><?php esc_html_e( 'Title', 'breeze-seo' ); ?></th>
					<th width="12%"><?php esc_html_e( 'Description', 'breeze-seo' ); ?></th>
					<th width="12%"><?php esc_html_e( 'OG Image', 'breeze-seo' ); ?></th>
					<th width="19%"><?php esc_html_e( 'Status', 'breeze-seo' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php if ( empty( $scores ) ) : ?>
				<tr><td colspan="6"><?php esc_html_e( 'No results.', 'breeze-seo' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $scores as $item ) : ?>
				<?php
				$score_class = $item['score'] >= 80 ? 'high' : ( $item['score'] >= 50 ? 'medium' : 'low' );
				?>
				<tr>
					<td>
						<strong><a href="<?php echo esc_url( $item['edit_url'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a></strong><br>
						<a href="<?php echo esc_url( $item['url'] ); ?>" target="_blank" class="bseo-tiny-url">
							<?php echo esc_html( wp_parse_url( $item['url'], PHP_URL_PATH ) ); ?>
						</a>
					</td>
					<td>
						<span class="bseo-score-badge bseo-score-<?php echo esc_attr( $score_class ); ?>">
							<?php echo esc_html( $item['score'] ); ?>/100
						</span>
					</td>
					<td>
						<?php if ( $item['checks']['title_length'] ) : ?>
							<span class="bseo-check-ok">&#10003;</span>
						<?php elseif ( $item['seo_title'] !== '' ) : ?>
							<span class="bseo-check-warn" title="<?php echo esc_attr( $item['title_len'] ); ?> chars">&#9888;</span>
						<?php else : ?>
							<span class="bseo-check-bad">&#8212;</span>
						<?php endif; ?>
					</td>
					<td>
						<?php if ( $item['checks']['description_length'] ) : ?>
							<span class="bseo-check-ok">&#10003;</span>
						<?php elseif ( $item['checks']['description_set'] ) : ?>
							<span class="bseo-check-warn" title="<?php echo esc_attr( $item['desc_len'] ); ?> chars">&#9888;</span>
						<?php else : ?>
							<span class="bseo-check-bad">&#8212;</span>
						<?php endif; ?>
					</td>
					<td>
						<?php if ( $item['checks']['has_og_image'] ) : ?>
							<span class="bseo-check-ok">&#10003;</span>
						<?php else : ?>
							<span class="bseo-check-bad">&#8212;</span>
						<?php endif; ?>
					</td>
					<td>
						<?php if ( $item['noindex'] ) : ?>
							<span class="bseo-badge bseo-badge-noindex">NOINDEX</span>
						<?php else : ?>
							<span class="bseo-badge bseo-badge-index">index</span>
						<?php endif; ?>
						<a href="<?php echo esc_url( $item['edit_url'] ); ?>" class="button button-small">
							<?php esc_html_e( 'Edit', 'breeze-seo' ); ?>
						</a>
					</td>
				</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>
	</div>

	<p class="description">
		<?php esc_html_e( 'Score breakdown: Title (25pts) + Description (25pts) + OG image (25pts) + H1/content (25pts).', 'breeze-seo' ); ?>
	</p>
</div>
