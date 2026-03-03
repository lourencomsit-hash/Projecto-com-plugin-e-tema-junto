<?php
/**
 * Custom XML Sitemaps — EN and PT language variants.
 *
 * Registers rewrite rules for:
 *   /sitemap.xml        → sitemap index (links to EN + PT sitemaps)
 *   /sitemap-en.xml     → all EN pages
 *   /sitemap-pt.xml     → all PT pages
 *
 * Note: WordPress core generates /wp-sitemap.xml automatically.
 * These custom sitemaps are separate and language-specific.
 *
 * @package breeze-codex-theme
 */

if (!defined('ABSPATH')) {
    exit;
}

// If Breeze SEO plugin is active it handles sitemaps — skip theme implementation.
if (class_exists('Breeze_SEO_Sitemap')) {
    return;
}

// ── Direct URI interception (bypasses rewrite rules entirely) ─────────────────
// Runs at init priority 1, before WP routing resolves anything.
// More reliable than rewrite rules which require a DB flush to take effect.

add_action('init', 'breeze_sitemap_intercept_uri', 1);
function breeze_sitemap_intercept_uri() {
    if (!isset($_SERVER['REQUEST_URI'])) {
        return;
    }

    $request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (!$request_path) {
        return;
    }

    // Strip the home path prefix (for WP installs in a subdirectory)
    $home_path = rtrim(parse_url(get_option('home'), PHP_URL_PATH), '/');
    if ($home_path && strpos($request_path, $home_path) === 0) {
        $request_path = substr($request_path, strlen($home_path));
    }
    $request_path = trim($request_path, '/');

    if ($request_path === 'sitemap.xml') {
        $type = 'index';
    } elseif ($request_path === 'sitemap-en.xml') {
        $type = 'en';
    } elseif ($request_path === 'sitemap-pt.xml') {
        $type = 'pt';
    } else {
        return;
    }

    // Discard any output WP or PHP files may have buffered (BOMs, whitespace, etc.)
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: application/xml; charset=UTF-8');
    header('X-Robots-Tag: noindex');

    if ($type === 'index') {
        breeze_output_sitemap_index();
    } elseif ($type === 'en') {
        breeze_output_sitemap_lang('en');
    } elseif ($type === 'pt') {
        breeze_output_sitemap_lang('pt');
    }

    exit;
}

// ── Sitemap index ──────────────────────────────────────────────────────────────

function breeze_output_sitemap_index() {
    $base     = trailingslashit(home_url('/'));
    $modified = date('Y-m-d');

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    echo '  <sitemap>' . "\n";
    echo '    <loc>' . esc_url($base . 'sitemap-en.xml') . '</loc>' . "\n";
    echo '    <lastmod>' . esc_html($modified) . '</lastmod>' . "\n";
    echo '  </sitemap>' . "\n";
    echo '  <sitemap>' . "\n";
    echo '    <loc>' . esc_url($base . 'sitemap-pt.xml') . '</loc>' . "\n";
    echo '    <lastmod>' . esc_html($modified) . '</lastmod>' . "\n";
    echo '  </sitemap>' . "\n";
    echo '</sitemapindex>' . "\n";
}

// ── Language sitemap ───────────────────────────────────────────────────────────

function breeze_output_sitemap_lang($lang) {
    $urls = ($lang === 'pt') ? breeze_sitemap_get_pt_urls() : breeze_sitemap_get_en_urls();

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    foreach ($urls as $entry) {
        $loc        = isset($entry['loc'])        ? $entry['loc']        : '';
        $lastmod    = isset($entry['lastmod'])    ? $entry['lastmod']    : date('Y-m-d');
        $changefreq = isset($entry['changefreq']) ? $entry['changefreq'] : 'monthly';
        $priority   = isset($entry['priority'])   ? $entry['priority']   : '0.7';

        if (!$loc) {
            continue;
        }

        echo '  <url>' . "\n";
        echo '    <loc>' . esc_url($loc) . '</loc>' . "\n";
        echo '    <lastmod>' . esc_html($lastmod) . '</lastmod>' . "\n";
        echo '    <changefreq>' . esc_html($changefreq) . '</changefreq>' . "\n";
        echo '    <priority>' . esc_html($priority) . '</priority>' . "\n";
        echo '  </url>' . "\n";
    }

    echo '</urlset>' . "\n";
}

// ── EN URL list ────────────────────────────────────────────────────────────────

