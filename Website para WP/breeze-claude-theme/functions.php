<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once get_template_directory() . '/inc/theme-data.php';
require_once get_template_directory() . '/inc/seo.php';
require_once get_template_directory() . '/inc/i18n.php';
require_once get_template_directory() . '/inc/sitemap.php';
require_once get_template_directory() . '/inc/redirects.php';

/**
 * Auto-apply page templates by slug so WP admin assignment is not required.
 * Packages and Privacy pages get their dedicated templates automatically.
 */
add_filter('template_include', function ($template) {
    if (!is_page()) {
        return $template;
    }
    $post   = get_queried_object();
    $slug   = $post->post_name ?? '';
    $parent = $post->post_parent ? get_post_field('post_name', $post->post_parent) : '';
    $path   = $parent ? $parent . '/' . $slug : $slug;

    $assigned = get_page_template_slug($post->ID);

    // Packages page (EN: packages, PT: pt/pacotes)
    if (in_array($path, array('packages', 'pt/pacotes'), true) && empty($assigned)) {
        $t = locate_template('page-templates/packages-page.php');
        if ($t) return $t;
    }

    // Privacy policy page
    if ($slug === 'privacy-policy' && empty($assigned)) {
        $t = locate_template('page-templates/privacy-page.php');
        if ($t) return $t;
    }

    return $template;
}, 10);

function breeze_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support(
        'custom-logo',
        array(
            'height'      => 200,
            'width'       => 800,
            'flex-height' => true,
            'flex-width'  => true,
        )
    );
    add_theme_support(
        'html5',
        array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script',
        )
    );

    register_nav_menus(
        array(
            'primary'             => __('Primary Menu', 'breeze-codex-theme'),
            'footer_explore'      => __('Footer Explore Menu', 'breeze-codex-theme'),
            'footer_destinations' => __('Footer Destinations Menu', 'breeze-codex-theme'),
        )
    );
}
add_action('after_setup_theme', 'breeze_theme_setup');

function breeze_enqueue_assets() {
    $version = wp_get_theme()->get('Version');
    $is_dest = is_page_template('page-templates/destination-page.php');

    // Main CSS (minified) – Google Fonts are loaded asynchronously via wp_head (see below)
    wp_enqueue_style(
        'breeze-main',
        get_template_directory_uri() . '/assets/css/styles.min.css',
        array(),
        $version
    );

    // Destination CSS – load only on destination template pages (saves ~4KB elsewhere)
    if ($is_dest) {
        wp_enqueue_style(
            'breeze-destination',
            get_template_directory_uri() . '/assets/css/destination.min.css',
            array('breeze-main'),
            $version
        );
        $theme_style_deps = array('breeze-destination');
    } else {
        $theme_style_deps = array('breeze-main');
    }

    // Theme style.css (theme identification header only – minimal weight)
    wp_enqueue_style(
        'breeze-theme',
        get_stylesheet_uri(),
        $theme_style_deps,
        $version
    );

    // Main JS – loaded in footer (deferred by position)
    wp_enqueue_script(
        'breeze-main',
        get_template_directory_uri() . '/assets/js/main.min.js',
        array(),
        $version,
        true
    );
}
add_action('wp_enqueue_scripts', 'breeze_enqueue_assets');

// ── Remove WordPress Head Bloat ────────────────────────
function breeze_disable_wp_bloat() {
    // Emoji scripts & styles (~10KB saved per page)
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    add_filter('emoji_svg_url', '__return_false');

    // Security: hide WP version string
    remove_action('wp_head', 'wp_generator');

    // Unused discovery links (blog desktop clients, XML-RPC discovery)
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');

    // Unnecessary head extras
    remove_action('wp_head', 'wp_shortlink_wp_head');
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
}
add_action('init', 'breeze_disable_wp_bloat');

// Remove emoji DNS-prefetch entry added by WP
add_filter('wp_resource_hints', function ($urls, $relation_type) {
    if ($relation_type === 'dns-prefetch' && is_array($urls)) {
        return array_filter($urls, function ($url) {
            return strpos((string) $url, 'https://s.w.org') === false;
        });
    }
    return $urls;
}, 10, 2);

// Dequeue WordPress extras not needed by this theme
add_action('wp_enqueue_scripts', function () {
    // WP 6.1+ loads a classic-theme compat stylesheet – this theme handles all styles itself
    wp_dequeue_style('classic-theme-styles');
    // Global styles from the Site Editor – not used by a classic theme
    wp_dequeue_style('global-styles');
}, 20);

// ── Google Fonts: preconnect + async (non-render-blocking) ────────────
// Preconnect hints reduce DNS + TCP latency by ~200-400ms.
// Loading the font CSS asynchronously (preload trick) eliminates render-blocking.
add_action('wp_head', function () {
    $font_url = 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Manrope:wght@400;500;600;700&display=swap';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
    // Async font CSS load – swaps to stylesheet on load; noscript fallback for safety
    echo '<link rel="preload" href="' . esc_url($font_url) . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n";
    echo '<noscript><link rel="stylesheet" href="' . esc_url($font_url) . '"></noscript>' . "\n";
}, 1);

// ── Contact: debug logger ──────────────────────────────
function breeze_contact_log($message) {
    error_log('[BREEZE_CONTACT] ' . $message);
}

