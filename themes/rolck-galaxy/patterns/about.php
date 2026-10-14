<?php
/**
 * Title: Sobre a empresa
 * Slug: rolck-galaxy/about
 * Categories: rolck-galaxy
 * Keywords: sobre, empresa, quem somos, valores
 * Description: Texto sobre a empresa, lista de compromissos e botão.
 *
 * @package RolckGalaxy
 */

$t = 'rolck_theme_t';
?>
<!-- wp:group {"tagName":"section","align":"full","className":"is-style-section","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull is-style-section"><!-- wp:columns {"verticalAlignment":"center","align":"wide","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|60"}}}} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"52%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:52%"><!-- wp:paragraph {"className":"is-style-eyebrow"} -->
<p class="is-style-eyebrow"><?php echo esc_html( $t( 'Sobre a Rolck Galaxy', 'About Rolck Galaxy' ) ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><?php echo esc_html( $t( 'Software com cuidado de produto, não de template', 'Software crafted like a product, not a template' ) ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"is-style-lead"} -->
<p class="is-style-lead"><?php echo esc_html( $t( 'A Rolck Galaxy é um estúdio brasileiro de produtos digitais. Criamos temas e plugins WordPress prontos para usar e projetos sob medida para empresas que querem um site rápido, bem ranqueado e fácil de manter.', 'Rolck Galaxy is a Brazilian digital product studio. We build ready-to-use WordPress themes and plugins, plus custom projects for companies that want a fast, well-ranked site that is easy to maintain.' ) ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"style":{"color":{"text":"var:preset|color|muted"}}} -->
<p class="has-text-color" style="color:var(--wp--preset--color--muted)"><?php echo esc_html( $t( 'Cada projeto nasce com a mesma regra: usar o que o WordPress já oferece de melhor, medir tudo e não depender de plugins pesados.', 'Every project starts with the same rule: use the best of what WordPress already offers, measure everything and never depend on heavy plugins.' ) ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-ghost"} -->
<div class="wp-block-button is-style-ghost"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( rolck_theme_url( '/sobre/', '/about/' ) ); ?>"><?php echo esc_html( $t( 'Conheça a Rolck Galaxy', 'Meet Rolck Galaxy' ) ); ?> →</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"48%","className":"is-style-glass"} -->
<div class="wp-block-column is-vertically-aligned-center is-style-glass" style="flex-basis:48%"><!-- wp:heading {"level":3,"fontSize":"large"} -->
<h3 class="wp-block-heading has-large-font-size"><?php echo esc_html( $t( 'Nossos compromissos', 'Our commitments' ) ); ?></h3>
<!-- /wp:heading -->

<!-- wp:list {"className":"is-style-check"} -->
<ul class="wp-block-list is-style-check"><!-- wp:list-item -->
<li><?php echo esc_html( $t( 'Performance medida no PageSpeed em cada entrega', 'Performance measured on PageSpeed in every release' ) ); ?></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><?php echo esc_html( $t( 'Acessibilidade seguindo as WCAG', 'Accessibility following WCAG' ) ); ?></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><?php echo esc_html( $t( 'Liberdade total para o cliente final personalizar tudo pelo painel, sem código', 'Full freedom for the end user to customize everything from the dashboard, no code' ) ); ?></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><?php echo esc_html( $t( 'Desenvolvido com código próprio e limpo, nunca montado a partir de plugins', 'Built with our own clean code, never pieced together from plugins' ) ); ?></li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></section>
<!-- /wp:group -->
