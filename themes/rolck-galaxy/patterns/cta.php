<?php
/**
 * Title: Chamada final
 * Slug: rolck-galaxy/cta
 * Categories: rolck-galaxy, call-to-action
 * Keywords: cta, chamada, contato, orçamento
 * Description: Caixa com borda em gradiente, título, texto e dois botões.
 *
 * @package RolckGalaxy
 */

$t = 'rolck_theme_t';
?>
<!-- wp:group {"tagName":"section","align":"full","className":"is-style-section","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull is-style-section"><!-- wp:group {"align":"wide","className":"is-style-gradient-border rolck-cta","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained","contentSize":"720px"}} -->
<div class="wp-block-group alignwide is-style-gradient-border rolck-cta" style="padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--50)"><!-- wp:paragraph {"align":"center","className":"is-style-eyebrow"} -->
<p class="has-text-align-center is-style-eyebrow"><?php echo esc_html( $t( 'Vamos conversar', 'Let’s talk' ) ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center"><?php echo esc_html( $t( 'Tem uma ideia? A gente coloca ela em órbita.', 'Got an idea? We will put it into orbit.' ) ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","className":"is-style-lead"} -->
<p class="has-text-align-center is-style-lead"><?php echo esc_html( $t( 'Conte o que você precisa: um tema, um plugin ou um site completo.', 'Tell us what you need: a theme, a plugin or a full website.' ) ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( rolck_theme_url( '/contato/', '/contact/' ) ); ?>"><?php echo esc_html( $t( 'Pedir um orçamento', 'Request a quote' ) ); ?></a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-ghost"} -->
<div class="wp-block-button is-style-ghost"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( rolck_theme_url( '/projetos/', '/projects/' ) ); ?>"><?php echo esc_html( $t( 'Ver os projetos', 'Browse projects' ) ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