// ── Contact Form Handler ───────────────────────────────
// Form is processed directly in page-templates/contact-page.php at the top,
// before get_header(), so no redirect is needed and the page always renders.

function breeze_body_classes($classes) {
    if (!is_front_page()) {
        $classes[] = 'inner-page';
    }

    return $classes;
}
add_filter('body_class', 'breeze_body_classes');

function breeze_home_anchor_url($anchor) {
    return home_url('/#' . ltrim((string) $anchor, '#'));
}

function breeze_get_page_url_by_slug($slug, $fallback = '') {
    $slug = trim((string) $slug, '/');
    if ($slug === '') {
        return $fallback ? $fallback : home_url('/');
    }

    $page = get_page_by_path($slug, OBJECT, 'page');
    if ($page instanceof WP_Post) {
        return get_permalink($page);
    }

    return $fallback ? $fallback : home_url('/' . $slug . '/');
}

function breeze_get_theme_image($setting_key, $fallback = '') {
    $url = get_theme_mod($setting_key, '');
    if (!empty($url)) {
        return esc_url($url);
    }

    return $fallback;
}

function breeze_get_logo_url() {
    if (function_exists('has_custom_logo') && has_custom_logo()) {
        $logo_id = (int) get_theme_mod('custom_logo');
        $logo_url = wp_get_attachment_image_url($logo_id, 'full');
        if (!empty($logo_url)) {
            return $logo_url;
        }
    }

    $defaults = breeze_get_home_defaults();
    return $defaults['logo'];
}

function breeze_render_editor_content($raw_content) {
    $raw_content = (string) $raw_content;
    if (trim($raw_content) === '') {
        return '';
    }

    if (has_blocks($raw_content)) {
        $rendered = do_blocks($raw_content);
    } else {
        $rendered = do_shortcode($raw_content);
    }

    // Apply WP's built-in content tag processing: adds loading="lazy" to images/iframes,
    // sets decoding="async", etc. (available since WP 5.5 – theme requires WP 6+)
    if (function_exists('wp_filter_content_tags')) {
        $rendered = wp_filter_content_tags($rendered, 'the_content');
    }

    return $rendered;
}

function breeze_strip_home_destinations_section($html) {
    $html = (string) $html;
    if ($html === '') {
        return $html;
    }

    // Remove the legacy Home destinations section to avoid duplicated destination blocks.
    $pattern = '/<section\b[^>]*\bid=(["\'])destinations\1[^>]*>.*?<\/section>/is';
    return (string) preg_replace($pattern, '', $html);
}

if (!class_exists('Breeze_Flat_Nav_Walker')) {
    class Breeze_Flat_Nav_Walker extends Walker_Nav_Menu {
        public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
            $atts = array();
            $atts['href'] = !empty($item->url) ? $item->url : '';

            if (!empty($item->target)) {
                $atts['target'] = $item->target;
            }
            if (!empty($item->xfn)) {
                $atts['rel'] = $item->xfn;
            }
            if (in_array('current-menu-item', (array) $item->classes, true)) {
                $atts['aria-current'] = 'page';
            }

            $attributes = '';
            foreach ($atts as $attr => $value) {
                if ($value === '') {
                    continue;
                }
                $attributes .= ' ' . $attr . '="' . esc_attr($value) . '"';
            }

            $title = apply_filters('the_title', $item->title, $item->ID);
            $output .= '<a' . $attributes . '>' . esc_html($title) . '</a>';
        }

        public function end_el(&$output, $item, $depth = 0, $args = null) {
        }
    }
}

function breeze_render_menu_links($links) {
    foreach ($links as $link) {
        $url = isset($link['url']) ? $link['url'] : '#';
        $label = isset($link['label']) ? $link['label'] : '';
        echo '<a href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
    }
}

function breeze_primary_fallback_links() {
    $is_home = is_front_page();

    return array(
        array(
            'label' => 'All Packages',
            'url'   => breeze_get_page_url_by_slug('packages', home_url('/#packages')),
        ),
        array(
            'label' => 'Discover Places',
            'url'   => $is_home ? '#parks-showcase' : breeze_home_anchor_url('parks-showcase'),
        ),
        array(
            'label' => 'Contact',
            'url'   => breeze_get_page_url_by_slug('contact', home_url('/contact/')),
        ),
    );
}

function breeze_footer_explore_fallback_links() {
    return array(
        array('label' => 'Packages', 'url' => breeze_get_page_url_by_slug('packages', home_url('/#packages'))),
        array('label' => 'Why Tanzania', 'url' => breeze_home_anchor_url('parks-showcase')),
        array('label' => 'About', 'url' => breeze_get_page_url_by_slug('about', home_url('/about/'))),
        array('label' => 'Privacy Policy', 'url' => breeze_get_page_url_by_slug('privacy-policy', home_url('/privacy-policy/'))),
    );
}

function breeze_footer_destinations_fallback_links() {
    return array(
        array('label' => 'Serengeti', 'url' => breeze_get_page_url_by_slug('serengeti-national-park')),
        array('label' => 'Ngorongoro', 'url' => breeze_get_page_url_by_slug('ngorongoro-conservation-area')),
        array('label' => 'Tarangire', 'url' => breeze_get_page_url_by_slug('tarangire-national-park')),
        array('label' => 'Zanzibar', 'url' => breeze_get_page_url_by_slug('zanzibar')),
    );
}

