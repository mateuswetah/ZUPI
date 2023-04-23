<?php
	$is_in_grid_two = $request['view_mode'] === 'zupigrid2';
?>

<?php if ( have_posts() ) : ?>
	<ul class="tainacan-zupi-grid-container">

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
						<div class="zupi-grid-item-thumbnail">
							<?php echo '<img alt="', esc_attr_e('Minatura da imagem do item', 'zupi'), '" src="', esc_url(get_stylesheet_directory_uri()), '/images/thumbnail_placeholder.png">'?>
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
