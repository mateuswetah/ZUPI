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
const ZUPI_VERSION = '0.1.2';

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
	wp_enqueue_style( 'blocksy-child-style', get_stylesheet_directory_uri() . '/style.min.css', ZUPI_VERSION );
	wp_enqueue_script( 'zupi-scripts', get_stylesheet_directory_uri() . '/js/scripts.js', array(), ZUPI_VERSION );
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

/* Custom item gallery for the Artists Collection */
function zupi_artists_media_gallery() {

	$collections_where_relationship_metadata_appear_as_gallery = [ 267, 5 ];
	$related_collections_that_should_have_media_gallery = [ 267, 20 ];
	$max_items_per_gallery = 4;
	
	if ( in_array( tainacan_get_collection_id(), $collections_where_relationship_metadata_appear_as_gallery ) ) {

		// Gets the current Item
		$item = tainacan_get_item();
		if ( !$item )
			return;
		
		// Then fetches related ones
		$related_items = $item->get_related_items([]);
		if ( !count($related_items) )
			return;

		echo '<section class="tainacan-item-section tainacan-item-section--document">';

		foreach($related_items as $collection_id => $related_group) {
		
			if (
				in_array($related_group['collection_id'], $related_collections_that_should_have_media_gallery) &&
				isset($related_group['items']) &&
				isset($related_group['total_items']) &&
				$related_group['total_items']
			) {
				$media_items_thumbnails = []; // Obter miniaturas dos itens
				$media_items_main = []; // Obter documentos dos itens
				$block_id = uniqid();

				foreach ( array_slice( $related_group['items'], 0, $max_items_per_gallery) as $related_item ) {
					if ( isset($related_item['thumbnail']) && isset($related_item['thumbnail']['tainacan-medium']) ) {
						
						$media_items_main[] = 
							tainacan_get_the_media_component_slide(array(
								'media_content' => tainacan_get_the_document($related_item['id']),
								'media_content_full' => (
									$related_item['document_type'] === 'attachment' ?
										tainacan_get_the_document($related_item['id'], 'full') :
										sprintf('<div class="attachment-without-image">%s</div>', tainacan_get_the_document($related_item['id'], 'full'))
								),
								'media_title' => $related_item['title'],
								'media_description' => $related_item['description'],
								'class_slide_metadata' => 'hide-name hide-description hide-caption'
							));

						$media_items_thumbnails[] = 
							tainacan_get_the_media_component_slide(array(
								'media_content' => get_the_post_thumbnail($related_item['id'], 'tainacan-medium'),
								'media_content_full' => '',
								'media_title' => $related_item['title'],
								'media_description' => $related_item['description'],
								'class_slide_metadata' => 'hide-description hide-caption'
							));
					}
				}
			
		
				echo tainacan_get_the_media_component(
					'tainacan-item-gallery-block_id-' . $block_id,
					count($media_items_thumbnails) > 1 ? $media_items_thumbnails : [],
					$media_items_main,
					array(
						'wrapper_attributes' => 'class="tainacan-media-component" ',
						'class_main_div' => '',
						'class_thumbs_div' => '',
						'swiper_main_options' => array(
							'navigation' => array(
								'nextEl' => sprintf('.swiper-navigation-next_tainacan-item-gallery-block_id-%s-main', $block_id),
								'prevEl' => sprintf('.swiper-navigation-prev_tainacan-item-gallery-block_id-%s-main', $block_id),
								'preloadImages' => false,
								'lazy' => true
							)
						),
						'swiper_thumbs_options' => '',
						'disable_lightbox' => false,
						'hide_media_name' => true,
						'hide_media_caption' => true,
						'hide_media_description' => true,
						'lightbox_has_light_background' => false
					)
				);

				if ( $related_group['total_items'] > $max_items_per_gallery ) {
					echo '<div class="wp-block-buttons">
						<div class="wp-block-button">
							<a class="wp-block-button__link" href="' . esc_url( get_permalink( $related_group['collection_id'] ) ) . '?metaquery[0][key]=' . esc_attr($related_group['metadata_id']) . '&metaquery[0][value][0]=' . esc_attr($item->get_ID()) . '&metaquery[0][compare]=IN">
								' . sprintf( __('View all %s related items', 'tainacan'), $related_group['total_items'] ) . '
							</a>
						</div>
					</div>';
				}
			}
		}

		echo '</section>';
	}
}
add_action( 'tainacan-blocksy-single-item-after-document', 'zupi_artists_media_gallery' );