function breeze_upsert_seed_page($definition, $menu_order = 0) {
    $slug = sanitize_title((string) $definition['slug']);
    $title = sanitize_text_field((string) $definition['title']);
    $content = isset($definition['content']) ? (string) $definition['content'] : '';
    $template = isset($definition['template']) ? (string) $definition['template'] : 'default';
    $parent_slug = isset($definition['parent_slug']) ? sanitize_title((string) $definition['parent_slug']) : '';
    $parent_id = 0;
    if ($parent_slug !== '') {
        $parent_page = get_page_by_path($parent_slug, OBJECT, 'page');
        if ($parent_page instanceof WP_Post) {
            $parent_id = (int) $parent_page->ID;
        }
    }

    $lookup_path = $slug;
    if ($parent_id > 0) {
        $parent_path = trim((string) get_page_uri($parent_id), '/');
        if ($parent_path !== '') {
            $lookup_path = $parent_path . '/' . $slug;
        }
    }

    $existing = get_page_by_path($lookup_path, OBJECT, 'page');
    if (!($existing instanceof WP_Post)) {
        $existing = get_page_by_path($slug, OBJECT, 'page');
    }

    $postarr = array(
        'post_title'   => $title,
        'post_name'    => $slug,
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'post_content' => $content,
        'post_parent'  => $parent_id,
        'menu_order'   => (int) $menu_order,
    );

    if ($existing instanceof WP_Post) {
        $postarr['ID'] = $existing->ID;
        $page_id = wp_update_post($postarr, true);
    } else {
        $page_id = wp_insert_post($postarr, true);
    }

    if (is_wp_error($page_id) || !$page_id) {
        return 0;
    }

    if ($template !== '' && $template !== 'default') {
        update_post_meta($page_id, '_wp_page_template', $template);
    } else {
        delete_post_meta($page_id, '_wp_page_template');
    }

    return (int) $page_id;
}

function breeze_ensure_seed_pages() {
    $definitions = breeze_get_seed_page_definitions();
    $page_ids = array();

    foreach ($definitions as $index => $definition) {
        $page_id = breeze_upsert_seed_page($definition, $index);
        if ($page_id > 0) {
            $page_ids[$definition['slug']] = $page_id;
        }
    }

    return $page_ids;
}

function breeze_clear_menu_items($menu_id) {
    $items = wp_get_nav_menu_items($menu_id, array('post_status' => 'any'));
    if (!is_array($items)) {
        return;
    }

    foreach ($items as $item) {
        wp_delete_post($item->ID, true);
    }
}

function breeze_ensure_primary_menu($page_ids) {
    $menu_name = 'Breeze Main Menu';
    $menu = wp_get_nav_menu_object($menu_name);
    if (!$menu) {
        $menu_id = wp_create_nav_menu($menu_name);
        if (is_wp_error($menu_id)) {
            return;
        }
    } else {
        $menu_id = (int) $menu->term_id;
    }

    breeze_clear_menu_items($menu_id);

    $primary_links = breeze_primary_fallback_links();
    $position = 1;
    foreach ($primary_links as $link) {
        wp_update_nav_menu_item(
            $menu_id,
            0,
            array(
                'menu-item-title'    => $link['label'],
                'menu-item-url'      => $link['url'],
                'menu-item-type'     => 'custom',
                'menu-item-status'   => 'publish',
                'menu-item-position' => $position,
            )
        );
        $position++;
    }

    if (!empty($page_ids) && is_array($page_ids)) {
        $pages_menu_name = 'Breeze Pages Menu';
        $pages_menu = wp_get_nav_menu_object($pages_menu_name);
        if (!$pages_menu) {
            $pages_menu_id = wp_create_nav_menu($pages_menu_name);
            if (!is_wp_error($pages_menu_id)) {
                $pages_menu = wp_get_nav_menu_object((int) $pages_menu_id);
            }
        }

        if ($pages_menu) {
            $pages_menu_id = (int) $pages_menu->term_id;
            breeze_clear_menu_items($pages_menu_id);

            $ordered_slugs = array(
                'home',
                'about',
                'packages',
                'ndutu-calving-season-safari',
                'luxury-migration-big-five-safari-8-day',
                'tanzania-zanzibar-honeymoon-9-day',
                'privacy-policy',
                'serengeti-national-park',
                'ngorongoro-conservation-area',
                'tarangire-national-park',
                'lake-manyara-national-park',
                'arusha-national-park',
                'ndutu-area',
                'zanzibar',
            );

            $position = 1;
            foreach ($ordered_slugs as $slug) {
                if (!isset($page_ids[$slug])) {
                    continue;
                }
                $page_id = (int) $page_ids[$slug];
                wp_update_nav_menu_item(
                    $pages_menu_id,
                    0,
                    array(
                        'menu-item-title'     => get_the_title($page_id),
                        'menu-item-object'    => 'page',
                        'menu-item-object-id' => $page_id,
                        'menu-item-type'      => 'post_type',
                        'menu-item-status'    => 'publish',
                        'menu-item-position'  => $position,
                    )
                );
                $position++;
            }
        }
    }

    $locations = get_theme_mod('nav_menu_locations', array());
    if (!is_array($locations)) {
        $locations = array();
    }
    $locations['primary'] = (int) $menu_id;
    set_theme_mod('nav_menu_locations', $locations);
}

