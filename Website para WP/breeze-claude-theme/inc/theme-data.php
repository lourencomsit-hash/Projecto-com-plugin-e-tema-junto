<?php
if (!defined('ABSPATH')) {
    exit;
}

function breeze_theme_media_url($path) {
    $path = trim((string) $path, '/');
    if ($path === '') {
        return '';
    }

    $segments = array_filter(explode('/', $path), 'strlen');
    $encoded_segments = array_map('rawurlencode', $segments);
    return trailingslashit(get_template_directory_uri()) . 'assets/media/' . implode('/', $encoded_segments);
}

function breeze_rewrite_static_seed_content_links($html) {
    $html = (string) $html;
    if ($html === '') {
        return '';
    }

    $photo_base = trailingslashit(breeze_theme_media_url('photos'));
    $video_base = trailingslashit(breeze_theme_media_url('videos'));

    $replace = array(
        'src="../media/photos/' => 'src="' . $photo_base,
        'src="media/photos/' => 'src="' . $photo_base,
        'href="../media/photos/' => 'href="' . $photo_base,
        'href="media/photos/' => 'href="' . $photo_base,
        'src="../media/videos/' => 'src="' . $video_base,
        'src="media/videos/' => 'src="' . $video_base,

        'href="../index.html#packages"' => 'href="' . home_url('/#packages') . '"',
        'href="index.html#packages"' => 'href="' . home_url('/#packages') . '"',
        'href="../index.html#parks-showcase"' => 'href="' . home_url('/#parks-showcase') . '"',
        'href="index.html#parks-showcase"' => 'href="' . home_url('/#parks-showcase') . '"',

        'href="../contact.html?' => 'href="' . home_url('/contact/') . '?',
        'href="contact.html?' => 'href="' . home_url('/contact/') . '?',
        'href="../contact.html"' => 'href="' . home_url('/contact/') . '"',
        'href="contact.html"' => 'href="' . home_url('/contact/') . '"',

        'href="../packages.html"' => 'href="' . home_url('/packages/') . '"',
        'href="packages.html"' => 'href="' . home_url('/packages/') . '"',
        'href="../about.html"' => 'href="' . home_url('/about/') . '"',
        'href="about.html"' => 'href="' . home_url('/about/') . '"',
        'href="../privacy-policy.html"' => 'href="' . home_url('/privacy-policy/') . '"',
        'href="privacy-policy.html"' => 'href="' . home_url('/privacy-policy/') . '"',

        'href="../destinations/serengeti-national-park.html"' => 'href="' . home_url('/serengeti-national-park/') . '"',
        'href="destinations/serengeti-national-park.html"' => 'href="' . home_url('/serengeti-national-park/') . '"',
        'href="../destinations/ngorongoro-conservation-area.html"' => 'href="' . home_url('/ngorongoro-conservation-area/') . '"',
        'href="destinations/ngorongoro-conservation-area.html"' => 'href="' . home_url('/ngorongoro-conservation-area/') . '"',
        'href="../destinations/tarangire-national-park.html"' => 'href="' . home_url('/tarangire-national-park/') . '"',
        'href="destinations/tarangire-national-park.html"' => 'href="' . home_url('/tarangire-national-park/') . '"',
        'href="../destinations/lake-manyara-national-park.html"' => 'href="' . home_url('/lake-manyara-national-park/') . '"',
        'href="destinations/lake-manyara-national-park.html"' => 'href="' . home_url('/lake-manyara-national-park/') . '"',
        'href="../destinations/arusha-national-park.html"' => 'href="' . home_url('/arusha-national-park/') . '"',
        'href="destinations/arusha-national-park.html"' => 'href="' . home_url('/arusha-national-park/') . '"',
        'href="../destinations/ndutu-area.html"' => 'href="' . home_url('/ndutu-area/') . '"',
        'href="destinations/ndutu-area.html"' => 'href="' . home_url('/ndutu-area/') . '"',
        'href="../destinations/zanzibar.html"' => 'href="' . home_url('/zanzibar/') . '"',
        'href="destinations/zanzibar.html"' => 'href="' . home_url('/zanzibar/') . '"',

        'href="packages/ndutu-calving-season-safari.html"' => 'href="' . home_url('/packages/ndutu-calving-season-safari/') . '"',
        'href="../packages/ndutu-calving-season-safari.html"' => 'href="' . home_url('/packages/ndutu-calving-season-safari/') . '"',
        'href="packages/luxury-migration-big-five-safari-8-day.html"' => 'href="' . home_url('/packages/luxury-migration-big-five-safari-8-day/') . '"',
        'href="../packages/luxury-migration-big-five-safari-8-day.html"' => 'href="' . home_url('/packages/luxury-migration-big-five-safari-8-day/') . '"',
        'href="packages/tanzania-zanzibar-honeymoon-9-day.html"' => 'href="' . home_url('/packages/tanzania-zanzibar-honeymoon-9-day/') . '"',
        'href="../packages/tanzania-zanzibar-honeymoon-9-day.html"' => 'href="' . home_url('/packages/tanzania-zanzibar-honeymoon-9-day/') . '"',
    );

    return str_replace(array_keys($replace), array_values($replace), $html);
}

function breeze_get_seed_content_from_static_file($relative_path) {
    $relative_path = ltrim((string) $relative_path, '/');
    if ($relative_path === '') {
        return '';
    }

    $file_path = trailingslashit(get_template_directory()) . 'seed-content/' . $relative_path;
    if (!file_exists($file_path) || !is_readable($file_path)) {
        return '';
    }

    $raw = file_get_contents($file_path);
    if (!is_string($raw) || trim($raw) === '') {
        return '';
    }

    $raw = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $raw);
    if (!is_string($raw)) {
        return '';
    }

    if (preg_match('/<main\b.*?(?=<footer\b)/is', $raw, $matches)) {
        $raw = $matches[0];
    } elseif (preg_match('/<main\b.*?<\/main>/is', $raw, $matches)) {
        $raw = $matches[0];
    }

    if (preg_match('/<main\b[^>]*>(.*?)<\/main>/is', $raw, $main_matches, PREG_OFFSET_CAPTURE)) {
        $main_inner = $main_matches[1][0];
        $main_full = $main_matches[0][0];
        $main_start = (int) $main_matches[0][1];
        $after_main = substr($raw, $main_start + strlen($main_full));
        $raw = $main_inner . $after_main;
    }

    return trim(breeze_rewrite_static_seed_content_links($raw));
}

function breeze_get_home_defaults() {
    $defaults = array(
        'logo' => 'https://breezesafaris.com/wp-content/uploads/2025/11/cropped-Breeze_LOGO_1-removebg-preview.png',
        'hero' => array(
            'video_url' => 'https://breezesafaris.com/wp-content/uploads/2025/11/BREEZE-VIDEO-1-1.mp4',
            'kicker' => 'Explore • Discover • Relax',
            'title' => 'Tailor-made safaris created by two safari lovers living in Tanzania.',
            'subtitle' => "Experience breathtaking wildlife, authentic local guidance, and handpicked lodges.<br>Finish your journey in the tropical paradise of Zanzibar.",
            'button_text' => 'Start Your Journey',
            'button_url' => '#packages',
        ),
        'packages' => array(
            'head_kicker' => 'Featured Journeys',
            'head_title' => 'Safari Packages',
            'cards' => array(
                array(
                    'key' => 'ndutu-calving-6d',
                    'image' => 'https://breezesafaris.com/wp-content/uploads/2017/09/Ndutu-1-scaled-1200x800.jpg',
                    'alt' => 'Ndutu migration wildlife safari',
                    'label' => 'December To March',
                    'title' => '6-Day Ndutu Great Migration Safari',
                    'text' => 'Track the migration in Ndutu and finish with a classic Ngorongoro Crater game-drive day.',
                    'price' => 'Starting from <strong>$2,310 pp</strong>',
                    'modal_price' => '<strong>Starting from:</strong> <strong>$2,310 per person</strong>',
                    'more_info_url' => '/packages/ndutu-calving-season-safari/',
                    'modal_image_alt' => 'Ndutu migration safari wildlife',
                    'inquiry_subject' => '6-Day%20Ndutu%20Great%20Migration%20Safari%20Inquiry',
                    'modal_points' => array(
                        'Best seasonal timing in Ndutu for migration movement and predator action.',
                        'Dedicated wildlife days across Ndutu plains and surrounding game-rich zones.',
                        'Full Ngorongoro Crater safari as a high-impact finale.',
                        "Stays include Arusha Planet Lodge, Ang'ata Migration Bologonja Camp and Embalakai Ngorongoro Camp.",
                        'Private 4x4 game-drive vehicle, park fees and guided safari logistics included.',
                    ),
                ),
                array(
                    'key' => 'luxury-migration-8d',
                    'image' => 'https://breezesafaris.com/wp-content/uploads/2025/11/pexels-followalice-667205-1024x706.jpg',
                    'alt' => 'Luxury migration safari in Tanzania',
                    'label' => 'Luxury Safari',
                    'title' => '8-Day Luxury Migration & Big Five Safari',
                    'text' => 'Tarangire, Northern Serengeti crossings and Ngorongoro, finished with Lake Manyara in one premium route.',
                    'price' => 'Starting from <strong>$6,545 pp</strong>',
                    'modal_price' => '<strong>Starting from:</strong> <strong>$6,545 per person</strong>',
                    'more_info_url' => '/packages/luxury-migration-big-five-safari-8-day/',
                    'modal_image_alt' => 'Luxury migration and Big Five safari',
                    'inquiry_subject' => '8-Day%20Luxury%20Migration%20%26%20Big%20Five%20Safari%20Inquiry',
                    'modal_points' => array(
                        'Premium northern circuit route with Tarangire, Serengeti and Ngorongoro highlights.',
                        'Northern Serengeti timing for migration action near Mara River crossing zones.',
                        'Luxury lodge stays across Elewana Tarangire Treetops, Kubu Kubu and more.',
                        'Private 4x4 with expert guide, park fees, airport transfers and full logistics included.',
                        'Ideal balance of iconic wildlife days and high-comfort accommodations.',
                    ),
                ),
                array(
                    'key' => 'tanzania-zanzibar-9d',
                    'image' => 'https://breezesafaris.com/wp-content/uploads/2026/01/zanzibar-1-1200x800.jpg',
                    'alt' => 'Tanzania and Zanzibar honeymoon journey',
                    'label' => 'June To July 2026',
                    'title' => '9-Day Tanzania & Zanzibar Honeymoon Journey',
                    'text' => 'Private safari in Tarangire and Manyara with a beach finale in Zanzibar for a special occasion.',
                    'price' => 'Starting from <strong>$3,580 pp</strong>',
                    'modal_price' => '<strong>Starting from:</strong> <strong>$3,580 per person</strong>',
                    'more_info_url' => '/packages/tanzania-zanzibar-honeymoon-9-day/',
                    'modal_image_alt' => 'Tanzania and Zanzibar private honeymoon journey',
                    'inquiry_subject' => '9-Day%20Tanzania%20%26%20Zanzibar%20Honeymoon%20Journey%20Inquiry',
                    'modal_points' => array(
                        'Private 9-day route for 2 travelers, ideal for honeymoon and special occasions.',
                        'Three full safari days in Tarangire plus Manyara highland lodge experience.',
                        'Internal flights included: Arusha to Zanzibar and Zanzibar to JRO.',
                        'Beach relaxation in Pwani Mchangani with half-board at Next Paradise Boutique Resort.',
                        'Professional guide, unlimited game drives, park fees and airport transfers included.',
                    ),
                ),
            ),
        ),
        'about' => array(
            'eyebrow' => 'Breeze Safaris',
            'title' => 'Travel Specialists Based in Tanzania',
            'text' => 'We are two safari specialists living in Tanzania, working with trusted local operators to design seamless journeys with genuine local depth.',
        ),
        'parks' => array(
            'head_kicker' => "Why You'll Love Tanzania",
            'head_title' => 'Places That Turn Trips Into Stories',
            'intro' => 'Exceptional camps, iconic landscapes and unforgettable wildlife moments across Tanzania.',
            'slides' => array(
                array(
                    'slug' => 'serengeti-national-park',
                    'tag' => 'Serengeti',
                    'title' => 'Predator Kingdom',
                    'text' => "Vast plains, big cats and migration movement make Serengeti one of Africa's defining safari ecosystems.",
                    'image' => 'https://breezesafaris.com/wp-content/uploads/2026/01/serengeti-4-1200x800.jpg',
                    'alt' => 'Serengeti wildlife plains',
                ),
                array(
                    'slug' => 'ngorongoro-conservation-area',
                    'tag' => 'Ngorongoro',
                    'title' => 'Into the Crater',
                    'text' => 'A dramatic caldera with dense wildlife sightings and one of the strongest single-day game drive experiences.',
                    'image' => 'https://breezesafaris.com/wp-content/uploads/2017/09/ngorongoro1-scaled-1200x800.jpg',
                    'alt' => 'Ngorongoro crater landscape',
                ),
                array(
                    'slug' => 'tarangire-national-park',
                    'tag' => 'Tarangire',
                    'title' => 'Baobab & Elephants',
                    'text' => 'Known for giant baobabs and elephant herds, Tarangire offers a relaxed pace and beautiful light.',
                    'image' => 'https://breezesafaris.com/wp-content/uploads/2017/09/tarangire-2-1200x800.jpg',
                    'alt' => 'Tarangire baobab landscape',
                ),
                array(
                    'slug' => 'lake-manyara-national-park',
                    'tag' => 'Lake Manyara',
                    'title' => 'Scenic Diversity',
                    'text' => 'Forest, lake edge and escarpment views create a compact park with excellent visual variety.',
                    'image' => 'https://breezesafaris.com/wp-content/uploads/2017/09/manyara-2-scaled-1200x800.jpg',
                    'alt' => 'Lake Manyara scenery',
                ),
                array(
                    'slug' => 'arusha-national-park',
                    'tag' => 'Arusha',
                    'title' => 'Soft Start Safari',
                    'text' => 'An ideal first stop near arrival for a smooth transition into longer northern circuit routes.',
                    'image' => 'https://breezesafaris.com/wp-content/uploads/2017/09/Arusha-1-scaled-1200x800.jpg',
                    'alt' => 'Arusha national park safari view',
                ),
                array(
                    'slug' => 'ndutu-area',
                    'tag' => 'Ndutu',
                    'title' => 'Calving Season Action',
                    'text' => 'Powerful migration moments and predator behavior for travelers planning around key wildlife windows.',
                    'image' => 'https://breezesafaris.com/wp-content/uploads/2017/09/Ndutu-1-scaled-1200x800.jpg',
                    'alt' => 'Ndutu migration plains',
                ),
                array(
                    'slug' => 'zanzibar',
                    'tag' => 'Zanzibar',
                    'title' => 'Beach Finale',
                    'text' => 'The perfect ending after safari days, with white sand, warm ocean water and curated coastal stays.',
                    'image' => 'https://breezesafaris.com/wp-content/uploads/2026/01/zanzibar-1-1200x800.jpg',
                    'alt' => 'Zanzibar beach extension',
                ),
            ),
        ),
        'destinations' => array(
            'head_kicker' => 'National Parks & Beach',
            'head_title' => 'Explore Tanzania Destinations',
            'cards' => array(
                array(
                    'slug' => 'serengeti-national-park',
                    'title' => 'Serengeti National Park',
                    'image' => 'https://breezesafaris.com/wp-content/uploads/2026/01/serengeti-4-1200x800.jpg',
                    'alt' => 'Serengeti National Park safari',
                ),
                array(
                    'slug' => 'ngorongoro-conservation-area',
                    'title' => 'Ngorongoro Conservation Area',
                    'image' => 'https://breezesafaris.com/wp-content/uploads/2017/09/ngorongoro1-scaled-1200x800.jpg',
                    'alt' => 'Ngorongoro Conservation Area safari',
                ),
                array(
                    'slug' => 'tarangire-national-park',
                    'title' => 'Tarangire National Park',
                    'image' => 'https://breezesafaris.com/wp-content/uploads/2017/09/tarangire-2-1200x800.jpg',
                    'alt' => 'Tarangire National Park safari',
                ),
                array(
                    'slug' => 'lake-manyara-national-park',
                    'title' => 'Lake Manyara National Park',
                    'image' => 'https://breezesafaris.com/wp-content/uploads/2017/09/manyara-2-scaled-1200x800.jpg',
                    'alt' => 'Lake Manyara National Park safari',
                ),
                array(
                    'slug' => 'arusha-national-park',
                    'title' => 'Arusha National Park',
                    'image' => 'https://breezesafaris.com/wp-content/uploads/2017/09/Arusha-1-scaled-1200x800.jpg',
                    'alt' => 'Arusha National Park safari',
                ),
                array(
                    'slug' => 'ndutu-area',
                    'title' => 'Ndutu Area',
                    'image' => 'https://breezesafaris.com/wp-content/uploads/2017/09/Ndutu-1-scaled-1200x800.jpg',
                    'alt' => 'Ndutu Area safari',
                ),
                array(
                    'slug' => 'zanzibar',
                    'title' => 'Zanzibar',
                    'image' => 'https://breezesafaris.com/wp-content/uploads/2026/01/zanzibar-1-1200x800.jpg',
                    'alt' => 'Zanzibar beach holiday',
                ),
            ),
        ),
        'footer' => array(
            'cta_kicker' => 'Breeze Safaris',
            'cta_title' => 'Your Tanzania Story Starts Here',
            'cta_button_text' => 'Start Planning',
            'cta_button_url' => home_url('/#packages'),
            'brand_text' => 'Private Tanzania safaris and Zanzibar journeys, planned with trusted local experts.',
            'email' => 'info@breezesafaris.com',
            'phone' => '',
            'location' => 'Based in Tanzania',
            'extra_line' => 'Private Safari Planning',
            'copyright' => '© 2026 Breeze Safaris',
            'tagline' => 'Explore • Discover • Relax',
        ),
    );

    $defaults['hero']['video_url'] = breeze_theme_media_url('videos/breezesite.mp4');

    $defaults['packages']['cards'][0]['image'] = breeze_theme_media_url('photos/Ndutu 1.jpg');
    $defaults['packages']['cards'][1]['image'] = breeze_theme_media_url('photos/migration.jpg');
    $defaults['packages']['cards'][2]['image'] = breeze_theme_media_url('photos/zanzibar1.jpg');

    $destination_card_images = array(
        'serengeti-national-park' => 'photos/serengeti 4.jpg',
        'ngorongoro-conservation-area' => 'photos/ngorongoro1.jpg',
        'tarangire-national-park' => 'photos/tarangire 2.jpg',
        'lake-manyara-national-park' => 'photos/manyara 2.jpg',
        'arusha-national-park' => 'photos/Arusha 1.jpg',
        'ndutu-area' => 'photos/Ndutu 1.jpg',
        'zanzibar' => 'photos/zanzibar3.jpg',
    );

    foreach ($defaults['parks']['slides'] as $index => $slide) {
        $slug = isset($slide['slug']) ? $slide['slug'] : '';
        if (isset($destination_card_images[$slug])) {
            $defaults['parks']['slides'][$index]['image'] = breeze_theme_media_url($destination_card_images[$slug]);
        }
    }

    foreach ($defaults['destinations']['cards'] as $index => $card) {
        $slug = isset($card['slug']) ? $card['slug'] : '';
        if (isset($destination_card_images[$slug])) {
            $defaults['destinations']['cards'][$index]['image'] = breeze_theme_media_url($destination_card_images[$slug]);
        }
    }

    return $defaults;
}

