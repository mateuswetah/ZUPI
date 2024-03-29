<?php
	$is_in_grid_two = $request['view_mode'] === 'zupigrid2';
	$is_in_grid_three = $request['view_mode'] === 'zupigrid3';

	$metadata_objects = [];

	if ( !$is_in_grid_two && !$is_in_grid_three ) {

		$metadata_repository = \Tainacan\Repositories\Metadata::get_instance();
		$is_repository_level = !isset($request['collection_id']);
		
		if ( !$is_repository_level ) {
			$collection = tainacan_get_collection([ 'collection_id' => $request['collection_id'] ]);
			$metadata_objects = $metadata_repository->fetch_by_collection(
				$collection,
				[
					'posts_per_page' => 50,
					// 'post_status' => 'publish'
				],
				'OBJECT'
			);
		} else {
			$metadata_objects = $metadata_repository->fetch(
				[ 
					'meta_query' => [
						[
							'key'     => 'collection_id',
							'value'   => 'default',
							'compare' => '='
						]
					],
					// 'post_status' => 'publish',
					'posts_per_page' => 50,
					'include_control_metadata_types' => true
				],
				'OBJECT'
			);
		}
	}
?>

<?php if ( have_posts() ) : ?>
	<ul class="tainacan-zupi-grid-container <?php echo $is_in_grid_two ? 'tainacan-zupi-grid-container--2' : ''; ?> <?php echo $is_in_grid_three ? 'tainacan-zupi-grid-container--3' : ''; ?>">

		<?php $item_index = 0; while ( have_posts() ) : the_post(); ?>
			
			<li class="tainacan-zupi-grid-item">
				<a href="<?php echo get_item_link_for_navigation(get_permalink(), $item_index); ?>">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="zupi-grid-item-thumbnail">
							<?php the_post_thumbnail( 'large' ); ?>
							<!-- <?php tainacan_the_document(); ?> -->
							<div class="skeleton"></div> 
						</div>
					<?php else : ?>
						<div class="zupi-grid-item-thumbnail placeholder">
							<?php echo '<img src="' . get_stylesheet_directory_uri() .'/images/artist_placeholder.gif" alt="' . esc_attr('Imagem não definida', 'zupi') . '">'?>
							<div class="skeleton"></div> 
						</div>
					<?php endif; ?>

					<?php
							$collection_id = str_replace('_item', '', str_replace('tnc_col_', '', get_post_type()));
							$title_class = 'metadata-title';
						?>

					<div class="<?php echo $title_class; ?>">
						<h3><?php the_title(); ?></h3>
					</div>

					<?php if ( !$is_in_grid_two && !$is_in_grid_three ) : ?>
						<div class="metadata-description">
							<?php
								foreach($metadata_objects as $metadata_object) {
									if ( $metadata_object->get_metadata_type() === 'Tainacan\Metadata_Types\Relationship' ) {
										$second_metadata_value = tainacan_get_the_metadata([ 'metadata' => $metadata_object ]);
										if ( !empty($second_metadata_value) ) {
											echo $second_metadata_value;
											break;
										}
									}
								}
							?>
						</div>
					<?php endif; ?>
				</a>
			</li>	
		
		<?php $item_index++; endwhile; ?>
	
	</ul>

<?php else : ?>
	<div class="tainacan-zupi-grid-container">
		<section class="section">
			<div class="content has-text-gray-4 has-text-centered">
				<p>
					<span class="icon is-large">
						<i class="tainacan-icon tainacan-icon-48px tainacan-icon-items"></i>
					</span>
				</p>
				<p><?php echo __( 'Nenhum item encontrado.','zupi' ); ?></p>
			</div>
		</section>
	</div>
<?php endif; ?>
