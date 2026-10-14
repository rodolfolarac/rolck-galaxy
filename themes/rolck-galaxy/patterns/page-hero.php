<?php
/**
 * Title: Topo de página interna
 * Slug: rolck-galaxy/page-hero
 * Categories: rolck-galaxy, banner
 * Keywords: topo, título, página
 * Description: Breadcrumbs, sobretítulo, título e texto de abertura. Use no começo das páginas com o modelo "Página de vendas".
 *
 * @package RolckGalaxy
 */

$t = 'rolck_theme_t';
?>
<!-- wp:group {"tagName":"section","align":"full","className":"rolck-page-header","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull rolck-page-header"><!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide"><!-- wp:rolck/breadcrumbs /-->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading"><?php echo esc_html( $t( 'Título da página', 'Page title' ) ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"is-style-lead"} -->
<p class="is-style-lead"><?php echo esc_html( $t( 'Uma frase que resume o que o visitante vai encontrar aqui.', 'One sentence that sums up what visitors will find here.' ) ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