function breeze_get_destination_defaults() {
    $defaults = array(
        'serengeti-national-park' => array(
            'title' => 'Serengeti National Park',
            'eyebrow' => 'Northern Tanzania Circuit',
            'hero_subtitle' => "Iconic open plains, high predator concentration and migration routes that create one of Africa's greatest safari experiences.",
            'hero_image' => 'https://breezesafaris.com/wp-content/uploads/2026/01/serengeti-4-2000x1333.jpg',
            'intro' => array(
                'Serengeti is the heartbeat of many Tanzania safari itineraries. Breeze Safaris designs each route around seasonality, preferred travel pace and accommodation style, so every game drive feels intentional rather than rushed.',
                'Whether you are chasing big cats, planning around migration movement, or combining northern parks with a Zanzibar extension, we build private journeys with trusted local partners and on-ground support.',
            ),
            'facts' => array(
                'Best for: Big cats, migration tracking, classic safari landscapes',
                'Ideal stay: 2 to 4 nights',
                'Pairs well with: Ngorongoro, Tarangire, Zanzibar',
                'Style: Private 4x4 game drives with expert local guides',
            ),
            'story_title' => 'Why Serengeti Feels Exceptional',
            'stories' => array(
                array(
                    'title' => 'Predator Action',
                    'text' => 'The Serengeti ecosystem consistently delivers excellent lion, cheetah and hyena encounters during well-planned game drives.',
                ),
                array(
                    'title' => 'Seasonal Routing',
                    'text' => 'Migration movement shifts throughout the year. We align your camps and drive areas with the strongest wildlife windows.',
                ),
                array(
                    'title' => 'Flexible Experience',
                    'text' => 'From authentic tented camps to premium lodges, your itinerary is tailored to budget, comfort preferences and travel rhythm.',
                ),
            ),
            'gallery_title' => 'Serengeti Gallery',
            'gallery' => array(
                array('class' => 'photo-xl', 'url' => 'https://breezesafaris.com/wp-content/uploads/2026/01/serengeti-4-2000x1333.jpg', 'alt' => 'Serengeti plains and wildlife view'),
                array('class' => 'photo-wide', 'url' => 'https://breezesafaris.com/wp-content/uploads/2026/01/serengeti-3-2000x1250.jpg', 'alt' => 'Serengeti game drive scenery'),
                array('class' => 'photo-mid', 'url' => 'https://breezesafaris.com/wp-content/uploads/2026/01/serengeti-5-2000x1196.jpg', 'alt' => 'Serengeti wildlife moment'),
                array('class' => 'photo-mid', 'url' => 'https://breezesafaris.com/wp-content/uploads/2026/01/serengeti-6-2000x1201.jpg', 'alt' => 'Serengeti safari photography'),
                array('class' => 'photo-sm', 'url' => 'https://breezesafaris.com/wp-content/uploads/2018/01/serengeti-scaled-2048x1536.jpg', 'alt' => 'Classic Serengeti landscape'),
            ),
            'cta_title' => 'Plan Your Serengeti Safari',
            'cta_text' => 'Tell us your dates, travel style and budget. We will shape the best Serengeti route for your trip.',
            'cta_button_text' => 'Inquire',
            'cta_button_url' => 'mailto:info@breezesafaris.com?subject=Serengeti%20Safari%20Inquiry',
        ),
        'ngorongoro-conservation-area' => array(
            'title' => 'Ngorongoro Conservation Area',
            'eyebrow' => 'Northern Tanzania Circuit',
            'hero_subtitle' => "A dramatic volcanic caldera with one of Africa's strongest single-day wildlife viewing experiences.",
            'hero_image' => 'https://breezesafaris.com/wp-content/uploads/2017/09/ngorongoro1-scaled-2048x1366.jpg',
            'intro' => array(
                'Ngorongoro is one of the most iconic safari locations in East Africa. The crater floor concentrates wildlife in an extraordinary setting, making it a key highlight for travelers who want strong sightings with efficient routing.',
                'Breeze Safaris combines Ngorongoro with Serengeti, Tarangire and Lake Manyara to create well-paced itineraries that maximize time in the field while keeping logistics smooth.',
            ),
            'facts' => array(
                'Best for: Dense wildlife viewing and dramatic scenery',
                'Ideal stay: 1 to 2 nights',
                'Pairs well with: Serengeti and Tarangire',
                'Style: Private crater descent and full-day game drives',
            ),
            'story_title' => 'Why Travelers Love Ngorongoro',
            'stories' => array(
                array(
                    'title' => 'Crater Ecology',
                    'text' => 'A compact ecosystem where herbivores and predators can be observed in a single, high-impact game drive day.',
                ),
                array(
                    'title' => 'Visual Drama',
                    'text' => "Steep crater walls, open plains and seasonal lakes create one of Tanzania's most photogenic safari landscapes.",
                ),
                array(
                    'title' => 'Route Efficiency',
                    'text' => 'Ideal as a strategic stop between Serengeti and other northern parks, especially for shorter premium itineraries.',
                ),
            ),
            'gallery_title' => 'Ngorongoro Gallery',
            'gallery' => array(
                array('class' => 'photo-xl', 'url' => 'https://breezesafaris.com/wp-content/uploads/2017/09/ngorongoro1-scaled-2048x1366.jpg', 'alt' => 'Ngorongoro crater panoramic view'),
                array('class' => 'photo-wide', 'url' => 'https://breezesafaris.com/wp-content/uploads/2017/09/ngorongoro1-2000x1333.jpg', 'alt' => 'Ngorongoro wildlife landscape'),
                array('class' => 'photo-mid', 'url' => 'https://breezesafaris.com/wp-content/uploads/2017/09/ngorongoro1-scaled-1536x1024.jpg', 'alt' => 'Ngorongoro conservation area safari'),
                array('class' => 'photo-mid', 'url' => 'https://breezesafaris.com/wp-content/uploads/2017/09/ngorongoro1-1024x683.jpg', 'alt' => 'Game drive in Ngorongoro'),
                array('class' => 'photo-sm', 'url' => 'https://breezesafaris.com/wp-content/uploads/2017/09/ngorongoro1-950x320.jpg', 'alt' => 'Ngorongoro crater rim view'),
            ),
            'cta_title' => 'Design Your Ngorongoro Stop',
            'cta_text' => 'We will align your crater day with the rest of your Tanzania safari for better pace, stronger sightings and less transit stress.',
            'cta_button_text' => 'Inquire',
            'cta_button_url' => 'mailto:info@breezesafaris.com?subject=Ngorongoro%20Safari%20Inquiry',
        ),
        'tarangire-national-park' => array(
            'title' => 'Tarangire National Park',
            'eyebrow' => 'Northern Tanzania Circuit',
            'hero_subtitle' => 'A quieter safari atmosphere known for giant baobabs, sweeping views and large seasonal elephant concentrations.',
            'hero_image' => 'https://breezesafaris.com/wp-content/uploads/2017/09/tarangire-2-scaled.jpg',
            'intro' => array(
                'Tarangire offers a distinct mood compared with other northern parks. It is spacious, scenic and ideal for travelers who enjoy slower-paced game viewing with fewer vehicles and rich photographic opportunities.',
                'Breeze Safaris often places Tarangire at the beginning of an itinerary, building momentum before moving into Ngorongoro and Serengeti for longer wildlife-focused circuits.',
            ),
            'facts' => array(
                'Best for: Elephant sightings, baobab scenery, calmer drives',
                'Ideal stay: 1 to 2 nights',
                'Pairs well with: Lake Manyara, Ngorongoro, Serengeti',
                'Style: Private drives with flexible timing',
            ),
            'story_title' => 'Why Tarangire Works So Well',
            'stories' => array(
                array(
                    'title' => 'Elephant Territory',
                    'text' => "Tarangire is one of Tanzania's standout parks for elephant encounters, especially in dry-season periods.",
                ),
                array(
                    'title' => 'Landscape Character',
                    'text' => 'Ancient baobabs, river channels and open woodlands create visual depth that feels different from open Serengeti plains.',
                ),
                array(
                    'title' => 'Pacing Advantage',
                    'text' => 'Its location and rhythm make Tarangire excellent for balancing travel flow in tailor-made northern itineraries.',
                ),
            ),
            'gallery_title' => 'Tarangire Gallery',
            'gallery' => array(
                array('class' => 'photo-xl', 'url' => 'https://breezesafaris.com/wp-content/uploads/2017/09/tarangire-2-scaled.jpg', 'alt' => 'Tarangire landscape with baobab trees'),
                array('class' => 'photo-wide', 'url' => 'https://breezesafaris.com/wp-content/uploads/2017/09/tarangire-2-2000x1723.jpg', 'alt' => 'Tarangire wildlife in golden light'),
                array('class' => 'photo-mid', 'url' => 'https://breezesafaris.com/wp-content/uploads/2017/09/tarangire-2-1200x800.jpg', 'alt' => 'Tarangire National Park game drive'),
                array('class' => 'photo-mid', 'url' => 'https://breezesafaris.com/wp-content/uploads/2017/09/tarangire-2-1024x882.jpg', 'alt' => 'Tarangire National Park safari view'),
                array('class' => 'photo-sm', 'url' => 'https://breezesafaris.com/wp-content/uploads/2017/09/tarangire-2-950x320.jpg', 'alt' => 'Wide Tarangire panorama'),
            ),
            'cta_title' => 'Build Your Tarangire Itinerary',
            'cta_text' => 'We can combine Tarangire with the right park sequence for your preferred trip length and wildlife priorities.',
            'cta_button_text' => 'Inquire',
            'cta_button_url' => 'mailto:info@breezesafaris.com?subject=Tarangire%20Safari%20Inquiry',
        ),
        'lake-manyara-national-park' => array(
            'title' => 'Lake Manyara National Park',
            'eyebrow' => 'Northern Tanzania Circuit',
            'hero_subtitle' => 'A compact but visually rich park with forest, lake-edge wildlife and dramatic Rift Valley escarpment scenery.',
            'hero_image' => 'https://breezesafaris.com/wp-content/uploads/2017/09/manyara-2-scaled-2048x1366.jpg',
            'intro' => array(
                'Lake Manyara is an excellent contrast point inside a northern Tanzania safari itinerary. The transition between forest zones, open areas and lake landscapes creates a distinct drive experience compared with larger parks.',
                'Breeze Safaris uses Manyara strategically for smooth routing, balanced wildlife variety and strong scenery before or after longer Serengeti segments.',
            ),
            'facts' => array(
                'Best for: Scenic diversity and short, high-value game drives',
                'Ideal stay: 1 night or full-day visit',
                'Pairs well with: Tarangire and Ngorongoro',
                'Style: Flexible private drives for mixed wildlife interests',
            ),
            'story_title' => 'What Makes Manyara Different',
            'stories' => array(
                array(
                    'title' => 'Habitat Variety',
                    'text' => 'From groundwater forest to open terrain and lake edge, Manyara offers visual and ecological diversity in a compact area.',
                ),
                array(
                    'title' => 'Great Transition Park',
                    'text' => 'Manyara fits naturally into northern circuits and helps optimize drive times between major safari zones.',
                ),
                array(
                    'title' => 'Photography Value',
                    'text' => 'Changing light and varied backdrops create strong opportunities for wildlife and landscape photography.',
                ),
            ),
            'gallery_title' => 'Lake Manyara Gallery',
            'gallery' => array(
                array('class' => 'photo-xl', 'url' => 'https://breezesafaris.com/wp-content/uploads/2017/09/manyara-2-scaled-2048x1366.jpg', 'alt' => 'Lake Manyara scenic wildlife view'),
                array('class' => 'photo-wide', 'url' => 'https://breezesafaris.com/wp-content/uploads/2017/09/manyara-2-2000x1334.jpg', 'alt' => 'Lake Manyara safari scenery'),
                array('class' => 'photo-mid', 'url' => 'https://breezesafaris.com/wp-content/uploads/2017/09/manyara-2-scaled-1536x1024.jpg', 'alt' => 'Lake Manyara National Park landscape'),
                array('class' => 'photo-mid', 'url' => 'https://breezesafaris.com/wp-content/uploads/2017/09/manyara-2-1024x683.jpg', 'alt' => 'Game drive in Lake Manyara'),
                array('class' => 'photo-sm', 'url' => 'https://breezesafaris.com/wp-content/uploads/2017/09/manyara-2-950x320.jpg', 'alt' => 'Lake Manyara panoramic view'),
            ),
            'cta_title' => 'Include Lake Manyara In Your Route',
            'cta_text' => 'We will position Manyara at the right point in your journey to add scenic variety and keep your safari pacing comfortable.',
            'cta_button_text' => 'Inquire',
            'cta_button_url' => 'mailto:info@breezesafaris.com?subject=Lake%20Manyara%20Safari%20Inquiry',
        ),
        'arusha-national-park' => array(
            'title' => 'Arusha National Park',
            'eyebrow' => 'Northern Tanzania Circuit',
            'hero_subtitle' => 'A beautiful soft-start safari park near Arusha, ideal for first wildlife encounters before deeper northern circuits.',
            'hero_image' => 'https://breezesafaris.com/wp-content/uploads/2017/09/Arusha-1-scaled-2048x1366.jpg',
            'intro' => array(
                'Arusha National Park is often overlooked, but it is an excellent beginning for travelers arriving in northern Tanzania. Its location makes it practical, while its scenery offers a calm and photogenic introduction to safari life.',
                'Breeze Safaris can include Arusha as a first-day game drive, helping you transition comfortably into longer wildlife routes across Tarangire, Ngorongoro and Serengeti.',
            ),
            'facts' => array(
                'Best for: Arrival-day safari and scenic variety',
                'Ideal stay: 1 day or 1 night',
                'Pairs well with: Tarangire and full northern circuit',
                'Style: Soft-start private drives with flexible timing',
            ),
            'story_title' => 'Why Add Arusha National Park',
            'stories' => array(
                array(
                    'title' => 'Excellent First Day',
                    'text' => 'Close proximity to Arusha allows for meaningful game viewing without long transit stress after flights.',
                ),
                array(
                    'title' => 'Visual Contrast',
                    'text' => 'Forest zones and open viewpoints bring a different atmosphere that complements later big-park experiences.',
                ),
                array(
                    'title' => 'Smart Route Design',
                    'text' => 'Works well as the opening chapter of a tailor-made itinerary before heading west into larger reserves.',
                ),
            ),
            'gallery_title' => 'Arusha Park Gallery',
            'gallery' => array(
                array('class' => 'photo-xl', 'url' => 'https://breezesafaris.com/wp-content/uploads/2017/09/Arusha-1-scaled-2048x1366.jpg', 'alt' => 'Arusha National Park scenic safari'),
                array('class' => 'photo-wide', 'url' => 'https://breezesafaris.com/wp-content/uploads/2017/09/Arusha-1-2000x1333.jpg', 'alt' => 'Arusha park landscape and wildlife'),
                array('class' => 'photo-mid', 'url' => 'https://breezesafaris.com/wp-content/uploads/2017/09/Arusha-1-scaled-1536x1024.jpg', 'alt' => 'Arusha National Park game drive'),
                array('class' => 'photo-mid', 'url' => 'https://breezesafaris.com/wp-content/uploads/2017/09/Arusha-1-1024x683.jpg', 'alt' => 'Arusha wildlife encounter'),
                array('class' => 'photo-sm', 'url' => 'https://breezesafaris.com/wp-content/uploads/2017/09/Arusha-1-950x320.jpg', 'alt' => 'Arusha panoramic scenery'),
            ),
            'cta_title' => 'Start Your Safari Smoothly',
            'cta_text' => 'We can include Arusha National Park as the perfect first chapter in your custom Tanzania route.',
            'cta_button_text' => 'Inquire',
            'cta_button_url' => 'mailto:info@breezesafaris.com?subject=Arusha%20National%20Park%20Inquiry',
        ),
        'ndutu-area' => array(
            'title' => 'Ndutu Area',
            'eyebrow' => 'Southern Serengeti Ecosystem',
            'hero_subtitle' => "One of Tanzania's key seasonal safari regions for migration calving activity and intense predator behavior.",
            'hero_image' => 'https://breezesafaris.com/wp-content/uploads/2017/09/Ndutu-1-scaled-2048x1366.jpg',
            'intro' => array(
                'Ndutu is all about timing. During the migration calving window, wildlife concentration can be extraordinary, making this one of the most dynamic safari locations for travelers focused on behavior, movement and photography.',
                'Breeze Safaris plans Ndutu around season, camp positioning and route flow, so your trip aligns with the strongest opportunities on the ground.',
            ),
            'facts' => array(
                'Best for: Migration calving season and predator activity',
                'Ideal stay: 2 to 3 nights in season',
                'Pairs well with: Serengeti and Ngorongoro',
                'Style: Seasonal private guiding with focused game drives',
            ),
            'story_title' => 'Why Ndutu Is Special',
            'stories' => array(
                array(
                    'title' => 'Calving Window',
                    'text' => 'When timed correctly, Ndutu offers unforgettable scenes tied to wildebeest births and predator responses.',
                ),
                array(
                    'title' => 'Action & Storytelling',
                    'text' => 'Rapid behavior changes and dynamic wildlife interactions make Ndutu a favorite for keen safari travelers.',
                ),
                array(
                    'title' => 'Route Integration',
                    'text' => 'Ndutu works best as part of a broader tailored circuit that includes Serengeti sectors and Ngorongoro access.',
                ),
            ),
            'gallery_title' => 'Ndutu Gallery',
            'gallery' => array(
                array('class' => 'photo-xl', 'url' => 'https://breezesafaris.com/wp-content/uploads/2017/09/Ndutu-1-scaled-2048x1366.jpg', 'alt' => 'Ndutu migration season scenery'),
                array('class' => 'photo-wide', 'url' => 'https://breezesafaris.com/wp-content/uploads/2017/09/Ndutu-1-2000x1334.jpg', 'alt' => 'Ndutu safari game drive landscape'),
                array('class' => 'photo-mid', 'url' => 'https://breezesafaris.com/wp-content/uploads/2017/09/Ndutu-1-scaled-1536x1024.jpg', 'alt' => 'Ndutu wildlife photography'),
                array('class' => 'photo-mid', 'url' => 'https://breezesafaris.com/wp-content/uploads/2017/09/Ndutu-1-1024x683.jpg', 'alt' => 'Ndutu plains and wildlife'),
                array('class' => 'photo-sm', 'url' => 'https://breezesafaris.com/wp-content/uploads/2017/09/Ndutu-1-950x320.jpg', 'alt' => 'Ndutu panoramic view'),
            ),
            'cta_title' => 'Time Your Ndutu Safari Right',
            'cta_text' => 'Share your travel window and we will design the strongest migration-focused Ndutu route for your trip.',
            'cta_button_text' => 'Inquire',
            'cta_button_url' => 'mailto:info@breezesafaris.com?subject=Ndutu%20Safari%20Inquiry',
        ),
        'zanzibar' => array(
            'title' => 'Zanzibar Extension',
            'eyebrow' => 'Safari + Beach Combination',
            'hero_subtitle' => 'Finish your Tanzania safari with curated Indian Ocean downtime in Zanzibar, planned to match your preferred pace and style.',
            'hero_image' => 'https://breezesafaris.com/wp-content/uploads/2025/11/pexels-followalice-667205-2000x1379.jpg',
            'intro' => array(
                'After intense safari days, Zanzibar is the ideal transition into rest, sea breeze and coastal comfort. Breeze Safaris arranges smooth domestic links from bush destinations to island stays with no unnecessary friction.',
                'From boutique hideaways to luxury beach resorts, we tailor your Zanzibar leg around trip length, budget and the atmosphere you want after the safari chapter.',
            ),
            'facts' => array(
                'Best for: Post-safari relaxation and celebration trips',
                'Ideal stay: 3 to 5 nights',
                'Pairs well with: Serengeti, Ngorongoro, Tarangire circuits',
                'Style: End-to-end safari + beach itinerary coordination',
            ),
            'story_title' => 'Why Add Zanzibar',
            'stories' => array(
                array(
                    'title' => 'Perfect Contrast',
                    'text' => 'Wildlife intensity followed by beach calm creates a complete and balanced Tanzania travel experience.',
                ),
                array(
                    'title' => 'Curated Stays',
                    'text' => 'We match property style to your travel profile, from intimate boutique options to premium oceanfront resorts.',
                ),
                array(
                    'title' => 'Seamless Logistics',
                    'text' => 'Flight timing and transfer coordination are built into your itinerary so the bush-to-beach transition stays smooth.',
                ),
            ),
            'gallery_title' => 'Zanzibar Gallery',
            'gallery' => array(
                array('class' => 'photo-xl', 'url' => 'https://breezesafaris.com/wp-content/uploads/2025/11/pexels-followalice-667205-2000x1379.jpg', 'alt' => 'Zanzibar beach view'),
                array('class' => 'photo-wide', 'url' => 'https://breezesafaris.com/wp-content/uploads/2025/11/IMG_7208-1-2000x1333.png', 'alt' => 'Luxury stay atmosphere for Zanzibar extension'),
                array('class' => 'photo-mid', 'url' => 'https://breezesafaris.com/wp-content/uploads/2025/11/MTC-tented-suite2.webp', 'alt' => 'Premium accommodation example'),
                array('class' => 'photo-mid', 'url' => 'https://breezesafaris.com/wp-content/uploads/2025/11/IMG_7903.jpg', 'alt' => 'Safari and beach lifestyle moment'),
                array('class' => 'photo-sm', 'url' => 'https://breezesafaris.com/wp-content/uploads/2026/02/image-2-edited.jpg', 'alt' => 'Zanzibar holiday detail'),
            ),
            'cta_title' => 'Add Zanzibar To Your Safari',
            'cta_text' => 'Tell us your preferred number of beach days and we will design a seamless Tanzania + Zanzibar combination.',
            'cta_button_text' => 'Inquire',
            'cta_button_url' => 'mailto:info@breezesafaris.com?subject=Zanzibar%20Extension%20Inquiry',
        ),
    );

    $destination_media = array(
        'serengeti-national-park' => array(
            'hero' => 'photos/serengeti 6.jpg',
            'gallery' => array('photos/serengeti 4.jpg', 'photos/serengeti 3.jpg', 'photos/serengeti 5.jpg', 'photos/chita.jpg', 'photos/zebra.jpg'),
        ),
        'ngorongoro-conservation-area' => array(
            'hero' => 'photos/ngorongoro1.jpg',
            'gallery' => array('photos/ngorongoro1.jpg', 'photos/zebra.jpg', 'photos/ngorongoro (1).jpg', 'photos/ngorongoro (2).jpg', 'photos/ngorongoro (3).jpg'),
        ),
        'tarangire-national-park' => array(
            'hero' => 'photos/tarangire 1.avif',
            'gallery' => array('photos/tarangire 2.jpg', 'photos/tarangire 1.avif', 'photos/zebra.jpg', 'photos/migration.jpg', 'photos/chita.jpg'),
        ),
        'lake-manyara-national-park' => array(
            'hero' => 'photos/manyara 2.jpg',
            'gallery' => array('photos/manyara 2.jpg', 'photos/manyara (1).jpg', 'photos/manyara (2).jpg', 'photos/manyara (3).jpg', 'photos/manyara (4).jpg'),
        ),
        'arusha-national-park' => array(
            'hero' => 'photos/arusha (1).jpg',
            'gallery' => array('photos/arusha (1).jpg', 'photos/arusha (2).jpg', 'photos/arusha (3).jpg', 'photos/arusha (4).jpg', 'photos/arusha 5.jpg'),
        ),
        'ndutu-area' => array(
            'hero' => 'photos/ndutu1.jpg',
            'gallery' => array('photos/Ndutu 1.jpg', 'photos/ndutu2.jpg', 'photos/ndutu3.jpg', 'photos/ndutu4.jpg', 'photos/ndutu5.jpg'),
        ),
        'zanzibar' => array(
            'hero' => 'photos/zanzibar3.jpg',
            'gallery' => array('photos/zanzibar.jpg', 'photos/zanzibar3.jpg', 'photos/1764942777293189ameliazanzibar-new-the-level-grand-3-bedroom-ocean-villa-master-room.jpg', 'photos/1764942777294189omeliazanzibar-new-the-level-grand-3-bedroom-ocean-villa-infinity-pool.jpg', 'photos/1764942777295189qmeliazanzibar-new-the-level-grand-3-bedroom-ocean-villa-sunrise.jpg'),
        ),
    );

    foreach ($destination_media as $slug => $media) {
        if (!isset($defaults[$slug])) {
            continue;
        }

        if (!empty($media['hero'])) {
            $defaults[$slug]['hero_image'] = breeze_theme_media_url($media['hero']);
        }

        if (isset($defaults[$slug]['gallery']) && is_array($defaults[$slug]['gallery']) && !empty($media['gallery'])) {
            foreach ($media['gallery'] as $index => $asset_path) {
                if (!isset($defaults[$slug]['gallery'][$index])) {
                    continue;
                }
                $defaults[$slug]['gallery'][$index]['url'] = breeze_theme_media_url($asset_path);
            }
        }
    }

    return $defaults;
}

