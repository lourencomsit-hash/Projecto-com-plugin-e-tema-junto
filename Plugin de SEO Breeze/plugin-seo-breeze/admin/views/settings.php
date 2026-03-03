<?php
/**
 * Admin view: Settings
 *
 * @package BreezeSEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = get_option( 'breeze_seo_settings', array() );
$defaults = bseo_default_settings();
$s        = wp_parse_args( $settings, $defaults );

$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';
?>
<div class="wrap bseo-wrap">
	<h1 class="bseo-page-title">
		<span class="dashicons dashicons-admin-settings"></span>
		<?php esc_html_e( 'Breeze SEO — Settings', 'breeze-seo' ); ?>
	</h1>

	<?php settings_errors( 'breeze_seo_settings' ); ?>

	<nav class="nav-tab-wrapper bseo-settings-tabs">
		<a href="<?php echo esc_url( add_query_arg( 'tab', 'general', Breeze_SEO_Admin::page_url( 'bseo-settings' ) ) ); ?>"
			class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'General', 'breeze-seo' ); ?>
		</a>
		<a href="<?php echo esc_url( add_query_arg( 'tab', 'schema', Breeze_SEO_Admin::page_url( 'bseo-settings' ) ) ); ?>"
			class="nav-tab <?php echo $active_tab === 'schema' ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Schema / JSON-LD', 'breeze-seo' ); ?>
		</a>
		<a href="<?php echo esc_url( add_query_arg( 'tab', 'sitemap', Breeze_SEO_Admin::page_url( 'bseo-settings' ) ) ); ?>"
			class="nav-tab <?php echo $active_tab === 'sitemap' ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Sitemap', 'breeze-seo' ); ?>
		</a>
	</nav>

	<form method="post" action="options.php" class="bseo-settings-form">
		<?php settings_fields( 'breeze_seo_settings_group' ); ?>

		<?php if ( $active_tab === 'general' ) : ?>
		<!-- ── General ─────────────────────────────────────────────────────── -->
		<div class="bseo-card">
			<h2><?php esc_html_e( 'Title & Description', 'breeze-seo' ); ?></h2>
			<table class="form-table">
				<tr>
					<th><label for="title_format"><?php esc_html_e( 'Title Format', 'breeze-seo' ); ?></label></th>
					<td>
						<input type="text" id="title_format" name="breeze_seo_settings[title_format]"
							value="<?php echo esc_attr( $s['title_format'] ); ?>"
							class="regular-text">
						<p class="description"><?php esc_html_e( 'Use %title% as placeholder for the page title. Example: %title% | Breeze Safaris', 'breeze-seo' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="default_description"><?php esc_html_e( 'Default Meta Description', 'breeze-seo' ); ?></label></th>
					<td>
						<textarea id="default_description" name="breeze_seo_settings[default_description]"
							rows="3" class="large-text"><?php echo esc_textarea( $s['default_description'] ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Used when a page has no custom meta description set.', 'breeze-seo' ); ?></p>
					</td>
				</tr>
			</table>
		</div>

		<div class="bseo-card">
			<h2><?php esc_html_e( 'Default OG Image', 'breeze-seo' ); ?></h2>
			<table class="form-table">
				<tr>
					<th><label for="og_image_url"><?php esc_html_e( 'OG Image URL', 'breeze-seo' ); ?></label></th>
					<td>
						<div class="bseo-media-field">
							<input type="url" id="og_image_url" name="breeze_seo_settings[og_image_url]"
								value="<?php echo esc_url( $s['og_image_url'] ); ?>"
								class="large-text">
							<input type="hidden" id="og_image_id" name="breeze_seo_settings[og_image_id]"
								value="<?php echo esc_attr( $s['og_image_id'] ); ?>">
							<button type="button" class="button bseo-media-upload-btn"
								data-target="#og_image_url"
								data-target-id="#og_image_id">
								<?php esc_html_e( 'Choose Image', 'breeze-seo' ); ?>
							</button>
						</div>
						<p class="description"><?php esc_html_e( 'Recommended: 1200×800px. Used when a page has no custom OG image.', 'breeze-seo' ); ?></p>
						<?php if ( $s['og_image_url'] ) : ?>
							<img src="<?php echo esc_url( $s['og_image_url'] ); ?>"
								style="max-width:300px;margin-top:8px;border-radius:4px;" alt="">
						<?php endif; ?>
					</td>
				</tr>
			</table>
		</div>

		<div class="bseo-card">
			<h2><?php esc_html_e( 'Search Engine Verification', 'breeze-seo' ); ?></h2>
			<table class="form-table">
				<tr>
					<th><label for="google_verification"><?php esc_html_e( 'Google Verification Code', 'breeze-seo' ); ?></label></th>
					<td>
						<input type="text" id="google_verification" name="breeze_seo_settings[google_verification]"
							value="<?php echo esc_attr( $s['google_verification'] ); ?>"
							class="regular-text" placeholder="abc123...">
						<p class="description"><?php esc_html_e( 'The content value of the Google Search Console meta tag.', 'breeze-seo' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="bing_verification"><?php esc_html_e( 'Bing Verification Code', 'breeze-seo' ); ?></label></th>
					<td>
						<input type="text" id="bing_verification" name="breeze_seo_settings[bing_verification]"
							value="<?php echo esc_attr( $s['bing_verification'] ); ?>"
							class="regular-text" placeholder="abc123...">
					</td>
				</tr>
			</table>
		</div>

		<div class="bseo-card">
			<h2><?php esc_html_e( 'robots.txt', 'breeze-seo' ); ?></h2>
			<table class="form-table">
				<tr>
					<th><label for="robots_txt"><?php esc_html_e( 'robots.txt Content', 'breeze-seo' ); ?></label></th>
					<td>
						<textarea id="robots_txt" name="breeze_seo_settings[robots_txt]"
							rows="8" class="large-text code"><?php echo esc_textarea( $s['robots_txt'] ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Use %SITEMAP_URL% as a placeholder for the sitemap URL. This replaces the WordPress default robots.txt.', 'breeze-seo' ); ?></p>
					</td>
				</tr>
			</table>
		</div>

		<?php elseif ( $active_tab === 'schema' ) : ?>
		<!-- ── Schema ─────────────────────────────────────────────────────── -->
		<div class="bseo-card">
			<h2><?php esc_html_e( 'Business / Organization Data', 'breeze-seo' ); ?></h2>
			<p class="description" style="margin-bottom:16px;"><?php esc_html_e( 'Used in JSON-LD structured data (schema.org) output in the page <head>.', 'breeze-seo' ); ?></p>
			<table class="form-table">
				<tr>
					<th><label for="schema_business_name"><?php esc_html_e( 'Business Name', 'breeze-seo' ); ?></label></th>
					<td><input type="text" id="schema_business_name" name="breeze_seo_settings[schema_business_name]"
						value="<?php echo esc_attr( $s['schema_business_name'] ); ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th><label for="schema_email"><?php esc_html_e( 'Contact Email', 'breeze-seo' ); ?></label></th>
					<td><input type="email" id="schema_email" name="breeze_seo_settings[schema_email]"
						value="<?php echo esc_attr( $s['schema_email'] ); ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th><label for="schema_phone"><?php esc_html_e( 'Phone (+intl format)', 'breeze-seo' ); ?></label></th>
					<td><input type="text" id="schema_phone" name="breeze_seo_settings[schema_phone]"
						value="<?php echo esc_attr( $s['schema_phone'] ); ?>" class="regular-text"
						placeholder="+255 xxx xxx xxx"></td>
				</tr>
				<tr>
					<th><label for="schema_address"><?php esc_html_e( 'City / Region', 'breeze-seo' ); ?></label></th>
					<td><input type="text" id="schema_address" name="breeze_seo_settings[schema_address]"
						value="<?php echo esc_attr( $s['schema_address'] ); ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th><label for="schema_country"><?php esc_html_e( 'Country Code (ISO 3166)', 'breeze-seo' ); ?></label></th>
					<td><input type="text" id="schema_country" name="breeze_seo_settings[schema_country]"
						value="<?php echo esc_attr( $s['schema_country'] ); ?>" class="small-text"
						maxlength="2" placeholder="TZ"></td>
				</tr>
				<tr>
					<th><label for="schema_logo_url"><?php esc_html_e( 'Logo URL', 'breeze-seo' ); ?></label></th>
					<td>
						<div class="bseo-media-field">
							<input type="url" id="schema_logo_url" name="breeze_seo_settings[schema_logo_url]"
								value="<?php echo esc_url( $s['schema_logo_url'] ); ?>" class="large-text">
							<input type="hidden" id="schema_logo_id" name="breeze_seo_settings[schema_logo_id]"
								value="<?php echo esc_attr( $s['schema_logo_id'] ); ?>">
							<button type="button" class="button bseo-media-upload-btn"
								data-target="#schema_logo_url" data-target-id="#schema_logo_id">
								<?php esc_html_e( 'Choose Image', 'breeze-seo' ); ?>
							</button>
						</div>
					</td>
				</tr>
			</table>
		</div>

		<?php elseif ( $active_tab === 'sitemap' ) : ?>
		<!-- ── Sitemap ─────────────────────────────────────────────────────── -->
		<div class="bseo-card">
			<h2><?php esc_html_e( 'Sitemap Settings', 'breeze-seo' ); ?></h2>
			<table class="form-table">
				<tr>
					<th><?php esc_html_e( 'Include in Sitemap', 'breeze-seo' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="breeze_seo_settings[sitemap_include_pages]"
								value="1" <?php checked( $s['sitemap_include_pages'], '1' ); ?>>
							<?php esc_html_e( 'Pages', 'breeze-seo' ); ?>
						</label><br>
						<label>
							<input type="checkbox" name="breeze_seo_settings[sitemap_include_posts]"
								value="1" <?php checked( $s['sitemap_include_posts'], '1' ); ?>>
							<?php esc_html_e( 'Blog Posts', 'breeze-seo' ); ?>
						</label><br>
						<label>
							<input type="checkbox" name="breeze_seo_settings[sitemap_include_categories]"
								value="1" <?php checked( $s['sitemap_include_categories'], '1' ); ?>>
							<?php esc_html_e( 'Categories', 'breeze-seo' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Google Ping', 'breeze-seo' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="breeze_seo_settings[sitemap_ping_google]"
								value="1" <?php checked( $s['sitemap_ping_google'], '1' ); ?>>
							<?php esc_html_e( 'Automatically ping Google when content is updated (daily cron)', 'breeze-seo' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th><label for="sitemap_excluded_ids"><?php esc_html_e( 'Exclude Page/Post IDs', 'breeze-seo' ); ?></label></th>
					<td>
						<input type="text" id="sitemap_excluded_ids" name="breeze_seo_settings[sitemap_excluded_ids]"
							value="<?php echo esc_attr( $s['sitemap_excluded_ids'] ); ?>"
							class="regular-text" placeholder="12, 45, 78">
						<p class="description"><?php esc_html_e( 'Comma-separated list of post/page IDs to exclude from all sitemaps.', 'breeze-seo' ); ?></p>
					</td>
				</tr>
			</table>
		</div>
		<?php endif; ?>

		<?php submit_button( __( 'Save Settings', 'breeze-seo' ) ); ?>
	</form>
</div>
