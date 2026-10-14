<?php
/**
 * Title: Vitrine de projetos
 * Slug: rolck-galaxy/projects
 * Categories: rolck-galaxy
 * Keywords: projetos, vitrine, portfolio, demo
 * Description: Título, texto, grade de projetos com filtro e botão para a vitrine completa.
 *
 * @package RolckGalaxy
 */

$t = 'rolck_theme_t';
?>
<!-- wp:group {"tagName":"section","align":"full","className":"is-style-section","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull is-style-section"><!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide"><!-- wp:group {"className":"rolck-section-head","layout":{"type":"default"}} -->
<div class="wp-block-group rolck-section-head"><!-- wp:paragraph {"className":"is-style-eyebrow"} -->
<p class="is-style-eyebrow"><?php echo esc_html( $t( 'Vitrine', 'Showcase' ) ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><?php echo esc_html( $t( 'Teste a demo antes de comprar', 'Try the demo before you buy' ) ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"is-style-lead"} -->
<p class="is-style-lead"><?php echo esc_html( $t( 'Cada projeto tem demo ao vivo, ficha técnica completa e compra direta.', 'Every project comes with a live demo, full specs and direct checkout.' ) ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:rolck/project-grid {"count":6,"columns":3} /-->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-ghost"} -->
<div class="wp-block-button is-style-ghost"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( rolck_theme_url( '/projetos/', '/projects/' ) ); ?>"><?php echo esc_html( $t( 'Ver todos os projetos', 'See all projects' ) ); ?> →</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
