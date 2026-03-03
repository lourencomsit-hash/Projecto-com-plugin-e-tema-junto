<?php
/**
 * Template Name: Packages Page
 *
 * @package breeze-codex-theme
 */

if (!defined('ABSPATH')) {
    exit;
}

$is_pt_page = function_exists('breeze_is_pt_page') && is_page() && breeze_is_pt_page();
$contact_url = breeze_get_page_url_by_slug('contact', home_url('/contact/'));

get_header();
?>

<main class="privacy-main">
  <section class="privacy-shell package-shell">
    <p class="privacy-kicker"><?php echo $is_pt_page ? 'Planeie a Sua Rota' : 'Plan Your Route'; ?></p>
    <h1><?php echo $is_pt_page ? 'Todos os Pacotes Safari' : 'All Safari Packages'; ?></h1>
    <p class="package-summary"><?php echo $is_pt_page
      ? 'Filtre por orçamento, duração, estilo e destinos para encontrar o itinerário que melhor se adapta à sua viagem.'
      : 'Filter by budget, duration, style and destinations to find the itinerary that best matches your trip.'; ?></p>

    <div class="packages-explorer-filters" id="packagesFilters">
      <div class="filter-field filter-field-search">
        <label for="filterSearch"><?php echo $is_pt_page ? 'Pesquisar pacote' : 'Search package'; ?></label>
        <input id="filterSearch" type="search" placeholder="<?php echo $is_pt_page ? 'Ndutu, migração, luxo...' : 'Ndutu, migration, luxury...'; ?>">
      </div>
      <div class="filter-field">
        <label for="filterDestination"><?php echo $is_pt_page ? 'Destino' : 'Destination'; ?></label>
        <select id="filterDestination">
          <option value="all"><?php echo $is_pt_page ? 'Todos os destinos' : 'All destinations'; ?></option>
          <option value="arusha">Arusha</option>
          <option value="ndutu">Ndutu</option>
          <option value="serengeti">Serengeti</option>
          <option value="ngorongoro">Ngorongoro</option>
          <option value="tarangire">Tarangire</option>
          <option value="manyara">Lake Manyara</option>
          <option value="zanzibar">Zanzibar</option>
        </select>
      </div>
      <div class="filter-field">
        <label for="filterStyle"><?php echo $is_pt_page ? 'Estilo' : 'Style'; ?></label>
        <select id="filterStyle">
          <option value="all"><?php echo $is_pt_page ? 'Todos os estilos' : 'All styles'; ?></option>
          <option value="classic"><?php echo $is_pt_page ? 'Classic' : 'Classic'; ?></option>
          <option value="luxury"><?php echo $is_pt_page ? 'Luxury' : 'Luxury'; ?></option>
        </select>
      </div>
      <div class="filter-field">
        <label for="filterDays"><?php echo $is_pt_page ? 'Duração' : 'Duration'; ?></label>
        <select id="filterDays">
          <option value="all"><?php echo $is_pt_page ? 'Qualquer duração' : 'Any duration'; ?></option>
          <option value="6"><?php echo $is_pt_page ? 'Até 6 dias' : 'Up to 6 days'; ?></option>
          <option value="8"><?php echo $is_pt_page ? 'Até 8 dias' : 'Up to 8 days'; ?></option>
          <option value="10"><?php echo $is_pt_page ? 'Até 10 dias' : 'Up to 10 days'; ?></option>
        </select>
      </div>
      <div class="filter-field">
        <label for="filterBudget"><?php echo $is_pt_page ? 'Orçamento máximo por pessoa:' : 'Max budget per person:'; ?> <span id="filterBudgetValue">$8,000</span></label>
        <input id="filterBudget" type="range" min="2000" max="8000" step="50" value="8000">
      </div>
      <div class="filter-check filter-field-beach">
        <input id="filterBeach" type="checkbox">
        <label for="filterBeach"><?php echo $is_pt_page ? 'Inclui extensão de praia' : 'Includes beach extension'; ?></label>
      </div>
      <div class="filter-actions">
        <p class="filter-hint"><?php echo $is_pt_page ? 'Ajuste os filtros e clique em pesquisar para atualizar os resultados.' : 'Adjust your filters, then click search to update the results.'; ?></p>
        <div class="filter-buttons">
          <button type="button" class="modal-btn" id="filterApply"><?php echo $is_pt_page ? 'Pesquisar Pacotes' : 'Search Packages'; ?></button>
          <button type="button" class="modal-btn modal-btn-secondary" id="filterReset"><?php echo $is_pt_page ? 'Limpar Filtros' : 'Reset Filters'; ?></button>
        </div>
      </div>
    </div>

    <p class="packages-results-count"><?php echo $is_pt_page ? 'A mostrar' : 'Showing'; ?> <strong id="resultsCount">3</strong> <span id="resultsLabel"><?php echo $is_pt_page ? 'pacotes' : 'packages'; ?></span></p>

    <div class="packages-results-grid" id="packagesExplorerGrid">

      <?php if ($is_pt_page) : ?>

      <article class="package-result-card"
        data-title="safari da grande migracao em ndutu 6 dias"
        data-destinations="ndutu,ngorongoro"
        data-style="classic"
        data-days="6"
        data-price="2310"
        data-beach="false">
        <img src="<?php echo esc_url(breeze_theme_media_url('photos/Ndutu 1.jpg')); ?>" alt="Safari de migração Ndutu" loading="lazy" decoding="async">
        <div class="package-result-body">
          <p class="card-label">Dezembro a Março</p>
          <h2>Safari da Grande Migração em Ndutu — 6 Dias</h2>
          <p>Ação sazonal de migração em Ndutu com safari completo na Cratera de Ngorongoro.</p>
          <p class="card-price">A partir de <strong>$2.310 por pessoa</strong></p>
          <div class="package-result-actions">
            <a class="modal-btn modal-btn-secondary" href="<?php echo esc_url(home_url('/pt/pacotes/safari-migracao-ndutu-6-dias/')); ?>">Mais Info</a>
            <a class="modal-btn" href="<?php echo esc_url(add_query_arg('package', 'Safari da Grande Migração em Ndutu — 6 Dias', $contact_url)); ?>">Contactar</a>
          </div>
        </div>
      </article>

      <article class="package-result-card"
        data-title="safari de luxo migracao big five 8 dias"
        data-destinations="tarangire,serengeti,ngorongoro,manyara"
        data-style="luxury"
        data-days="8"
        data-price="6545"
        data-beach="false">
        <img src="<?php echo esc_url(breeze_theme_media_url('photos/migration.jpg')); ?>" alt="Safari de luxo migração" loading="lazy" decoding="async">
        <div class="package-result-body">
          <p class="card-label">Safari de Luxo</p>
          <h2>Safari de Luxo Migração &amp; Big Five — 8 Dias</h2>
          <p>Tarangire, rotas de migração no Serengeti Norte, Ngorongoro e Lake Manyara.</p>
          <p class="card-price">A partir de <strong>$6.545 por pessoa</strong></p>
          <div class="package-result-actions">
            <a class="modal-btn modal-btn-secondary" href="<?php echo esc_url(home_url('/pt/pacotes/safari-luxo-big-five-8-dias/')); ?>">Mais Info</a>
            <a class="modal-btn" href="<?php echo esc_url(add_query_arg('package', 'Safari de Luxo Migração & Big Five — 8 Dias', $contact_url)); ?>">Contactar</a>
          </div>
        </div>
      </article>

      <article class="package-result-card"
        data-title="lua de mel tanzania zanzibar 9 dias"
        data-destinations="arusha,tarangire,manyara,zanzibar"
        data-style="luxury"
        data-days="9"
        data-price="3580"
        data-beach="true">
        <img src="<?php echo esc_url(breeze_theme_media_url('photos/zanzibar1.jpg')); ?>" alt="Pacote lua de mel Tanzânia e Zanzibar" loading="lazy" decoding="async">
        <div class="package-result-body">
          <p class="card-label">Junho a Julho 2026</p>
          <h2>Viagem Lua de Mel Tanzânia &amp; Zanzibar — 9 Dias</h2>
          <p>Rota privada de lua de mel com safaris em Tarangire, estadia em Manyara e dias de praia em Zanzibar.</p>
          <p class="card-price">A partir de <strong>$3.580 por pessoa</strong></p>
          <div class="package-result-actions">
            <a class="modal-btn modal-btn-secondary" href="<?php echo esc_url(home_url('/pt/pacotes/lua-de-mel-tanzania-zanzibar-9-dias/')); ?>">Mais Info</a>
            <a class="modal-btn" href="<?php echo esc_url(add_query_arg('package', 'Viagem Lua de Mel Tanzânia & Zanzibar — 9 Dias', $contact_url)); ?>">Contactar</a>
          </div>
        </div>
      </article>

      <?php else : ?>

      <article class="package-result-card"
        data-title="6-day ndutu great migration safari"
        data-destinations="ndutu,ngorongoro"
        data-style="classic"
        data-days="6"
        data-price="2310"
        data-beach="false">
        <img src="<?php echo esc_url(breeze_theme_media_url('photos/Ndutu 1.jpg')); ?>" alt="Ndutu migration safari" loading="lazy" decoding="async">
        <div class="package-result-body">
          <p class="card-label">December To March</p>
          <h2>6-Day Ndutu Great Migration Safari</h2>
          <p>Seasonal migration action in Ndutu with a full Ngorongoro Crater safari.</p>
          <p class="card-price">Starting from <strong>$2,310 per person</strong></p>
          <div class="package-result-actions">
            <a class="modal-btn modal-btn-secondary" href="<?php echo esc_url(home_url('/packages/ndutu-calving-season-safari/')); ?>">More Info</a>
            <a class="modal-btn" href="<?php echo esc_url(add_query_arg('package', '6-Day Ndutu Great Migration Safari', $contact_url)); ?>">Inquire</a>
          </div>
        </div>
      </article>

      <article class="package-result-card"
        data-title="8-day luxury migration and big five safari"
        data-destinations="tarangire,serengeti,ngorongoro,manyara"
        data-style="luxury"
        data-days="8"
        data-price="6545"
        data-beach="false">
        <img src="<?php echo esc_url(breeze_theme_media_url('photos/migration.jpg')); ?>" alt="Luxury migration safari" loading="lazy" decoding="async">
        <div class="package-result-body">
          <p class="card-label">Luxury Safari</p>
          <h2>8-Day Luxury Migration &amp; Big Five Safari</h2>
          <p>Tarangire, Northern Serengeti migration routes, Ngorongoro and Lake Manyara.</p>
          <p class="card-price">Starting from <strong>$6,545 per person</strong></p>
          <div class="package-result-actions">
            <a class="modal-btn modal-btn-secondary" href="<?php echo esc_url(home_url('/packages/luxury-migration-big-five-safari-8-day/')); ?>">More Info</a>
            <a class="modal-btn" href="<?php echo esc_url(add_query_arg('package', '8-Day Luxury Migration & Big Five Safari', $contact_url)); ?>">Inquire</a>
          </div>
        </div>
      </article>

      <article class="package-result-card"
        data-title="9-day tanzania zanzibar honeymoon journey"
        data-destinations="arusha,tarangire,manyara,zanzibar"
        data-style="luxury"
        data-days="9"
        data-price="3580"
        data-beach="true">
        <img src="<?php echo esc_url(breeze_theme_media_url('photos/zanzibar1.jpg')); ?>" alt="Tanzania and Zanzibar honeymoon package" loading="lazy" decoding="async">
        <div class="package-result-body">
          <p class="card-label">June To July 2026</p>
          <h2>9-Day Tanzania &amp; Zanzibar Honeymoon Journey</h2>
          <p>Private honeymoon route with Tarangire safaris, Manyara lodge stay and beach days in Zanzibar.</p>
          <p class="card-price">Starting from <strong>$3,580 per person</strong></p>
          <div class="package-result-actions">
            <a class="modal-btn modal-btn-secondary" href="<?php echo esc_url(home_url('/packages/tanzania-zanzibar-honeymoon-9-day/')); ?>">More Info</a>
            <a class="modal-btn" href="<?php echo esc_url(add_query_arg('package', '9-Day Tanzania & Zanzibar Honeymoon Journey', $contact_url)); ?>">Inquire</a>
          </div>
        </div>
      </article>

      <?php endif; ?>

    </div>

    <p class="packages-empty" id="packagesEmpty" hidden><?php echo $is_pt_page
      ? 'Nenhum pacote corresponde a estes filtros. Tente aumentar o orçamento ou limpar um filtro.'
      : 'No package matches these filters. Try increasing budget or clearing one filter.'; ?></p>
  </section>
</main>

<?php get_footer(); ?>
