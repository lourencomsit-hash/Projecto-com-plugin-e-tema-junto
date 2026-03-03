<?php
/**
 * Template Name: Contact Page
 *
 * @package breeze-codex-theme
 */

if (!defined('ABSPATH')) {
    exit;
}

// ── Process form before any output ────────────────────────────────────────────
$sent  = false;
$error = false;

if (
    isset($_POST['breeze_contact_nonce']) &&
    wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['breeze_contact_nonce'])), 'breeze_contact')
) {
    $name    = sanitize_text_field(wp_unslash($_POST['contact_name'] ?? ''));
    $email   = sanitize_email(wp_unslash($_POST['contact_email'] ?? ''));
    $package = sanitize_text_field(wp_unslash($_POST['contact_package'] ?? ''));
    $persons = sanitize_text_field(wp_unslash($_POST['contact_persons'] ?? ''));
    $message = sanitize_textarea_field(wp_unslash($_POST['contact_message'] ?? ''));

    breeze_contact_log("Form submitted — name='{$name}' email='{$email}' package='{$package}'");

    if (empty($name) || empty($email) || !is_email($email)) {
        breeze_contact_log('Validation FAILED');
        $error = true;
    } else {
        $to      = 'info@breezesafaris.com';
        $subject = sprintf('Safari Inquiry from %s', $name);

        $body  = "Name: {$name}\n";
        $body .= "Email: {$email}\n";
        if (!empty($package)) {
            $body .= "Package: {$package}\n";
        }
        if (!empty($persons)) {
            $body .= "Persons: {$persons}\n";
        }
        if (!empty($message)) {
            $body .= "\nMessage:\n{$message}\n";
        }

        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            sprintf('Reply-To: %s <%s>', $name, $email),
        );

        $mail_sent = wp_mail($to, $subject, $body, $headers);
        breeze_contact_log('wp_mail() result: ' . ($mail_sent ? 'SUCCESS' : 'FAILED'));
        $sent = true;
    }
} elseif (isset($_GET['sent']) && $_GET['sent'] === '1') {
    // Fallback: support redirect-based flow
    $sent = true;
} elseif (isset($_GET['error']) && $_GET['error'] === '1') {
    $error = true;
}

$is_pt_page = function_exists('breeze_is_pt_page') && is_page() && breeze_is_pt_page();

$packages_url = $is_pt_page
    ? breeze_get_page_url_by_slug('pt/pacotes', home_url('/pt/pacotes/'))
    : breeze_get_page_url_by_slug('packages', home_url('/packages/'));

// Pre-fill values (from GET params for Inquire links, or POST on error)
$pre_package = '';
$pre_persons = '';
if ($error) {
    $pre_package = sanitize_text_field(wp_unslash($_POST['contact_package'] ?? ''));
    $pre_persons = sanitize_text_field(wp_unslash($_POST['contact_persons'] ?? ''));
} else {
    $pre_package = sanitize_text_field(wp_unslash($_GET['package'] ?? ''));
    $pre_persons = sanitize_text_field(wp_unslash($_GET['persons'] ?? ''));
}

get_header();
?>

