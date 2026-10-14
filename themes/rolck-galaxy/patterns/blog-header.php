<?php
/**
 * Title: Topo do blog
 * Slug: rolck-galaxy/blog-header
 * Inserter: no
 * Description: Usado no modelo do blog. Os textos ficam em Rolck Galaxy → Textos do site.
 *
 * @package RolckGalaxy
 */

$rolck_blog_page = (int) get_option( 'page_for_posts' );
$rolck_title     = $rolck_blog_page ? get_the_title( $rolck_blog_page ) : rolck_theme_string( 'blog', 'Blog' );
?>
<!-- wp:group {"tagName":"section","align":"full","className":"rolck-page-header","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull rolck-page-header"><!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide"><!-- wp:rolck/breadcrumbs /-->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading"><?php echo esc_html( $rolck_title ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"is-style-lead"} -->
<p class="is-style-lead"><?php echo esc_html( rolck_theme_string( 'blog_intro' ) ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