function breeze_get_destination_default($slug) {
    $all = breeze_get_destination_defaults();

    if (isset($all[$slug])) {
        return $all[$slug];
    }

    return array(
        'title' => '',
        'eyebrow' => 'Tanzania Destination',
        'hero_subtitle' => '',
        'hero_image' => '',
        'intro' => array(),
        'facts' => array(),
        'story_title' => 'Highlights',
        'stories' => array(),
        'gallery_title' => 'Gallery',
        'gallery' => array(),
        'cta_title' => '',
        'cta_text' => '',
        'cta_button_text' => 'Inquire',
        'cta_button_url' => 'mailto:info@breezesafaris.com',
    );
}

function breeze_get_privacy_policy_default_content() {
    return <<<HTML
<p><strong>Last updated: February 2026</strong></p>
<p>Breeze Safaris is committed to protecting your personal data and respecting your privacy. This Privacy Policy explains how we collect, use, process and safeguard your information when you visit <a href="https://breezesafaris.com">https://breezesafaris.com</a> or contact us regarding our services.</p>
<h2>1. Who We Are</h2>
<p>Our website address is: <a href="https://breezesafaris.com">https://breezesafaris.com</a>.</p>
<h2>2. Data Controller</h2>
<p>For the purposes of the General Data Protection Regulation, GDPR, Breeze Safaris acts as the Data Controller in relation to personal data collected through this website.</p>
<p>Brand name: Breeze Safaris<br>Website: <a href="https://breezesafaris.com">https://breezesafaris.com</a><br>Contact email: <a href="mailto:info@breezesafaris.com">info@breezesafaris.com</a></p>
<p>If you have any questions regarding this Privacy Policy or your personal data, please contact us using the email above.</p>
<h2>3. Our Business Model</h2>
<p>Breeze Safaris is not a licensed tour operator. We act as an intermediary between clients and licensed local tour operators in Tanzania.</p>
<p>We design tailor made safari itineraries and connect clients with carefully selected local partners who execute the travel services. Once a booking is confirmed, certain personal data may be shared with the relevant local operator strictly for the purpose of delivering the travel services.</p>
<h2>4. Personal Data We Collect</h2>
<p>We may collect and process the following personal data:</p>
<ul>
<li>Full name</li>
<li>Email address</li>
<li>Telephone number</li>
<li>Country of residence</li>
<li>Travel preferences and itinerary requirements</li>
<li>Passport information when required for bookings</li>
<li>Any additional information voluntarily provided through contact forms or email communication</li>
</ul>
<p>We may also collect technical data automatically, including:</p>
<ul>
<li>IP address</li>
<li>Browser type and version</li>
<li>Device type</li>
<li>Pages visited</li>
<li>Date and time of access</li>
</ul>
<h2>5. Legal Basis for Processing</h2>
<p>We process your personal data based on one or more of the following legal grounds:</p>
<ul>
<li>Your consent when submitting a contact form</li>
<li>Pre contractual steps taken at your request</li>
<li>Performance of a contract once a booking is confirmed</li>
<li>Compliance with legal obligations</li>
<li>Legitimate interests in operating and improving our services</li>
</ul>
<h2>6. Purpose of Processing</h2>
<p>Your personal data is processed for the following purposes:</p>
<ul>
<li>Responding to enquiries</li>
<li>Preparing customised safari proposals</li>
<li>Coordinating bookings with local tour operators</li>
<li>Communicating important travel information</li>
<li>Improving website performance and user experience</li>
<li>Marketing activities where consent has been provided</li>
</ul>
<p>We do not sell, rent or trade your personal data.</p>
<h2>7. Data Sharing and Third Parties</h2>
<p>Your personal data may be shared with:</p>
<ul>
<li>Licensed local tour operators in Tanzania responsible for delivering the travel services</li>
<li>Accommodation providers and service suppliers relevant to your itinerary</li>
<li>Website hosting providers</li>
<li>Email service providers</li>
<li>Marketing and analytics providers such as Google Analytics, Google Ads and Meta Ads</li>
<li>Legal or regulatory authorities when required by law</li>
</ul>
<p>Local tour operators receiving your data may act as independent Data Controllers under applicable laws in their jurisdiction.</p>
<p>We ensure that data is shared only when necessary and proportionate to deliver the requested services.</p>
<h2>8. International Data Transfers</h2>
<p>As we work with partners based in Tanzania, your personal data may be transferred outside the European Economic Area.</p>
<p>When such transfers occur, we take reasonable steps to ensure appropriate safeguards are in place to protect your data in accordance with GDPR requirements.</p>
<p>By requesting our services, you acknowledge that such international transfers may be necessary for the performance of your travel arrangements.</p>
<h2>9. Data Retention</h2>
<p>We retain personal data only for as long as necessary to:</p>
<ul>
<li>Fulfil the purposes outlined in this Policy</li>
<li>Comply with legal, tax and accounting obligations</li>
<li>Resolve disputes</li>
</ul>
<p>When data is no longer required, it will be securely deleted or anonymised.</p>
<h2>10. Your Rights Under GDPR</h2>
<p>If you are located in the European Union or United Kingdom, you have the right to:</p>
<ul>
<li>Access your personal data</li>
<li>Request correction of inaccurate data</li>
<li>Request erasure of your data</li>
<li>Request restriction of processing</li>
<li>Object to processing</li>
<li>Request data portability</li>
<li>Withdraw consent at any time</li>
</ul>
<p>To exercise any of these rights, please contact us at <a href="mailto:info@breezesafaris.com">info@breezesafaris.com</a>.</p>
<p>You also have the right to lodge a complaint with your local data protection supervisory authority.</p>
<h2>11. Cookies and Tracking Technologies</h2>
<p>Our website may use cookies and similar technologies to:</p>
<ul>
<li>Analyse website traffic</li>
<li>Improve user experience</li>
<li>Deliver relevant advertising</li>
</ul>
<p>You may control or disable cookies through your browser settings. A cookie consent banner may appear when you first visit our website.</p>
<h2>12. Data Security</h2>
<p>We implement appropriate technical and organisational measures to protect personal data against unauthorised access, alteration, disclosure or destruction.</p>
<p>However, no method of transmission over the internet is completely secure and we cannot guarantee absolute security.</p>
<h2>13. Changes to This Policy</h2>
<p>We reserve the right to update this Privacy Policy at any time. Any changes will be published on this page with an updated revision date.</p>
<p>We encourage users to review this Policy periodically.</p>
<h2>14. Exercising Your Data Rights</h2>
<p>To exercise any of your rights under applicable data protection laws, including access, rectification, restriction, objection or erasure of your personal data, please contact:</p>
<p><strong><a href="mailto:info@breezesafaris.com">info@breezesafaris.com</a></strong></p>
<p>We may request proof of identity to ensure data security. All valid requests will be handled within the timeframes required under GDPR.</p>
HTML;
}

