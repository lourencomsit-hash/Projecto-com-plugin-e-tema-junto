<?php
/*
Template Name: Destination Page
*/

get_header();

if (have_posts()) :
    while (have_posts()) :
        the_post();
        $raw_content = trim((string) get_post_field('post_content', get_the_ID()));
        if ($raw_content !== '') {
            echo breeze_render_editor_content($raw_content);
            continue;
        }
        $data       = breeze_get_destination_view_data(get_the_ID());
        $next_dest  = breeze_get_next_destination(get_the_ID());
        $is_pt_dest = function_exists('breeze_is_pt_page') && breeze_is_pt_page(get_the_ID());
        ?>
        <main class="destination-main<?php echo $next_dest ? ' has-next-dest' : ''; ?>">
          <section class="destination-hero" id="hero" style="background-image:url('<?php echo esc_url($data['hero_image']); ?>')">
            <div class="destination-hero-inner">
              <p class="destination-eyebrow"><?php echo esc_html($data['hero_eyebrow']); ?></p>
              <h1><?php echo esc_html($data['title']); ?></h1>
              <p><?php echo esc_html($data['hero_subtitle']); ?></p>
            </div>
          </section>

          <section class="destination-intro section-shell">
            <div class="intro-copy">
              <?php if ($data['has_content']) : ?>
                <?php the_content(); ?>
              <?php else : ?>
                <?php foreach ($data['intro_paragraphs'] as $paragraph) : ?>
                  <p><?php echo esc_html($paragraph); ?></p>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
            <?php if (!empty($data['facts'])) : ?>
              <aside class="facts-card">
                <h2><?php echo $is_pt_dest ? 'Em Resumo' : esc_html__('At a Glance', 'breeze-codex-theme'); ?></h2>
                <ul>
                  <?php foreach ($data['facts'] as $fact) : ?>
                    <li><?php echo esc_html($fact); ?></li>
                  <?php endforeach; ?>
                </ul>
              </aside>
            <?php endif; ?>
          </section>

          <?php if (!empty($data['stories'])) : ?>
            <section class="section-shell">
              <h2 class="section-title"><?php echo esc_html($data['story_title']); ?></h2>
              <div class="story-grid">
                <?php foreach ($data['stories'] as $story) : ?>
                  <article class="story-card">
                    <h3><?php echo esc_html($story['title']); ?></h3>
                    <p><?php echo esc_html($story['text']); ?></p>
                  </article>
                <?php endforeach; ?>
              </div>
            </section>
          <?php endif; ?>

          <?php if (!empty($data['gallery'])) : ?>
            <section class="section-shell">
              <h2 class="section-title"><?php echo esc_html($data['gallery_title']); ?></h2>
              <div class="photo-grid">
                <?php foreach ($data['gallery'] as $photo) : ?>
                  <figure class="<?php echo esc_attr($photo['class']); ?>">
                    <img src="<?php echo esc_url($photo['url']); ?>" alt="<?php echo esc_attr($photo['alt']); ?>" loading="lazy" decoding="async">
                  </figure>
                <?php endforeach; ?>
              </div>
            </section>
          <?php endif; ?>

          <section class="section-shell">
            <div class="cta-band">
              <div>
                <h2><?php echo esc_html($data['cta_title']); ?></h2>
                <p><?php echo esc_html($data['cta_text']); ?></p>
              </div>
              <?php
              $contact_base = $is_pt_dest
                  ? trailingslashit(home_url('/pt/contacto'))
                  : breeze_get_page_url_by_slug('contact', home_url('/contact/'));
              $prefill = $is_pt_dest
                  ? 'Tenho interesse num safari para ' . $data['title'] . '.'
                  : 'I am interested in a safari to ' . $data['title'] . '.';
              $contact_url = add_query_arg('contact_message', $prefill, $contact_base);
              ?>
              <a href="<?php echo esc_url($contact_url); ?>"><?php echo esc_html($data['cta_button_text']); ?></a>
            </div>
          </section>

          <?php if ($next_dest) : ?>
          <section class="next-dest-teaser">
            <a href="<?php echo esc_url($next_dest['url']); ?>" class="next-dest-link" style="background-image:url('<?php echo esc_url($next_dest['hero_image']); ?>')">
              <div class="next-dest-inner">
                <span class="next-dest-label"><?php echo $is_pt_dest ? 'Explorar a Seguir' : 'Explore Next'; ?></span>
                <p class="next-dest-name"><?php echo esc_html($next_dest['title']); ?></p>
                <span class="next-dest-arrow" aria-hidden="true">&#8594;</span>
              </div>
            </a>
          </section>
          <?php endif; ?>

        </main>
        <?php
    endwhile;
endif;

get_footer();
