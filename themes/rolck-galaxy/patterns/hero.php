<?php
/**
 * Title: Hero com visual cósmico
 * Slug: rolck-galaxy/hero
 * Categories: rolck-galaxy, banner
 * Keywords: hero, topo, banner, título
 * Description: Título principal, texto, dois botões, provas rápidas e a ilustração animada.
 *
 * @package RolckGalaxy
 */

$t = 'rolck_theme_t';
?>
<!-- wp:group {"tagName":"section","align":"full","className":"rolck-hero","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull rolck-hero"><!-- wp:columns {"verticalAlignment":"center","align":"wide"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"56%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:56%"><!-- wp:paragraph {"className":"is-style-eyebrow"} -->
<p class="is-style-eyebrow"><?php echo esc_html( $t( 'Temas WordPress premium', 'Premium WordPress themes' ) ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading"><?php echo wp_kses_post( $t( 'Sites e produtos WordPress que <em>carregam rápido</em> e vendem mais', 'WordPress websites and products that <em>load fast</em> and sell more' ) ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"is-style-lead rolck-hero__lead"} -->
<p class="is-style-lead rolck-hero__lead"><?php echo esc_html( $t( 'A Rolck Galaxy cria temas WordPress, plugins e templates com foco total em performance, SEO técnico e acessibilidade. Teste a demo ao vivo de cada projeto antes de comprar.', 'Rolck Galaxy builds WordPress themes, plugins and templates with a relentless focus on performance, technical SEO and accessibility. Try the live demo of every project before you buy.' ) ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( rolck_theme_url( '/projetos/', '/projects/' ) ); ?>"><?php echo esc_html( $t( 'Ver os projetos', 'Browse projects' ) ); ?></a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-ghost"} -->
<div class="wp-block-button is-style-ghost"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( rolck_theme_url( '/contato/', '/contact/' ) ); ?>"><?php echo esc_html( $t( 'Fale com a gente', 'Talk to us' ) ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:group {"className":"rolck-hero__proof","fontSize":"small","layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group rolck-hero__proof has-small-font-size"><!-- wp:paragraph -->
<p><?php echo esc_html( $t( 'Código limpo, nada montado com plugins', 'Clean code, nothing pieced together from plugins' ) ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><?php echo esc_html( $t( 'Personalização total pelo painel, sem código', 'Fully customizable from the dashboard, no code' ) ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"44%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:44%"><!-- wp:rolck/cosmic-visual /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></section>
<!-- /wp:group -->
