<?php
/**
 * Importador — Migração de dados SEO do tema anterior + Yoast + CSV.
 *
 * @package BreezeSEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Breeze_SEO_Importer {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	public function init() {
		add_action( 'wp_ajax_bseo_import_theme_data',   array( $this, 'ajax_import_theme_data' ) );
		add_action( 'wp_ajax_bseo_import_yoast',        array( $this, 'ajax_import_yoast' ) );
		add_action( 'wp_ajax_bseo_import_csv',          array( $this, 'ajax_import_csv' ) );
		add_action( 'wp_ajax_bseo_force_update_titles', array( $this, 'ajax_force_update_titles' ) );
	}

	// ── Theme SEO data (hardcoded) ────────────────────────────────────────────

	private function get_theme_seo_data() {
		return array(
			'front_page' => array(
				'title' => 'Breeze Safaris | Tanzania Safari, Zanzibar & National Parks',
				'desc'  => 'Breeze Safaris creates tailor-made Tanzania safari holidays, Serengeti tours, Ngorongoro trips and Zanzibar extensions with trusted local experts.',
			),
			'about' => array(
				'title' => 'About Us | Breeze Safaris Tanzania Experts',
				'desc'  => 'Meet Breeze Safaris, two Tanzania-based safari specialists creating tailor-made journeys with trusted local partners.',
			),
			'contact' => array(
				'title' => 'Plan Your Safari | Contact Breeze Safaris',
				'desc'  => 'Get in touch with Breeze Safaris to plan your tailor-made Tanzania safari. Fill in our contact form for a personalised itinerary proposal.',
			),
			'packages' => array(
				'title' => 'All Safari Packages | Breeze Safaris',
				'desc'  => 'Browse Breeze Safaris Tanzania safari packages. Private itineraries from Great Migration safaris to honeymoon journeys and Zanzibar beach extensions.',
			),
			'privacy-policy' => array(
				'title'   => 'Privacy Policy | Breeze Safaris',
				'desc'    => 'Breeze Safaris privacy policy — how we collect, use and protect your personal data.',
				'noindex' => true,
			),
			'serengeti-national-park' => array(
				'title' => 'Serengeti National Park Safari | Breeze Safaris Tanzania',
				'desc'  => 'Discover Serengeti National Park with Breeze Safaris. Private game drives, migration-focused planning, handpicked camps and full Tanzania safari support.',
			),
			'ngorongoro-conservation-area' => array(
				'title' => 'Ngorongoro Conservation Area | Breeze Safaris Tanzania',
				'desc'  => 'Explore Ngorongoro Conservation Area with Breeze Safaris. Crater descent game drives, dense wildlife sightings and private Tanzania safari planning.',
			),
			'tarangire-national-park' => array(
				'title' => 'Tarangire National Park Safari | Breeze Safaris Tanzania',
				'desc'  => 'Tarangire National Park safari with Breeze Safaris. Private drives, elephant encounters and baobab landscapes on a Tanzania northern circuit.',
			),
			'lake-manyara-national-park' => array(
				'title' => 'Lake Manyara National Park Safari | Breeze Safaris Tanzania',
				'desc'  => 'Lake Manyara National Park safari with Breeze Safaris. Forest, lake-edge wildlife and Rift Valley escarpment scenery in a compact Tanzania circuit stop.',
			),
			'arusha-national-park' => array(
				'title' => 'Arusha National Park Safari | Breeze Safaris Tanzania',
				'desc'  => 'Arusha National Park safari with Breeze Safaris. Perfect first Tanzania circuit stop near Kilimanjaro airport with Mount Meru views and diverse habitats.',
			),
			'ndutu-area' => array(
				'title' => 'Ndutu Area Migration Safari | Breeze Safaris Tanzania',
				'desc'  => 'Ndutu Area safari with Breeze Safaris. Migration calving season game drives, predator tracking and wildebeest movement from December to March.',
			),
			'zanzibar' => array(
				'title' => 'Zanzibar Beach Holiday | Breeze Safaris Tanzania',
				'desc'  => 'Zanzibar beach holiday with Breeze Safaris. White sand beaches, warm Indian Ocean water and curated coastal stays as the perfect safari finale.',
			),
			// EN packages (child of /packages/)
			'ndutu-calving-season-safari' => array(
				'title'  => '6-Day Ndutu Great Migration Safari | Breeze Safaris',
				'desc'   => '6-day Ndutu Great Migration Safari from December to March, with migration-focused game drives across Ndutu plains and a Ngorongoro Crater finale.',
				'parent' => 'packages',
			),
			'luxury-migration-big-five-safari-8-day' => array(
				'title'  => '8-Day Luxury Migration & Big Five Safari | Breeze Safaris',
				'desc'   => '8-day luxury Tanzania safari through Tarangire, Northern Serengeti and Ngorongoro, with premium lodge stays and migration-focused routing.',
				'parent' => 'packages',
			),
			'tanzania-zanzibar-honeymoon-9-day' => array(
				'title'  => '9-Day Tanzania & Zanzibar Honeymoon Journey | Breeze Safaris',
				'desc'   => '9-day private honeymoon safari through Tarangire and Lake Manyara with a Zanzibar beach finale, designed for couples and special occasions.',
				'parent' => 'packages',
			),
			// PT pages — main
			'pt' => array(
				'title' => 'Breeze Safaris | Safaris na Tanzânia & Zanzibar',
				'desc'  => 'A Breeze Safaris cria safaris privados à medida na Tanzânia, com guias locais de confiança e extensões de praia em Zanzibar.',
			),
			'sobre-nos' => array(
				'title'  => 'Sobre Nós | Breeze Safaris — Especialistas em Safari',
				'desc'   => 'Conheça a Breeze Safaris, dois especialistas em safari baseados na Tanzânia a criar viagens à medida com parceiros locais de confiança.',
				'parent' => 'pt',
			),
			'contacto' => array(
				'title'  => 'Planear o Seu Safari | Contactar a Breeze Safaris',
				'desc'   => 'Entre em contacto com a Breeze Safaris para planear o seu safari privado à medida na Tanzânia. Preencha o formulário para uma proposta personalizada.',
				'parent' => 'pt',
			),
			'pacotes' => array(
				'title'  => 'Todos os Pacotes de Safari | Breeze Safaris',
				'desc'   => 'Explore os pacotes de safari da Breeze Safaris na Tanzânia. Itinerários privados desde safaris de Grande Migração a luas-de-mel e extensões em Zanzibar.',
				'parent' => 'pt',
			),
			'politica-de-privacidade' => array(
				'title'   => 'Política de Privacidade | Breeze Safaris',
				'desc'    => 'Política de privacidade da Breeze Safaris — como recolhemos, utilizamos e protegemos os seus dados pessoais.',
				'noindex' => true,
				'parent'  => 'pt',
			),
			// PT destinations (child of /pt/)
			'parque-nacional-serengeti' => array(
				'title'  => 'Safari no Serengeti | Breeze Safaris Tanzânia',
				'desc'   => 'Safari no Parque Nacional do Serengeti com a Breeze Safaris. Jeeps privados, planeamento focado na migração, campos seleccionados e apoio completo na Tanzânia.',
				'parent' => 'pt',
			),
			'area-conservacao-ngorongoro' => array(
				'title'  => 'Safari em Ngorongoro | Breeze Safaris Tanzânia',
				'desc'   => 'Explore a Área de Conservação de Ngorongoro com a Breeze Safaris. Descidas à cratera, avistamentos densos de fauna e planeamento de safari privado na Tanzânia.',
				'parent' => 'pt',
			),
			'parque-nacional-tarangire' => array(
				'title'  => 'Safari em Tarangire | Breeze Safaris Tanzânia',
				'desc'   => 'Safari no Parque Nacional de Tarangire com a Breeze Safaris. Jeeps privados, encontros com elefantes e paisagens de baobab no circuito norte da Tanzânia.',
				'parent' => 'pt',
			),
			'parque-nacional-lago-manyara' => array(
				'title'  => 'Safari no Lago Manyara | Breeze Safaris Tanzânia',
				'desc'   => 'Safari no Lago Manyara com a Breeze Safaris. Fauna nas margens do lago e paisagens do Vale do Rift numa paragem compacta do circuito norte da Tanzânia.',
				'parent' => 'pt',
			),
			'parque-nacional-arusha' => array(
				'title'  => 'Safari em Arusha | Breeze Safaris Tanzânia',
				'desc'   => 'Safari no Parque Nacional de Arusha com a Breeze Safaris. Paragem perfeita perto do aeroporto do Kilimanjaro com vistas do Monte Meru e habitats diversos.',
				'parent' => 'pt',
			),
			'area-ndutu' => array(
				'title'  => 'Safari em Ndutu | Breeze Safaris Tanzânia',
				'desc'   => 'Safari na Área de Ndutu com a Breeze Safaris. Safaris de época de parição, rastreio de predadores e movimentos dos gnus de dezembro a março.',
				'parent' => 'pt',
			),
			'zanzibar' => array(
				'title'  => 'Férias de Praia em Zanzibar | Breeze Safaris Tanzânia',
				'desc'   => 'Férias de praia em Zanzibar com a Breeze Safaris. Areias brancas, águas quentes do Oceano Índico e estadias costeiras seleccionadas como final de safari.',
				'parent' => 'pt',
			),
			// PT packages (child of /pt/pacotes/)
			'safari-migracao-ndutu-6-dias' => array(
				'title'  => 'Safari 6 Dias Grande Migração Ndutu | Breeze Safaris',
				'desc'   => 'Safari de 6 dias da Grande Migração em Ndutu de dezembro a março, com safaris focados na migração nas planícies de Ndutu e final na Cratera do Ngorongoro.',
				'parent' => 'pt/pacotes',
			),
			'safari-luxo-big-five-8-dias' => array(
				'title'  => 'Safari de Luxo 8 Dias Migração & Big Five | Breeze Safaris',
				'desc'   => 'Safari de luxo de 8 dias na Tanzânia pelo Tarangire, Norte do Serengeti e Ngorongoro, com estadias premium e rotas focadas na migração.',
				'parent' => 'pt/pacotes',
			),
			'lua-de-mel-tanzania-zanzibar-9-dias' => array(
				'title'  => 'Lua de Mel Tanzânia & Zanzibar 9 Dias | Breeze Safaris',
				'desc'   => 'Safari privado de 9 dias para lua de mel pelo Tarangire e Lago Manyara com final de praia em Zanzibar, concebido para casais e ocasiões especiais.',
				'parent' => 'pt/pacotes',
			),
		);
	}

	// ── Theme redirect map ────────────────────────────────────────────────────

	private function get_theme_redirects() {
		return array(
			array( 'url_old' => '/tanzania-safari-experts/',                  'url_new' => '/about/',                       'redirect_type' => 301 ),
			array( 'url_old' => '/pt/especialistas-portugueses-em-safaris/',  'url_new' => '/pt/sobre-nos/',                'redirect_type' => 301 ),
			array( 'url_old' => '/pt/contacte-nos/',                          'url_new' => '/pt/contacto/',                 'redirect_type' => 301 ),
			array( 'url_old' => '/project/',                                  'url_new' => '/',                             'redirect_type' => 301 ),
			array( 'url_old' => '/project/serengeti-national-park/',          'url_new' => '/serengeti-national-park/',     'redirect_type' => 301 ),
			array( 'url_old' => '/project/ngorongoro-conservation-area/',     'url_new' => '/ngorongoro-conservation-area/', 'redirect_type' => 301 ),
			array( 'url_old' => '/project/tarangire-national-park/',          'url_new' => '/tarangire-national-park/',     'redirect_type' => 301 ),
			array( 'url_old' => '/project/lake-manyara-national-park/',       'url_new' => '/lake-manyara-national-park/',  'redirect_type' => 301 ),
			array( 'url_old' => '/project/arusha-national-park/',             'url_new' => '/arusha-national-park/',        'redirect_type' => 301 ),
			array( 'url_old' => '/project/ndutu-area/',                       'url_new' => '/ndutu-area/',                  'redirect_type' => 301 ),
			array( 'url_old' => '/project/zanzibar/',                         'url_new' => '/zanzibar/',                    'redirect_type' => 301 ),
			array( 'url_old' => '/cookie-policy-eu/',                         'url_new' => '/privacy-policy/',              'redirect_type' => 301 ),
			array( 'url_old' => '/pt',                                        'url_new' => '/pt/',                          'redirect_type' => 301 ),
		);
	}

	// ── Import theme SEO data ─────────────────────────────────────────────────

	public function import_theme_seo_data() {
		$data     = $this->get_theme_seo_data();
		$imported = 0;
		$skipped  = 0;
		$errors   = array();

		foreach ( $data as $slug => $seo ) {
			$post = null;

			if ( 'front_page' === $slug ) {
				$fp_id = (int) get_option( 'page_on_front' );
				if ( $fp_id > 0 ) $post = get_post( $fp_id );
			} elseif ( isset( $seo['parent'] ) ) {
				$post = get_page_by_path( $seo['parent'] . '/' . $slug, OBJECT, 'page' );
				if ( ! $post ) $post = get_page_by_path( $slug, OBJECT, 'page' );
			} else {
				$post = get_page_by_path( $slug, OBJECT, 'page' );
				if ( ! $post ) $post = get_page_by_path( $slug, OBJECT, 'post' );
			}

			if ( ! $post ) {
				$errors[] = 'Page not found for slug: ' . $slug;
				continue;
			}

			// Skip if already has custom SEO title (don't overwrite user edits)
			if ( ! empty( get_post_meta( $post->ID, '_bseo_title', true ) ) ) {
				$skipped++;
				continue;
			}

			update_post_meta( $post->ID, '_bseo_title',       sanitize_text_field( $seo['title'] ) );
			update_post_meta( $post->ID, '_bseo_description', sanitize_textarea_field( $seo['desc'] ) );
			if ( ! empty( $seo['noindex'] ) ) {
				update_post_meta( $post->ID, '_bseo_noindex', '1' );
			}

			$imported++;
		}

		return array( 'imported' => $imported, 'skipped' => $skipped, 'errors' => $errors );
	}

	// ── Import theme redirects ────────────────────────────────────────────────

	public function import_theme_redirects() {
		global $wpdb;
		$table    = $wpdb->prefix . 'breeze_redirects';
		$map      = $this->get_theme_redirects();
		$imported = 0;
		$skipped  = 0;

		foreach ( $map as $row ) {
			$exists = (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*) FROM `$table` WHERE url_old = %s", $row['url_old'] ) // phpcs:ignore
			);
			if ( $exists > 0 ) { $skipped++; continue; }

			$result = $wpdb->insert( $table, array(
				'url_old'       => $row['url_old'],
				'url_new'       => $row['url_new'],
				'redirect_type' => (int) $row['redirect_type'],
				'created_at'    => current_time( 'mysql' ),
			), array( '%s', '%s', '%d', '%s' ) );

			if ( false !== $result ) $imported++;
		}

		return array( 'imported' => $imported, 'skipped' => $skipped );
	}

	// ── Import from CSV ───────────────────────────────────────────────────────

	public function import_from_csv( $file_path ) {
		global $wpdb;

		if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
			return array( 'imported' => 0, 'skipped' => 0, 'errors' => array( 'File not found or not readable.' ) );
		}

		$handle = fopen( $file_path, 'r' );
		if ( ! $handle ) {
			return array( 'imported' => 0, 'skipped' => 0, 'errors' => array( 'Could not open file.' ) );
		}

		// Detect delimiter
		$first_line = fgets( $handle );
		rewind( $handle );
		$delim = substr_count( $first_line, ';' ) > substr_count( $first_line, ',' ) ? ';' : ',';

		$table    = $wpdb->prefix . 'breeze_redirects';
		$imported = 0;
		$skipped  = 0;
		$errors   = array();
		$header   = null;
		$line_num = 0;

		while ( ( $row = fgetcsv( $handle, 4096, $delim ) ) !== false ) {
			$line_num++;

			if ( $line_num === 1 ) {
				$header = array_map( 'trim', $row );
				continue;
			}

			$data    = $header ? array_combine( array_map( 'strtolower', $header ), $row ) : $row;
			$url_old = esc_url_raw( trim( $data['url_antigo'] ?? $data['old_url'] ?? $data['from'] ?? $row[0] ?? '' ) );
			$url_new = esc_url_raw( trim( $data['url_novo']   ?? $data['new_url'] ?? $data['to']   ?? $row[1] ?? '' ) );
			$type    = (int) ( $data['tipo'] ?? $data['type'] ?? 301 );
			$notes   = sanitize_textarea_field( $data['notas'] ?? $data['notes'] ?? '' );

			if ( empty( $url_old ) || empty( $url_new ) ) { $skipped++; continue; }
			if ( ! in_array( $type, array( 301, 302 ), true ) ) $type = 301;

			$exists = (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*) FROM `$table` WHERE url_old = %s", $url_old ) // phpcs:ignore
			);
			if ( $exists > 0 ) { $skipped++; continue; }

			$result = $wpdb->insert( $table, array(
				'url_old'       => $url_old,
				'url_new'       => $url_new,
				'redirect_type' => $type,
				'notes'         => $notes,
				'created_at'    => current_time( 'mysql' ),
			), array( '%s', '%s', '%d', '%s', '%s' ) );

			if ( false !== $result ) { $imported++; }
			else { $errors[] = "Line $line_num: DB insert failed."; }
		}

		fclose( $handle );
		return array( 'imported' => $imported, 'skipped' => $skipped, 'errors' => $errors );
	}

	// ── Import Yoast ──────────────────────────────────────────────────────────

	public function import_yoast_data() {
		global $wpdb;

		$post_ids = $wpdb->get_col(
			"SELECT DISTINCT pm_y.post_id
			FROM {$wpdb->postmeta} AS pm_y
			LEFT JOIN {$wpdb->postmeta} AS pm_b
				ON pm_b.post_id = pm_y.post_id
				AND pm_b.meta_key = '_bseo_title'
				AND pm_b.meta_value != ''
			WHERE pm_y.meta_key = '_yoast_wpseo_title'
			  AND pm_y.meta_value != ''
			  AND pm_b.meta_id IS NULL"
		);

		if ( empty( $post_ids ) ) return array( 'imported' => 0 );

		$meta_map = array(
			'_yoast_wpseo_title'    => '_bseo_title',
			'_yoast_wpseo_metadesc' => '_bseo_description',
			'_yoast_wpseo_canonical' => '_bseo_canonical',
		);

		$imported = 0;
		foreach ( $post_ids as $post_id ) {
			$post_id = (int) $post_id;
			foreach ( $meta_map as $yoast_key => $bseo_key ) {
				$val = get_post_meta( $post_id, $yoast_key, true );
				if ( ! empty( $val ) ) {
					update_post_meta( $post_id, $bseo_key, sanitize_text_field( $val ) );
				}
			}
			$noindex = get_post_meta( $post_id, '_yoast_wpseo_meta-robots-noindex', true );
			if ( $noindex === '1' ) {
				update_post_meta( $post_id, '_bseo_noindex', '1' );
			}
			$imported++;
		}

		return array( 'imported' => $imported );
	}

	// ── Force-update titles from theme data ───────────────────────────────────

	/**
	 * Like import_theme_seo_data() but ALWAYS overwrites _bseo_title,
	 * even if one is already set. Used to push corrected/shortened titles.
	 */
	public function force_update_titles() {
		$data    = $this->get_theme_seo_data();
		$updated = 0;
		$skipped = 0; // pages not found in DB
		$errors  = array();

		foreach ( $data as $slug => $seo ) {
			$post = null;

			if ( 'front_page' === $slug ) {
				$fp_id = (int) get_option( 'page_on_front' );
				if ( $fp_id > 0 ) $post = get_post( $fp_id );
			} elseif ( isset( $seo['parent'] ) ) {
				$post = get_page_by_path( $seo['parent'] . '/' . $slug, OBJECT, 'page' );
				if ( ! $post ) $post = get_page_by_path( $slug, OBJECT, 'page' );
			} else {
				$post = get_page_by_path( $slug, OBJECT, 'page' );
				if ( ! $post ) $post = get_page_by_path( $slug, OBJECT, 'post' );
			}

			if ( ! $post ) {
				$errors[] = 'Page not found: ' . $slug;
				$skipped++;
				continue;
			}

			update_post_meta( $post->ID, '_bseo_title', sanitize_text_field( $seo['title'] ) );
			$updated++;
		}

		return array( 'updated' => $updated, 'skipped' => $skipped, 'errors' => $errors );
	}

	// ── AJAX handlers ─────────────────────────────────────────────────────────

	public function ajax_import_theme_data() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Permission denied.' ), 403 );
		}
		check_ajax_referer( 'bseo_importer_nonce', 'nonce' );

		$seo       = $this->import_theme_seo_data();
		$redirects = $this->import_theme_redirects();

		wp_send_json_success( array( 'seo' => $seo, 'redirects' => $redirects ) );
	}

	public function ajax_import_yoast() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Permission denied.' ), 403 );
		}
		check_ajax_referer( 'bseo_importer_nonce', 'nonce' );
		wp_send_json_success( $this->import_yoast_data() );
	}

	public function ajax_import_csv() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Permission denied.' ), 403 );
		}
		check_ajax_referer( 'bseo_importer_nonce', 'nonce' );

		if ( empty( $_FILES['csv_file']['tmp_name'] ) ) {
			wp_send_json_error( array( 'message' => 'No file uploaded.' ) );
		}

		$file = $_FILES['csv_file']; // phpcs:ignore
		if ( ! is_uploaded_file( $file['tmp_name'] ) ) {
			wp_send_json_error( array( 'message' => 'File upload verification failed.' ) );
		}

		$result = $this->import_from_csv( $file['tmp_name'] );
		wp_send_json_success( $result );
	}

	public function ajax_force_update_titles() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Permission denied.' ), 403 );
		}
		check_ajax_referer( 'bseo_importer_nonce', 'nonce' );

		$result = $this->force_update_titles();

		wp_send_json_success( array(
			'message' => sprintf(
				/* translators: 1: updated count, 2: skipped count */
				__( 'Done. Titles updated: %1$d, Pages not found: %2$d.', 'breeze-seo' ),
				$result['updated'],
				$result['skipped']
			),
		) );
	}
}
