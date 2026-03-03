# Auditoria SEO — Tema breeze-claude-theme

**Data:** 2026-03-03
**Tema auditado:** breeze-claude-theme (staging.breezesafaris.com)
**Plugin criado:** Breeze Safaris SEO (`plugin-seo-breeze`)

---

## Resumo

O tema continha um sistema SEO completo implementado em 3 ficheiros dedicados no diretório `inc/`, mais referências em `functions.php`. Todo o código SEO foi removido do tema e migrado para o plugin `plugin-seo-breeze`.

---

## Ficheiros com Código SEO

### 1. `inc/seo.php` (704 linhas) — REMOVIDO PARA PLUGIN

**Backup criado:** `inc/seo.php.bak`

#### Código encontrado e o que faz:

| Linha | Código | O que faz |
|-------|--------|-----------|
| 18-22 | `add_filter('pre_get_document_title', 'breeze_override_document_title')` | Override do `<title>` por página via filtro WordPress |
| 25-66 | `add_action('wp_head', 'breeze_output_seo_meta_tags', 2)` | Output de meta description, canonical, robots, Open Graph (locale, type, title, description, url, site_name, image, width, height, alt) e Twitter Cards (card, title, description, image) |
| 69-78 | `add_action('wp_head', 'breeze_output_json_ld', 3)` | Output de JSON-LD Structured Data (schema.org) |
| 80-351 | `function breeze_build_json_ld()` | Gera o array JSON-LD completo com @graph, suportando: Organization, WebSite, TravelAgency, TouristDestination, TouristTrip, WebPage, ContactPage, AboutPage, BreadcrumbList — com lógica por tipo de página/template |
| 355-362 | `function breeze_resolve_current_page_seo()` | Cache estático de SEO por request (chamado múltiplas vezes) |
| 364-540 | `function breeze_compute_current_page_seo()` | Resolver principal de SEO por página (EN): homepage, destinos, pacotes, páginas nomeadas, fallback genérico. **Todos os dados SEO estão hardcoded nesta função** — não há post meta |
| 543-703 | `function breeze_compute_pt_page_seo()` | Resolver SEO para páginas em português: `/pt/`, destinos PT, pacotes PT, páginas nomeadas PT (`contacto`, `sobre-nos`, `pacotes`, `politica-de-privacidade`) |

#### Funções definidas:
- `breeze_override_document_title($title)` — hook `pre_get_document_title`
- `breeze_output_seo_meta_tags()` — hook `wp_head` prioridade 2
- `breeze_output_json_ld()` — hook `wp_head` prioridade 3
- `breeze_build_json_ld()` — gera schema JSON-LD
- `breeze_resolve_current_page_seo()` — resolver com cache
- `breeze_compute_current_page_seo()` — lógica de SEO EN
- `breeze_compute_pt_page_seo($post_id, $slug, $canonical)` — lógica de SEO PT

#### Dados SEO hardcoded (sem post meta):
- **Todas as páginas** têm título, description e og_description hardcoded em PHP
- A imagem OG padrão estava hardcoded: `https://breezesafaris.com/wp-content/uploads/2026/01/serengeti-4-1200x800.jpg`
- Não havia nenhuma post meta key para guardar títulos/descriptions por página

---

### 2. `inc/sitemap.php` (297 linhas) — REMOVIDO PARA PLUGIN

**Backup criado:** `inc/sitemap.php.bak`

#### Código encontrado e o que faz:

| Linha | Código | O que faz |
|-------|--------|-----------|
| 23-27 | `add_action('init', 'breeze_sitemap_rewrite_rules')` | Regista rewrite rules para `/sitemap.xml`, `/sitemap-en.xml`, `/sitemap-pt.xml` |
| 29-33 | `add_filter('query_vars', 'breeze_sitemap_query_vars')` | Adiciona `breeze_sitemap` como query var |
| 37-56 | `add_action('template_redirect', 'breeze_sitemap_template_redirect')` | Interceta requests de sitemap, envia header XML e faz output |
| 60-75 | `function breeze_output_sitemap_index()` | Gera sitemap index com links para EN e PT sitemaps |
| 79-104 | `function breeze_output_sitemap_lang($lang)` | Gera urlset XML para uma língua |
| 108-202 | `function breeze_sitemap_get_en_urls()` | Lista de URLs EN (homepage, destinos, pacotes, páginas nomeadas) — slugs hardcoded |
| 206-293 | `function breeze_sitemap_get_pt_urls()` | Lista de URLs PT (pt/, destinos PT, pacotes PT, páginas PT) — slugs hardcoded |

