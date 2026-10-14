<?php
/**
 * Title: Faixa de tecnologias
 * Slug: rolck-galaxy/tech-stack
 * Categories: rolck-galaxy
 * Keywords: tecnologias, stack, ferramentas
 * Description: Frase curta e a faixa animada de tecnologias.
 *
 * @package RolckGalaxy
 */

$t = 'rolck_theme_t';
?>
<!-- wp:group {"tagName":"section","align":"full","className":"rolck-stack","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull rolck-stack"><!-- wp:paragraph {"align":"center","style":{"color":{"text":"var:preset|color|muted"}},"fontSize":"small"} -->
<p class="has-text-align-center has-text-color has-small-font-size" style="color:var(--wp--preset--color--muted)"><?php echo esc_html( $t( 'Construído com as ferramentas que movem a web moderna', 'Built with the tools that power the modern web' ) ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:rolck/tech-marquee {"align":"wide","label":"<?php echo esc_attr( $t( 'Tecnologias', 'Technologies' ) ); ?>"} /--></section>
<!-- /wp:group -->
