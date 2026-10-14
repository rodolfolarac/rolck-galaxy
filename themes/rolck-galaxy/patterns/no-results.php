<?php
/**
 * Title: Nenhum resultado
 * Slug: rolck-galaxy/no-results
 * Inserter: no
 * Description: Mensagem e busca quando uma lista não tem resultados.
 *
 * @package RolckGalaxy
 */

?>
<!-- wp:paragraph {"style":{"color":{"text":"var:preset|color|muted"}}} -->
<p class="has-text-color" style="color:var(--wp--preset--color--muted)"><?php echo esc_html( rolck_theme_string( 'search_empty', 'Nada encontrado.' ) ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:search {"showLabel":false} /-->
