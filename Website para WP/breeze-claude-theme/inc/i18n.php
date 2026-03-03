<?php
/**
 * i18n: Language detection, hreflang and translation helpers.
 *
 * Implements EN / pt-PT bilingual support via WordPress page hierarchy.
 * The PT language tree lives under the page with slug "pt" (/pt/).
 * Each page pair (EN ↔ PT) is linked via post meta _breeze_translation_id.
 * PT pages also carry _breeze_lang = 'pt' and _breeze_en_slug (destination pages).
 *
 * @package breeze-codex-theme
 */

if (!defined('ABSPATH')) {
    exit;
}

// ── Language detection ─────────────────────────────────────────────────────────

/**
 * Returns true when the given (or current queried) page is a PT page.
 * Detection is based on the _breeze_lang post meta set during PT seed creation.
 *
 * @param int|null $post_id WP post ID, or null to use current queried object.
 * @return bool
 */
function breeze_is_pt_page($post_id = null) {
    if ($post_id === null) {
        $post_id = (int) get_queried_object_id();
    }
    if (!$post_id) {
        return false;
    }
    return get_post_meta((int) $post_id, '_breeze_lang', true) === 'pt';
}

/**
 * Returns the WP post ID of the root "pt" page (/pt/).
 * Cached in a static variable per request.
 *
 * @return int Post ID, or 0 if not found.
 */
function breeze_get_pt_root_id() {
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    $page = get_page_by_path('pt', OBJECT, 'page');
    $cached = ($page instanceof WP_Post) ? (int) $page->ID : 0;
    return $cached;
}

/**
 * Returns the ID of the linked translation for a given page.
 *
 * @param int    $post_id Source page ID.
 * @param string $dir     'pt' to get PT from EN, 'en' to get EN from PT.
 * @return int Translation post ID, or 0 if none set.
 */
function breeze_get_translation_id($post_id, $dir = 'pt') {
    return (int) get_post_meta((int) $post_id, '_breeze_translation_id', true);
}

// ── hreflang output ───────────────────────────────────────────────────────────

/**
 * Returns EN and PT canonical URLs for the current page.
 *
 * @return array{en_url: string, pt_url: string}
 */
function breeze_get_hreflang_urls() {
    // Front page (EN homepage, is_front_page() === true)
    if (is_front_page()) {
        $en_url = trailingslashit(home_url('/'));
        $pt_root = breeze_get_pt_root_id();
        $pt_url  = $pt_root ? trailingslashit((string) get_permalink($pt_root)) : '';
        return array('en_url' => $en_url, 'pt_url' => $pt_url);
    }

    if (!is_singular()) {
        return array('en_url' => '', 'pt_url' => '');
    }

    $post_id = get_queried_object_id();
    if (!$post_id) {
        return array('en_url' => '', 'pt_url' => '');
    }

    $current_url = trailingslashit((string) get_permalink($post_id));
    $trans_id    = breeze_get_translation_id($post_id);
    $trans_url   = $trans_id ? trailingslashit((string) get_permalink($trans_id)) : '';

    if (breeze_is_pt_page($post_id)) {
        return array('en_url' => $trans_url, 'pt_url' => $current_url);
    } else {
        return array('en_url' => $current_url, 'pt_url' => $trans_url);
    }
}

/**
 * Outputs <link rel="alternate" hreflang> tags into <head>.
 * Hooked at wp_head priority 2 (alongside other SEO meta).
 */
add_action('wp_head', 'breeze_output_hreflang_tags', 2);
function breeze_output_hreflang_tags() {
    $urls = breeze_get_hreflang_urls();

    $en_url = isset($urls['en_url']) ? $urls['en_url'] : '';
    $pt_url = isset($urls['pt_url']) ? $urls['pt_url'] : '';

    if ($en_url) {
        echo '<link rel="alternate" hreflang="en"      href="' . esc_url($en_url) . '">' . "\n";
    }
    if ($pt_url) {
        echo '<link rel="alternate" hreflang="pt-PT"   href="' . esc_url($pt_url) . '">' . "\n";
    }
    // x-default always points to the EN (canonical) URL
    if ($en_url) {
        echo '<link rel="alternate" hreflang="x-default" href="' . esc_url($en_url) . '">' . "\n";
    }
}

// ── Language switcher helper ──────────────────────────────────────────────────

/**
 * Returns the URL to switch from current page to the other language.
 *
 * @param string $current_lang 'en' or 'pt'
 * @return string URL to the alternate language version, or '' if not found.
 */
function breeze_get_lang_switch_url($current_lang = 'en') {
    $urls = breeze_get_hreflang_urls();

    if ($current_lang === 'en') {
        return isset($urls['pt_url']) ? $urls['pt_url'] : trailingslashit(home_url('/pt/'));
    } else {
        return isset($urls['en_url']) ? $urls['en_url'] : trailingslashit(home_url('/'));
    }
}

/**
 * Returns the current page language: 'pt' or 'en'.
 *
 * @return string
 */
function breeze_get_current_lang() {
    if (is_front_page()) {
        return 'en';
    }
    $post_id = get_queried_object_id();
    return ($post_id && breeze_is_pt_page($post_id)) ? 'pt' : 'en';
}