#### Rewrite rules registadas:
- `^sitemap\.xml$` → `index.php?breeze_sitemap=index`
- `^sitemap-en\.xml$` → `index.php?breeze_sitemap=en`
- `^sitemap-pt\.xml$` → `index.php?breeze_sitemap=pt`

#### Post meta keys usadas:
- `_breeze_lang` — valor `'pt'` para identificar páginas PT (meta do sistema i18n, NÃO é meta SEO — **não migrar para o plugin SEO**)

---

### 3. `inc/redirects.php` (68 linhas) — REMOVIDO PARA PLUGIN

**Backup criado:** `inc/redirects.php.bak`

#### Código encontrado e o que faz:

| Linha | Código | O que faz |
|-------|--------|-----------|
| 15-17 | `add_action('init', ...)` | Regista rewrite rule para `/pt` → `pagename=pt` |
| 20-22 | `add_action('after_switch_theme', ...)` | Flush rewrite rules na ativação do tema |
| 25-29 | `add_filter('mod_rewrite_rules', ...)` | Injeta redirect 301 `/pt` → `/pt/` no `.htaccess` |
| 31-68 | `add_action('template_redirect', ...)` | Interceta requests e faz redirects 301 para mapa de URLs |

#### Mapa de redirects 301 hardcoded:
| URL Antigo | URL Novo |
|------------|----------|
| `/tanzania-safari-experts/` | `/about/` |
| `/pt/especialistas-portugueses-em-safaris/` | `/pt/sobre-nos/` |
| `/pt/contacte-nos/` | `/pt/contacto/` |
| `/project/` | `/` |
| `/project/serengeti-national-park/` | `/serengeti-national-park/` |
| `/project/ngorongoro-conservation-area/` | `/ngorongoro-conservation-area/` |
| `/project/tarangire-national-park/` | `/tarangire-national-park/` |
| `/project/lake-manyara-national-park/` | `/lake-manyara-national-park/` |
| `/project/arusha-national-park/` | `/arusha-national-park/` |
| `/project/ndutu-area/` | `/ndutu-area/` |
| `/project/zanzibar/` | `/zanzibar/` |
| `/cookie-policy-eu/` | `/privacy-policy/` |
| Qualquer `/project/*` | `/` |
| `/pt` (sem slash) | `/pt/` |

---

### 4. `functions.php` — REQUIRES REMOVIDOS

**Backup criado:** NÃO (apenas removidos os `require_once` para os ficheiros SEO)

#### Código modificado:

| Linha original | Código | Ação |
|---------------|--------|------|
| 7 | `require_once get_template_directory() . '/inc/seo.php';` | REMOVIDO — gerido pelo plugin |
| 9 | `require_once get_template_directory() . '/inc/sitemap.php';` | REMOVIDO — gerido pelo plugin |
| 10 | `require_once get_template_directory() . '/inc/redirects.php';` | REMOVIDO — gerido pelo plugin |

#### Funções mantidas em `functions.php` (não são SEO):
- `breeze_get_logo_url()` — obtém URL do logo (usada pelo plugin no schema)
- `breeze_theme_setup()` — setup do tema (add_theme_support etc.)
- `breeze_enqueue_assets()` — scripts/styles do tema
- Todas as funções de destino, menus, customizer

---

### 5. `header.php` — SEM CÓDIGO SEO

O header.php apenas tem `<?php wp_head(); ?>` que é o hook correto. Sem output SEO direto.

---

## Post Meta Keys Existentes no Tema

### Meta keys SEO (NENHUMA — todos os dados estavam hardcoded)

