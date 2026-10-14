<?php
/**
 * Title: Contato com formulário
 * Slug: rolck-galaxy/contact
 * Categories: rolck-galaxy
 * Keywords: contato, formulário, email, whatsapp
 * Description: Texto, dados de contato e redes à esquerda; formulário à direita.
 *
 * @package RolckGalaxy
 */

$t = 'rolck_theme_t';
?>
<!-- wp:group {"tagName":"section","align":"full","className":"is-style-section","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull is-style-section"><!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|60"}}}} -->
<div class="wp-block-columns alignwide"><!-- wp:column {"width":"40%"} -->
<div class="wp-block-column" style="flex-basis:40%"><!-- wp:paragraph {"className":"is-style-eyebrow"} -->
<p class="is-style-eyebrow"><?php echo esc_html( $t( 'Contato', 'Contact' ) ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><?php echo esc_html( $t( 'Conte sobre o seu projeto', 'Tell us about your project' ) ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"is-style-lead"} -->
<p class="is-style-lead"><?php echo esc_html( $t( 'Dúvidas sobre um tema ou um projeto sob medida: escreva por aqui.', 'Questions about a theme or a custom project: write to us here.' ) ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:rolck/contact-info /-->

<!-- wp:rolck/social-links /--></div>
<!-- /wp:column -->

<!-- wp:column {"width":"60%","className":"is-style-glass"} -->
<div class="wp-block-column is-style-glass" style="flex-basis:60%"><!-- wp:rolck/contact-form /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></section>
<!-- /wp:group -->