function breeze_build_intro_content($paragraphs) {
    if (!is_array($paragraphs) || empty($paragraphs)) {
        return '';
    }

    $html = '';
    foreach ($paragraphs as $paragraph) {
        $text = trim((string) $paragraph);
        if ($text === '') {
            continue;
        }
        $html .= '<p>' . esc_html($text) . '</p>' . "\n";
    }

    return trim($html);
}

function breeze_get_home_default_content() {
    $defaults = breeze_get_home_defaults();
    $hero = $defaults['hero'];
    $packages = $defaults['packages']['cards'];
    $parks = $defaults['parks']['slides'];
    $home_story_image = breeze_theme_media_url('photos/lion.jpg');

    ob_start();
    ?>
<main>
  <section class="hero" id="hero">
    <video autoplay muted loop playsinline preload="auto" class="hero-video">
      <source src="<?php echo esc_url($hero['video_url']); ?>" type="video/mp4">
    </video>
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <p class="hero-kicker"><?php echo esc_html($hero['kicker']); ?></p>
      <h1><?php echo esc_html($hero['title']); ?></h1>
      <p class="hero-sub"><?php echo wp_kses_post($hero['subtitle']); ?></p>
      <a href="<?php echo esc_url($hero['button_url']); ?>" class="btn"><?php echo esc_html($hero['button_text']); ?></a>
    </div>
  </section>

  <section class="packages" id="packages">
    <div class="section-head">
      <p><?php echo esc_html($defaults['packages']['head_kicker']); ?></p>
      <h2><?php echo esc_html($defaults['packages']['head_title']); ?></h2>
      <div class="section-cta">
        <a class="modal-btn modal-btn-secondary" href="<?php echo esc_url(home_url('/packages/')); ?>">View All Packages</a>
      </div>
    </div>
    <div class="packages-controls">
      <button type="button" class="packages-arrow" id="packagesPrev" aria-label="Previous packages">‹</button>
      <button type="button" class="packages-arrow" id="packagesNext" aria-label="Next packages">›</button>
    </div>
    <div class="cards packages-carousel" id="packagesCarousel">
      <?php foreach ($packages as $card) : ?>
        <article class="card interactive-card" role="button" tabindex="0" data-package="<?php echo esc_attr($card['key']); ?>" aria-label="<?php echo esc_attr('Open ' . $card['title'] . ' details'); ?>">
          <img src="<?php echo esc_url($card['image']); ?>" alt="<?php echo esc_attr($card['alt']); ?>">
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
    <div class="packages-dots" id="packagesDots" aria-label="Packages slider navigation">
      <?php foreach ($packages as $index => $card) : ?>
        <button type="button" class="dot <?php echo $index === 0 ? 'is-active' : ''; ?>" data-index="<?php echo esc_attr((string) $index); ?>" aria-label="<?php echo esc_attr('Go to package ' . ($index + 1)); ?>"></button>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="home-story" id="journey-design">
    <div class="home-story-shell">
      <figure class="home-story-media">
        <img src="<?php echo esc_url($home_story_image); ?>" alt="Lion in Tanzania during golden-hour safari light">
      </figure>
      <div class="home-story-copy">
        <p class="home-story-kicker">Designed in Tanzania</p>
        <h2>Every Journey Is Built Around Your Rhythm</h2>
        <p>
          We design each itinerary around your travel pace, preferred comfort level and the wildlife moments you care about most.
          The result is a trip that feels calm, intentional and deeply personal.
        </p>
        <ul class="home-story-points">
          <li>Private safari routing with realistic pacing across the northern circuit.</li>
          <li>Balanced combinations of iconic parks, boutique stays and optional Zanzibar finale.</li>
          <li>On-ground support from a Tanzania-based team from first inquiry to return flight.</li>
        </ul>
        <a class="modal-btn" href="<?php echo esc_url(home_url('/about/')); ?>">Meet Breeze Safaris</a>
      </div>
    </div>
  </section>

  <section class="parks-showcase" id="parks-showcase">
    <div class="parks-head section-head">
      <div>
        <p><?php echo esc_html($defaults['parks']['head_kicker']); ?></p>
        <h2><?php echo esc_html($defaults['parks']['head_title']); ?></h2>
        <p class="parks-intro"><?php echo esc_html($defaults['parks']['intro']); ?></p>
        <div class="parks-dots" id="parksDots" aria-label="Places slider navigation">
          <?php foreach ($parks as $index => $park) : ?>
            <button type="button" class="dot <?php echo $index === 0 ? 'is-active' : ''; ?>" data-index="<?php echo esc_attr((string) $index); ?>" aria-label="<?php echo esc_attr('Go to slide ' . ($index + 1)); ?>"></button>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="parks-controls">
        <button type="button" class="parks-arrow" id="parksPrev" aria-label="Previous places">‹</button>
        <button type="button" class="parks-arrow" id="parksNext" aria-label="Next places">›</button>
      </div>
    </div>
    <div class="parks-carousel-wrap">
      <div class="parks-carousel" id="parksCarousel">
        <?php foreach ($parks as $park) : ?>
          <article class="park-slide">
            <img src="<?php echo esc_url($park['image']); ?>" alt="<?php echo esc_attr($park['alt']); ?>">
            <div class="park-slide-content">
              <p class="park-tag"><?php echo esc_html($park['tag']); ?></p>
              <h3><?php echo esc_html($park['title']); ?></h3>
              <p><?php echo esc_html($park['text']); ?></p>
              <a href="<?php echo esc_url(home_url('/' . $park['slug'] . '/')); ?>">Explore Place</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

</main>

<div class="modal-overlay" id="packageModal" aria-hidden="true">
  <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <button class="modal-close" id="modalClose" aria-label="Close details">×</button>
    <div id="modalContent"></div>
  </div>
</div>

<?php foreach ($packages as $card) : ?>
<template id="tpl-<?php echo esc_attr($card['key']); ?>">
  <div class="modal-grid">
    <img src="<?php echo esc_url($card['image']); ?>" alt="<?php echo esc_attr(isset($card['modal_image_alt']) ? $card['modal_image_alt'] : $card['title']); ?>">
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
        <a class="modal-btn" href="<?php echo esc_url(add_query_arg('package', $card['title'], breeze_get_page_url_by_slug('contact', home_url('/contact/')))); ?>">Inquire</a>
        <?php if (!empty($card['more_info_url'])) : ?>
          <a class="modal-btn modal-btn-secondary" href="<?php echo esc_url($card['more_info_url']); ?>">More Info</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</template>
<?php endforeach; ?>
    <?php

    return trim((string) ob_get_clean());
}