function breeze_configure_reading_options($page_ids) {
    if (isset($page_ids['home'])) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', (int) $page_ids['home']);
        update_option('page_for_posts', 0);
    }

    if (isset($page_ids['privacy-policy'])) {
        update_option('wp_page_for_privacy_policy', (int) $page_ids['privacy-policy']);
    }
}

function breeze_cleanup_duplicate_seed_pages() {
    $known_slugs = array(
        'home', 'about', 'packages', 'contact', 'privacy-policy',
        'ndutu-calving-season-safari', 'luxury-migration-big-five-safari-8-day',
        'tanzania-zanzibar-honeymoon-9-day',
        'serengeti-national-park', 'ngorongoro-conservation-area',
        'tarangire-national-park', 'lake-manyara-national-park',
        'arusha-national-park', 'ndutu-area', 'zanzibar',
    );

    foreach ($known_slugs as $slug) {
        for ($suffix = 2; $suffix <= 5; $suffix++) {
            $duplicate_slug = $slug . '-' . $suffix;
            $duplicate_page = get_page_by_path($duplicate_slug, OBJECT, 'page');
            if ($duplicate_page && get_post_status($duplicate_page->ID) !== 'trash') {
                wp_trash_post((int) $duplicate_page->ID);
            }
        }
    }
}

function breeze_bootstrap_site_on_activation() {
    breeze_cleanup_duplicate_seed_pages();
    $page_ids = breeze_ensure_seed_pages();
    breeze_configure_reading_options($page_ids);
    breeze_ensure_primary_menu($page_ids);
    // Seed PT pages after EN pages exist so translation links can be established
    if (function_exists('breeze_ensure_seed_pages_pt')) {
        breeze_ensure_seed_pages_pt();
    }
    flush_rewrite_rules(false);
    update_option('breeze_seed_content_version', '12');
    update_option('breeze_seed_pt_version', '3');
}
add_action('after_switch_theme', 'breeze_bootstrap_site_on_activation');

function breeze_maybe_run_content_migration() {
    if (get_option('breeze_seed_content_version') === '12' && get_option('breeze_seed_pt_version') === '3') {
        return;
    }

    breeze_bootstrap_site_on_activation();
}
add_action('after_setup_theme', 'breeze_maybe_run_content_migration', 20);

function breeze_maybe_upgrade_home_media_theme_mods() {
    if (get_option('breeze_theme_mods_version') === '1') {
        return;
    }

    $legacy_video_url = 'https://breezesafaris.com/wp-content/uploads/2025/11/BREEZE-VIDEO-1-1.mp4';
    $new_video_url = breeze_theme_media_url('videos/breezesite.mp4');
    $current_video_url = (string) get_theme_mod('breeze_home_hero_video_url', '');
    if ($current_video_url === '' || $current_video_url === $legacy_video_url) {
        set_theme_mod('breeze_home_hero_video_url', $new_video_url);
    }

    update_option('breeze_theme_mods_version', '1');
}
add_action('after_setup_theme', 'breeze_maybe_upgrade_home_media_theme_mods', 30);