/* Custom item related items for the Works Collection */
function zupi_works_related_column() {

	$collections_where_relationship_metadata_appear_as_column = [ 267, 20, 5054 ];
	
	if ( in_array( tainacan_get_collection_id(), $collections_where_relationship_metadata_appear_as_column ) ) {

		// Gets the current Item
		$item = tainacan_get_item();
		if ( !$item )
			return;		

		$item_metadata = $item->get_metadata();

		if ( !count($item_metadata) )
			return;

		echo '<section class="tainacan-item-section tainacan-item-section--items-related-to-that">';

		foreach ( $item_metadata as $item_metadatum ) {

			$metadatum = $item_metadatum->get_metadatum();
			
			if ( $metadatum && $item_metadatum->get_value()  ) {
				$metadata_type = $metadatum->get_metadata_type();
				$options = $metadatum->get_metadata_type_options();
	
				if (
						$metadata_type === 'Tainacan\Metadata_Types\Relationship' &&
						(
							is_array($item_metadatum->get_value()) && count($item_metadatum->get_value()) ||
							!empty($item_metadatum->get_value())
						)
				) {
					echo '<h3 style="margin-bottom: -1rem;" class="tainacan-metadata-label">' . $metadatum->get_name() . '</h3>';
					echo \Tainacan\Theme_Helper::get_instance()->get_tainacan_dynamic_items_list(array(
						'collection_id' => $options['collection_id'],
						'load_strategy' => 'selection',
						'selected_items' => json_encode($item_metadatum->get_value()),
						'layout' => 'grid',
						'max_columns_count' => 1,
						'image_size' => ($metadatum->get_id() == 3527 || $metadatum->get_id() == 4677) ? 'tainacan-medium-full' : 'tainacan-medium'
					));
				}
			}
		}

		echo '</section>';
	}
}
add_action( 'tainacan-blocksy-single-item-after-items-related-to-this', 'zupi_works_related_column' );

/* Adds audio attachment to in the middle of metadata */
function zupi_add_audiodescription_to_works($after, $metadata_section) {
	$item = tainacan_get_item();

	if ( !$item )
		return $after;

	$attachments = $item->get_attachments();
	$audio_attachments = array_filter( $attachments, function($attachment) {
		return get_post_mime_type($attachment) == "audio/mpeg" || get_post_mime_type($attachment) == "audio/wav" || get_post_mime_type($attachment) == "audio/ogg" || get_post_mime_type($attachment) == "audio/flac";
	});

	$output = '';
	foreach($audio_attachments as $audio_attachment) {
		$output .= '<div class="metadata-slug-audio-descricao tainacan-item-section__metadatum">
			<h3 class="tainacan-metadata-label">Áudio descrição</h3>
			<p class="tainacan-metadata-value">'. tainacan_get_attachment_as_html($audio_attachment->ID) . '</p>' . 	
		'</div>';
	}
	return $after . $output;
}
add_filter( 'tainacan-get-metadata-section-as-html-after--id-' . \Tainacan\Entities\Metadata_Section::$default_section_slug, 'zupi_add_audiodescription_to_works', 2, 10 ); 

/* Removes the audio attachment from the gallery list. The gallery component uses tainacan_get_the_attachment. Thats why in the filter above we fetch them directly from the item */
function zupi_remove_audio_from_attachments_list($attachments, $item) {
	$non_audio_attachments = array_filter( $attachments, function($attachment) {
		return get_post_mime_type($attachment) != "audio/mpeg" && get_post_mime_type($attachment) != "audio/wav" && get_post_mime_type($attachment) != "audio/ogg" && get_post_mime_type($attachment) != "audio/flac";
	});

	return $non_audio_attachments;
}
add_filter('tainacan-get-the-attachments', 'zupi_remove_audio_from_attachments_list', 2, 12);