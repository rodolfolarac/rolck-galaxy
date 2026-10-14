<?php
/**
 * Title: Projeto em destaque
 * Slug: rolck-galaxy/featured-project
 * Categories: rolck-galaxy
 * Keywords: destaque, lançamento, projeto
 * Description: Título da seção e o projeto marcado como destaque em tamanho grande.
 *
 * @package RolckGalaxy
 */

$t = 'rolck_theme_t';
?>
<!-- wp:group {"tagName":"section","align":"full","className":"is-style-section","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull is-style-section"><!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide"><!-- wp:group {"className":"rolck-section-head","layout":{"type":"default"}} -->
<div class="wp-block-group rolck-section-head"><!-- wp:paragraph {"className":"is-style-eyebrow"} -->
<p class="is-style-eyebrow"><?php echo esc_html( $t( 'Em destaque', 'Spotlight' ) ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><?php echo esc_html( $t( 'O próximo lançamento da galáxia', 'The next launch in the galaxy' ) ); ?></h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:rolck/featured-project /--></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