function breeze_customize_register($wp_customize) {
    $defaults = breeze_get_home_defaults();

    $wp_customize->add_section(
        'breeze_home_hero',
        array(
            'title'    => __('Breeze: Homepage Hero', 'breeze-codex-theme'),
            'priority' => 30,
        )
    );

    $wp_customize->add_setting(
        'breeze_home_hero_video_url',
        array(
            'default'           => $defaults['hero']['video_url'],
            'sanitize_callback' => 'esc_url_raw',
        )
    );
    $wp_customize->add_control(
        'breeze_home_hero_video_url',
        array(
            'label'   => __('Hero Video URL', 'breeze-codex-theme'),
            'section' => 'breeze_home_hero',
            'type'    => 'url',
        )
    );

    $wp_customize->add_setting(
        'breeze_home_hero_kicker',
        array(
            'default'           => $defaults['hero']['kicker'],
            'sanitize_callback' => 'sanitize_text_field',
        )
    );
    $wp_customize->add_control(
        'breeze_home_hero_kicker',
        array(
            'label'   => __('Hero Kicker', 'breeze-codex-theme'),
            'section' => 'breeze_home_hero',
            'type'    => 'text',
        )
    );

    $wp_customize->add_setting(
        'breeze_home_hero_title',
        array(
            'default'           => $defaults['hero']['title'],
            'sanitize_callback' => 'sanitize_text_field',
        )
    );
    $wp_customize->add_control(
        'breeze_home_hero_title',
        array(
            'label'   => __('Hero Title', 'breeze-codex-theme'),
            'section' => 'breeze_home_hero',
            'type'    => 'text',
        )
    );

    $wp_customize->add_setting(
        'breeze_home_hero_subtitle',
        array(
            'default'           => $defaults['hero']['subtitle'],
            'sanitize_callback' => 'wp_kses_post',
        )
    );
    $wp_customize->add_control(
        'breeze_home_hero_subtitle',
        array(
            'label'   => __('Hero Subtitle (supports basic HTML)', 'breeze-codex-theme'),
            'section' => 'breeze_home_hero',
            'type'    => 'textarea',
        )
    );

    $wp_customize->add_setting(
        'breeze_home_hero_button_text',
        array(
            'default'           => $defaults['hero']['button_text'],
            'sanitize_callback' => 'sanitize_text_field',
        )
    );
    $wp_customize->add_control(
        'breeze_home_hero_button_text',
        array(
            'label'   => __('Hero Button Text', 'breeze-codex-theme'),
            'section' => 'breeze_home_hero',
            'type'    => 'text',
        )
    );

    $wp_customize->add_setting(
        'breeze_home_hero_button_url',
        array(
            'default'           => $defaults['hero']['button_url'],
            'sanitize_callback' => 'breeze_sanitize_anchor_or_url',
        )
    );
    $wp_customize->add_control(
        'breeze_home_hero_button_url',
        array(
            'label'   => __('Hero Button URL', 'breeze-codex-theme'),
            'section' => 'breeze_home_hero',
            'type'    => 'text',
        )
    );

    $wp_customize->add_section(
        'breeze_home_images',
        array(
            'title'    => __('Breeze: Homepage Images', 'breeze-codex-theme'),
            'priority' => 31,
        )
    );

    for ($i = 1; $i <= 3; $i++) {
        $wp_customize->add_setting(
            'breeze_home_package_' . $i . '_image',
            array(
                'default'           => '',
                'sanitize_callback' => 'esc_url_raw',
            )
        );
        $wp_customize->add_control(
            new WP_Customize_Image_Control(
                $wp_customize,
                'breeze_home_package_' . $i . '_image',
                array(
                    'label'   => sprintf(__('Package %d Image', 'breeze-codex-theme'), $i),
                    'section' => 'breeze_home_images',
                )
            )
        );
    }

    for ($i = 1; $i <= 7; $i++) {
        $wp_customize->add_setting(
            'breeze_home_park_' . $i . '_image',
            array(
                'default'           => '',
                'sanitize_callback' => 'esc_url_raw',
            )
        );
        $wp_customize->add_control(
            new WP_Customize_Image_Control(
                $wp_customize,
                'breeze_home_park_' . $i . '_image',
                array(
                    'label'   => sprintf(__('Park Slide %d Image', 'breeze-codex-theme'), $i),
                    'section' => 'breeze_home_images',
                )
            )
        );
    }

    for ($i = 1; $i <= 7; $i++) {
        $wp_customize->add_setting(
            'breeze_home_destination_' . $i . '_image',
            array(
                'default'           => '',
                'sanitize_callback' => 'esc_url_raw',
            )
        );
        $wp_customize->add_control(
            new WP_Customize_Image_Control(
                $wp_customize,
                'breeze_home_destination_' . $i . '_image',
                array(
                    'label'   => sprintf(__('Destination Card %d Image', 'breeze-codex-theme'), $i),
                    'section' => 'breeze_home_images',
                )
            )
        );
    }

    $wp_customize->add_section(
        'breeze_footer_content',
        array(
            'title'    => __('Breeze: Footer', 'breeze-codex-theme'),
            'priority' => 32,
        )
    );

    $footer_fields = array(
        'breeze_footer_cta_kicker' => array('label' => 'Footer CTA Kicker', 'type' => 'text', 'default' => $defaults['footer']['cta_kicker'], 'sanitize' => 'sanitize_text_field'),
        'breeze_footer_cta_title' => array('label' => 'Footer CTA Title', 'type' => 'text', 'default' => $defaults['footer']['cta_title'], 'sanitize' => 'sanitize_text_field'),
        'breeze_footer_cta_button_text' => array('label' => 'Footer CTA Button Text', 'type' => 'text', 'default' => $defaults['footer']['cta_button_text'], 'sanitize' => 'sanitize_text_field'),
        'breeze_footer_cta_button_url' => array('label' => 'Footer CTA Button URL', 'type' => 'url', 'default' => $defaults['footer']['cta_button_url'], 'sanitize' => 'esc_url_raw'),
        'breeze_footer_brand_text' => array('label' => 'Footer Brand Description', 'type' => 'textarea', 'default' => $defaults['footer']['brand_text'], 'sanitize' => 'sanitize_textarea_field'),
        'breeze_footer_email' => array('label' => 'Footer Email', 'type' => 'email', 'default' => $defaults['footer']['email'], 'sanitize' => 'sanitize_email'),
        'breeze_footer_phone' => array('label' => 'Footer Phone', 'type' => 'text', 'default' => $defaults['footer']['phone'], 'sanitize' => 'sanitize_text_field'),
        'breeze_footer_location' => array('label' => 'Footer Location', 'type' => 'text', 'default' => $defaults['footer']['location'], 'sanitize' => 'sanitize_text_field'),
        'breeze_footer_extra_line' => array('label' => 'Footer Extra Line', 'type' => 'text', 'default' => $defaults['footer']['extra_line'], 'sanitize' => 'sanitize_text_field'),
        'breeze_footer_copyright' => array('label' => 'Footer Copyright', 'type' => 'text', 'default' => $defaults['footer']['copyright'], 'sanitize' => 'sanitize_text_field'),
        'breeze_footer_tagline' => array('label' => 'Footer Tagline', 'type' => 'text', 'default' => $defaults['footer']['tagline'], 'sanitize' => 'sanitize_text_field'),
    );

    foreach ($footer_fields as $key => $field) {
        $wp_customize->add_setting(
            $key,
            array(
                'default'           => $field['default'],
                'sanitize_callback' => $field['sanitize'],
            )
        );
        $wp_customize->add_control(
            $key,
            array(
                'label'   => __($field['label'], 'breeze-codex-theme'),
                'section' => 'breeze_footer_content',
                'type'    => $field['type'],
            )
        );
    }
}
add_action('customize_register', 'breeze_customize_register');