O tema **não utilizava post meta para guardar dados SEO**. Todos os títulos, descriptions e imagens OG estavam hardcoded nas funções PHP de `inc/seo.php`.

### Meta keys de i18n (NÃO migrar para o plugin SEO):
- `_breeze_lang` — `'pt'` para páginas em português
- `_breeze_en_slug` — slug EN equivalente para páginas PT

### Meta keys de conteúdo de destino (NÃO migrar):
- `_breeze_dest_hero_image_url`, `_breeze_dest_hero_eyebrow`, `_breeze_dest_hero_subtitle`
- `_breeze_dest_facts`, `_breeze_dest_story_*`, `_breeze_dest_gallery_*`
- `_breeze_dest_cta_*`

---

## Post Meta Keys Introduzidas pelo Plugin

O plugin `plugin-seo-breeze` introduz as seguintes meta keys por página/post:

| Meta Key | Tipo | Descrição |
|----------|------|-----------|
| `_bseo_title` | string | Título SEO personalizado (substitui o título WP no `<title>`) |
| `_bseo_description` | string | Meta description |
| `_bseo_og_image` | string (URL) | URL da imagem Open Graph |
| `_bseo_og_image_id` | int | ID do attachment WP para a imagem OG |
| `_bseo_og_type` | string | Tipo OG ('website', 'article') |
| `_bseo_robots` | string | Directiva robots completa (ex: 'noindex,follow') |
| `_bseo_canonical` | string (URL) | URL canónico personalizado |
| `_bseo_noindex` | '1'/'' | Noindex activado |

---

## Migração Automática

O plugin inclui um **importador** (`class-importer.php`) com as seguintes funções:

1. **`import_theme_seo_data()`** — lê os dados SEO hardcoded do tema e converte para post meta nas páginas WP existentes
2. **`import_theme_redirects()`** — importa o mapa de redirects do tema para a tabela `{prefix}breeze_redirects`
3. **`import_from_csv($file)`** — importa redirects de ficheiro CSV (colunas: `url_antigo`, `url_novo`)
4. **`import_yoast_data()`** — importa dados do Yoast SEO (se existir)

---

## Tabelas de Base de Dados Criadas

### `{prefix}breeze_redirects`
```sql
CREATE TABLE {prefix}breeze_redirects (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  url_old varchar(2048) NOT NULL,
  url_new varchar(2048) NOT NULL,
  redirect_type smallint(4) NOT NULL DEFAULT 301,
  hits bigint(20) NOT NULL DEFAULT 0,
  notes text,
  created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY url_old (url_old(191))
)
```

### `{prefix}breeze_crawl_log`
```sql
CREATE TABLE {prefix}breeze_crawl_log (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  url_requested varchar(2048) NOT NULL,
  referrer varchar(2048),
  user_agent varchar(512),
  created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY created_at (created_at)
)
```

---

## Verificação Final

- [x] Nenhum meta tag SEO duplicado no `<head>` — tema limpo, plugin assume controlo total
- [x] Plugin controla `<title>` via `pre_get_document_title`
- [x] Tema não tem nenhum hook SEO ativo (seo.php/sitemap.php/redirects.php substituídos por stubs)
- [x] Dados antigos de post meta preservados (não havia SEO em post meta — dados migráveis via importador)
- [x] Backups `.bak` criados: `inc/seo.php.bak`, `inc/sitemap.php.bak`, `inc/redirects.php.bak`
- [x] Este relatório `auditoria-seo-tema.md` criado e completo
- [x] Script `build.sh` criado para gerar ZIP do plugin

---

## Redirects a Configurar no Google Search Console

Após submissão do novo sitemap, confirmar redirects 301 para os seguintes grupos de URLs antigas do site de produção (`breezesafaris.com`):

1. `/project/*` → URL equivalente nova (destinos) ou `/`
2. `/tanzania-safari-experts/` → `/about/`
3. `/cookie-policy-eu/` → `/privacy-policy/`
4. `/pt/especialistas-portugueses-em-safaris/` → `/pt/sobre-nos/`
5. `/pt/contacte-nos/` → `/pt/contacto/`
