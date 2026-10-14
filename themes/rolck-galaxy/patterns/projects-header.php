<?php
/**
 * Title: Topo da vitrine de projetos
 * Slug: rolck-galaxy/projects-header
 * Inserter: no
 * Description: Usado no modelo da vitrine. Os textos ficam em Rolck Galaxy → Textos do site.
 *
 * @package RolckGalaxy
 */

?>
<!-- wp:group {"tagName":"section","align":"full","className":"rolck-page-header","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull rolck-page-header"><!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide"><!-- wp:rolck/breadcrumbs /-->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading"><?php echo esc_html( rolck_theme_string( 'projects', 'Projetos' ) ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"is-style-lead"} -->
<p class="is-style-lead"><?php echo esc_html( rolck_theme_string( 'projects_intro' ) ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