function breeze_get_destination_default_content($slug) {
    $data = breeze_get_destination_default($slug);
    if (empty($data['title'])) {
        return '';
    }

    $intro = isset($data['intro']) && is_array($data['intro']) ? $data['intro'] : array();
    $facts = isset($data['facts']) && is_array($data['facts']) ? $data['facts'] : array();
    $stories = isset($data['stories']) && is_array($data['stories']) ? $data['stories'] : array();
    $gallery = isset($data['gallery']) && is_array($data['gallery']) ? $data['gallery'] : array();

    ob_start();
    ?>
<main class="destination-main">
  <section class="destination-hero" id="hero" style="background-image:url('<?php echo esc_url($data['hero_image']); ?>')">
    <div class="destination-hero-inner">
      <p class="destination-eyebrow"><?php echo esc_html($data['eyebrow']); ?></p>
      <h1><?php echo esc_html($data['title']); ?></h1>
      <p><?php echo esc_html($data['hero_subtitle']); ?></p>
    </div>
  </section>

  <section class="destination-intro section-shell">
    <div class="intro-copy">
      <?php foreach ($intro as $paragraph) : ?>
        <p><?php echo esc_html($paragraph); ?></p>
      <?php endforeach; ?>
    </div>
    <?php if (!empty($facts)) : ?>
      <aside class="facts-card">
        <h2>At a Glance</h2>
        <ul>
          <?php foreach ($facts as $fact) : ?>
            <li><?php echo esc_html($fact); ?></li>
          <?php endforeach; ?>
        </ul>
      </aside>
    <?php endif; ?>
  </section>

  <?php if (!empty($stories)) : ?>
    <section class="section-shell">
      <h2 class="section-title"><?php echo esc_html($data['story_title']); ?></h2>
      <div class="story-grid">
        <?php foreach ($stories as $story) : ?>
          <article class="story-card">
            <h3><?php echo esc_html($story['title']); ?></h3>
            <p><?php echo esc_html($story['text']); ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <?php if (!empty($gallery)) : ?>
    <section class="section-shell">
      <h2 class="section-title"><?php echo esc_html($data['gallery_title']); ?></h2>
      <div class="photo-grid">
        <?php foreach ($gallery as $photo) : ?>
          <figure class="<?php echo esc_attr($photo['class']); ?>"><img src="<?php echo esc_url($photo['url']); ?>" alt="<?php echo esc_attr($photo['alt']); ?>"></figure>
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
      <a href="<?php echo esc_url(breeze_get_page_url_by_slug('contact', home_url('/contact/'))); ?>"><?php echo esc_html($data['cta_button_text']); ?></a>
    </div>
  </section>
</main>
    <?php

    return trim((string) ob_get_clean());
}

function breeze_get_package_page_seed_definitions() {
    return array(
        array(
            'title' => '6-Day Ndutu Great Migration Safari',
            'slug' => 'ndutu-calving-season-safari',
            'file' => 'packages/ndutu-calving-season-safari.html',
        ),
        array(
            'title' => '8-Day Luxury Migration & Big Five Safari',
            'slug' => 'luxury-migration-big-five-safari-8-day',
            'file' => 'packages/luxury-migration-big-five-safari-8-day.html',
        ),
        array(
            'title' => 'Tanzania & Zanzibar: 9-Day Honeymoon Journey',
            'slug' => 'tanzania-zanzibar-honeymoon-9-day',
            'file' => 'packages/tanzania-zanzibar-honeymoon-9-day.html',
        ),
    );
}

function breeze_get_seed_page_definitions() {
    $pages = array(
        array(
            'title'    => 'Home',
            'slug'     => 'home',
            'content'  => breeze_get_home_default_content(),
            'template' => 'default',
        ),
        array(
            'title'    => 'About Us',
            'slug'     => 'about',
            'content'  => breeze_get_seed_content_from_static_file('about.html'),
            'template' => 'default',
        ),
        array(
            'title'    => 'All Safari Packages',
            'slug'     => 'packages',
            'content'  => breeze_get_seed_content_from_static_file('packages.html'),
            'template' => 'default',
        ),
        array(
            'title'    => 'Privacy Policy',
            'slug'     => 'privacy-policy',
            'content'  => breeze_get_privacy_policy_default_content(),
            'template' => 'default',
        ),
        array(
            'title'    => 'Contact',
            'slug'     => 'contact',
            'content'  => '',
            'template' => 'page-templates/contact-page.php',
        ),
    );

    foreach (breeze_get_package_page_seed_definitions() as $package_page) {
        $pages[] = array(
            'title'      => $package_page['title'],
            'slug'       => $package_page['slug'],
            'parent_slug'=> 'packages',
            'content'    => breeze_get_seed_content_from_static_file($package_page['file']),
            'template'   => 'default',
        );
    }

    foreach (breeze_get_destination_defaults() as $slug => $defaults) {
        $pages[] = array(
            'title'    => $defaults['title'],
            'slug'     => $slug,
            'content'  => '', // destination-page.php reads from breeze_get_destination_view_data()
            'template' => 'page-templates/destination-page.php',
        );
    }

    return $pages;
}

// ══════════════════════════════════════════════════════════════════════════════
// PT (Portuguese / Portugal) — data, seed definitions and page creation
// ══════════════════════════════════════════════════════════════════════════════

// ── PT link rewriter ──────────────────────────────────────────────────────────
function breeze_rewrite_static_seed_content_links_pt($html) {
    $html = (string) $html;
    if ($html === '') {
        return '';
    }

    $photo_base = trailingslashit(breeze_theme_media_url('photos'));
    $video_base = trailingslashit(breeze_theme_media_url('videos'));

    $pt_home    = trailingslashit(home_url('/pt'));
    $contacto   = trailingslashit(home_url('/pt/contacto'));
    $pacotes    = trailingslashit(home_url('/pt/pacotes'));
    $sobre_nos  = trailingslashit(home_url('/pt/sobre-nos'));

    $replace = array(
        // Media paths — handle 1 or 2 levels up (packages under /pt/pacotes/ need ../../)
        'src="../../media/photos/'  => 'src="' . $photo_base,
        'href="../../media/photos/' => 'href="' . $photo_base,
        'src="../media/photos/'     => 'src="' . $photo_base,
        'src="media/photos/'        => 'src="' . $photo_base,
        'href="../media/photos/'    => 'href="' . $photo_base,
        'href="media/photos/'       => 'href="' . $photo_base,
        'src="../media/videos/'     => 'src="' . $video_base,
        'src="media/videos/'        => 'src="' . $video_base,

        // Homepage anchors
        'href="../index.html#packages"'      => 'href="' . $pt_home . '#packages"',
        'href="index.html#packages"'         => 'href="' . $pt_home . '#packages"',
        'href="../index.html#parks-showcase"'=> 'href="' . $pt_home . '#parks-showcase"',
        'href="index.html#parks-showcase"'   => 'href="' . $pt_home . '#parks-showcase"',

        // Contact
        'href="../contacto.html?' => 'href="' . $contacto . '?',
        'href="contacto.html?'    => 'href="' . $contacto . '?',
        'href="../contacto.html"' => 'href="' . $contacto . '"',
        'href="contacto.html"'    => 'href="' . $contacto . '"',

        // Packages listing
        'href="../pacotes.html"'  => 'href="' . $pacotes . '"',
        'href="pacotes.html"'     => 'href="' . $pacotes . '"',

        // About
        'href="../sobre-nos.html"'=> 'href="' . $sobre_nos . '"',
        'href="sobre-nos.html"'   => 'href="' . $sobre_nos . '"',

        // PT destinations
        'href="destinos/parque-nacional-serengeti.html"'    => 'href="' . trailingslashit(home_url('/pt/parque-nacional-serengeti')) . '"',
        'href="destinos/area-conservacao-ngorongoro.html"'  => 'href="' . trailingslashit(home_url('/pt/area-conservacao-ngorongoro')) . '"',
        'href="destinos/parque-nacional-tarangire.html"'    => 'href="' . trailingslashit(home_url('/pt/parque-nacional-tarangire')) . '"',
        'href="destinos/parque-nacional-lago-manyara.html"' => 'href="' . trailingslashit(home_url('/pt/parque-nacional-lago-manyara')) . '"',
        'href="destinos/parque-nacional-arusha.html"'       => 'href="' . trailingslashit(home_url('/pt/parque-nacional-arusha')) . '"',
        'href="destinos/area-ndutu.html"'                   => 'href="' . trailingslashit(home_url('/pt/area-ndutu')) . '"',
        'href="destinos/zanzibar.html"'                     => 'href="' . trailingslashit(home_url('/pt/zanzibar')) . '"',

        // PT packages
        'href="pacotes/safari-migracao-ndutu-6-dias.html"'        => 'href="' . trailingslashit(home_url('/pt/pacotes/safari-migracao-ndutu-6-dias')) . '"',
        'href="pacotes/safari-luxo-big-five-8-dias.html"'          => 'href="' . trailingslashit(home_url('/pt/pacotes/safari-luxo-big-five-8-dias')) . '"',
        'href="pacotes/lua-de-mel-tanzania-zanzibar-9-dias.html"'  => 'href="' . trailingslashit(home_url('/pt/pacotes/lua-de-mel-tanzania-zanzibar-9-dias')) . '"',
    );

    return str_replace(array_keys($replace), array_values($replace), $html);
}

function breeze_get_seed_content_from_static_file_pt($relative_path) {
    $relative_path = ltrim((string) $relative_path, '/');
    if ($relative_path === '') {
        return '';
    }

    $file_path = trailingslashit(get_template_directory()) . 'seed-content/pt/' . $relative_path;
    if (!file_exists($file_path) || !is_readable($file_path)) {
        return '';
    }

    $raw = file_get_contents($file_path);
    if (!is_string($raw) || trim($raw) === '') {
        return '';
    }

    $raw = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $raw);
    if (!is_string($raw)) {
        return '';
    }

    if (preg_match('/<main\b.*?(?=<footer\b)/is', $raw, $matches)) {
        $raw = $matches[0];
    } elseif (preg_match('/<main\b.*?<\/main>/is', $raw, $matches)) {
        $raw = $matches[0];
    }

    if (preg_match('/<main\b[^>]*>(.*?)<\/main>/is', $raw, $main_matches, PREG_OFFSET_CAPTURE)) {
        $main_inner = $main_matches[1][0];
        $main_full  = $main_matches[0][0];
        $main_start = (int) $main_matches[0][1];
        $after_main = substr($raw, $main_start + strlen($main_full));
        $raw        = $main_inner . $after_main;
    }

    return trim(breeze_rewrite_static_seed_content_links_pt($raw));
}