function breeze_sitemap_get_en_urls() {
    $urls    = array();
    $home    = trailingslashit(home_url('/'));
    $today   = date('Y-m-d');

    // Homepage
    $urls[] = array('loc' => $home, 'priority' => '1.0', 'changefreq' => 'weekly', 'lastmod' => $today);

    // Destination pages (EN only — no _breeze_lang meta)
    $dest_slugs = array(
        'serengeti-national-park',
        'ngorongoro-conservation-area',
        'tarangire-national-park',
        'lake-manyara-national-park',
        'arusha-national-park',
        'ndutu-area',
        'zanzibar',
    );
    foreach ($dest_slugs as $slug) {
        $page = get_page_by_path($slug, OBJECT, 'page');
        if (!($page instanceof WP_Post)) {
            continue;
        }
        // Skip PT pages (shouldn't appear in EN sitemap)
        if (get_post_meta($page->ID, '_breeze_lang', true) === 'pt') {
            continue;
        }
        $urls[] = array(
            'loc'        => trailingslashit((string) get_permalink($page)),
            'priority'   => '0.9',
            'changefreq' => 'monthly',
            'lastmod'    => date('Y-m-d', strtotime($page->post_modified)),
        );
    }

    // Packages listing
    $pkg_page = get_page_by_path('packages', OBJECT, 'page');
    if ($pkg_page instanceof WP_Post && get_post_meta($pkg_page->ID, '_breeze_lang', true) !== 'pt') {
        $urls[] = array(
            'loc'        => trailingslashit((string) get_permalink($pkg_page)),
            'priority'   => '0.8',
            'changefreq' => 'monthly',
            'lastmod'    => date('Y-m-d', strtotime($pkg_page->post_modified)),
        );
    }

    // Package detail pages (children of EN 'packages')
    $pkg_slugs = array(
        'ndutu-calving-season-safari',
        'luxury-migration-big-five-safari-8-day',
        'tanzania-zanzibar-honeymoon-9-day',
    );
    foreach ($pkg_slugs as $slug) {
        $page = get_page_by_path('packages/' . $slug, OBJECT, 'page');
        if (!($page instanceof WP_Post)) {
            $page = get_page_by_path($slug, OBJECT, 'page');
        }
        if (!($page instanceof WP_Post)) {
            continue;
        }
        if (get_post_meta($page->ID, '_breeze_lang', true) === 'pt') {
            continue;
        }
        $urls[] = array(
            'loc'        => trailingslashit((string) get_permalink($page)),
            'priority'   => '0.8',
            'changefreq' => 'monthly',
            'lastmod'    => date('Y-m-d', strtotime($page->post_modified)),
        );
    }

    // Named pages
    $named = array(
        'about'   => array('priority' => '0.7', 'changefreq' => 'monthly'),
        'contact' => array('priority' => '0.7', 'changefreq' => 'monthly'),
        // privacy-policy excluded (noindex)
    );
    foreach ($named as $slug => $meta) {
        $page = get_page_by_path($slug, OBJECT, 'page');
        if (!($page instanceof WP_Post)) {
            continue;
        }
        if (get_post_meta($page->ID, '_breeze_lang', true) === 'pt') {
            continue;
        }
        $urls[] = array(
            'loc'        => trailingslashit((string) get_permalink($page)),
            'priority'   => $meta['priority'],
            'changefreq' => $meta['changefreq'],
            'lastmod'    => date('Y-m-d', strtotime($page->post_modified)),
        );
    }

    return $urls;
}

// ── PT URL list ────────────────────────────────────────────────────────────────

function breeze_sitemap_get_pt_urls() {
    $urls  = array();
    $today = date('Y-m-d');

    // PT homepage (/pt/)
    $pt_home = get_page_by_path('pt', OBJECT, 'page');
    if ($pt_home instanceof WP_Post) {
        $urls[] = array(
            'loc'        => trailingslashit((string) get_permalink($pt_home)),
            'priority'   => '1.0',
            'changefreq' => 'weekly',
            'lastmod'    => date('Y-m-d', strtotime($pt_home->post_modified)),
        );
    }

    // PT destination pages (children of pt, with destination template)
    $pt_dest_slugs = array(
        'parque-nacional-serengeti',
        'area-conservacao-ngorongoro',
        'parque-nacional-tarangire',
        'parque-nacional-lago-manyara',
        'parque-nacional-arusha',
        'area-ndutu',
        'zanzibar',
    );
    foreach ($pt_dest_slugs as $slug) {
        $page = get_page_by_path('pt/' . $slug, OBJECT, 'page');
        if (!($page instanceof WP_Post)) {
            continue;
        }
        $urls[] = array(
            'loc'        => trailingslashit((string) get_permalink($page)),
            'priority'   => '0.9',
            'changefreq' => 'monthly',
            'lastmod'    => date('Y-m-d', strtotime($page->post_modified)),
        );
    }

    // PT packages listing
    $pacotes_page = get_page_by_path('pt/pacotes', OBJECT, 'page');
    if ($pacotes_page instanceof WP_Post) {
        $urls[] = array(
            'loc'        => trailingslashit((string) get_permalink($pacotes_page)),
            'priority'   => '0.8',
            'changefreq' => 'monthly',
            'lastmod'    => date('Y-m-d', strtotime($pacotes_page->post_modified)),
        );
    }

    // PT package detail pages
    $pt_pkg_slugs = array(
        'safari-migracao-ndutu-6-dias',
        'safari-luxo-big-five-8-dias',
        'lua-de-mel-tanzania-zanzibar-9-dias',
    );
    foreach ($pt_pkg_slugs as $slug) {
        $page = get_page_by_path('pt/pacotes/' . $slug, OBJECT, 'page');
        if (!($page instanceof WP_Post)) {
            continue;
        }
        $urls[] = array(
            'loc'        => trailingslashit((string) get_permalink($page)),
            'priority'   => '0.8',
            'changefreq' => 'monthly',
            'lastmod'    => date('Y-m-d', strtotime($page->post_modified)),
        );
    }

    // PT named pages (about, contact — exclude privacy-policy PT since noindex)
    $pt_named_slugs = array(
        'pt/sobre-nos' => array('priority' => '0.7', 'changefreq' => 'monthly'),
        'pt/contacto'  => array('priority' => '0.7', 'changefreq' => 'monthly'),
    );
    foreach ($pt_named_slugs as $path => $meta) {
        $page = get_page_by_path($path, OBJECT, 'page');
        if (!($page instanceof WP_Post)) {
            continue;
        }
        $urls[] = array(
            'loc'        => trailingslashit((string) get_permalink($page)),
            'priority'   => $meta['priority'],
            'changefreq' => $meta['changefreq'],
            'lastmod'    => date('Y-m-d', strtotime($page->post_modified)),
        );
    }

    return $urls;
}