function breeze_sanitize_anchor_or_url($value) {
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }
    if (strpos($value, '#') === 0) {
        return '#' . sanitize_title(ltrim($value, '#'));
    }

    return esc_url_raw($value);
}

function breeze_destination_meta_fields() {
    return array(
        'hero_image_url'    => array('label' => 'Hero Image URL', 'type' => 'url'),
        'hero_eyebrow'      => array('label' => 'Hero Eyebrow', 'type' => 'text'),
        'hero_subtitle'     => array('label' => 'Hero Subtitle', 'type' => 'textarea'),
        'facts'             => array('label' => 'Facts (one per line)', 'type' => 'textarea'),
        'story_title'       => array('label' => 'Story Section Title', 'type' => 'text'),
        'story_1_title'     => array('label' => 'Story 1 Title', 'type' => 'text'),
        'story_1_text'      => array('label' => 'Story 1 Text', 'type' => 'textarea'),
        'story_2_title'     => array('label' => 'Story 2 Title', 'type' => 'text'),
        'story_2_text'      => array('label' => 'Story 2 Text', 'type' => 'textarea'),
        'story_3_title'     => array('label' => 'Story 3 Title', 'type' => 'text'),
        'story_3_text'      => array('label' => 'Story 3 Text', 'type' => 'textarea'),
        'gallery_1_url'     => array('label' => 'Gallery Image 1 URL', 'type' => 'url'),
        'gallery_1_alt'     => array('label' => 'Gallery Image 1 Alt', 'type' => 'text'),
        'gallery_2_url'     => array('label' => 'Gallery Image 2 URL', 'type' => 'url'),
        'gallery_2_alt'     => array('label' => 'Gallery Image 2 Alt', 'type' => 'text'),
        'gallery_3_url'     => array('label' => 'Gallery Image 3 URL', 'type' => 'url'),
        'gallery_3_alt'     => array('label' => 'Gallery Image 3 Alt', 'type' => 'text'),
        'gallery_4_url'     => array('label' => 'Gallery Image 4 URL', 'type' => 'url'),
        'gallery_4_alt'     => array('label' => 'Gallery Image 4 Alt', 'type' => 'text'),
        'gallery_5_url'     => array('label' => 'Gallery Image 5 URL', 'type' => 'url'),
        'gallery_5_alt'     => array('label' => 'Gallery Image 5 Alt', 'type' => 'text'),
        'cta_title'         => array('label' => 'CTA Title', 'type' => 'text'),
        'cta_text'          => array('label' => 'CTA Text', 'type' => 'textarea'),
        'cta_button_text'   => array('label' => 'CTA Button Text', 'type' => 'text'),
        'cta_button_url'    => array('label' => 'CTA Button URL', 'type' => 'url'),
    );
}