// ── PT home defaults ──────────────────────────────────────────────────────────
function breeze_get_home_defaults_pt() {
    $en = breeze_get_home_defaults(); // inherit media URLs from EN

    return array(
        'logo' => $en['logo'],
        'hero' => array(
            'video_url'   => $en['hero']['video_url'],
            'kicker'      => 'Descobrir • Explorar • Descansar',
            'title'       => '',
            'subtitle'    => 'Experiências únicas com animais selvagens, guias locais de confiança e lodges cuidadosamente escolhidos.<br>Finalize a sua viagem no paraíso tropical de Zanzibar.',
            'button_text' => 'Começar a Planear',
            'button_url'  => '#packages',
        ),
        'packages' => array(
            'head_kicker' => 'Viagens em Destaque',
            'head_title'  => 'Pacotes de Safari',
            'cards'       => array(
                array(
                    'key'            => 'ndutu-calving-6d',
                    'image'          => $en['packages']['cards'][0]['image'],
                    'alt'            => 'Safari de migração de vida selvagem em Ndutu',
                    'label'          => 'Dezembro a Março',
                    'title'          => 'Safari da Grande Migração em Ndutu — 6 Dias',
                    'text'           => 'Siga a migração em Ndutu e termine com o clássico safari de um dia na Cratera de Ngorongoro.',
                    'price'          => 'A partir de <strong>$2.310 pp</strong>',
                    'modal_price'    => '<strong>A partir de:</strong> <strong>$2.310 por pessoa</strong>',
                    'more_info_url'  => '/pt/pacotes/safari-migracao-ndutu-6-dias/',
                    'modal_image_alt'=> 'Safari de migração em Ndutu',
                    'inquiry_subject'=> 'Inqu%C3%A9rito%20Safari%20Migra%C3%A7%C3%A3o%20Ndutu%206%20Dias',
                    'modal_points'   => array(
                        'Melhor calendarização sazonal em Ndutu para o movimento da migração e acção dos predadores.',
                        'Dias dedicados à fauna nas planícies de Ndutu e nas zonas ricas em vida selvagem circundantes.',
                        'Safari completo na Cratera de Ngorongoro como final de alto impacto.',
                        "Estadias incluem Arusha Planet Lodge, Ang'ata Migration Bologonja Camp e Embalakai Ngorongoro Camp.",
                        'Viatura 4x4 privada, taxas de parque e logística de safari guiada incluídas.',
                    ),
                ),
                array(
                    'key'            => 'luxury-migration-8d',
                    'image'          => $en['packages']['cards'][1]['image'],
                    'alt'            => 'Safari de luxo de migração na Tanzânia',
                    'label'          => 'Safari de Luxo',
                    'title'          => 'Safari de Luxo Migração & Big Five — 8 Dias',
                    'text'           => 'Tarangire, cruzamentos no Serengeti Norte e Ngorongoro, com final no Lago Manyara, numa rota premium.',
                    'price'          => 'A partir de <strong>$6.545 pp</strong>',
                    'modal_price'    => '<strong>A partir de:</strong> <strong>$6.545 por pessoa</strong>',
                    'more_info_url'  => '/pt/pacotes/safari-luxo-big-five-8-dias/',
                    'modal_image_alt'=> 'Safari de luxo de migração e Big Five',
                    'inquiry_subject'=> 'Inqu%C3%A9rito%20Safari%20Luxo%20Migra%C3%A7%C3%A3o%208%20Dias',
                    'modal_points'   => array(
                        'Rota premium pelo circuito norte com os destaques de Tarangire, Serengeti e Ngorongoro.',
                        'Timing no Serengeti Norte para acção da migração perto das zonas de cruzamento do Rio Mara.',
                        'Estadias em lodges de luxo incluindo Elewana Tarangire Treetops, Kubu Kubu e outros.',
                        '4x4 privado com guia especialista, taxas de parque, transfers aeroportuários e logística completa incluídos.',
                        'Equilíbrio ideal entre dias de fauna icónica e alojamentos de alto conforto.',
                    ),
                ),
                array(
                    'key'            => 'tanzania-zanzibar-9d',
                    'image'          => $en['packages']['cards'][2]['image'],
                    'alt'            => 'Viagem lua de mel Tanzânia e Zanzibar',
                    'label'          => 'Junho a Julho 2026',
                    'title'          => 'Viagem Lua de Mel Tanzânia & Zanzibar — 9 Dias',
                    'text'           => 'Safari privado em Tarangire e Manyara com final de praia em Zanzibar para uma ocasião especial.',
                    'price'          => 'A partir de <strong>$3.580 pp</strong>',
                    'modal_price'    => '<strong>A partir de:</strong> <strong>$3.580 por pessoa</strong>',
                    'more_info_url'  => '/pt/pacotes/lua-de-mel-tanzania-zanzibar-9-dias/',
                    'modal_image_alt'=> 'Viagem privada de lua de mel Tanzânia e Zanzibar',
                    'inquiry_subject'=> 'Inqu%C3%A9rito%20Lua%20de%20Mel%20Tanz%C3%A2nia%20Zanzibar%209%20Dias',
                    'modal_points'   => array(
                        'Rota privada de 9 dias para 2 viajantes, ideal para lua de mel e ocasiões especiais.',
                        'Três dias completos de safari em Tarangire e a experiência da lodge de montanha em Manyara.',
                        'Voos internos incluídos: Arusha para Zanzibar e Zanzibar para JRO.',
                        'Descanso na praia em Pwani Mchangani com meia-pensão no Next Paradise Boutique Resort.',
                        'Guia profissional, excursões ilimitadas, taxas de parque e transfers aeroportuários incluídos.',
                    ),
                ),
            ),
        ),
        'parks' => array(
            'head_kicker' => 'Porque Vai Adorar a Tanzânia',
            'head_title'  => 'Lugares que Transformam Viagens em Histórias',
            'intro'       => 'Campos excepcionais, paisagens icónicas e momentos inesquecíveis com a vida selvagem por toda a Tanzânia.',
            'slides'      => array(
                array('slug' => 'parque-nacional-serengeti',  'tag' => 'Serengeti',    'title' => 'Reino dos Predadores',    'text' => "Planícies vastas, grandes felinos e os movimentos da migração fazem do Serengeti um dos ecossistemas de safari mais marcantes de África.", 'image' => $en['parks']['slides'][0]['image'], 'alt' => 'Planícies e fauna do Serengeti'),
                array('slug' => 'area-conservacao-ngorongoro','tag' => 'Ngorongoro',   'title' => 'No Interior da Cratera',         'text' => 'Uma caldera dramática com avistamentos extraordinários de fauna, numa das experiências de safari mais intensas do dia.',             'image' => $en['parks']['slides'][1]['image'], 'alt' => 'Paisagem da cratera de Ngorongoro'),
                array('slug' => 'parque-nacional-tarangire',  'tag' => 'Tarangire',    'title' => 'Baobás e Elefantes',       'text' => 'Conhecido pelos enormes baobás e pelos bandos de elefantes, Tarangire oferece um ritmo calmo e uma luz fotográfica excepcional.',  'image' => $en['parks']['slides'][2]['image'], 'alt' => 'Paisagem de baobás em Tarangire'),
                array('slug' => 'parque-nacional-lago-manyara','tag' => 'Lago Manyara','title' => 'Diversidade Paisagística',  'text' => 'Floresta, margem de lago e vistas sobre a escarpa do Rift criam um parque compacto com uma variedade visual notável.',          'image' => $en['parks']['slides'][3]['image'], 'alt' => 'Paisagem do Lago Manyara'),
                array('slug' => 'parque-nacional-arusha',     'tag' => 'Arusha',       'title' => 'Começo Suave',             'text' => 'A paragem ideal no início do circuito norte, próxima da chegada, para uma transição tranquila para os grandes parques.',        'image' => $en['parks']['slides'][4]['image'], 'alt' => 'Vista do parque de Arusha'),
                array('slug' => 'area-ndutu',                 'tag' => 'Ndutu',        'title' => 'Época de Parição',         'text' => 'Momentos poderosos de migração e comportamentos de predação para viajantes que planeiam as janelas de maior actividade.',       'image' => $en['parks']['slides'][5]['image'], 'alt' => 'Planícies de migração de Ndutu'),
                array('slug' => 'zanzibar',                   'tag' => 'Zanzibar',     'title' => 'Final de Praia',           'text' => 'O desfecho perfeito após os dias de safari, com areia branca, oceano morno e estadias costeiras cuidadosamente seleccionadas.',  'image' => $en['parks']['slides'][6]['image'], 'alt' => 'Extensão de praia em Zanzibar'),
            ),
        ),
        'destinations' => array(
            'head_kicker' => 'Parques Nacionais e Praia',
            'head_title'  => 'Explorar os Destinos da Tanzânia',
            'cards'       => array(
                array('slug' => 'parque-nacional-serengeti',   'title' => 'Parque Nacional do Serengeti',   'image' => $en['destinations']['cards'][0]['image'], 'alt' => 'Safari no Parque Nacional do Serengeti'),
                array('slug' => 'area-conservacao-ngorongoro', 'title' => 'Área de Conservação de Ngorongoro','image' => $en['destinations']['cards'][1]['image'], 'alt' => 'Safari na Área de Conservação de Ngorongoro'),
                array('slug' => 'parque-nacional-tarangire',   'title' => 'Parque Nacional de Tarangire',   'image' => $en['destinations']['cards'][2]['image'], 'alt' => 'Safari no Parque Nacional de Tarangire'),
                array('slug' => 'parque-nacional-lago-manyara','title' => 'Parque Nacional do Lago Manyara', 'image' => $en['destinations']['cards'][3]['image'], 'alt' => 'Safari no Parque Nacional do Lago Manyara'),
                array('slug' => 'parque-nacional-arusha',      'title' => 'Parque Nacional de Arusha',      'image' => $en['destinations']['cards'][4]['image'], 'alt' => 'Safari no Parque Nacional de Arusha'),
                array('slug' => 'area-ndutu',                  'title' => 'Área de Ndutu',                  'image' => $en['destinations']['cards'][5]['image'], 'alt' => 'Safari na Área de Ndutu'),
                array('slug' => 'zanzibar',                    'title' => 'Zanzibar',                       'image' => $en['destinations']['cards'][6]['image'], 'alt' => 'Férias de praia em Zanzibar'),
            ),
        ),
        'footer' => array(
            'cta_kicker'      => 'Breeze Safaris',
            'cta_title'       => 'A Sua História na Tanzânia Começa Aqui',
            'cta_button_text' => 'Começar a Planear',
            'cta_button_url'  => trailingslashit(home_url('/pt')) . '#packages',
            'brand_text'      => 'Safaris privados na Tanzânia e viagens a Zanzibar, planeados com especialistas locais de confiança.',
            'email'           => 'info@breezesafaris.com',
            'phone'           => '',
            'location'        => 'Baseados na Tanzânia',
            'extra_line'      => 'Planeamento de Safari Privado',
            'copyright'       => '© 2026 Breeze Safaris',
            'tagline'         => 'Descobrir • Explorar • Descansar',
        ),
    );
}

