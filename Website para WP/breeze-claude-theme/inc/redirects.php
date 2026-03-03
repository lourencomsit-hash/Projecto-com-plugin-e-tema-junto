<?php
/**
 * REMOVIDO: gerido pelo plugin Breeze Safaris SEO (plugin-seo-breeze)
 *
 * Todo o código de redirects foi migrado para o plugin. O backup do código original
 * está disponível em inc/redirects.php.bak
 *
 * Funcionalidades migradas:
 *  - Rewrite rule para /pt → pagename=pt
 *  - Flush rewrite rules na ativação do tema
 *  - Injeção de R=301 para /pt → /pt/ no .htaccess
 *  - Mapa de redirects 301: /project/*, /tanzania-safari-experts/, etc.
 *
 * Os redirects foram importados para a tabela {prefix}breeze_redirects da base de dados.
 *
 * @package breeze-claude-theme
 * @see plugin-seo-breeze/includes/class-redirects.php
 */

if (!defined('ABSPATH')) {
    exit;
}

// Manter o rewrite rule do /pt para que as páginas PT continuem a funcionar.
// Este rewrite NÃO é SEO — é necessário para o routing do WordPress.
add_action('init', function () {
    add_rewrite_rule('^pt$', 'index.php?pagename=pt', 'top');
});

add_action('after_switch_theme', function () {
    flush_rewrite_rules();
});
