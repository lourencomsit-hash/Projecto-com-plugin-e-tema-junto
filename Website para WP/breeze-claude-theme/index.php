<?php
get_header();
?>

<main class="privacy-main">
  <section class="privacy-shell">
    <h1><?php esc_html_e('Latest Posts', 'breeze-codex-theme'); ?></h1>
    <?php if (have_posts()) : ?>
      <?php while (have_posts()) : the_post(); ?>
        <article class="search-result-item">
          <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
          <p><?php echo esc_html(get_the_excerpt()); ?></p>
        </article>
      <?php endwhile; ?>
    <?php else : ?>
      <p><?php esc_html_e('No posts found.', 'breeze-codex-theme'); ?></p>
    <?php endif; ?>
  </section>
</main>

<?php
get_footer();