// ── PT destination defaults (indexed by EN slug for easy lookup) ──────────────
function breeze_get_destination_defaults_pt() {
    $en = breeze_get_destination_defaults(); // share gallery URLs, hero_image

    return array(
        'serengeti-national-park' => array(
            'title'        => 'Parque Nacional do Serengeti',
            'eyebrow'      => 'Circuito Norte da Tanzânia',
            'hero_subtitle'=> 'Planícies icónicas, elevada concentração de predadores e rotas de migração que criam uma das maiores experiências de safari em África.',
            'hero_image'   => $en['serengeti-national-park']['hero_image'],
            'intro'        => array(
                'O Serengeti é o coração de muitos itinerários de safari na Tanzânia. A Breeze Safaris desenha cada rota com base na sazonalidade, no ritmo de viagem preferido e no estilo de alojamento, para que cada saída em jipe seja intencional e não apressada.',
                'Seja a seguir os grandes felinos, a planear em torno da migração ou a combinar os parques do norte com uma extensão a Zanzibar, construímos viagens privadas com parceiros locais de confiança e apoio no terreno.',
            ),
            'facts'        => array(
                'Ideal para: Grandes felinos, migração, paisagens clássicas de safari',
                'Estadia recomendada: 2 a 4 noites',
                'Combina bem com: Ngorongoro, Tarangire, Zanzibar',
                'Estilo: Excursões privadas em 4x4 com guias locais experientes',
            ),
            'story_title'  => 'Porque o Serengeti é Excepcional',
            'stories'      => array(
                array('title' => 'Acção dos Predadores',  'text' => 'O ecossistema do Serengeti oferece de forma consistente excelentes avistamentos de leões, guepardos e hienas durante saídas bem planeadas.'),
                array('title' => 'Rotas Sazonais',        'text' => 'O movimento da migração altera-se ao longo do ano. Alinhamos os seus acampamentos e zonas de safari com as melhores janelas de avistamento.'),
                array('title' => 'Experiência Flexível',  'text' => 'De tendas autênticas a lodges de luxo, o seu itinerário é adaptado ao orçamento, às preferências de conforto e ao ritmo de viagem.'),
            ),
            'gallery_title'=> 'Galeria do Serengeti',
            'gallery'      => $en['serengeti-national-park']['gallery'],
            'cta_title'    => 'Planear o Seu Safari no Serengeti',
            'cta_text'     => 'Diga-nos as suas datas, estilo de viagem e orçamento. Criaremos a melhor rota pelo Serengeti para a sua viagem.',
            'cta_button_text' => 'Contactar',
            'cta_button_url'  => home_url('/pt/contacto/') . '?package=Safari+Serengeti',
        ),
        'ngorongoro-conservation-area' => array(
            'title'        => 'Área de Conservação de Ngorongoro',
            'eyebrow'      => 'Circuito Norte da Tanzânia',
            'hero_subtitle'=> 'Uma caldera vulcânica dramática com uma das mais intensas experiências de avistamento de fauna selvagem de toda a África Oriental.',
            'hero_image'   => $en['ngorongoro-conservation-area']['hero_image'],
            'intro'        => array(
                'Ngorongoro é um dos destinos de safari mais icónicos da África Oriental. O interior da cratera concentra a fauna numa envolvência extraordinária, tornando-o num ponto alto para viajantes que procuram avistamentos intensos com logística eficiente.',
                'A Breeze Safaris combina Ngorongoro com Serengeti, Tarangire e Lago Manyara para criar itinerários bem ritmados que maximizam o tempo no campo mantendo a logística fluida.',
            ),
            'facts'        => array(
                'Ideal para: Avistamento intenso de fauna e cenário dramático',
                'Estadia recomendada: 1 a 2 noites',
                'Combina bem com: Serengeti e Tarangire',
                'Estilo: Descida privada à cratera e excursões de dia inteiro',
            ),
            'story_title'  => 'Porque Ngorongoro Surpreende Sempre',
            'stories'      => array(
                array('title' => 'Ecologia da Cratera', 'text' => 'Um ecossistema compacto onde herbívoros e predadores podem ser observados numa única e intensa jornada de safari.'),
                array('title' => 'Drama Visual',        'text' => "As paredes íngremes da cratera, as planícies abertas e os lagos sazonais criam uma das paisagens de safari mais fotogénicas da Tanzânia."),
                array('title' => 'Eficiência de Rota',  'text' => 'Posicionamento estratégico ideal entre o Serengeti e os outros parques do norte, especialmente em itinerários premium mais curtos.'),
            ),
            'gallery_title'=> 'Galeria de Ngorongoro',
            'gallery'      => $en['ngorongoro-conservation-area']['gallery'],
            'cta_title'    => 'Planear a Sua Visita a Ngorongoro',
            'cta_text'     => 'Alinhamos o dia na cratera com o resto do seu safari na Tanzânia para um ritmo melhor, avistamentos mais fortes e menos stress de trânsito.',
            'cta_button_text' => 'Contactar',
            'cta_button_url'  => home_url('/pt/contacto/') . '?package=Safari+Ngorongoro',
        ),
        'tarangire-national-park' => array(
            'title'        => 'Parque Nacional de Tarangire',
            'eyebrow'      => 'Circuito Norte da Tanzânia',
            'hero_subtitle'=> 'Uma atmosfera de safari mais tranquila, conhecida pelos enormes baobás, vistas deslumbrantes e grandes concentrações sazonais de elefantes.',
            'hero_image'   => $en['tarangire-national-park']['hero_image'],
            'intro'        => array(
                'Tarangire tem um ambiente distinto dos outros parques do norte. É espaçoso, paisagisticamente rico e ideal para viajantes que apreciam um ritmo de avistamento mais pausado, com menos viaturas e excelentes oportunidades fotográficas.',
                'A Breeze Safaris posiciona frequentemente Tarangire no início de um itinerário, criando momentum antes de avançar para Ngorongoro e Serengeti em circuitos mais longos focados na fauna.',
            ),
            'facts'        => array(
                'Ideal para: Elefantes, baobás, excursões tranquilas',
                'Estadia recomendada: 1 a 2 noites',
                'Combina bem com: Lago Manyara, Ngorongoro, Serengeti',
                'Estilo: Excursões privadas com horários flexíveis',
            ),
            'story_title'  => 'Porque Tarangire Funciona Tão Bem',
            'stories'      => array(
                array('title' => 'Território dos Elefantes', 'text' => "Tarangire é um dos parques mais notáveis da Tanzânia para avistamentos de elefantes, especialmente nas épocas de seca."),
                array('title' => 'Carácter da Paisagem',     'text' => 'Baobás ancestrais, canais fluviais e matos abertos criam uma profundidade visual que se distingue das planícies abertas do Serengeti.'),
                array('title' => 'Vantagem de Ritmo',        'text' => 'A sua localização e cadência tornam Tarangire excelente para equilibrar o fluxo de viagem em itinerários personalizados do circuito norte.'),
            ),
            'gallery_title'=> 'Galeria de Tarangire',
            'gallery'      => $en['tarangire-national-park']['gallery'],
            'cta_title'    => 'Criar o Seu Itinerário por Tarangire',
            'cta_text'     => 'Combinamos Tarangire com a sequência de parques certa para a sua duração de viagem e prioridades de avistamento.',
            'cta_button_text' => 'Contactar',
            'cta_button_url'  => home_url('/pt/contacto/') . '?package=Safari+Tarangire',
        ),
        'lake-manyara-national-park' => array(
            'title'        => 'Parque Nacional do Lago Manyara',
            'eyebrow'      => 'Circuito Norte da Tanzânia',
            'hero_subtitle'=> 'Um parque compacto mas visualmente rico, com fauna florestal, margem de lago e o cenário dramático da escarpa do Rift Valley.',
            'hero_image'   => $en['lake-manyara-national-park']['hero_image'],
            'intro'        => array(
                'O Lago Manyara é um excelente ponto de contraste num itinerário de safari pelo norte da Tanzânia. A transição entre zonas florestais, áreas abertas e paisagens lacustres cria uma experiência de excursão distinta dos parques maiores.',
                'A Breeze Safaris utiliza Manyara de forma estratégica para um encadeamento fluido, com variedade de fauna equilibrada e forte cenário antes ou depois de segmentos mais longos no Serengeti.',
            ),
            'facts'        => array(
                'Ideal para: Diversidade de paisagens e excursões de alto valor',
                'Estadia recomendada: 1 noite ou visita de dia inteiro',
                'Combina bem com: Tarangire e Ngorongoro',
                'Estilo: Excursões privadas flexíveis para interesses diversificados',
            ),
            'story_title'  => 'O Que Torna Manyara Diferente',
            'stories'      => array(
                array('title' => 'Variedade de Habitats',        'text' => 'Da floresta ripária ao terreno aberto e à margem do lago, Manyara oferece diversidade visual e ecológica numa área compacta.'),
                array('title' => 'Parque de Transição Ideal',    'text' => 'Manyara integra-se naturalmente nos circuitos do norte e ajuda a optimizar os tempos de condução entre as grandes zonas de safari.'),
                array('title' => 'Valor Fotográfico',            'text' => 'A luz variável e os cenários diversificados criam excelentes oportunidades para fotografia de fauna e paisagem.'),
            ),
            'gallery_title'=> 'Galeria do Lago Manyara',
            'gallery'      => $en['lake-manyara-national-park']['gallery'],
            'cta_title'    => 'Incluir o Lago Manyara na Sua Rota',
            'cta_text'     => 'Posicionamos Manyara no momento certo da sua viagem para acrescentar variedade visual e manter o ritmo confortável do safari.',
            'cta_button_text' => 'Contactar',
            'cta_button_url'  => home_url('/pt/contacto/') . '?package=Safari+Lago+Manyara',
        ),
        'arusha-national-park' => array(
            'title'        => 'Parque Nacional de Arusha',
            'eyebrow'      => 'Circuito Norte da Tanzânia',
            'hero_subtitle'=> 'Um belo parque de arranque suave perto de Arusha, ideal para os primeiros contactos com a fauna antes de avançar para circuitos mais extensos do norte.',
            'hero_image'   => $en['arusha-national-park']['hero_image'],
            'intro'        => array(
                'O Parque Nacional de Arusha é frequentemente subestimado, mas é uma excelente forma de começar para viajantes que chegam ao norte da Tanzânia. A sua localização é prática, enquanto a paisagem oferece uma introdução calma e fotogénica ao safari.',
                'A Breeze Safaris pode incluir Arusha como uma excursão de primeiro dia, facilitando a transição confortável para rotas de fauna mais longas por Tarangire, Ngorongoro e Serengeti.',
            ),
            'facts'        => array(
                'Ideal para: Safari no dia de chegada e variedade de paisagens',
                'Estadia recomendada: 1 dia ou 1 noite',
                'Combina bem com: Tarangire e o circuito norte completo',
                'Estilo: Excursões privadas de arranque suave com horários flexíveis',
            ),
            'story_title'  => 'Porque Incluir o Parque Nacional de Arusha',
            'stories'      => array(
                array('title' => 'Excelente Primeiro Dia', 'text' => 'A proximidade a Arusha permite um avistamento significativo de fauna sem o stress de longa condução após os voos.'),
                array('title' => 'Contraste Visual',       'text' => 'As zonas florestais e os miradouros criam uma atmosfera diferente que complementa as experiências nos grandes parques a seguir.'),
                array('title' => 'Design de Rota Inteligente','text' => 'Funciona bem como o capítulo de abertura de um itinerário personalizado antes de avançar para oeste para as grandes reservas.'),
            ),
            'gallery_title'=> 'Galeria do Parque de Arusha',
            'gallery'      => $en['arusha-national-park']['gallery'],
            'cta_title'    => 'Começar o Safari com Tranquilidade',
            'cta_text'     => 'Podemos incluir o Parque Nacional de Arusha como o capítulo de abertura perfeito do seu itinerário personalizado na Tanzânia.',
            'cta_button_text' => 'Contactar',
            'cta_button_url'  => home_url('/pt/contacto/') . '?package=Safari+Arusha',
        ),
        'ndutu-area' => array(
            'title'        => 'Área de Ndutu',
            'eyebrow'      => 'Ecossistema do Serengeti Sul',
            'hero_subtitle'=> "Uma das principais regiões sazonais de safari da Tanzânia, com actividade de parição da migração e comportamento intenso de predadores.",
            'hero_image'   => $en['ndutu-area']['hero_image'],
            'intro'        => array(
                'Ndutu é uma questão de calendário. Durante a janela de parição da migração, a concentração de fauna pode ser extraordinária, tornando este num dos locais de safari mais dinâmicos para viajantes focados em comportamento animal, movimento e fotografia.',
                'A Breeze Safaris planeia Ndutu em torno da sazonalidade, do posicionamento dos acampamentos e do fluxo da rota, para que a sua viagem se alinhe com as melhores oportunidades no terreno.',
            ),
            'facts'        => array(
                'Ideal para: Época de parição da migração e actividade de predadores',
                'Estadia recomendada: 2 a 3 noites na época certa',
                'Combina bem com: Serengeti e Ngorongoro',
                'Estilo: Guia privado sazonal com excursões focadas',
            ),
            'story_title'  => 'Porque Ndutu é Especial',
            'stories'      => array(
                array('title' => 'Janela de Parição',  'text' => 'Quando bem calendarizado, Ndutu oferece cenas inesquecíveis ligadas ao nascimento dos gnus e às respostas dos predadores.'),
                array('title' => 'Acção e Narrativa',  'text' => 'Mudanças rápidas de comportamento e interacções dinâmicas de fauna tornam Ndutu um favorito de viajantes de safari mais experientes.'),
                array('title' => 'Integração de Rota', 'text' => 'Ndutu funciona melhor como parte de um circuito mais amplo que inclui sectores do Serengeti e acesso a Ngorongoro.'),
            ),
            'gallery_title'=> 'Galeria de Ndutu',
            'gallery'      => $en['ndutu-area']['gallery'],
            'cta_title'    => 'Planear o Safari em Ndutu na Época Certa',
            'cta_text'     => 'Partilhe a sua janela de viagem e desenhamos a rota Ndutu mais forte focada na migração para a sua viagem.',
            'cta_button_text' => 'Contactar',
            'cta_button_url'  => home_url('/pt/contacto/') . '?package=Safari+Ndutu',
        ),
        'zanzibar' => array(
            'title'        => 'Extensão a Zanzibar',
            'eyebrow'      => 'Combinação Safari + Praia',
            'hero_subtitle'=> 'Termine o seu safari na Tanzânia com dias curados de descanso no Oceano Índico, em Zanzibar, planeados ao seu ritmo e estilo.',
            'hero_image'   => $en['zanzibar']['hero_image'],
            'intro'        => array(
                'Após os intensos dias de safari, Zanzibar é a transição ideal para o descanso, a brisa do mar e o conforto costeiro. A Breeze Safaris organiza ligações domésticas tranquilas dos destinos de campo para estadias na ilha, sem fricções desnecessárias.',
                'De refúgios boutique a resorts de praia de luxo, adaptamos a sua fase em Zanzibar à duração da viagem, ao orçamento e à atmosfera que deseja depois do capítulo do safari.',
            ),
            'facts'        => array(
                'Ideal para: Relaxamento pós-safari e viagens de celebração',
                'Estadia recomendada: 3 a 5 noites',
                'Combina bem com: Circuitos com Serengeti, Ngorongoro, Tarangire',
                'Estilo: Coordenação completa de itinerário safari + praia',
            ),
            'story_title'  => 'Porque Acrescentar Zanzibar',
            'stories'      => array(
                array('title' => 'Contraste Perfeito',  'text' => 'A intensidade da fauna seguida de calma na praia cria uma experiência de viagem na Tanzânia completa e equilibrada.'),
                array('title' => 'Estadias Seleccionadas','text'=> 'Correspondemos o estilo da propriedade ao seu perfil de viagem, desde opções boutique íntimas a resorts de frente de mar de primeira linha.'),
                array('title' => 'Logística Fluida',    'text' => 'A calendarização de voos e a coordenação de transfers estão integradas no seu itinerário para que a transição do campo para a praia seja perfeita.'),
            ),
            'gallery_title'=> 'Galeria de Zanzibar',
            'gallery'      => $en['zanzibar']['gallery'],
            'cta_title'    => 'Acrescentar Zanzibar ao Seu Safari',
            'cta_text'     => 'Diga-nos o número de dias de praia que prefere e criamos uma combinação perfeita de Tanzânia + Zanzibar.',
            'cta_button_text' => 'Contactar',
            'cta_button_url'  => home_url('/pt/contacto/') . '?package=Extens%C3%A3o+Zanzibar',
        ),
    );
}

