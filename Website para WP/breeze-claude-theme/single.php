<?php
get_header();
?>

<main class="privacy-main">
  <?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
      <article class="privacy-shell">
        <h1><?php the_title(); ?></h1>
        <p><strong><?php echo esc_html(get_the_date()); ?></strong></p>
        <div class="entry-content">
          <?php the_content(); ?>
        </div>
      </article>
    <?php endwhile; ?>
  <?php endif; ?>
</main>

<?php
get_footer();
