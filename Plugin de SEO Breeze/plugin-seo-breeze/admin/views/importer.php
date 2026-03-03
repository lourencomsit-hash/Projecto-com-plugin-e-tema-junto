<?php
/**
 * Admin view: Import / Export
 *
 * @package BreezeSEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap bseo-wrap">
	<h1 class="bseo-page-title">
		<span class="dashicons dashicons-upload"></span>
		<?php esc_html_e( 'Import / Export', 'breeze-seo' ); ?>
	</h1>

	<!-- ── Import from theme ────────────────────────────────────────────────── -->
	<div class="bseo-card">
		<h2><?php esc_html_e( 'Import from Previous Theme (breeze-claude-theme)', 'breeze-seo' ); ?></h2>
		<p><?php esc_html_e( 'Imports hardcoded SEO data (titles, descriptions, noindex) from the old theme into post meta. Also imports the redirect map. Existing custom data is not overwritten.', 'breeze-seo' ); ?></p>
		<button type="button" id="bseo-import-theme" class="button button-primary">
			<?php esc_html_e( 'Import Theme SEO Data + Redirects', 'breeze-seo' ); ?>
		</button>
		<span id="bseo-import-theme-result" class="bseo-ajax-result"></span>
	</div>

	<!-- ── Force-update titles from theme data ──────────────────────────────── -->
	<div class="bseo-card">
		<h2><?php esc_html_e( 'Force Update SEO Titles', 'breeze-seo' ); ?></h2>
		<p><?php esc_html_e( 'Overwrites the SEO title for every page listed in the plugin\'s theme data, even if a title was already set. Use this after editing the hardcoded titles to push the corrected values to the database.', 'breeze-seo' ); ?></p>
		<button type="button" id="bseo-force-update-titles" class="button button-secondary">
			<?php esc_html_e( 'Force Update Titles from Theme Data', 'breeze-seo' ); ?>
		</button>
		<span id="bseo-force-titles-result" class="bseo-ajax-result"></span>
	</div>

	<!-- ── Import from Yoast ────────────────────────────────────────────────── -->
	<div class="bseo-card">
		<h2><?php esc_html_e( 'Import from Yoast SEO', 'breeze-seo' ); ?></h2>
		<p><?php esc_html_e( 'Reads _yoast_wpseo_title, _yoast_wpseo_metadesc and _yoast_wpseo_canonical for all posts/pages that do not already have Breeze SEO data. Only runs on posts with Yoast data.', 'breeze-seo' ); ?></p>
		<button type="button" id="bseo-import-yoast" class="button button-primary">
			<?php esc_html_e( 'Import from Yoast', 'breeze-seo' ); ?>
		</button>
		<span id="bseo-import-yoast-result" class="bseo-ajax-result"></span>
	</div>

	<!-- ── Import redirects CSV ─────────────────────────────────────────────── -->
	<div class="bseo-card">
		<h2><?php esc_html_e( 'Import Redirects from CSV', 'breeze-seo' ); ?></h2>
		<p><?php esc_html_e( 'Upload a CSV with columns: url_antigo, url_novo (+ optional: tipo, notas). Duplicates are skipped.', 'breeze-seo' ); ?></p>
		<div class="bseo-inline-actions">
			<input type="file" id="bseo-import-redir-csv" accept=".csv">
			<button type="button" id="bseo-import-redir-csv-btn" class="button button-primary">
				<?php esc_html_e( 'Import', 'breeze-seo' ); ?>
			</button>
			<button type="button" id="bseo-export-sample-importer" class="button">
				<?php esc_html_e( 'Download Sample CSV', 'breeze-seo' ); ?>
			</button>
		</div>
		<span id="bseo-import-redir-result" class="bseo-ajax-result"></span>
	</div>

	<!-- ── Bulk set default OG image ────────────────────────────────────────── -->
	<div class="bseo-card">
		<h2><?php esc_html_e( 'Set Default OG Image for All Pages', 'breeze-seo' ); ?></h2>
		<?php $default_og = bseo_get_setting( 'og_image_url' ); ?>
		<?php if ( $default_og ) : ?>
			<p><?php esc_html_e( 'Sets the default OG image (from Settings → General) as the OG image for all pages/posts that do not already have one set.', 'breeze-seo' ); ?><br>
			<strong><?php esc_html_e( 'Current default:', 'breeze-seo' ); ?></strong> <a href="<?php echo esc_url( $default_og ); ?>" target="_blank"><?php echo esc_html( $default_og ); ?></a></p>
			<button type="button" id="bseo-bulk-set-og" class="button button-primary">
				<?php esc_html_e( 'Apply Default OG Image to All Pages', 'breeze-seo' ); ?>
			</button>
		<?php else : ?>
			<p><strong style="color:#d63638"><?php esc_html_e( 'No default OG image configured. Go to Settings → General and set a Default OG Image first.', 'breeze-seo' ); ?></strong></p>
			<a href="<?php echo esc_url( Breeze_SEO_Admin::page_url( 'bseo-settings' ) ); ?>" class="button"><?php esc_html_e( 'Go to Settings', 'breeze-seo' ); ?></a>
		<?php endif; ?>
		<span id="bseo-bulk-og-result" class="bseo-ajax-result"></span>
	</div>

	<!-- ── Export ────────────────────────────────────────────────────────────── -->
	<div class="bseo-card">
		<h2><?php esc_html_e( 'Export', 'breeze-seo' ); ?></h2>
		<ul class="bseo-quick-links">
			<li>
				<button type="button" id="bseo-export-redirects-importer" class="button">
					<?php esc_html_e( 'Export All Redirects as CSV', 'breeze-seo' ); ?>
				</button>
			</li>
			<li>
				<button type="button" id="bseo-export-audit-importer" class="button">
					<?php esc_html_e( 'Export SEO Audit as CSV', 'breeze-seo' ); ?>
				</button>
			</li>
		</ul>
	</div>
</div>