function breeze_get_destination_default_pt($en_slug) {
    $all = breeze_get_destination_defaults_pt();
    if (isset($all[$en_slug])) {
        return $all[$en_slug];
    }
    $en = breeze_get_destination_default($en_slug);
    return array_merge($en, array(
        'eyebrow'         => 'Destino na Tanzânia',
        'story_title'     => 'Destaques',
        'gallery_title'   => 'Galeria',
        'cta_button_text' => 'Contactar',
    ));
}

// ── PT privacy policy ─────────────────────────────────────────────────────────
function breeze_get_privacy_policy_default_content_pt() {
    return <<<HTML
<p><strong>Última actualização: Fevereiro de 2026</strong></p>
<p>A Breeze Safaris está empenhada em proteger os seus dados pessoais e respeitar a sua privacidade. Esta Política de Privacidade explica como recolhemos, utilizamos, processamos e salvaguardamos as suas informações quando visita <a href="https://breezesafaris.com">https://breezesafaris.com</a> ou nos contacta relativamente aos nossos serviços.</p>
<h2>1. Quem Somos</h2>
<p>O endereço do nosso website é: <a href="https://breezesafaris.com">https://breezesafaris.com</a>.</p>
<h2>2. Responsável pelo Tratamento de Dados</h2>
<p>Para efeitos do Regulamento Geral sobre a Protecção de Dados (RGPD), a Breeze Safaris actua como Responsável pelo Tratamento dos dados pessoais recolhidos através deste website.</p>
<p>Marca: Breeze Safaris<br>Website: <a href="https://breezesafaris.com">https://breezesafaris.com</a><br>Email de contacto: <a href="mailto:info@breezesafaris.com">info@breezesafaris.com</a></p>
<h2>3. Modelo de Negócio</h2>
<p>A Breeze Safaris não é um operador turístico licenciado. Actuamos como intermediário entre clientes e operadores turísticos locais licenciados na Tanzânia, desenhando itinerários de safari à medida e ligando os clientes a parceiros locais cuidadosamente seleccionados.</p>
<h2>4. Dados Pessoais que Recolhemos</h2>
<p>Podemos recolher e tratar os seguintes dados pessoais: nome completo, endereço de email, número de telefone, país de residência, preferências de viagem e requisitos de itinerário, informação de passaporte quando necessário para reservas, e qualquer informação adicional voluntariamente fornecida através de formulários de contacto ou comunicação por email. Podemos também recolher dados técnicos automaticamente, incluindo endereço IP, tipo e versão de browser, tipo de dispositivo, páginas visitadas e data e hora de acesso.</p>
<h2>5. Base Legal para o Tratamento</h2>
<p>Tratamos os seus dados pessoais com base no seu consentimento ao submeter um formulário de contacto, em diligências pré-contratuais a seu pedido, na execução de um contrato após confirmação de reserva, no cumprimento de obrigações legais e em interesses legítimos na operação e melhoria dos nossos serviços.</p>
<h2>6. Finalidade do Tratamento</h2>
<p>Os seus dados pessoais são tratados para: responder a pedidos de informação, preparar propostas de safari personalizadas, coordenar reservas com operadores turísticos locais, comunicar informações importantes de viagem, melhorar o desempenho do website, e actividades de marketing onde tenha sido fornecido consentimento. Não vendemos, alugamos nem transaccionamos os seus dados pessoais.</p>
<h2>7. Os Seus Direitos ao Abrigo do RGPD</h2>
<p>Se se encontrar na União Europeia ou no Reino Unido, tem direito a: aceder aos seus dados pessoais, solicitar a correcção de dados inexactos, solicitar o apagamento dos seus dados, solicitar a limitação do tratamento, opor-se ao tratamento, solicitar a portabilidade dos dados e retirar o consentimento a qualquer momento.</p>
<p>Para exercer qualquer destes direitos, contacte-nos em <a href="mailto:info@breezesafaris.com">info@breezesafaris.com</a>.</p>
<h2>8. Alterações a Esta Política</h2>
<p>Reservamo-nos o direito de actualizar esta Política de Privacidade a qualquer momento. Quaisquer alterações serão publicadas nesta página com uma data de revisão actualizada.</p>
HTML;
}

// ── PT package seed definitions ───────────────────────────────────────────────
function breeze_get_package_page_seed_definitions_pt() {
    return array(
        array(
            'title'       => 'Safari da Grande Migração em Ndutu — 6 Dias',
            'slug'        => 'safari-migracao-ndutu-6-dias',
            'file'        => 'packages/safari-migracao-ndutu-6-dias.html',
            'en_slug'     => 'ndutu-calving-season-safari',
        ),
        array(
            'title'       => 'Safari de Luxo Migração & Big Five — 8 Dias',
            'slug'        => 'safari-luxo-big-five-8-dias',
            'file'        => 'packages/safari-luxo-big-five-8-dias.html',
            'en_slug'     => 'luxury-migration-big-five-safari-8-day',
        ),
        array(
            'title'       => 'Viagem Lua de Mel Tanzânia & Zanzibar — 9 Dias',
            'slug'        => 'lua-de-mel-tanzania-zanzibar-9-dias',
            'file'        => 'packages/lua-de-mel-tanzania-zanzibar-9-dias.html',
            'en_slug'     => 'tanzania-zanzibar-honeymoon-9-day',
        ),
    );
}

// ── PT destination slug map (EN slug → PT slug) ───────────────────────────────
function breeze_get_pt_dest_slug_map() {
    return array(
        'serengeti-national-park'      => 'parque-nacional-serengeti',
        'ngorongoro-conservation-area' => 'area-conservacao-ngorongoro',
        'tarangire-national-park'      => 'parque-nacional-tarangire',
        'lake-manyara-national-park'   => 'parque-nacional-lago-manyara',
        'arusha-national-park'         => 'parque-nacional-arusha',
        'ndutu-area'                   => 'area-ndutu',
        'zanzibar'                     => 'zanzibar',
    );
}

// ── PT all seed page definitions ──────────────────────────────────────────────
function breeze_get_seed_page_definitions_pt() {
    $dest_pt = breeze_get_destination_defaults_pt();

    // Map EN destination slug → PT page slug
    $dest_slug_map = breeze_get_pt_dest_slug_map();

    $pages = array(
        // Root PT page — homepage
        array(
            'title'       => 'Breeze Safaris — Safaris na Tanzânia',
            'slug'        => 'pt',
            'content'     => '',
            'template'    => 'page-templates/home-pt.php',
            'en_slug'     => 'home',
        ),
        // Named pages under /pt/
        array(
            'title'       => 'Sobre Nós',
            'slug'        => 'sobre-nos',
            'parent_slug' => 'pt',
            'content'     => breeze_get_seed_content_from_static_file_pt('about.html'),
            'template'    => 'default',
            'en_slug'     => 'about',
        ),
        array(
            'title'       => 'Contacto',
            'slug'        => 'contacto',
            'parent_slug' => 'pt',
            'content'     => '',
            'template'    => 'page-templates/contact-page.php',
            'en_slug'     => 'contact',
        ),
        array(
            'title'       => 'Todos os Pacotes de Safari',
            'slug'        => 'pacotes',
            'parent_slug' => 'pt',
            'content'     => breeze_get_seed_content_from_static_file_pt('packages.html'),
            'template'    => 'default',
            'en_slug'     => 'packages',
        ),
        array(
            'title'       => 'Política de Privacidade',
            'slug'        => 'politica-de-privacidade',
            'parent_slug' => 'pt',
            'content'     => breeze_get_privacy_policy_default_content_pt(),
            'template'    => 'default',
            'en_slug'     => 'privacy-policy',
        ),
    );

    // PT package detail pages (children of PT 'pacotes')
    foreach (breeze_get_package_page_seed_definitions_pt() as $pkg) {
        $pages[] = array(
            'title'       => $pkg['title'],
            'slug'        => $pkg['slug'],
            'parent_slug' => 'pacotes',
            'parent_under'=> 'pt',     // grandparent slug for path lookup
            'content'     => breeze_get_seed_content_from_static_file_pt($pkg['file']),
            'template'    => 'default',
            'en_slug'     => $pkg['en_slug'],
        );
    }

    // PT destination pages (children of PT 'pt' root)
    foreach ($dest_slug_map as $en_slug => $pt_slug) {
        $dest_data = isset($dest_pt[$en_slug]) ? $dest_pt[$en_slug] : array();
        $pages[]   = array(
            'title'       => isset($dest_data['title']) ? $dest_data['title'] : $pt_slug,
            'slug'        => $pt_slug,
            'parent_slug' => 'pt',
            'content'     => '',   // destination-page.php reads from breeze_get_destination_view_data()
            'template'    => 'page-templates/destination-page.php',
            'en_slug'     => $en_slug,
        );
    }

    return $pages;
}

// ── Create PT seed pages with translation links ───────────────────────────────
function breeze_ensure_seed_pages_pt() {
    $definitions = breeze_get_seed_page_definitions_pt();

    // First pass: create/update all PT pages
    $pt_page_ids = array(); // keyed by 'slug' (unique within this set)
    $order       = 0;
    foreach ($definitions as $definition) {
        $slug        = sanitize_title((string) $definition['slug']);
        $title       = sanitize_text_field((string) $definition['title']);
        $content     = isset($definition['content'])     ? (string) $definition['content']     : '';
        $template    = isset($definition['template'])    ? (string) $definition['template']    : 'default';
        $parent_slug = isset($definition['parent_slug']) ? sanitize_title((string) $definition['parent_slug']) : '';
        $en_slug     = isset($definition['en_slug'])     ? sanitize_title((string) $definition['en_slug'])     : '';
        $parent_under= isset($definition['parent_under'])? sanitize_title((string) $definition['parent_under']): '';

        // Resolve parent ID
        $parent_id   = 0;
        if ($parent_slug !== '') {
            if ($parent_under !== '') {
                // grandparent scenario: parent is /pt/pacotes/
                $parent_page = get_page_by_path($parent_under . '/' . $parent_slug, OBJECT, 'page');
                if (!($parent_page instanceof WP_Post)) {
                    $parent_page = get_page_by_path($parent_slug, OBJECT, 'page');
                }
            } else {
                $parent_page = get_page_by_path($parent_slug, OBJECT, 'page');
            }
            if ($parent_page instanceof WP_Post) {
                $parent_id = (int) $parent_page->ID;
            }
        }

        // Build lookup path
        $lookup_path = $slug;
        if ($parent_id > 0) {
            $parent_path = trim((string) get_page_uri($parent_id), '/');
            if ($parent_path !== '') {
                $lookup_path = $parent_path . '/' . $slug;
            }
        }

        $existing = get_page_by_path($lookup_path, OBJECT, 'page');
        if (!($existing instanceof WP_Post)) {
            $existing = get_page_by_path($slug, OBJECT, 'page');
        }

        $postarr = array(
            'post_title'   => $title,
            'post_name'    => $slug,
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_content' => $content,
            'post_parent'  => $parent_id,
            'menu_order'   => $order,
        );

        if ($existing instanceof WP_Post) {
            $postarr['ID'] = $existing->ID;
            $page_id       = wp_update_post($postarr, true);
        } else {
            $page_id = wp_insert_post($postarr, true);
        }

        if (is_wp_error($page_id) || !$page_id) {
            $order++;
            continue;
        }

        $page_id = (int) $page_id;

        // Set template
        if ($template !== '' && $template !== 'default') {
            update_post_meta($page_id, '_wp_page_template', $template);
        } else {
            delete_post_meta($page_id, '_wp_page_template');
        }

        // Mark as PT
        update_post_meta($page_id, '_breeze_lang', 'pt');

        // Store EN slug for destination pages (used by view data resolver)
        if ($en_slug !== '') {
            update_post_meta($page_id, '_breeze_en_slug', $en_slug);
        }

        $pt_page_ids[$slug] = $page_id;
        $order++;
    }

    // Second pass: link EN ↔ PT translation IDs
    foreach ($definitions as $definition) {
        $slug    = sanitize_title((string) $definition['slug']);
        $en_slug = isset($definition['en_slug']) ? sanitize_title((string) $definition['en_slug']) : '';

        if (!$en_slug || !isset($pt_page_ids[$slug])) {
            continue;
        }

        $pt_id = $pt_page_ids[$slug];

        // Find EN page by slug (handles nested packages via parent)
        $en_page = get_page_by_path($en_slug, OBJECT, 'page');
        if (!($en_page instanceof WP_Post)) {
            // Try with packages/ prefix for package detail pages
            $en_page = get_page_by_path('packages/' . $en_slug, OBJECT, 'page');
        }

        if ($en_page instanceof WP_Post) {
            $en_id = (int) $en_page->ID;
            // Skip if this EN page is itself a PT page
            if (get_post_meta($en_id, '_breeze_lang', true) === 'pt') {
                continue;
            }
            update_post_meta($en_id, '_breeze_translation_id', $pt_id);
            update_post_meta($pt_id, '_breeze_translation_id', $en_id);
        }
    }

    update_option('breeze_seed_pt_version', '1');

    return $pt_page_ids;
}