function breeze_add_destination_meta_box() {
    add_meta_box(
        'breeze_destination_fields',
        __('Destination Extra Fields', 'breeze-codex-theme'),
        'breeze_render_destination_meta_box',
        'page',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes_page', 'breeze_add_destination_meta_box');

function breeze_render_destination_meta_box($post) {
    wp_nonce_field('breeze_destination_fields_nonce', 'breeze_destination_fields_nonce');

    $template = get_page_template_slug($post->ID);
    if ($template !== 'page-templates/destination-page.php') {
        echo '<p>' . esc_html__('Use this meta box with the template "Destination Page".', 'breeze-codex-theme') . '</p>';
        return;
    }

    $fields = breeze_destination_meta_fields();
    foreach ($fields as $key => $field) {
        $meta_key = '_breeze_dest_' . $key;
        $value = get_post_meta($post->ID, $meta_key, true);

        echo '<p>';
        echo '<label for="' . esc_attr($meta_key) . '"><strong>' . esc_html($field['label']) . '</strong></label><br>';

        if ($field['type'] === 'textarea') {
            echo '<textarea id="' . esc_attr($meta_key) . '" name="' . esc_attr($meta_key) . '" rows="3" style="width:100%;">' . esc_textarea($value) . '</textarea>';
        } elseif ($field['type'] === 'url') {
            echo '<div class="breeze-image-field" style="display:flex;align-items:flex-start;gap:6px;flex-wrap:wrap;">';
            echo '<input type="url" id="' . esc_attr($meta_key) . '" name="' . esc_attr($meta_key) . '" value="' . esc_attr($value) . '" style="flex:1;min-width:180px;">';
            echo '<button type="button" class="button breeze-choose-image">Choose Image</button>';
            if ($value) {
                echo '<button type="button" class="button-link breeze-remove-image" style="color:#b32d2e;line-height:2;">&#10005; Remove</button>';
            }
            $preview_style = $value ? '' : 'display:none;';
            echo '<div class="breeze-image-preview" style="width:100%;' . $preview_style . '">';
            echo '<img src="' . esc_url($value) . '" style="max-height:80px;max-width:160px;display:block;margin-top:4px;border:1px solid #ddd;border-radius:3px;object-fit:cover;">';
            echo '</div></div>';
        } else {
            echo '<input type="text" id="' . esc_attr($meta_key) . '" name="' . esc_attr($meta_key) . '" value="' . esc_attr($value) . '" style="width:100%;">';
        }

        echo '</p>';
    }
}

function breeze_save_destination_meta_box($post_id) {
    if (!isset($_POST['breeze_destination_fields_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['breeze_destination_fields_nonce'])), 'breeze_destination_fields_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_page', $post_id)) {
        return;
    }

    $fields = breeze_destination_meta_fields();
    foreach ($fields as $key => $field) {
        $meta_key = '_breeze_dest_' . $key;

        if (!isset($_POST[$meta_key])) {
            continue;
        }

        $raw = wp_unslash($_POST[$meta_key]);
        if ($field['type'] === 'url') {
            $value = esc_url_raw($raw);
        } elseif ($field['type'] === 'textarea') {
            $value = sanitize_textarea_field($raw);
        } else {
            $value = sanitize_text_field($raw);
        }

        if ($value === '') {
            delete_post_meta($post_id, $meta_key);
        } else {
            update_post_meta($post_id, $meta_key, $value);
        }
    }
}
add_action('save_post_page', 'breeze_save_destination_meta_box');

function breeze_get_destination_field($post_id, $field, $default = '') {
    $value = get_post_meta($post_id, '_breeze_dest_' . $field, true);
    if ($value !== '') {
        return $value;
    }
    return $default;
}

function breeze_get_destination_view_data($post_id) {
    $post = get_post($post_id);
    if (!$post instanceof WP_Post) {
        return array();
    }

    $en_slug  = (string) get_post_meta($post_id, '_breeze_en_slug', true);
    $is_pt    = $en_slug !== '' && get_post_meta($post_id, '_breeze_lang', true) === 'pt';
    $defaults = $is_pt
        ? breeze_get_destination_default_pt($en_slug)
        : breeze_get_destination_default($post->post_name);
    $featured_image = get_the_post_thumbnail_url($post_id, 'full');
    $hero_image = $featured_image ? $featured_image : breeze_get_destination_field($post_id, 'hero_image_url', $defaults['hero_image']);

    $content = trim((string) get_post_field('post_content', $post_id));
    $intro_paragraphs = array();
    if (isset($defaults['intro']) && is_array($defaults['intro'])) {
        $intro_paragraphs = $defaults['intro'];
    }

    $facts_raw = breeze_get_destination_field($post_id, 'facts', '');
    if ($facts_raw !== '') {
        $facts = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $facts_raw))));
    } else {
        $facts = isset($defaults['facts']) && is_array($defaults['facts']) ? $defaults['facts'] : array();
    }

    $gallery_classes = array('photo-xl', 'photo-wide', 'photo-mid', 'photo-mid', 'photo-sm');
    $gallery = array();
    for ($i = 1; $i <= 5; $i++) {
        $default_gallery_item = isset($defaults['gallery'][$i - 1]) ? $defaults['gallery'][$i - 1] : array();
        $default_gallery_url = isset($default_gallery_item['url']) ? $default_gallery_item['url'] : '';
        $default_gallery_alt = isset($default_gallery_item['alt']) ? $default_gallery_item['alt'] : '';

        $image_url = breeze_get_destination_field($post_id, 'gallery_' . $i . '_url', $default_gallery_url);
        $image_alt = breeze_get_destination_field($post_id, 'gallery_' . $i . '_alt', $default_gallery_alt);
        if ($image_url !== '') {
            $gallery[] = array(
                'class' => isset($default_gallery_item['class']) ? $default_gallery_item['class'] : $gallery_classes[$i - 1],
                'url'   => $image_url,
                'alt'   => $image_alt,
            );
        }
    }

    $stories = array();
    for ($i = 1; $i <= 3; $i++) {
        $default_story = isset($defaults['stories'][$i - 1]) ? $defaults['stories'][$i - 1] : array();
        $story_title = breeze_get_destination_field($post_id, 'story_' . $i . '_title', isset($default_story['title']) ? $default_story['title'] : '');
        $story_text = breeze_get_destination_field($post_id, 'story_' . $i . '_text', isset($default_story['text']) ? $default_story['text'] : '');
        if ($story_title !== '' || $story_text !== '') {
            $stories[] = array(
                'title' => $story_title,
                'text'  => $story_text,
            );
        }
    }

    return array(
        'title'           => get_the_title($post_id) !== '' ? get_the_title($post_id) : $defaults['title'],
        'hero_eyebrow'    => breeze_get_destination_field($post_id, 'hero_eyebrow', $defaults['eyebrow']),
        'hero_subtitle'   => breeze_get_destination_field($post_id, 'hero_subtitle', $defaults['hero_subtitle']),
        'hero_image'      => $hero_image,
        'has_content'     => $content !== '',
        'intro_paragraphs'=> $intro_paragraphs,
        'facts'           => $facts,
        'story_title'     => breeze_get_destination_field($post_id, 'story_title', $defaults['story_title']),
        'stories'         => $stories,
        'gallery_title'   => isset($defaults['gallery_title']) ? $defaults['gallery_title'] : 'Gallery',
        'gallery'         => $gallery,
        'cta_title'       => breeze_get_destination_field($post_id, 'cta_title', $defaults['cta_title']),
        'cta_text'        => breeze_get_destination_field($post_id, 'cta_text', $defaults['cta_text']),
        'cta_button_text' => breeze_get_destination_field($post_id, 'cta_button_text', $defaults['cta_button_text']),
        'cta_button_url'  => breeze_get_destination_field($post_id, 'cta_button_url', $defaults['cta_button_url']),
    );
}

