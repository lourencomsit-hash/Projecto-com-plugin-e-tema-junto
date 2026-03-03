<?php
get_header();

$defaults = breeze_get_home_defaults();

$hero_video = get_theme_mod('breeze_home_hero_video_url', $defaults['hero']['video_url']);
$hero_kicker = get_theme_mod('breeze_home_hero_kicker', $defaults['hero']['kicker']);
$hero_title = get_theme_mod('breeze_home_hero_title', $defaults['hero']['title']);
$hero_subtitle = get_theme_mod('breeze_home_hero_subtitle', $defaults['hero']['subtitle']);
$hero_button_text = get_theme_mod('breeze_home_hero_button_text', $defaults['hero']['button_text']);
$hero_button_url = get_theme_mod('breeze_home_hero_button_url', $defaults['hero']['button_url']);

$package_cards = $defaults['packages']['cards'];
foreach ($package_cards as $index => $card) {
    $override_image = breeze_get_theme_image('breeze_home_package_' . ($index + 1) . '_image', '');
    if (!empty($override_image)) {
        $package_cards[$index]['image'] = $override_image;
    }
}

$parks = $defaults['parks']['slides'];
foreach ($parks as $index => $park) {
    $override_image = breeze_get_theme_image('breeze_home_park_' . ($index + 1) . '_image', '');
    if (!empty($override_image)) {
        $parks[$index]['image'] = $override_image;
    }
}

$home_story_image = breeze_get_theme_image('breeze_home_story_image', breeze_theme_media_url('photos/lion.jpg'));
?>

<main>
  <section class="hero" id="hero">
    <video autoplay muted loop playsinline preload="auto" class="hero-video">
      <source src="<?php echo esc_url($hero_video); ?>" type="video/mp4">
    </video>
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <p class="home-story-kicker"><?php echo esc_html($hero_kicker); ?></p>
      <p class="hero-sub"><?php echo wp_kses_post($hero_subtitle); ?></p>
      <a href="<?php echo esc_url($hero_button_url); ?>" class="btn"><?php echo esc_html($hero_button_text); ?></a>
    </div>
  </section>

  <section class="packages" id="packages">
    <div class="section-head">
      <p><?php echo esc_html($defaults['packages']['head_kicker']); ?></p>
      <h2><?php echo esc_html($defaults['packages']['head_title']); ?></h2>
      <div class="section-cta">
        <a class="modal-btn modal-btn-secondary" href="<?php echo esc_url(breeze_get_page_url_by_slug('packages', home_url('/packages/'))); ?>"><?php esc_html_e('View All Packages', 'breeze-codex-theme'); ?></a>
      </div>
    </div>
    <div class="packages-controls">
      <button type="button" class="packages-arrow" id="packagesPrev" aria-label="<?php esc_attr_e('Previous packages', 'breeze-codex-theme'); ?>">‹</button>
      <button type="button" class="packages-arrow" id="packagesNext" aria-label="<?php esc_attr_e('Next packages', 'breeze-codex-theme'); ?>">›</button>
    </div>
    <div class="cards packages-carousel" id="packagesCarousel">
      <?php foreach ($package_cards as $index => $card) : ?>
        <article class="card interactive-card" role="button" tabindex="0" data-package="<?php echo esc_attr($card['key']); ?>" aria-label="<?php echo esc_attr('Open ' . $card['title'] . ' details'); ?>">
          <img src="<?php echo esc_url($card['image']); ?>" alt="<?php echo esc_attr($card['alt']); ?>"
            <?php if ($index === 0) : ?>fetchpriority="high"<?php else : ?>loading="lazy" decoding="async"<?php endif; ?>>
          <div class="card-content">
            <p class="card-label"><?php echo esc_html($card['label']); ?></p>
            <h3><?php echo esc_html($card['title']); ?></h3>
            <p><?php echo esc_html($card['text']); ?></p>
            <?php if (!empty($card['price'])) : ?>
              <p class="card-price"><?php echo wp_kses_post($card['price']); ?></p>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <div class="packages-dots" id="packagesDots" aria-label="<?php esc_attr_e('Packages slider navigation', 'breeze-codex-theme'); ?>">
      <?php foreach ($package_cards as $index => $card) : ?>
        <button type="button" class="dot <?php echo $index === 0 ? 'is-active' : ''; ?>" data-index="<?php echo esc_attr((string) $index); ?>" aria-label="<?php echo esc_attr('Go to package ' . ($index + 1)); ?>"></button>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="home-story" id="journey-design">
    <div class="home-story-shell">
      <figure class="home-story-media">
        <img src="<?php echo esc_url($home_story_image); ?>" alt="<?php esc_attr_e('Lion in Tanzania during golden-hour safari light', 'breeze-codex-theme'); ?>" loading="lazy" decoding="async">
      </figure>
      <div class="home-story-copy">
        <p class="home-story-kicker"><?php esc_html_e('Designed in Tanzania', 'breeze-codex-theme'); ?></p>
        <h2><?php esc_html_e('Every Journey Is Built Around Your Rhythm', 'breeze-codex-theme'); ?></h2>
        <p><?php esc_html_e('We design each itinerary around your travel pace, preferred comfort level and the wildlife moments you care about most. The result is a trip that feels calm, intentional and deeply personal.', 'breeze-codex-theme'); ?></p>
        <ul class="home-story-points">
          <li><?php esc_html_e('Private safari routing with realistic pacing across the northern circuit.', 'breeze-codex-theme'); ?></li>
          <li><?php esc_html_e('Balanced combinations of iconic parks, boutique stays and optional Zanzibar finale.', 'breeze-codex-theme'); ?></li>
          <li><?php esc_html_e('On-ground support from a Tanzania-based team from first inquiry to return flight.', 'breeze-codex-theme'); ?></li>
        </ul>
        <a class="modal-btn" href="<?php echo esc_url(breeze_get_page_url_by_slug('about', home_url('/about/'))); ?>"><?php esc_html_e('Meet Breeze Safaris', 'breeze-codex-theme'); ?></a>
      </div>
    </div>
  </section>

  <section class="parks-showcase" id="parks-showcase">
    <div class="parks-head section-head">
      <div>
        <p><?php echo esc_html($defaults['parks']['head_kicker']); ?></p>
        <h2><?php echo esc_html($defaults['parks']['head_title']); ?></h2>
        <p class="parks-intro"><?php echo esc_html($defaults['parks']['intro']); ?></p>
        <div class="parks-dots" id="parksDots" aria-label="<?php esc_attr_e('Places slider navigation', 'breeze-codex-theme'); ?>">
          <?php foreach ($parks as $index => $park) : ?>
            <button type="button" class="dot <?php echo $index === 0 ? 'is-active' : ''; ?>" data-index="<?php echo esc_attr((string) $index); ?>" aria-label="<?php echo esc_attr('Go to slide ' . ($index + 1)); ?>"></button>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="parks-controls">
        <button type="button" class="parks-arrow" id="parksPrev" aria-label="<?php esc_attr_e('Previous places', 'breeze-codex-theme'); ?>">‹</button>
        <button type="button" class="parks-arrow" id="parksNext" aria-label="<?php esc_attr_e('Next places', 'breeze-codex-theme'); ?>">›</button>
      </div>
    </div>
    <div class="parks-carousel-wrap">
      <div class="parks-carousel" id="parksCarousel">
        <?php foreach ($parks as $park) : ?>
          <article class="park-slide">
            <img src="<?php echo esc_url($park['image']); ?>" alt="<?php echo esc_attr($park['alt']); ?>" loading="lazy" decoding="async">
            <div class="park-slide-content">
              <p class="park-tag"><?php echo esc_html($park['tag']); ?></p>
              <h3><?php echo esc_html($park['title']); ?></h3>
              <p><?php echo esc_html($park['text']); ?></p>
              <a href="<?php echo esc_url(breeze_get_page_url_by_slug($park['slug'])); ?>"><?php esc_html_e('Explore Place', 'breeze-codex-theme'); ?></a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>