<main class="contact-main">
  <section class="contact-shell">

    <header class="contact-page-header">
      <div>
        <p class="privacy-kicker">Breeze Safaris</p>
        <h1><?php echo $is_pt_page ? 'Planear o Seu Safari' : esc_html__('Plan Your Safari', 'breeze-codex-theme'); ?></h1>
        <p class="contact-intro-text"><?php echo $is_pt_page ? 'Conte-nos sobre a sua viagem de sonho e entraremos em contacto dentro de 24 horas.' : esc_html__('Tell us about your dream journey and we\'ll be in touch within 24 hours.', 'breeze-codex-theme'); ?></p>
      </div>
      <a class="modal-btn modal-btn-secondary" href="<?php echo esc_url($packages_url); ?>">
        <?php echo $is_pt_page ? 'Explorar Todos os Pacotes' : esc_html__('Explore All Packages', 'breeze-codex-theme'); ?>
      </a>
    </header>

    <div class="contact-grid">

      <div class="contact-form-wrap">

        <?php if ($sent) : ?>
          <div class="contact-notice contact-notice--success" role="alert">
            <?php if ($is_pt_page) : ?>
              <strong>Mensagem enviada!</strong> Obrigado pelo contacto. Responderemos dentro de 24 horas.
            <?php else : ?>
              <strong><?php esc_html_e('Message sent!', 'breeze-codex-theme'); ?></strong>
              <?php esc_html_e(' Thank you for reaching out. We\'ll reply within 24 hours.', 'breeze-codex-theme'); ?>
            <?php endif; ?>
          </div>
        <?php else : ?>

          <?php if ($error) : ?>
          <div class="contact-notice contact-notice--error" role="alert">
            <?php echo $is_pt_page ? 'Por favor preencha o seu nome e um endereço de email válido.' : esc_html__('Please fill in your name and a valid email address.', 'breeze-codex-theme'); ?>
          </div>
          <?php endif; ?>

          <form class="contact-form" method="post" action="<?php echo esc_url(get_permalink() ?: home_url('/contact/')); ?>" novalidate>
            <?php wp_nonce_field('breeze_contact', 'breeze_contact_nonce'); ?>

            <!-- Required fields -->
            <div class="contact-field">
              <label for="cName"><?php echo $is_pt_page ? 'O Seu Nome' : esc_html__('Your Name', 'breeze-codex-theme'); ?> <span aria-hidden="true">*</span></label>
              <input type="text" id="cName" name="contact_name"
                value="<?php echo esc_attr($error ? sanitize_text_field(wp_unslash($_POST['contact_name'] ?? '')) : ($_GET['contact_name'] ?? '')); ?>"
                placeholder="<?php echo $is_pt_page ? esc_attr('Ana Silva') : esc_attr__('Jane Smith', 'breeze-codex-theme'); ?>"
                required autocomplete="name">
            </div>
            <div class="contact-field">
              <label for="cEmail"><?php echo $is_pt_page ? 'Endereço de Email' : esc_html__('Email Address', 'breeze-codex-theme'); ?> <span aria-hidden="true">*</span></label>
              <input type="email" id="cEmail" name="contact_email"
                value="<?php echo esc_attr($error ? sanitize_email(wp_unslash($_POST['contact_email'] ?? '')) : ($_GET['contact_email'] ?? '')); ?>"
                placeholder="<?php esc_attr_e('jane@example.com', 'breeze-codex-theme'); ?>"
                required autocomplete="email">
            </div>

            <!-- Optional fields -->
            <?php
            if ($is_pt_page) {
                $pkg_options = array(
                    'Safari da Grande Migração em Ndutu — 6 Dias'    => 'Safari da Grande Migração em Ndutu — 6 Dias',
                    'Safari de Luxo Migração & Big Five — 8 Dias'    => 'Safari de Luxo Migração & Big Five — 8 Dias',
                    'Viagem Lua de Mel Tanzânia & Zanzibar — 9 Dias' => 'Viagem Lua de Mel Tanzânia & Zanzibar — 9 Dias',
                );
            } else {
                $pkg_options = array(
                    '6-Day Ndutu Great Migration Safari'          => '6-Day Ndutu Great Migration Safari',
                    '8-Day Luxury Migration & Big Five Safari'    => '8-Day Luxury Migration & Big Five Safari',
                    '9-Day Tanzania & Zanzibar Honeymoon Journey' => '9-Day Tanzania & Zanzibar Honeymoon Journey',
                );
            }
            ?>
            <div class="contact-field">
              <label for="cPackage"><?php echo $is_pt_page ? 'Pacote de Interesse' : esc_html__('Interested Package', 'breeze-codex-theme'); ?></label>
              <select id="cPackage" name="contact_package">
                <option value=""><?php echo $is_pt_page ? 'Sem pacote específico por agora' : esc_html__('No specific package yet', 'breeze-codex-theme'); ?></option>
                <?php foreach ($pkg_options as $value => $label) : ?>
                <option value="<?php echo esc_attr($value); ?>"<?php selected($pre_package, $value); ?>>
                  <?php echo esc_html($label); ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="contact-field">
              <label for="cPersons"><?php echo $is_pt_page ? 'Número de Pessoas' : esc_html__('Number of Persons', 'breeze-codex-theme'); ?></label>
              <select id="cPersons" name="contact_persons">
                <option value=""><?php echo $is_pt_page ? 'Selecionar…' : esc_html__('Select…', 'breeze-codex-theme'); ?></option>
                <?php
                $persons_opts = $is_pt_page
                    ? array('1'=>'1 pessoa','2'=>'2 pessoas','3'=>'3 pessoas','4'=>'4 pessoas','5'=>'5 pessoas','6'=>'6 pessoas','6+'=>'6+ pessoas')
                    : array('1'=>'1 person','2'=>'2 persons','3'=>'3 persons','4'=>'4 persons','5'=>'5 persons','6'=>'6 persons','6+'=>'6+ persons');
                foreach ($persons_opts as $v => $l) : ?>
                <option value="<?php echo esc_attr($v); ?>"<?php selected($pre_persons, $v); ?>><?php echo esc_html($l); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="contact-field contact-field-full">
              <label for="cMessage"><?php echo $is_pt_page ? 'Mensagem' : esc_html__('Message', 'breeze-codex-theme'); ?></label>
              <textarea id="cMessage" name="contact_message" rows="5"
                placeholder="<?php echo $is_pt_page ? esc_attr('Conte-nos as suas datas, interesses ou qualquer questão…') : esc_attr__('Tell us about your travel dates, interests, or any questions…', 'breeze-codex-theme'); ?>"><?php echo esc_textarea($error ? sanitize_textarea_field(wp_unslash($_POST['contact_message'] ?? '')) : ($_GET['contact_message'] ?? '')); ?></textarea>
            </div>

            <div class="contact-actions">
              <button type="submit" class="modal-btn">
                <?php echo $is_pt_page ? 'Enviar Mensagem' : esc_html__('Send Message', 'breeze-codex-theme'); ?>
              </button>
              <p class="contact-privacy-note">
                <?php echo $is_pt_page ? 'Respeitamos a sua privacidade. Os seus dados são utilizados apenas para responder ao seu pedido.' : esc_html__('We respect your privacy. Your details are used only to respond to your inquiry.', 'breeze-codex-theme'); ?>
              </p>
            </div>
          </form>

        <?php endif; ?>

      </div><!-- .contact-form-wrap -->

      <aside class="contact-aside">
        <h2><?php echo $is_pt_page ? 'Entrar em Contacto' : esc_html__('Get in Touch', 'breeze-codex-theme'); ?></h2>
        <p><?php echo $is_pt_page ? 'Somos uma equipa pequena baseada na Tanzânia, apaixonada por criar viagens que se sentem pessoais e sem esforço.' : esc_html__("We're a small team based in Tanzania, passionate about crafting journeys that feel personal and effortless.", 'breeze-codex-theme'); ?></p>
        <?php
        $email    = get_theme_mod('breeze_footer_email', 'info@breezesafaris.com');
        $phone    = get_theme_mod('breeze_footer_phone', '');
        $location = get_theme_mod('breeze_footer_location', 'Tanzania, East Africa');
        ?>
        <?php if ($email) : ?>
        <div class="contact-detail">
          <strong>Email</strong>
          <a href="<?php echo esc_url('mailto:' . sanitize_email($email)); ?>"><?php echo esc_html($email); ?></a>
        </div>
        <?php endif; ?>
        <?php if ($phone) : ?>
        <div class="contact-detail">
          <strong><?php echo $is_pt_page ? 'Telefone' : 'Phone'; ?></strong>
          <a href="<?php echo esc_url('tel:' . preg_replace('/[^0-9\+]/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a>
        </div>
        <?php endif; ?>
        <?php if ($location) : ?>
        <div class="contact-detail">
          <strong><?php echo $is_pt_page ? 'Baseados em' : esc_html__('Based in', 'breeze-codex-theme'); ?></strong>
          <p><?php echo esc_html($location); ?></p>
        </div>
        <?php endif; ?>
        <div class="contact-detail">
          <strong><?php echo $is_pt_page ? 'Tempo de resposta' : esc_html__('Response time', 'breeze-codex-theme'); ?></strong>
          <p><?php echo $is_pt_page ? 'Dentro de 24 horas' : esc_html__('Within 24 hours', 'breeze-codex-theme'); ?></p>
        </div>
        <div class="contact-detail">
          <strong><?php echo $is_pt_page ? 'Serviço' : esc_html__('Planning', 'breeze-codex-theme'); ?></strong>
          <p><?php echo $is_pt_page ? 'Planeamento de Safari Privado' : esc_html__('Private Safari Planning', 'breeze-codex-theme'); ?></p>
        </div>
      </aside>

    </div><!-- .contact-grid -->

  </section>
</main>

<script>
(function() {
  var params = new URLSearchParams(window.location.search);
  var pkg = params.get('package');
  var persons = params.get('persons');
  if (pkg) {
    var sel = document.getElementById('cPackage');
    if (sel) {
      for (var i = 0; i < sel.options.length; i++) {
        if (sel.options[i].value === pkg) { sel.selectedIndex = i; break; }
      }
    }
  }
  if (persons) {
    var selP = document.getElementById('cPersons');
    if (selP) {
      for (var j = 0; j < selP.options.length; j++) {
        if (selP.options[j].value === persons) { selP.selectedIndex = j; break; }
      }
    }
  }
})();
</script>

<?php get_footer(); ?>
