<?php
/**
 * Title: Números
 * Slug: rolck-galaxy/stats
 * Categories: rolck-galaxy
 * Keywords: números, estatísticas, contador
 * Description: Faixa de números com contagem animada.
 *
 * @package RolckGalaxy
 */

$t     = 'rolck_theme_t';
$items = array(
	array(
		'value'  => '3',
		'prefix' => '',
		'suffix' => '',
		'label'  => $t( 'Projetos na vitrine', 'Projects in the showcase' ),
	),
	array(
		'value'  => '0',
		'prefix' => '',
		'suffix' => '',
		'label'  => $t( 'Plugins de terceiros obrigatórios', 'Required third-party plugins' ),
	),
	array(
		'value'  => '100',
		'prefix' => '',
		'suffix' => '%',
		'label'  => $t( 'Código próprio e documentado', 'In-house, documented code' ),
	),
);
?>
<!-- wp:group {"tagName":"section","align":"full","className":"rolck-stats-band","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull rolck-stats-band"><!-- wp:rolck/stats <?php echo wp_json_encode( array( 'align' => 'wide', 'items' => $items ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?> /--></section>
<!-- /wp:group -->