<div class="modal-overlay" id="packageModal" aria-hidden="true">
  <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <button class="modal-close" id="modalClose" aria-label="<?php esc_attr_e('Close details', 'breeze-codex-theme'); ?>">×</button>
    <div id="modalContent"></div>
  </div>
</div>

<?php foreach ($package_cards as $card) : ?>
  <template id="tpl-<?php echo esc_attr($card['key']); ?>">
    <div class="modal-grid">
      <img src="<?php echo esc_url($card['image']); ?>" alt="<?php echo esc_attr($card['modal_image_alt'] ?? $card['title']); ?>" loading="lazy" decoding="async">
      <div class="modal-copy">
        <p class="modal-kicker"><?php echo esc_html($card['label']); ?></p>
        <h3 id="modalTitle"><?php echo esc_html($card['title']); ?></h3>
        <ul>
          <?php foreach ($card['modal_points'] as $point) : ?>
            <li><?php echo esc_html($point); ?></li>
          <?php endforeach; ?>
        </ul>
        <?php if (!empty($card['modal_price'])) : ?>
          <p class="modal-price"><?php echo wp_kses_post($card['modal_price']); ?></p>
        <?php endif; ?>
        <div class="modal-actions">
          <a class="modal-btn" href="<?php echo esc_url(add_query_arg('package', $card['title'], breeze_get_page_url_by_slug('contact', home_url('/contact/')))); ?>"><?php esc_html_e('Inquire', 'breeze-codex-theme'); ?></a>
          <?php if (!empty($card['more_info_url'])) : ?>
            <a class="modal-btn modal-btn-secondary" href="<?php echo esc_url($card['more_info_url']); ?>"><?php esc_html_e('More Info', 'breeze-codex-theme'); ?></a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </template>
<?php endforeach; ?>

<?php get_footer(); ?>
