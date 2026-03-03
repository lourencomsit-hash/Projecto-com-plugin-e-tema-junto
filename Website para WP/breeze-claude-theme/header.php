<?php
$logo_url = breeze_get_logo_url();
$header_classes = array('site-header');
$is_destination_template = is_page_template('page-templates/destination-page.php');
$is_home_pt_template     = is_page_template('page-templates/home-pt.php');
if (!is_front_page() && !$is_destination_template && !$is_home_pt_template) {
    $header_classes[] = 'is-solid';
}
$is_pt = function_exists('breeze_is_pt_page') && breeze_is_pt_page();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
  <header class="<?php echo esc_attr(implode(' ', $header_classes)); ?>" id="siteHeader">
    <a class="brand" href="<?php echo esc_url($is_pt ? home_url('/pt/') : home_url('/')); ?>">
      <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?> logo" fetchpriority="high" decoding="async">
    </a>
    <button class="nav-toggle" id="navToggle" type="button" aria-label="<?php echo $is_pt ? 'Abrir menu de navegação' : 'Open navigation menu'; ?>" aria-expanded="false" aria-controls="siteNav">
      <span></span>
      <span></span>
      <span></span>
    </button>
    <nav id="siteNav" class="site-nav">
      <div class="nav-search" id="navSearch">
        <button class="nav-search-toggle" aria-label="<?php echo $is_pt ? 'Pesquisar' : 'Search site'; ?>"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></button>
        <input class="nav-search-input" type="search" placeholder="<?php echo $is_pt ? 'Pesquisar…' : 'Search…'; ?>" aria-label="<?php echo $is_pt ? 'Pesquisar' : 'Search site'; ?>">
      </div>
      <?php if ($is_pt) : ?>
        <a href="<?php echo esc_url(breeze_get_page_url_by_slug('pt/pacotes', home_url('/pt/pacotes/'))); ?>">Todos os Pacotes</a>
        <a href="<?php echo esc_url(home_url('/pt/#parks-showcase')); ?>">Descobrir Destinos</a>
        <a class="nav-btn nav-btn-inquire" href="<?php echo esc_url(breeze_get_page_url_by_slug('pt/contacto', home_url('/pt/contacto/'))); ?>">Contactar</a>
      <?php else : ?>
        <a href="<?php echo esc_url(breeze_get_page_url_by_slug('packages', home_url('/packages/'))); ?>">All Packages</a>
        <a href="<?php echo esc_url(home_url('/#parks-showcase')); ?>">Discover Places</a>
        <a class="nav-btn nav-btn-inquire" href="<?php echo esc_url(breeze_get_page_url_by_slug('contact', home_url('/contact/'))); ?>"><?php esc_html_e('Inquire', 'breeze-codex-theme'); ?></a>
      <?php endif; ?>
    </nav>
  </header>
