<?php
/**
 * Admin view: Dashboard
 *
 * @package BreezeSEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$audit       = Breeze_SEO_Audit::get_instance();
$stats       = $audit->get_summary_stats();
$redirects   = Breeze_SEO_Redirects::get_instance();
$redir_count = $redirects->get_total_count();
$last_ping   = get_option( 'bseo_last_ping_time', '' );
$last_status = get_option( 'bseo_last_ping_status', '' );
$sitemap_url = home_url( '/sitemap.xml' );
?>
<div class="wrap bseo-wrap">
	<h1 class="bseo-page-title">
		<span class="dashicons dashicons-search"></span>
		Breeze SEO
		<span class="bseo-version">v<?php echo esc_html( BREEZE_SEO_VERSION ); ?></span>
	</h1>

	<div class="bseo-stats-grid">
		<div class="bseo-stat-card">
			<div class="bseo-stat-number"><?php echo esc_html( $stats['total_pages'] ); ?></div>
			<div class="bseo-stat-label"><?php esc_html_e( 'Total Pages / Posts', 'breeze-seo' ); ?></div>
		</div>
		<div class="bseo-stat-card bseo-stat-<?php echo $stats['with_description'] >= $stats['total_pages'] ? 'good' : 'warn'; ?>">
			<div class="bseo-stat-number"><?php echo esc_html( $stats['with_description'] ); ?> / <?php echo esc_html( $stats['total_pages'] ); ?></div>
			<div class="bseo-stat-label"><?php esc_html_e( 'With Meta Description', 'breeze-seo' ); ?></div>
		</div>
		<div class="bseo-stat-card bseo-stat-<?php echo $stats['with_title'] >= $stats['total_pages'] ? 'good' : 'warn'; ?>">
			<div class="bseo-stat-number"><?php echo esc_html( $stats['with_title'] ); ?> / <?php echo esc_html( $stats['total_pages'] ); ?></div>
			<div class="bseo-stat-label"><?php esc_html_e( 'With SEO Title', 'breeze-seo' ); ?></div>
		</div>
		<div class="bseo-stat-card">
			<div class="bseo-stat-number"><?php echo esc_html( number_format( $stats['avg_score'], 1 ) ); ?>/100</div>
			<div class="bseo-stat-label"><?php esc_html_e( 'Average SEO Score', 'breeze-seo' ); ?></div>
		</div>
		<div class="bseo-stat-card">
			<div class="bseo-stat-number"><?php echo esc_html( $redir_count ); ?></div>
			<div class="bseo-stat-label"><?php esc_html_e( 'Active Redirects', 'breeze-seo' ); ?></div>
		</div>
		<div class="bseo-stat-card">
			<div class="bseo-stat-number"><?php echo esc_html( $stats['noindex_count'] ); ?></div>
			<div class="bseo-stat-label"><?php esc_html_e( 'Noindex Pages', 'breeze-seo' ); ?></div>
		</div>
	</div>

	<div class="bseo-dashboard-cols">

		<div class="bseo-card">
			<h2><?php esc_html_e( 'Quick Links', 'breeze-seo' ); ?></h2>
			<ul class="bseo-quick-links">
				<li><a href="<?php echo esc_url( Breeze_SEO_Admin::page_url( 'bseo-settings' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Settings', 'breeze-seo' ); ?></a></li>
				<li><a href="<?php echo esc_url( Breeze_SEO_Admin::page_url( 'bseo-redirects' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Manage Redirects', 'breeze-seo' ); ?></a></li>
				<li><a href="<?php echo esc_url( Breeze_SEO_Admin::page_url( 'bseo-audit' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'SEO Audit', 'breeze-seo' ); ?></a></li>
				<li><a href="<?php echo esc_url( Breeze_SEO_Admin::page_url( 'bseo-import' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Import / Export', 'breeze-seo' ); ?></a></li>
			</ul>
		</div>

		<div class="bseo-card">
			<h2><?php esc_html_e( 'Sitemap', 'breeze-seo' ); ?></h2>
			<p>
				<a href="<?php echo esc_url( $sitemap_url ); ?>" target="_blank"><?php echo esc_html( $sitemap_url ); ?></a>
			</p>
			<p>
				<a href="<?php echo esc_url( home_url( '/sitemap-en.xml' ) ); ?>" target="_blank">sitemap-en.xml</a> &nbsp;|&nbsp;
				<a href="<?php echo esc_url( home_url( '/sitemap-pt.xml' ) ); ?>" target="_blank">sitemap-pt.xml</a>
			</p>
			<?php if ( $last_ping ) : ?>
				<p class="description">
					<?php
					/* translators: 1: date/time, 2: HTTP status code */
					printf(
						esc_html__( 'Last Google ping: %1$s (HTTP %2$s)', 'breeze-seo' ),
						esc_html( $last_ping ),
						'<strong>' . esc_html( $last_status ) . '</strong>'
					);
					?>
				</p>
			<?php endif; ?>
			<button type="button" id="bseo-ping-sitemap" class="button">
				<?php esc_html_e( 'Ping Google Now', 'breeze-seo' ); ?>
			</button>
			<span id="bseo-ping-result" class="bseo-ajax-result"></span>
		</div>

	</div><!-- .bseo-dashboard-cols -->
</div>
