<?php
get_header();
?>

<main class="privacy-main">
  <section class="privacy-shell">
    <p class="privacy-kicker"><?php esc_html_e('Search', 'breeze-codex-theme'); ?></p>
    <h1>
      <?php
      printf(
          /* translators: %s: search query */
          esc_html__('Results for: %s', 'breeze-codex-theme'),
          '<span>' . esc_html(get_search_query()) . '</span>'
      );
      ?>
    </h1>

    <?php if (have_posts()) : ?>
      <?php while (have_posts()) : the_post(); ?>
        <article class="search-result-item">
          <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
          <p><?php echo esc_html(get_the_excerpt()); ?></p>
        </article>
      <?php endwhile; ?>
    <?php else : ?>
      <p><?php esc_html_e('No results found.', 'breeze-codex-theme'); ?></p>
    <?php endif; ?>
  </section>
</main>

<?php
get_footer();
