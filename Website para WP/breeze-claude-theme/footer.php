<?php
$home_defaults = breeze_get_home_defaults();
$footer_defaults = $home_defaults['footer'];

$is_pt = function_exists('breeze_is_pt_page') && breeze_is_pt_page();

$cta_title       = get_theme_mod('breeze_footer_cta_title',        $footer_defaults['cta_title']);
$cta_button_text = get_theme_mod('breeze_footer_cta_button_text',  $footer_defaults['cta_button_text']);
$cta_button_url  = get_theme_mod('breeze_footer_cta_button_url',   $footer_defaults['cta_button_url']);
$brand_text      = get_theme_mod('breeze_footer_brand_text',       $footer_defaults['brand_text']);
$email           = get_theme_mod('breeze_footer_email',            $footer_defaults['email']);
$phone           = get_theme_mod('breeze_footer_phone',            $footer_defaults['phone']);
$location        = get_theme_mod('breeze_footer_location',         $footer_defaults['location']);
$extra_line      = get_theme_mod('breeze_footer_extra_line',       $footer_defaults['extra_line']);
$copyright       = get_theme_mod('breeze_footer_copyright',        $footer_defaults['copyright']);
$tagline         = get_theme_mod('breeze_footer_tagline',          $footer_defaults['tagline']);
$logo_url        = breeze_get_logo_url();
$brand_summary   = wp_trim_words(wp_strip_all_tags((string) $brand_text), 13, '.');

if ($is_pt) {
    $cta_title        = 'A Sua História na Tanzânia Começa Aqui';
    $cta_button_text  = 'Começar a Planear';
    $cta_button_url   = breeze_get_page_url_by_slug('pt/pacotes', home_url('/pt/pacotes/'));
    $contact_url      = breeze_get_page_url_by_slug('pt/contacto', home_url('/pt/contacto/'));
    $col_explore      = 'Explorar';
    $col_destinations = 'Principais Destinos';
    $col_contact      = 'Contacto';
} else {
    $contact_url      = breeze_get_page_url_by_slug('contact', home_url('/contact/'));
    $col_explore      = 'Explore';
    $col_destinations = 'Top Destinations';
    $col_contact      = 'Contact';
}
?>
  <footer class="site-footer" id="contact">
    <div class="footer-shell">
      <div class="footer-hero">
        <div>
          <p class="footer-kicker">
            <img class="footer-brand-logo" src="<?php echo esc_url($logo_url); ?>" alt="<?php esc_attr_e('Breeze Safaris logo', 'breeze-codex-theme'); ?>" loading="lazy" decoding="async">
          </p>
          <h2><?php echo esc_html($cta_title); ?></h2>
        </div>
        <a class="footer-cta" href="<?php echo esc_url($cta_button_url); ?>"><?php echo esc_html($cta_button_text); ?></a>
      </div>
      <div class="footer-grid">
        <div class="footer-col footer-col-brand">
          <p class="footer-brand-text"><?php echo esc_html($brand_summary); ?></p>
          <div class="footer-social">
            <a href="https://www.instagram.com/breeze.safaris" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="5"/><circle cx="17.5" cy="6.5" r=".5" fill="currentColor" stroke="none"/></svg>
            </a>
            <a href="https://www.facebook.com/breeze.safaris" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
            </a>
            <a href="https://wa.me/255749776859" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
              <svg width="20" height="20" viewBox="0 0 16 16" fill="currentColor"><path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/></svg>
            </a>
          </div>
        </div>
        <div class="footer-col">
          <h3><?php echo esc_html($col_explore); ?></h3>
          <?php if ($is_pt) : ?>
            <a href="<?php echo esc_url(home_url('/pt/')); ?>">Início</a>
            <a href="<?php echo esc_url(breeze_get_page_url_by_slug('pt/pacotes', home_url('/pt/pacotes/'))); ?>">Todos os Pacotes</a>
            <a href="<?php echo esc_url(breeze_get_page_url_by_slug('pt/sobre-nos', home_url('/pt/sobre-nos/'))); ?>">Sobre Nós</a>
          <?php elseif (has_nav_menu('footer_explore')) : ?>
            <?php wp_nav_menu(array(
                'theme_location' => 'footer_explore',
                'container'      => false,
                'items_wrap'     => '%3$s',
                'walker'         => new Breeze_Flat_Nav_Walker(),
                'fallback_cb'    => false,
            )); ?>
          <?php else : ?>
            <?php breeze_render_menu_links(breeze_footer_explore_fallback_links()); ?>
          <?php endif; ?>
        </div>
        <div class="footer-col">
          <h3><?php echo esc_html($col_destinations); ?></h3>
          <?php if ($is_pt) : ?>
            <a href="<?php echo esc_url(breeze_get_page_url_by_slug('pt/parque-nacional-serengeti', home_url('/pt/parque-nacional-serengeti/'))); ?>">Serengeti</a>
            <a href="<?php echo esc_url(breeze_get_page_url_by_slug('pt/area-conservacao-ngorongoro', home_url('/pt/area-conservacao-ngorongoro/'))); ?>">Ngorongoro</a>
            <a href="<?php echo esc_url(breeze_get_page_url_by_slug('pt/parque-nacional-tarangire', home_url('/pt/parque-nacional-tarangire/'))); ?>">Tarangire</a>
            <a href="<?php echo esc_url(breeze_get_page_url_by_slug('pt/zanzibar', home_url('/pt/zanzibar/'))); ?>">Zanzibar</a>
          <?php elseif (has_nav_menu('footer_destinations')) : ?>
            <?php wp_nav_menu(array(
                'theme_location' => 'footer_destinations',
                'container'      => false,
                'items_wrap'     => '%3$s',
                'walker'         => new Breeze_Flat_Nav_Walker(),
                'fallback_cb'    => false,
            )); ?>
          <?php else : ?>
            <?php breeze_render_menu_links(breeze_footer_destinations_fallback_links()); ?>
          <?php endif; ?>
        </div>
        <div class="footer-col">
          <h3><a href="<?php echo esc_url($contact_url); ?>"><?php echo esc_html($col_contact); ?></a></h3>
          <?php if (!empty($email)) : ?>
            <a href="<?php echo esc_url('mailto:' . sanitize_email($email)); ?>"><?php echo esc_html($email); ?></a>
          <?php endif; ?>
          <?php if (!empty($phone)) : ?>
            <a href="<?php echo esc_url('tel:' . preg_replace('/[^0-9\+]/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a>
          <?php endif; ?>
          <?php if (!empty($location)) : ?>
            <p><?php echo esc_html($location); ?></p>
          <?php endif; ?>
          <?php if (!empty($extra_line)) : ?>
            <p><?php echo esc_html($extra_line); ?></p>
          <?php endif; ?>
        </div>
      </div>
      <div class="footer-bottom">
        <p><?php echo esc_html($copyright); ?></p>
        <p><?php echo esc_html($tagline); ?></p>
      </div>
    </div>
  </footer>
<?php wp_footer(); ?>
</body>
</html>
