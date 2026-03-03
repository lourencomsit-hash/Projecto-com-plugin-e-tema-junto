<?php
/**
 * Meta Tags — SEO title, description, canonical, robots, Open Graph, Twitter Cards.
 * Also provides meta box in post/page editor.
 *
 * @package BreezeSEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Breeze_SEO_Meta_Tags {

	private static $instance = null;

	/** Per-request cache of resolved SEO data for the current page. */
	private $page_seo_cache = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	public function init() {
		// Title control
		add_filter( 'pre_get_document_title', array( $this, 'document_title' ), 10 );

		// Head output: meta tags at priority 1 (very early, before theme can add duplicates)
		add_action( 'wp_head', array( $this, 'output_meta_tags' ), 1 );

		// Meta box
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post',      array( $this, 'save_meta_box' ), 10, 2 );

		// Admin columns
		add_filter( 'manage_pages_columns',     array( $this, 'add_seo_column' ) );
		add_filter( 'manage_posts_columns',     array( $this, 'add_seo_column' ) );
		add_action( 'manage_pages_custom_column', array( $this, 'render_seo_column' ), 10, 2 );
		add_action( 'manage_posts_custom_column', array( $this, 'render_seo_column' ), 10, 2 );
	}

	// ── Document Title ───────────────────────────────────────────────────────

	public function document_title( $title ) {
		// Don't interfere in admin
		if ( is_admin() ) {
			return $title;
		}

		if ( is_singular() ) {
			$post_id      = get_the_ID();
			$custom_title = get_post_meta( $post_id, '_bseo_title', true );
			if ( ! empty( $custom_title ) ) {
				return $custom_title;
			}
		}

		// Apply global title format
		$format = bseo_get_setting( 'title_format', '%title% | Breeze Safaris' );

		if ( is_front_page() ) {
			return str_replace( '%title%', get_bloginfo( 'name' ), $format );
		}

		if ( is_singular() ) {
			return str_replace( '%title%', get_the_title(), $format );
		}

		if ( is_archive() ) {
			$archive_title = get_the_archive_title();
			return str_replace( '%title%', wp_strip_all_tags( $archive_title ), $format );
		}

		if ( is_search() ) {
			/* translators: %s: search query */
			$search_title = sprintf( __( 'Search: %s', 'breeze-seo' ), get_search_query() );
			return str_replace( '%title%', $search_title, $format );
		}

		if ( is_404() ) {
			return str_replace( '%title%', __( 'Page Not Found', 'breeze-seo' ), $format );
		}

		return $title;
	}

	// ── Meta Tags Output ─────────────────────────────────────────────────────

	public function output_meta_tags() {
		$seo = $this->resolve_page_seo();
		if ( empty( $seo ) ) {
			return;
		}

		$desc      = $seo['description']    ?? '';
		$canonical = $seo['canonical']      ?? '';
		$robots    = $seo['robots']         ?? 'index,follow,max-image-preview:large,max-snippet:-1';
		$og_title  = $seo['og_title']       ?? '';
		$og_desc   = $seo['og_description'] ?? $desc;
		$og_image  = $seo['og_image']       ?? '';
		$og_type   = $seo['og_type']        ?? 'website';
		$og_locale = $seo['og_locale']      ?? 'en_US';

		// ── Core meta ──────────────────────────────────────────────────────
		if ( $desc ) {
			echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
		}
		if ( $canonical ) {
			echo '<link rel="canonical" href="' . esc_url( $canonical ) . '">' . "\n";
		}
		echo '<meta name="robots" content="' . esc_attr( $robots ) . '">' . "\n";

		// ── Open Graph ─────────────────────────────────────────────────────
		echo '<meta property="og:locale" content="' . esc_attr( $og_locale ) . '">' . "\n";
		echo '<meta property="og:type" content="' . esc_attr( $og_type ) . '">' . "\n";
		if ( $og_title ) {
			echo '<meta property="og:title" content="' . esc_attr( $og_title ) . '">' . "\n";
		}
		if ( $og_desc ) {
			echo '<meta property="og:description" content="' . esc_attr( $og_desc ) . '">' . "\n";
		}
		if ( $canonical ) {
			echo '<meta property="og:url" content="' . esc_url( $canonical ) . '">' . "\n";
		}
		echo '<meta property="og:site_name" content="' . esc_attr( bseo_get_setting( 'schema_business_name', 'Breeze Safaris' ) ) . '">' . "\n";
		if ( $og_image ) {
			echo '<meta property="og:image" content="' . esc_url( $og_image ) . '">' . "\n";
			echo '<meta property="og:image:width" content="1200">' . "\n";
			echo '<meta property="og:image:height" content="800">' . "\n";
			if ( $og_title ) {
				echo '<meta property="og:image:alt" content="' . esc_attr( $og_title ) . '">' . "\n";
			}
		}

		// ── Twitter Cards ──────────────────────────────────────────────────
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
		if ( $og_title ) {
			echo '<meta name="twitter:title" content="' . esc_attr( $og_title ) . '">' . "\n";
		}
		if ( $og_desc ) {
			echo '<meta name="twitter:description" content="' . esc_attr( $og_desc ) . '">' . "\n";
		}
		if ( $og_image ) {
			echo '<meta name="twitter:image" content="' . esc_url( $og_image ) . '">' . "\n";
		}
	}

	// ── Resolve current page SEO data ─────────────────────────────────────────

	/**
	 * Returns SEO data array for the current page, with caching.
	 */
	public function resolve_page_seo() {
		if ( $this->page_seo_cache !== null ) {
			return $this->page_seo_cache;
		}
		$this->page_seo_cache = $this->compute_page_seo();
		return $this->page_seo_cache;
	}

	/**
	 * Compute SEO data for the current page.
	 * Priority: post meta > global settings > fallbacks
	 */
	private function compute_page_seo() {
		$home_url         = trailingslashit( home_url( '/' ) );
		$default_og_image = bseo_get_setting( 'og_image_url' );
		$title_format     = bseo_get_setting( 'title_format', '%title% | Breeze Safaris' );
		$default_desc     = bseo_get_setting( 'default_description' );
		$is_pt            = $this->is_pt_page();
		$og_locale        = $is_pt ? 'pt_PT' : 'en_US';

		// ── Homepage ───────────────────────────────────────────────────────
		if ( is_front_page() ) {
			$canonical = $home_url;
			return array(
				'description'    => $default_desc,
				'canonical'      => $canonical,
				'og_title'       => str_replace( '%title%', get_bloginfo( 'name' ), $title_format ),
				'og_description' => $default_desc,
				'og_image'       => $default_og_image,
				'og_type'        => 'website',
				'og_locale'      => $og_locale,
				'robots'         => 'index,follow,max-image-preview:large,max-snippet:-1',
			);
		}

		// ── Singular (page, post, CPT) ────────────────────────────────────
		if ( is_singular() ) {
			$post_id   = get_the_ID();
			$canonical = trailingslashit( (string) get_permalink( $post_id ) );

			// Read from post meta
			$custom_title = get_post_meta( $post_id, '_bseo_title', true );
			$description  = get_post_meta( $post_id, '_bseo_description', true );
			$og_image     = get_post_meta( $post_id, '_bseo_og_image', true );
			$og_type      = get_post_meta( $post_id, '_bseo_og_type', true );
			$custom_canon = get_post_meta( $post_id, '_bseo_canonical', true );
			$noindex      = get_post_meta( $post_id, '_bseo_noindex', true );
			$robots_custom = get_post_meta( $post_id, '_bseo_robots', true );

			// Fallbacks
			if ( empty( $og_title = $custom_title ) ) {
				$og_title = str_replace( '%title%', get_the_title( $post_id ), $title_format );
			}
			if ( empty( $description ) ) {
				$description = $default_desc;
			}
			if ( empty( $og_image ) ) {
				$thumb = get_the_post_thumbnail_url( $post_id, 'large' );
				$og_image = $thumb ? $thumb : $default_og_image;
			}
			if ( empty( $og_type ) ) {
				$og_type = get_post_type( $post_id ) === 'page' ? 'website' : 'article';
			}
			if ( ! empty( $custom_canon ) ) {
				$canonical = $custom_canon;
			}

			// Robots
			if ( ! empty( $robots_custom ) ) {
				$robots = $robots_custom;
			} elseif ( $noindex === '1' ) {
				$robots = 'noindex,follow';
			} else {
				$robots = 'index,follow,max-image-preview:large,max-snippet:-1';
			}

			return array(
				'description'    => $description,
				'canonical'      => $canonical,
				'og_title'       => $og_title,
				'og_description' => $description,
				'og_image'       => $og_image,
				'og_type'        => $og_type,
				'og_locale'      => $og_locale,
				'robots'         => $robots,
			);
		}

		// ── Archives, Search, 404 ─────────────────────────────────────────
		if ( is_archive() || is_search() || is_404() ) {
			return array(
				'description'    => $default_desc,
				'canonical'      => '',
				'og_title'       => wp_strip_all_tags( get_the_archive_title() ),
				'og_description' => $default_desc,
				'og_image'       => $default_og_image,
				'og_type'        => 'website',
				'og_locale'      => $og_locale,
				'robots'         => is_404() ? 'noindex,follow' : 'index,follow',
			);
		}

		return array();
	}

	/**
	 * Detect if the current page is a PT page.
	 */
	private function is_pt_page( $post_id = null ) {
		if ( null === $post_id && is_singular() ) {
			$post_id = get_the_ID();
		}
		if ( ! $post_id ) {
			return false;
		}
		return get_post_meta( $post_id, '_breeze_lang', true ) === 'pt';
	}

	// ── Meta Box ──────────────────────────────────────────────────────────────

	public function add_meta_box() {
		$post_types = array( 'page', 'post' );
		// Add CPTs
		$custom_types = get_post_types( array( 'public' => true, '_builtin' => false ) );
		$post_types   = array_merge( $post_types, array_keys( $custom_types ) );

		foreach ( $post_types as $pt ) {
			add_meta_box(
				'bseo_meta_box',
				'<img src="' . esc_url( BREEZE_SEO_URL . 'admin/assets/images/icon-16.png' ) . '" style="vertical-align:middle;margin-right:5px;width:16px;" alt=""> ' . __( 'Breeze SEO', 'breeze-seo' ),
				array( $this, 'render_meta_box' ),
				$pt,
				'normal',
				'high'
			);
		}
	}

	public function render_meta_box( $post ) {
		wp_nonce_field( 'bseo_save_meta', 'bseo_meta_nonce' );

		$title        = get_post_meta( $post->ID, '_bseo_title', true );
		$description  = get_post_meta( $post->ID, '_bseo_description', true );
		$og_image     = get_post_meta( $post->ID, '_bseo_og_image', true );
		$og_image_id  = get_post_meta( $post->ID, '_bseo_og_image_id', true );
		$og_type      = get_post_meta( $post->ID, '_bseo_og_type', true ) ?: 'website';
		$canonical    = get_post_meta( $post->ID, '_bseo_canonical', true );
		$noindex      = get_post_meta( $post->ID, '_bseo_noindex', true );
		$robots       = get_post_meta( $post->ID, '_bseo_robots', true );

		$post_title   = get_the_title( $post->ID );
		$post_url     = get_permalink( $post->ID );
		$title_format = bseo_get_setting( 'title_format', '%title% | Breeze Safaris' );
		$fallback_title = str_replace( '%title%', $post_title, $title_format );

		$title_len   = mb_strlen( $title ?: $fallback_title );
		$desc_len    = mb_strlen( $description );
		?>
		<div class="bseo-meta-box">
			<div class="bseo-meta-tabs">
				<button type="button" class="bseo-tab-btn active" data-tab="general"><?php esc_html_e( 'General', 'breeze-seo' ); ?></button>
				<button type="button" class="bseo-tab-btn" data-tab="social"><?php esc_html_e( 'Social / OG', 'breeze-seo' ); ?></button>
				<button type="button" class="bseo-tab-btn" data-tab="advanced"><?php esc_html_e( 'Advanced', 'breeze-seo' ); ?></button>
				<button type="button" class="bseo-tab-btn" data-tab="preview"><?php esc_html_e( 'Preview', 'breeze-seo' ); ?></button>
			</div>

			<?php /* ── General tab ── */ ?>
			<div class="bseo-tab-panel" data-tab="general" style="display:block;">
				<table class="form-table bseo-form-table">
					<tr>
						<th><label for="_bseo_title"><?php esc_html_e( 'SEO Title', 'breeze-seo' ); ?></label></th>
						<td>
							<input type="text" id="_bseo_title" name="_bseo_title"
								value="<?php echo esc_attr( $title ); ?>"
								placeholder="<?php echo esc_attr( $fallback_title ); ?>"
								class="large-text"
								data-char-limit="60"
								data-char-min="30">
							<span class="bseo-char-count" id="bseo-title-count">
								<?php
								echo esc_html( $title_len );
								echo ' / 60 ' . esc_html__( 'characters', 'breeze-seo' );
								if ( $title_len < 30 || $title_len > 60 ) {
									echo ' <span class="bseo-warn">⚠ ' . esc_html( $title_len < 30 ? __( 'too short', 'breeze-seo' ) : __( 'too long', 'breeze-seo' ) ) . '</span>';
								}
								?>
							</span>
							<p class="description"><?php esc_html_e( 'Leave empty to use the global format:', 'breeze-seo' ); ?> <em><?php echo esc_html( $fallback_title ); ?></em></p>
						</td>
					</tr>
					<tr>
						<th><label for="_bseo_description"><?php esc_html_e( 'Meta Description', 'breeze-seo' ); ?></label></th>
						<td>
							<textarea id="_bseo_description" name="_bseo_description"
								rows="3" class="large-text"
								data-char-limit="160"
								data-char-min="100"><?php echo esc_textarea( $description ); ?></textarea>
							<span class="bseo-char-count" id="bseo-desc-count">
								<?php
								echo esc_html( $desc_len );
								echo ' / 160 ' . esc_html__( 'characters', 'breeze-seo' );
								if ( $desc_len > 0 && ( $desc_len < 100 || $desc_len > 160 ) ) {
									echo ' <span class="bseo-warn">⚠ ' . esc_html( $desc_len < 100 ? __( 'too short (min 100)', 'breeze-seo' ) : __( 'too long (max 160)', 'breeze-seo' ) ) . '</span>';
								}
								?>
							</span>
							<p class="description"><?php esc_html_e( 'Recommended: 100–160 characters.', 'breeze-seo' ); ?></p>
						</td>
					</tr>
				</table>
			</div>

			<?php /* ── Social / OG tab ── */ ?>
			<div class="bseo-tab-panel" data-tab="social" style="display:none;">
				<table class="form-table bseo-form-table">
					<tr>
						<th><label for="_bseo_og_image"><?php esc_html_e( 'OG Image URL', 'breeze-seo' ); ?></label></th>
						<td>
							<div class="bseo-media-field">
								<input type="url" id="_bseo_og_image" name="_bseo_og_image"
									value="<?php echo esc_url( $og_image ); ?>"
									class="large-text">
								<input type="hidden" id="_bseo_og_image_id" name="_bseo_og_image_id"
									value="<?php echo esc_attr( $og_image_id ); ?>">
								<button type="button" class="button bseo-media-upload-btn"
									data-target="#_bseo_og_image"
									data-target-id="#_bseo_og_image_id">
									<?php esc_html_e( 'Choose Image', 'breeze-seo' ); ?>
								</button>
							</div>
							<p class="description"><?php esc_html_e( 'Recommended size: 1200×800px (min 600×315px). Leave empty to use the default OG image from settings.', 'breeze-seo' ); ?></p>
							<?php if ( $og_image ) : ?>
								<img src="<?php echo esc_url( $og_image ); ?>" id="bseo-og-preview-img"
									style="max-width:300px;margin-top:8px;border-radius:4px;" alt="">
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th><label for="_bseo_og_type"><?php esc_html_e( 'OG Type', 'breeze-seo' ); ?></label></th>
						<td>
							<select id="_bseo_og_type" name="_bseo_og_type">
								<option value="website" <?php selected( $og_type, 'website' ); ?>>website</option>
								<option value="article" <?php selected( $og_type, 'article' ); ?>>article</option>
								<option value="product" <?php selected( $og_type, 'product' ); ?>>product</option>
							</select>
						</td>
					</tr>
				</table>
			</div>

			<?php /* ── Advanced tab ── */ ?>
			<div class="bseo-tab-panel" data-tab="advanced" style="display:none;">
				<table class="form-table bseo-form-table">
					<tr>
						<th><label for="_bseo_canonical"><?php esc_html_e( 'Canonical URL', 'breeze-seo' ); ?></label></th>
						<td>
							<input type="url" id="_bseo_canonical" name="_bseo_canonical"
								value="<?php echo esc_url( $canonical ); ?>"
								placeholder="<?php echo esc_url( $post_url ); ?>"
								class="large-text">
							<p class="description"><?php esc_html_e( 'Leave empty to use the page permalink. Only set if this page duplicates another URL.', 'breeze-seo' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Robots', 'breeze-seo' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="_bseo_noindex" value="1" <?php checked( $noindex, '1' ); ?>>
								<?php esc_html_e( 'Noindex — hide this page from search engines', 'breeze-seo' ); ?>
							</label>
							<br><br>
							<label for="_bseo_robots"><?php esc_html_e( 'Custom robots string (optional):', 'breeze-seo' ); ?></label>
							<input type="text" id="_bseo_robots" name="_bseo_robots"
								value="<?php echo esc_attr( $robots ); ?>"
								placeholder="index,follow,max-image-preview:large,max-snippet:-1"
								class="large-text">
							<p class="description"><?php esc_html_e( 'Override the robots directive for this page. Noindex checkbox above takes precedence if checked.', 'breeze-seo' ); ?></p>
						</td>
					</tr>
				</table>
			</div>

			<?php /* ── Preview tab ── */ ?>
			<div class="bseo-tab-panel" data-tab="preview" style="display:none;">
				<h4><?php esc_html_e( 'Google Search Result Preview', 'breeze-seo' ); ?></h4>
				<div class="bseo-preview-google">
					<div class="bseo-preview-google-url"><?php echo esc_html( $post_url ? wp_parse_url( $post_url, PHP_URL_HOST ) . wp_parse_url( $post_url, PHP_URL_PATH ) : '' ); ?></div>
					<div class="bseo-preview-google-title"><?php echo esc_html( $title ?: $fallback_title ); ?></div>
					<div class="bseo-preview-google-desc"><?php echo esc_html( $description ?: bseo_get_setting( 'default_description' ) ); ?></div>
				</div>

				<h4><?php esc_html_e( 'Social / Facebook Preview', 'breeze-seo' ); ?></h4>
				<div class="bseo-preview-facebook">
					<?php if ( $og_image ) : ?>
						<img class="bseo-preview-facebook-img" src="<?php echo esc_url( $og_image ); ?>" alt="">
					<?php else : ?>
						<div class="bseo-preview-facebook-img-placeholder"><?php esc_html_e( 'No OG image set', 'breeze-seo' ); ?></div>
					<?php endif; ?>
					<div class="bseo-preview-facebook-body">
						<div class="bseo-preview-facebook-domain"><?php echo esc_html( $post_url ? wp_parse_url( $post_url, PHP_URL_HOST ) : '' ); ?></div>
						<div class="bseo-preview-facebook-title"><?php echo esc_html( $title ?: $fallback_title ); ?></div>
						<div class="bseo-preview-facebook-desc"><?php echo esc_html( $description ?: bseo_get_setting( 'default_description' ) ); ?></div>
					</div>
				</div>
				<p class="description" style="margin-top:10px;">
					<?php esc_html_e( 'Preview updates live as you type in the General and Social tabs.', 'breeze-seo' ); ?>
				</p>
			</div>

			<?php /* ── SEO score indicator ── */ ?>
			<div class="bseo-meta-box-footer">
				<?php $score = $this->calculate_quick_score( $post->ID, $title, $description, $og_image ); ?>
				<div class="bseo-score-indicator">
					<span class="bseo-score-label"><?php esc_html_e( 'SEO Score:', 'breeze-seo' ); ?></span>
					<span class="bseo-score-badge bseo-score-<?php echo $score >= 80 ? 'high' : ( $score >= 50 ? 'medium' : 'low' ); ?>">
						<?php echo esc_html( $score ); ?>/100
					</span>
					<div class="bseo-score-bar">
						<div class="bseo-score-fill" style="width:<?php echo esc_attr( $score ); ?>%"></div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Calculate a quick SEO score for meta box display.
	 */
	private function calculate_quick_score( $post_id, $title, $description, $og_image ) {
		$score = 0;
		$title_len = mb_strlen( $title );
		$desc_len  = mb_strlen( $description );

		if ( $title_len >= 30 && $title_len <= 60 ) {
			$score += 25;
		} elseif ( $title_len > 0 ) {
			$score += 10;
		}

		if ( $desc_len >= 100 && $desc_len <= 160 ) {
			$score += 25;
		} elseif ( $desc_len > 0 ) {
			$score += 10;
		}

		if ( $og_image ) {
			$score += 25;
		} elseif ( get_the_post_thumbnail( $post_id ) ) {
			$score += 15;
		}

		// Check H1 in content (simple heuristic)
		$content = get_post_field( 'post_content', $post_id );
		if ( preg_match( '/<h1\b/i', $content ) || get_the_title( $post_id ) ) {
			$score += 25;
		}

		return min( 100, $score );
	}

	public function save_meta_box( $post_id, $post ) {
		// Nonce check
		if ( ! isset( $_POST['bseo_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bseo_meta_nonce'] ) ), 'bseo_save_meta' ) ) {
			return;
		}
		// Autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		// Permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = array(
			'_bseo_title'       => 'sanitize_text_field',
			'_bseo_description' => 'sanitize_textarea_field',
			'_bseo_og_image'    => 'esc_url_raw',
			'_bseo_og_type'     => 'sanitize_text_field',
			'_bseo_canonical'   => 'esc_url_raw',
			'_bseo_robots'      => 'sanitize_text_field',
		);

		foreach ( $fields as $key => $sanitizer ) {
			$raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
			$value = call_user_func( $sanitizer, $raw );
			if ( $value === '' ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $value );
			}
		}

		// Checkbox: noindex
		$noindex = isset( $_POST['_bseo_noindex'] ) && $_POST['_bseo_noindex'] === '1' ? '1' : '';
		if ( $noindex ) {
			update_post_meta( $post_id, '_bseo_noindex', '1' );
		} else {
			delete_post_meta( $post_id, '_bseo_noindex' );
		}

		// OG image ID (integer)
		if ( isset( $_POST['_bseo_og_image_id'] ) ) {
			$id = absint( $_POST['_bseo_og_image_id'] );
			if ( $id > 0 ) {
				update_post_meta( $post_id, '_bseo_og_image_id', $id );
			} else {
				delete_post_meta( $post_id, '_bseo_og_image_id' );
			}
		}
	}

	// ── Admin columns ─────────────────────────────────────────────────────────

	public function add_seo_column( $columns ) {
		$columns['bseo_status'] = __( 'SEO', 'breeze-seo' );
		return $columns;
	}

	public function render_seo_column( $column, $post_id ) {
		if ( $column !== 'bseo_status' ) {
			return;
		}
		$title       = get_post_meta( $post_id, '_bseo_title', true );
		$description = get_post_meta( $post_id, '_bseo_description', true );
		$noindex     = get_post_meta( $post_id, '_bseo_noindex', true );

		$has_title = ! empty( $title );
		$has_desc  = ! empty( $description );

		if ( $noindex === '1' ) {
			echo '<span style="color:#888;font-size:11px;">NOINDEX</span>';
			return;
		}

		$score = 0;
		if ( $has_title ) $score++;
		if ( $has_desc )  $score++;

		$icons = array(
			'title' => $has_title ? '🟢' : '🔴',
			'desc'  => $has_desc  ? '🟢' : '🔴',
		);
		echo '<span title="' . esc_attr( __( 'Title', 'breeze-seo' ) ) . '">' . esc_html( $icons['title'] ) . '</span> ';
		echo '<span title="' . esc_attr( __( 'Description', 'breeze-seo' ) ) . '">' . esc_html( $icons['desc'] ) . '</span>';
	}
}
