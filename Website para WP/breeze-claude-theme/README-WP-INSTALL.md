# Breeze Codex Theme - Instalacao no WordPress (Amen Staging)

Este tema converte o projecto estatico para WordPress sem dependencias externas de runtime em producao.

## Estrutura do Tema

- `style.css` (cabecalho oficial do tema WordPress)
- `functions.php` (enqueue, menus, customizer, campos extra de destinos)
- `header.php` / `footer.php` (head/header/footer centralizados)
- `front-page.php` (homepage)
- `page.php` (paginas internas genericas)
- `single.php` (posts)
- `search.php` e `404.php`
- `page-templates/destination-page.php` (template especifico para paginas de destino)
- `assets/css` e `assets/js`
- `extras/robots.txt` e `extras/sitemap.xml` (referencia SEO)

## Passos exactos para staging da Amen

a) Como entrar no WP Admin do staging

1. Abra o URL de staging do seu dominio (exemplo: `https://staging.seudominio.com/wp-admin`).
2. Entre com o utilizador administrador do WordPress da instância staging.

b) Como instalar o tema via Aparencia > Temas > Adicionar > Carregar tema > Instalar > Activar

1. No WP Admin, abra `Aparencia > Temas`.
2. Clique `Adicionar novo`.
3. Clique `Carregar tema`.
4. Seleccione o ficheiro zip `breeze-codex-theme.zip`.
5. Clique `Instalar agora`.
6. Clique `Activar`.

c) Como configurar menus

1. Ao activar o tema, o menu principal e criado automaticamente com os links visuais originais:
   - Packages
   - Why Tanzania
   - Destinations
   - About
   - Contact
2. O menu e associado automaticamente a localizacao `Primary Menu`.
3. Um menu adicional `Breeze Pages Menu` e criado com todas as paginas do site.
4. Se quiser ajustar ordem/labels, edite em `Aparencia > Menus`.

d) Como criar ou associar a homepage, e definir em Definicoes > Leitura

1. Ao activar o tema, as paginas sao criadas automaticamente:
   - `Home`
   - `Privacy Policy`
   - `Serengeti National Park`
   - `Ngorongoro Conservation Area`
   - `Tarangire National Park`
   - `Lake Manyara National Park`
   - `Arusha National Park`
   - `Ndutu Area`
   - `Zanzibar Extension`
2. A homepage e definida automaticamente em `Definicoes > Leitura` para a pagina `Home`.
3. A pagina de privacidade e definida automaticamente como `Privacy Policy`.

e) Como inserir conteudo nas paginas e trocar imagens

1. Homepage:
   - Abra `Paginas > Home > Editar` e edite diretamente no Gutenberg.
   - O tema renderiza `the_content`, por isso texto e imagens da Home ficam editaveis no editor.
2. Paginas de destinos:
   - Abra cada pagina de destino em `Paginas` e edite no Gutenberg.
   - O tema renderiza `the_content` quando existir, mantendo o layout visual via classes CSS do tema.
3. Paginas internas genericas:
   - Edite normalmente no editor WordPress (`the_content`).
4. Troca de imagens via Media Library:
   - Faça upload em `Media > Adicionar`.
   - Substitua os URLs das imagens no conteúdo (bloco HTML) ou use blocos de imagem do Gutenberg.

f) Como validar rapidamente (homepage, paginas internas, mobile, formularios, velocidade)

1. Verifique homepage:
   - hero video, cards, carousel, grid de destinos, header sticky, modal de packages.
2. Verifique paginas internas:
   - `privacy-policy` e outras paginas comuns no template `page.php`.
3. Verifique destinos:
   - layout `Destination Page`, hero, facts, stories, galeria, cta.
4. Verifique mobile:
   - menu hamburguer, legibilidade, overflow horizontal, carousel.
5. Verifique formularios/cta:
   - links `mailto:` e quaisquer forms de plugin (Contact Form 7, WPForms, etc.).
6. Verifique performance:
   - teste rapido em PageSpeed/Lighthouse no staging.

## SEO (WordPress)

- O tema usa `wp_head`, `wp_footer` e `add_theme_support('title-tag')`, por isso e compativel com plugins SEO.
- Para title/meta description por pagina, use plugin SEO (Yoast SEO ou Rank Math).
- `robots.txt` e `sitemap.xml` em WordPress normalmente devem ser geridos por plugin SEO:
  - active o sitemap no plugin;
  - configure robots no plugin.
- Os ficheiros em `extras/` servem como referencia de conteudo base, nao sao aplicados automaticamente pelo tema.

## Limitacoes conhecidas desta conversao

1. O fluxo editorial e de publicacao e o nativo do WordPress.
2. `robots.txt` e `sitemap.xml` no root do dominio devem ser geridos por plugin SEO ou configuracao do servidor, nao pelo tema em si.
