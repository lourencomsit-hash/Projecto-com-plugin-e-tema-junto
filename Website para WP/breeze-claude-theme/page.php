<?php
get_header();
?>

<main class="privacy-main">
  <?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
      <?php $page_content = trim((string) get_post_field('post_content', get_the_ID())); ?>
      <?php $page_uri = trim((string) get_page_uri(get_the_ID()), '/'); ?>
      <?php $is_package_detail = preg_match('#^packages/#', $page_uri) === 1; ?>
      <?php $is_preformatted_layout = $page_content !== '' && (
        $is_package_detail ||
        stripos($page_content, 'class="privacy-shell') !== false ||
        stripos($page_content, "class='privacy-shell") !== false ||
        stripos($page_content, 'class="package-shell') !== false ||
        stripos($page_content, "class='package-shell") !== false ||
        stripos($page_content, 'class="itinerary-day') !== false ||
        stripos($page_content, "class='itinerary-day") !== false
      ); ?>
      <?php if ($is_preformatted_layout) : ?>
        <?php echo breeze_render_editor_content($page_content); ?>
        <?php continue; ?>
      <?php endif; ?>
      <?php $content_has_h1 = stripos($page_content, '<h1') !== false; ?>
      <section class="privacy-shell">
        <?php if (get_post_field('post_name', get_the_ID()) === 'privacy-policy') : ?>
          <p class="privacy-kicker"><?php esc_html_e('Legal', 'breeze-codex-theme'); ?></p>
        <?php endif; ?>
        <?php if (!$content_has_h1) : ?>
          <h1><?php the_title(); ?></h1>
        <?php endif; ?>
        <div class="entry-content">
          <?php if ($page_content !== '') : ?>
            <?php the_content(); ?>
          <?php endif; ?>
        </div>
      </section>
    <?php endwhile; ?>
  <?php endif; ?>
</main>

<?php
get_footer();
