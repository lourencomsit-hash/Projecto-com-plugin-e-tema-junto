<?php
get_header();
?>

<main class="privacy-main">
  <section class="privacy-shell">
    <p class="privacy-kicker"><?php esc_html_e('Error', 'breeze-codex-theme'); ?></p>
    <h1><?php esc_html_e('Page Not Found', 'breeze-codex-theme'); ?></h1>
    <p><?php esc_html_e('The page you tried to open does not exist or has moved.', 'breeze-codex-theme'); ?></p>
    <p><a class="btn" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Back to Homepage', 'breeze-codex-theme'); ?></a></p>
  </section>
</main>

<?php
get_footer();
