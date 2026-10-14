<?php
/**
 * Title: Serviços
 * Slug: rolck-galaxy/services
 * Categories: rolck-galaxy
 * Keywords: serviços, services, o que fazemos
 * Description: Seis cartões de serviço com ícone, título e texto.
 *
 * @package RolckGalaxy
 */

$t = 'rolck_theme_t';
?>
<!-- wp:group {"tagName":"section","align":"full","className":"is-style-section","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull is-style-section"><!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide"><!-- wp:group {"className":"rolck-section-head","layout":{"type":"default"}} -->
<div class="wp-block-group rolck-section-head"><!-- wp:paragraph {"className":"is-style-eyebrow"} -->
<p class="is-style-eyebrow"><?php echo esc_html( $t( 'O que fazemos', 'What we do' ) ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><?php echo esc_html( $t( 'Do primeiro bloco ao topo do Google', 'From the first block to the top of Google' ) ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"is-style-lead"} -->
<p class="is-style-lead"><?php echo esc_html( $t( 'Produtos e serviços para quem precisa de um site rápido, bonito e fácil de editar.', 'Products and services for anyone who needs a fast, beautiful site that is easy to edit.' ) ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"top":"var:preset|spacing|40","left":"var:preset|spacing|40"}}}} -->
<div class="wp-block-columns"><!-- wp:column {"className":"is-style-glass rolck-service rolck-service--palette"} -->
<div class="wp-block-column is-style-glass rolck-service rolck-service--palette"><!-- wp:heading {"level":3,"fontSize":"large"} -->
<h3 class="wp-block-heading has-large-font-size"><?php echo esc_html( $t( 'Temas WordPress', 'WordPress themes' ) ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"color":{"text":"var:preset|color|muted"}},"fontSize":"small"} -->
<p class="has-text-color has-small-font-size" style="color:var(--wp--preset--color--muted)"><?php echo esc_html( $t( 'Temas prontos para usar, com personalização completa pelo painel: cores, fontes, textos e seções, sem tocar em código.', 'Ready-to-use themes with full customization from the dashboard: colors, fonts, text and sections, without touching code.' ) ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"is-style-glass rolck-service rolck-service--plug"} -->
<div class="wp-block-column is-style-glass rolck-service rolck-service--plug"><!-- wp:heading {"level":3,"fontSize":"large"} -->
<h3 class="wp-block-heading has-large-font-size"><?php echo esc_html( $t( 'Plugins sob medida', 'Custom plugins' ) ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"color":{"text":"var:preset|color|muted"}},"fontSize":"small"} -->
<p class="has-text-color has-small-font-size" style="color:var(--wp--preset--color--muted)"><?php echo esc_html( $t( 'Funcionalidades nativas em PHP e React, seguindo os padrões do WordPress e sem dependências pesadas.', 'Native features in PHP and React, following WordPress standards with no heavy dependencies.' ) ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"is-style-glass rolck-service rolck-service--bolt"} -->
<div class="wp-block-column is-style-glass rolck-service rolck-service--bolt"><!-- wp:heading {"level":3,"fontSize":"large"} -->
<h3 class="wp-block-heading has-large-font-size"><?php echo esc_html( $t( 'Performance', 'Performance' ) ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"color":{"text":"var:preset|color|muted"}},"fontSize":"small"} -->
<p class="has-text-color has-small-font-size" style="color:var(--wp--preset--color--muted)"><?php echo esc_html( $t( 'Core Web Vitals no verde: CSS por bloco, JavaScript mínimo, imagens responsivas e fontes locais.', 'Core Web Vitals in the green: per-block CSS, minimal JavaScript, responsive images and local fonts.' ) ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"top":"var:preset|spacing|40","left":"var:preset|spacing|40"}}}} -->
<div class="wp-block-columns"><!-- wp:column {"className":"is-style-glass rolck-service rolck-service--search"} -->
<div class="wp-block-column is-style-glass rolck-service rolck-service--search"><!-- wp:heading {"level":3,"fontSize":"large"} -->
<h3 class="wp-block-heading has-large-font-size"><?php echo esc_html( $t( 'SEO técnico', 'Technical SEO' ) ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"color":{"text":"var:preset|color|muted"}},"fontSize":"small"} -->
<p class="has-text-color has-small-font-size" style="color:var(--wp--preset--color--muted)"><?php echo esc_html( $t( 'Dados estruturados, canonical, hreflang, sitemap e metadados em cada página, sem plugin de SEO.', 'Structured data, canonical, hreflang, sitemap and metadata on every page, with no SEO plugin.' ) ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"is-style-glass rolck-service rolck-service--globe"} -->
<div class="wp-block-column is-style-glass rolck-service rolck-service--globe"><!-- wp:heading {"level":3,"fontSize":"large"} -->
<h3 class="wp-block-heading has-large-font-size"><?php echo esc_html( $t( 'Sites em mais de um idioma', 'Multilingual websites' ) ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"color":{"text":"var:preset|color|muted"}},"fontSize":"small"} -->
<p class="has-text-color has-small-font-size" style="color:var(--wp--preset--color--muted)"><?php echo esc_html( $t( 'Sob encomenda: URLs próprias por idioma e seletor de idioma, sem plugin de tradução.', 'Made to order: dedicated URLs per language and a language switcher, with no translation plugin.' ) ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"is-style-glass rolck-service rolck-service--tools"} -->
<div class="wp-block-column is-style-glass rolck-service rolck-service--tools"><!-- wp:heading {"level":3,"fontSize":"large"} -->
<h3 class="wp-block-heading has-large-font-size"><?php echo esc_html( $t( 'Personalização sem código', 'No-code customization' ) ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"color":{"text":"var:preset|color|muted"}},"fontSize":"small"} -->
<p class="has-text-color has-small-font-size" style="color:var(--wp--preset--color--muted)"><?php echo esc_html( $t( 'Seu cliente muda o que quiser pelo painel, e tudo por trás é código limpo, sem depender de plugins.', 'Your client changes whatever they want from the dashboard, and everything behind it is clean code that does not rely on plugins.' ) ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