function breeze_get_next_destination($current_post_id) {
    $is_pt        = function_exists('breeze_is_pt_page') && breeze_is_pt_page($current_post_id);
    $all_defaults = breeze_get_destination_defaults(); // EN slugs define the ordering
    $slugs        = array_keys($all_defaults);

    // PT slug map (EN → PT) and its reverse
    $pt_slug_map  = function_exists('breeze_get_pt_dest_slug_map') ? breeze_get_pt_dest_slug_map() : array();
    $pt_slug_rmap = array_flip($pt_slug_map); // PT slug → EN slug

    // Fallback PT detection: check parent slug OR current slug is a known PT dest slug
    if (!$is_pt) {
        $parent_id = (int) get_post_field('post_parent', $current_post_id);
        if ($parent_id > 0 && get_post_field('post_name', $parent_id) === 'pt') {
            $is_pt = true;
        }
    }

    // Resolve the EN slug for ordering purposes
    if ($is_pt) {
        $current_slug = (string) get_post_meta($current_post_id, '_breeze_en_slug', true);
        if ($current_slug === '') {
            $pt_page_slug = get_post_field('post_name', $current_post_id);
            $current_slug = isset($pt_slug_rmap[$pt_page_slug]) ? $pt_slug_rmap[$pt_page_slug] : '';
        }
    } else {
        $current_slug = get_post_field('post_name', $current_post_id);
    }

    $current_index = array_search($current_slug, $slugs, true);
    if ($current_index === false) {
        return null;
    }

    // Build EN-slug → page lookup
    $en_slug_to_page = array();

    if ($is_pt) {
        // For PT: look up each destination page directly by known PT slug.
        // This works even if _wp_page_template or _breeze_lang meta are not set.
        foreach ($pt_slug_map as $en_s => $pt_s) {
            $pt_page = get_page_by_path('pt/' . $pt_s, OBJECT, 'page');
            if (!($pt_page instanceof WP_Post)) {
                $pt_page = get_page_by_path($pt_s, OBJECT, 'page');
            }
            if ($pt_page instanceof WP_Post && get_post_status($pt_page->ID) === 'publish') {
                $en_slug_to_page[$en_s] = $pt_page;
            }
        }
    } else {
        // For EN: use meta query
        $pages = get_pages(array(
            'meta_key'    => '_wp_page_template',
            'meta_value'  => 'page-templates/destination-page.php',
            'post_status' => 'publish',
        ));
        foreach ($pages as $page) {
            if (get_post_meta($page->ID, '_breeze_lang', true) !== 'pt') {
                $en_slug_to_page[$page->post_name] = $page;
            }
        }
    }

    $count = count($slugs);
    for ($i = 1; $i < $count; $i++) {
        $next_slug = $slugs[($current_index + $i) % $count];
        if (!isset($en_slug_to_page[$next_slug])) {
            continue;
        }
        $next_page = $en_slug_to_page[$next_slug];
        $defaults  = $is_pt
            ? breeze_get_destination_default_pt($next_slug)
            : breeze_get_destination_default($next_slug);
        $hero_image = get_the_post_thumbnail_url($next_page->ID, 'full');
        if (!$hero_image) {
            $hero_image = $defaults['hero_image'];
        }
        return array(
            'title'      => $next_page->post_title !== '' ? $next_page->post_title : $defaults['title'],
            'url'        => get_permalink($next_page->ID),
            'hero_image' => $hero_image,
        );
    }

    return null;
}

// ─── Admin: enqueue media picker for destination meta box ───────────────────

function breeze_admin_enqueue_scripts($hook) {
    if (!in_array($hook, array('post.php', 'post-new.php'), true)) {
        return;
    }
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'page') {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_script(
        'breeze-admin-media',
        get_template_directory_uri() . '/assets/js/admin-media.js',
        array('jquery'),
        '1.0',
        true
    );
}
add_action('admin_enqueue_scripts', 'breeze_admin_enqueue_scripts');

// ─── Disable block editor for pages whose templates don't use the_content() ─

function breeze_maybe_disable_block_editor($use_block_editor, $post) {
    if (!$post instanceof WP_Post) {
        return $use_block_editor;
    }
    $template = get_page_template_slug($post->ID);
    $classic_templates = array(
        'page-templates/packages-page.php',
        'page-templates/contact-page.php',
        'page-templates/privacy-page.php',
        'page-templates/home-pt.php',
    );
    if (in_array($template, $classic_templates, true)) {
        return false;
    }
    return $use_block_editor;
}
add_filter('use_block_editor_for_post', 'breeze_maybe_disable_block_editor', 10, 2);

