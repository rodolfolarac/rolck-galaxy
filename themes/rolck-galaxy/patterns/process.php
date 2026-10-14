<?php
/**
 * Title: Como trabalhamos
 * Slug: rolck-galaxy/process
 * Categories: rolck-galaxy
 * Keywords: processo, etapas, como trabalhamos
 * Description: Quatro etapas numeradas automaticamente.
 *
 * @package RolckGalaxy
 */

$t = 'rolck_theme_t';
?>
<!-- wp:group {"tagName":"section","align":"full","className":"is-style-section","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull is-style-section"><!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide"><!-- wp:group {"className":"rolck-section-head","layout":{"type":"default"}} -->
<div class="wp-block-group rolck-section-head"><!-- wp:paragraph {"className":"is-style-eyebrow"} -->
<p class="is-style-eyebrow"><?php echo esc_html( $t( 'Como trabalhamos', 'How we work' ) ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><?php echo esc_html( $t( 'Um processo claro, do briefing à entrega', 'A clear process, from brief to delivery' ) ); ?></h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:columns {"className":"rolck-steps"} -->
<div class="wp-block-columns rolck-steps"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3,"fontSize":"large"} -->
<h3 class="wp-block-heading has-large-font-size"><?php echo esc_html( $t( 'Descoberta', 'Discovery' ) ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"color":{"text":"var:preset|color|muted"}},"fontSize":"small"} -->
<p class="has-text-color has-small-font-size" style="color:var(--wp--preset--color--muted)"><?php echo esc_html( $t( 'Entendemos o público, os objetivos e o que precisa estar no ar no primeiro dia.', 'We learn about your audience, goals and what must be live on day one.' ) ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3,"fontSize":"large"} -->
<h3 class="wp-block-heading has-large-font-size"><?php echo esc_html( $t( 'Design', 'Design' ) ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"color":{"text":"var:preset|color|muted"}},"fontSize":"small"} -->
<p class="has-text-color has-small-font-size" style="color:var(--wp--preset--color--muted)"><?php echo esc_html( $t( 'Layout pensado para conversão e para o seu conteúdo, testado em celular, tablet e desktop.', 'A layout built for conversion and for your content, tested on phone, tablet and desktop.' ) ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3,"fontSize":"large"} -->
<h3 class="wp-block-heading has-large-font-size"><?php echo esc_html( $t( 'Desenvolvimento', 'Development' ) ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"color":{"text":"var:preset|color|muted"}},"fontSize":"small"} -->
<p class="has-text-color has-small-font-size" style="color:var(--wp--preset--color--muted)"><?php echo esc_html( $t( 'Código limpo e documentado, com performance e acessibilidade medidas a cada etapa.', 'Clean, documented code, with performance and accessibility measured at every step.' ) ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
