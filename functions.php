<?php
/**
 * Theme Name: ZUPI
 * Description: Tema do Acervo Zupi
 * Author: wetah
 * Template: blocksy
 * Text Domain: zupi
 */

if (! defined('WP_DEBUG')) {
	die( 'Direct access forbidden.' );
}

/** Child Theme version */
const ZUPI_VERSION = '0.0.20';

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
	wp_enqueue_style( 'blocksy-child-style', get_stylesheet_directory_uri() . '/style.min.css', ZUPI_VERSION );
});

/**
 * Uses filters to add the collections links to tainacan archives.
 */
function zupi_add_collections_links_to_archive_header() {

	if ( is_post_type_archive() || is_tax() || is_archive() ) {

		$Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();

		$collections = $Tainacan_Collections->fetch(
			array(
				'posts_per_page' => 10,
				'status' => 'publish',
				'post__not_in' => [ tainacan_get_collection_id() ]
			)
		, 'OBJECT' );

		if ( is_array($collections) && count($collections) >= 0 ) {
		
			$collections_links_html = '';

			ob_start();
			?>
			<nav>
				<ul class="tainacan-collections-links">
					<?php foreach($collections as $collection) : ?>
						<li><a href="<?php echo $collection->get_url(); ?>"><?php echo $collection->get_name(); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
			<div class="tainacan-items-list-heading">
				<?php if ( is_post_type_archive() ) : ?>
					<h1><?php tainacan_the_collection_name(); ?></h1>
					<p><?php tainacan_the_collection_description(); ?></p>
				<?php elseif( is_tax() ): ?>
					<h1><?php tainacan_the_term_name(); ?></h1>
					<p><?php tainacan_the_term_description(); ?></p>
				<?php else: ?>
					<h1><?php echo __('Todos os itens do acervo', 'zupi'); ?></h1>
				<?php endif; ?>
			</div>
			<?php

			$collections_links_html = ob_get_contents();
			ob_clean();

			if ( !empty($collections_links_html) ) {
				echo '<script type="text/javascript">
						wp.hooks.addFilter("tainacan_faceted_search_search_control_before", "tainacan-hooks", function() { return `' . $collections_links_html . '`; });
					</script>';
			}
		}
	}
}
add_action( 'wp_body_open', 'zupi_add_collections_links_to_archive_header' );


/* Registers Zupi Custom View Modes */
function zupi_register_tainacan_view_modes() {
	if ( function_exists( 'tainacan_register_view_mode' ) ) {

		// Grid
		tainacan_register_view_mode('zupigrid', array(
			'label' => __( 'Fichas quadradas', 'zupi' ),
			'description' => __( 'Uma grade de itens feita para a revista Zupi', 'zupi' ),
			'icon' => '<span class="icon"><i class="tainacan-icon tainacan-icon-viewcards tainacan-icon-1-25em"></i></span>',
			'dynamic_metadata' => false,
			'template' => get_stylesheet_directory() . '/tainacan/view-mode-zupigrid.php'
		));

		// Grid 2
		tainacan_register_view_mode('zupigrid2', array(
			'label' => __( 'Fichas pequenas', 'zupi' ),
			'description' => __( 'Uma grade de itens menores, feita para a revista Zupi', 'zupi' ),
			'icon' => '<span class="icon"><i class="tainacan-icon tainacan-icon-viewminiature tainacan-icon-1-25em"></i></span>',
			'dynamic_metadata' => false,
			'template' => get_stylesheet_directory() . '/tainacan/view-mode-zupigrid.php'
		));

		// Grid 3
		tainacan_register_view_mode('zupigrid3', array(
			'label' => __( 'Fichas retangulares', 'zupi' ),
			'description' => __( 'Uma grade de itens maior, feita para os eventos revista Zupi', 'zupi' ),
			'icon' => '<span class="icon"><i class="tainacan-icon tainacan-icon-viewrecords tainacan-icon-1-25em"></i></span>',
			'dynamic_metadata' => false,
			'template' => get_stylesheet_directory() . '/tainacan/view-mode-zupigrid.php'
		));
	}
}
add_action( 'after_setup_theme', 'zupi_register_tainacan_view_modes' );


/* Builds navigation link for custom view modes */
function get_item_link_for_navigation($item_url, $index) {
		
	if ( $_GET && isset($_GET['paged']) && isset($_GET['perpage']) ) {
		$query = '';
		$perpage = (int)$_GET['perpage'];
		$paged = (int)$_GET['paged'];
		$index = (int)$index;
		$query .= '&pos=' . ( ($paged - 1) * $perpage + $index );
		$query .= '&source_list=' . (is_tax() ? 'term' : 'collection');
		return $item_url . '?' .  $_SERVER['QUERY_STRING'] . $query;
	}
	return $item_url;
}