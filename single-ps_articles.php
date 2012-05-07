<?php
/**
 * Singular Template for Articles
 *
 * This adds the timeline to all posts that contain the category "Articles"
 *
 * @package Hybrid
 * @subpackage Template
 */

get_header(); // Loads the header.php template. ?>

	<div id="content" class="hfeed content">

		<?php do_atomic( 'before_content' ); // hybrid_before_content ?>

		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

			<div id="post-<?php the_ID(); ?>" class="<?php hybrid_entry_class(); ?>">

				<?php do_atomic( 'before_entry' ); // hybrid_before_entry ?>
				<?php $start_date = get_post_meta( $post->ID, 'art_date_started', true ); ?>
				<?php $end_date = get_post_meta( $post->ID, 'art_date_ended', true ); ?>
				<?php if (strlen($start_date) > 0)
					if (strlen($end_date) > 0) 
						echo "<div class='timeline_date'>(" . $start_date . ' - ' . $end_date . ')</div>';
					else
						echo "<div class='timeline_date'>" . $start_date . "</div>";
				?>
				<div class="entry-content">
					<div class="extract">
					<h3 class='article_extract_label'>Abstract</h3>
					<div class="article_extract">
						<?php the_excerpt(); ?>						
					</div>
					</div>
					<?php //the_post_thumbnail(); ?>
					<?php the_content(); ?>
					<?php wp_link_pages( array( 'before' => '<p class="page-links pages">' . __( 'Pages:', hybrid_get_textdomain() ), 'after' => '</p>' ) ); ?>
				</div><!-- .entry-content -->

				<?php do_atomic( 'after_entry' ); // hybrid_after_entry ?>

			</div><!-- .hentry -->

			<?php do_atomic( 'after_singular' ); // hybrid_after_singular ?>

			<?php comments_template( '/comments.php', true ); // Loads the comments.php template ?>

			<?php endwhile; ?>

		<?php else : ?>

			<?php get_template_part( 'loop-error' ); // Loads the loop-error.php template. ?>

		<?php endif; ?>

		<?php echo $performantTimeline->performantTimeline(); ?>
		
		<?php do_atomic( 'after_content' ); // hybrid_after_content ?>

	</div><!-- .content .hfeed -->

<?php get_footer(); // Loads the footer.php template. ?>