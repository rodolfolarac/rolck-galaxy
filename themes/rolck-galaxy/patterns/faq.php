<?php
/**
 * Title: Perguntas frequentes
 * Slug: rolck-galaxy/faq
 * Categories: rolck-galaxy
 * Keywords: faq, dúvidas, perguntas
 * Description: Título à esquerda e perguntas abre-e-fecha à direita (bloco Detalhes, sem JavaScript).
 *
 * @package RolckGalaxy
 */

$t = 'rolck_theme_t';
?>
<!-- wp:group {"tagName":"section","align":"full","className":"is-style-section","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull is-style-section"><!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide"><!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|60"}}}} -->
<div class="wp-block-columns"><!-- wp:column {"width":"38%"} -->
<div class="wp-block-column" style="flex-basis:38%"><!-- wp:group {"className":"rolck-section-head","layout":{"type":"default"}} -->
<div class="wp-block-group rolck-section-head"><!-- wp:paragraph {"className":"is-style-eyebrow"} -->
<p class="is-style-eyebrow"><?php echo esc_html( $t( 'Dúvidas frequentes', 'FAQ' ) ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><?php echo esc_html( $t( 'Perguntas que sempre aparecem', 'Questions we hear a lot' ) ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"is-style-lead"} -->
<p class="is-style-lead"><?php echo esc_html( $t( 'Não achou a sua? Fale com a gente.', 'Did not find yours? Get in touch.' ) ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"62%"} -->
<div class="wp-block-column" style="flex-basis:62%"><!-- wp:details {"className":"is-style-faq"} -->
<details class="wp-block-details is-style-faq"><summary><?php echo esc_html( $t( 'As demos mostram exatamente o que vem no produto?', 'Do the demos show exactly what ships with the product?' ) ); ?></summary><!-- wp:paragraph -->
<p><?php echo esc_html( $t( 'Sim. A demo ao vivo usa o mesmo tema do pacote vendido, e o conteúdo de exemplo dela vem junto para você importar.', 'Yes. The live demo runs the same theme as the package you buy, and its sample content is included for you to import.' ) ); ?></p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details {"className":"is-style-faq"} -->
<details class="wp-block-details is-style-faq"><summary><?php echo esc_html( $t( 'Preciso comprar plugins extras para usar os temas?', 'Do I need to buy extra plugins to use the themes?' ) ); ?></summary><!-- wp:paragraph -->
<p><?php echo esc_html( $t( 'Não. Tudo o que o tema precisa vem no pacote, feito por nós. Integrações opcionais, como WooCommerce para vender, ficam indicadas na ficha técnica.', 'No. Everything the theme needs ships in the package, built by us. Optional integrations, such as WooCommerce for selling, are listed in the specs.' ) ); ?></p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details {"className":"is-style-faq"} -->
<details class="wp-block-details is-style-faq"><summary><?php echo esc_html( $t( 'Vocês fazem projetos sob medida?', 'Do you build custom projects?' ) ); ?></summary><!-- wp:paragraph -->
<p><?php echo esc_html( $t( 'Fazemos. Conte o que você precisa pelo formulário de contato e respondemos com escopo, prazo e investimento.', 'We do. Tell us what you need through the contact form and we will reply with scope, timeline and pricing.' ) ); ?></p>
<!-- /wp:paragraph --></details>
<!-- /wp:details --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
