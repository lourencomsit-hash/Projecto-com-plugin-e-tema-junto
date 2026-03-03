<?php
/*
Template Name: Home PT
*/

get_header();

$defaults = breeze_get_home_defaults_pt();

$hero_video       = $defaults['hero']['video_url'];
$hero_kicker      = $defaults['hero']['kicker'];
$hero_title       = $defaults['hero']['title'];
$hero_subtitle    = $defaults['hero']['subtitle'];
$hero_button_text = $defaults['hero']['button_text'];
$hero_button_url  = $defaults['hero']['button_url'];

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

$pt_contact_url  = breeze_get_page_url_by_slug('pt/contacto', home_url('/pt/contacto/'));
$pt_about_url    = breeze_get_page_url_by_slug('pt/sobre-nos', home_url('/pt/sobre-nos/'));
$pt_packages_url = breeze_get_page_url_by_slug('pt/pacotes', home_url('/pt/pacotes/'));
?>

<main>
  <section class="hero" id="hero">
    <video autoplay muted loop playsinline preload="auto" class="hero-video">
      <source src="<?php echo esc_url($hero_video); ?>" type="video/mp4">
    </video>
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <p class="home-story-kicker home-story-kicker--pt"><?php echo esc_html($hero_kicker); ?></p>
      <p class="hero-sub"><?php echo wp_kses_post($hero_subtitle); ?></p>
      <a href="<?php echo esc_url($hero_button_url); ?>" class="btn"><?php echo esc_html($hero_button_text); ?></a>
    </div>
  </section>

  <section class="packages" id="packages">
    <div class="section-head">
      <p><?php echo esc_html($defaults['packages']['head_kicker']); ?></p>
      <h2><?php echo esc_html($defaults['packages']['head_title']); ?></h2>
      <div class="section-cta">
        <a class="modal-btn modal-btn-secondary" href="<?php echo esc_url($pt_packages_url); ?>">Ver Todos os Pacotes</a>
      </div>
    </div>
    <div class="packages-controls">
      <button type="button" class="packages-arrow" id="packagesPrev" aria-label="Pacotes anteriores">‹</button>
      <button type="button" class="packages-arrow" id="packagesNext" aria-label="Pacotes seguintes">›</button>
    </div>
    <div class="cards packages-carousel" id="packagesCarousel">
      <?php foreach ($package_cards as $index => $card) : ?>
        <article class="card interactive-card" role="button" tabindex="0" data-package="<?php echo esc_attr($card['key']); ?>" aria-label="<?php echo esc_attr('Abrir detalhes de ' . $card['title']); ?>">
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
    <div class="packages-dots" id="packagesDots" aria-label="Navegação do carrossel de pacotes">
      <?php foreach ($package_cards as $index => $card) : ?>
        <button type="button" class="dot <?php echo $index === 0 ? 'is-active' : ''; ?>" data-index="<?php echo esc_attr((string) $index); ?>" aria-label="<?php echo esc_attr('Ir para pacote ' . ($index + 1)); ?>"></button>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="home-story" id="journey-design">
    <div class="home-story-shell">
      <figure class="home-story-media">
        <img src="<?php echo esc_url($home_story_image); ?>" alt="Leão na Tanzânia durante a luz dourada do safari" loading="lazy" decoding="async">
      </figure>
      <div class="home-story-copy">
        <p class="home-story-kicker">Desenhado na Tanzânia</p>
        <h2>Cada Viagem é Construída em Torno do Seu Ritmo</h2>
        <p>Desenhamos cada itinerário em função do seu ritmo de viagem, nível de conforto preferido e os momentos com a vida selvagem que mais valoriza. O resultado é uma viagem que parece calma, intencional e profundamente pessoal.</p>
        <ul class="home-story-points">
          <li>Planeamento de safari privado com cadência realista pelo circuito norte.</li>
          <li>Combinações equilibradas entre parques icónicos, estadias boutique e extensão opcional a Zanzibar.</li>
          <li>Apoio no terreno de uma equipa baseada na Tanzânia, desde o primeiro contacto ao último safari.</li>
        </ul>
        <a class="modal-btn" href="<?php echo esc_url($pt_about_url); ?>">Conhecer a Breeze Safaris</a>
      </div>
    </div>
  </section>

  <section class="parks-showcase" id="parks-showcase">
    <div class="parks-head section-head">
      <div>
        <p><?php echo esc_html($defaults['parks']['head_kicker']); ?></p>
        <h2><?php echo esc_html($defaults['parks']['head_title']); ?></h2>
        <p class="parks-intro"><?php echo esc_html($defaults['parks']['intro']); ?></p>
        <div class="parks-dots" id="parksDots" aria-label="Navegação de destinos">
          <?php foreach ($parks as $index => $park) : ?>
            <button type="button" class="dot <?php echo $index === 0 ? 'is-active' : ''; ?>" data-index="<?php echo esc_attr((string) $index); ?>" aria-label="<?php echo esc_attr('Ir para destino ' . ($index + 1)); ?>"></button>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="parks-controls">
        <button type="button" class="parks-arrow" id="parksPrev" aria-label="Destino anterior">‹</button>
        <button type="button" class="parks-arrow" id="parksNext" aria-label="Destino seguinte">›</button>
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
              <a href="<?php echo esc_url(breeze_get_page_url_by_slug('pt/' . $park['slug'])); ?>">Explorar Destino</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>

<div class="modal-overlay" id="packageModal" aria-hidden="true">
  <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <button class="modal-close" id="modalClose" aria-label="Fechar detalhes">×</button>
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
          <a class="modal-btn" href="<?php echo esc_url(add_query_arg('package', $card['title'], $pt_contact_url)); ?>">Contactar</a>
          <?php if (!empty($card['more_info_url'])) : ?>
            <a class="modal-btn modal-btn-secondary" href="<?php echo esc_url($card['more_info_url']); ?>">Mais Informações</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </template>
<?php endforeach; ?>

<?php get_footer(); ?>
