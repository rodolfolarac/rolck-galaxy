<?php
/**
 * Title: Página não encontrada
 * Slug: rolck-galaxy/not-found
 * Inserter: no
 * Description: Conteúdo da página 404. Os textos ficam em Rolck Galaxy → Textos do site.
 *
 * @package RolckGalaxy
 */

?>
<!-- wp:group {"tagName":"section","align":"full","className":"rolck-hero","layout":{"type":"constrained","contentSize":"640px"}} -->
<section class="wp-block-group alignfull rolck-hero"><!-- wp:rolck/cosmic-visual {"chips":["404"],"center":"?"} /-->

<!-- wp:heading {"textAlign":"center","level":1,"fontSize":"xx-large"} -->
<h1 class="wp-block-heading has-text-align-center has-xx-large-font-size"><?php echo esc_html( rolck_theme_string( 'not_found_title', 'Esta página se perdeu no espaço' ) ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","className":"is-style-lead"} -->
<p class="has-text-align-center is-style-lead"><?php echo esc_html( rolck_theme_string( 'not_found_text' ) ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( rolck_theme_string( 'back_home', 'Voltar ao início' ) ); ?></a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-ghost"} -->
<div class="wp-block-button is-style-ghost"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( (string) get_post_type_archive_link( 'rolck_project' ) ); ?>"><?php echo esc_html( rolck_theme_string( 'projects', 'Projetos' ) ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></section>
<!-- /wp:group -->
