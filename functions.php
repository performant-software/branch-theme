<?php

//*************************************
// Change the breadcrumbs to be hidden for pages and be formatted differently
// for posts
function modify_breadcrumbs($breadcrumb) {
	if ( ! is_admin() ) {
		$breadcrumb = "";
	}

	return $breadcrumb;
}

add_filter( 'breadcrumb_trail', 'modify_breadcrumbs');

//*************************************
// Change the title to be more normal

add_filter( 'wp_title', 'filter_wp_title', 10, 2 );
function filter_wp_title( $title, $separator ){
	if ($title == "Welcome to BRANCH!")
		return "BRANCH";
	return $title . " | " . get_bloginfo('name');
}


//*************************************
//
// Use the primary sidebar for only the home page
//
function disable_primary($sidebars_widgets) {
	if ( !is_admin() && !is_page('Home') && !is_page('Credits') ) {
		$sidebars_widgets['primary'] = false;
	}
	if ( !is_admin() && !is_page('Home') && !is_page('Timeline') ) {
		$sidebars_widgets['after-content'] = false;
	}

	return $sidebars_widgets;
}

add_filter( 'sidebars_widgets', 'disable_primary');

//*************************************
//
// For creating the custom post types
//

//
// Definition of the Article post type
//
$article_fields = array(
	array( 'label' => 'Featured Event', 'name' => 'art_event', 'type' => 'string', 'placeholder' => '(use event slug)'),
	array( 'label' => 'In Carousel', 'name' => 'art_carousel', 'type' => 'bool')
);

$article_post_type_data = array(
	'labels' => array(
		'name' => __( 'Articles' ),
		'singular_name' => __( 'Article' )
	),
	'public' => true,
	'has_archive' => true,
	'rewrite' => array('slug' => 'article'),
	'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
	'taxonomies' => array( 'ps_topic'),
);

$article_post_type = array( 
	'post_type' => 'ps_articles',
	'post_type_data' => $article_post_type_data,
	'meta_box_label' => __( 'Article Info' ),
	'meta_box_name' => 'article_info',
	'fields' => $article_fields
);

//
// Definition of the Event post type
//
$event_fields = array(
	array( 'label' => 'Date Started', 'name' => 'event_date_started', 'type' => 'string', 'placeholder' => 'yyyy-mm-dd'),
	array( 'label' => 'Date Ended', 'name' => 'event_date_ended', 'type' => 'string', 'placeholder' => 'yyyy-mm-dd'),
	array( 'label' => 'Duration', 'name' => 'event_duration', 'type' => 'bool')
);

$event_post_type_data = array(
	'labels' => array(
		'name' => __( 'Events' ),
		'singular_name' => __( 'Event' )
	),
	'public' => true,
	'has_archive' => true,
	'rewrite' => array('slug' => 'event'),
	'supports' => array('title', 'thumbnail', 'editor')
);

$event_post_type = array( 
	'post_type' => 'ps_event',
	'post_type_data' => $event_post_type_data,
	'meta_box_label' => __( 'Event Info' ),
	'meta_box_name' => 'event_info',
	'fields' => $event_fields
);

// Include the post types to be seen by the registration code below.
$custom_post_types = array(
	$article_post_type,
	$event_post_type
);

// Initialize the custom posts
if (class_exists('PsCustomFields')) {
	new PsCustomFields($custom_post_types);
}

//*************************************
//
// For the carousel
//

function ps_load_carousel_js() {
//	if ( is_page('Home') ) {
		echo '<link rel="stylesheet" href="'. get_stylesheet_directory_uri() . '/skins/branch/skin.css" type="text/css" media="all" />';	
		echo '<script src="'. get_stylesheet_directory_uri() . '/jquery.jcarousel.min.js" type="text/javascript"></script>';
		echo '<script type="text/javascript">jQuery(document).ready(function() { jQuery("#mycarousel").jcarousel(); });</script>';
//	}
}
add_action ( 'hybrid_before_html', 'ps_load_carousel_js');


// function carousel_scripts() {
// 	wp_register_style( 'pss-carousel-skin', get_stylesheet_directory_uri() . '/skins/branch/skin.css');
// 	wp_enqueue_style( 'pss-carousel-skin' );
// 	wp_register_script( 'pss-carousel', get_stylesheet_directory_uri() . '/jquery.jcarousel.min.js', array('jquery'));
// 	wp_enqueue_script( 'pss-carousel' );
// 	// wp_register_script( 'pss-init-carousel', get_stylesheet_directory_uri() . '/init_carousel.js', array('pss-carousel'));
// 	// wp_enqueue_script( 'pss-init-carousel' );
// }
 
add_action('wp_enqueue_scripts', 'carousel_scripts');

function populate_carousel() {
	global $article_post_type;
	$carousel = "";
	$carousel .= '<ul id="mycarousel" class="jcarousel-skin-branch">';
	$args = array( 'post_type' => $article_post_type['post_type'], 'posts_per_page' => -1 );
	$loop = new WP_Query( $args );
	while ( $loop->have_posts() ) : $loop->the_post();
		$title = get_the_title();
		$link = get_permalink();
		$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), "medium" );
		if ($thumbnail[0])
			$thumb = $thumbnail[0];
		else
			$thumb = "";
//		$thumb = get_the_post_thumbnail();
		$display = get_post_meta( get_the_ID(), 'art_carousel', true );
		if (strlen($thumb) > 0 && $display == 'Yes')
			$carousel .= '<li><a href="' . $link . '"><div>' . '<img src="' . $thumb . '" alt="article thumbnail" />' . $title . '</div></a></li>';
	endwhile;
	$carousel .= '</ul>';
	return $carousel;
}

add_shortcode('ps-carousel', 'populate_carousel');

//*************************************
//
// For the timeline
//

if (class_exists('PsTimeline')) {
	$performantTimeline = new PsTimeline(array('post_type' => $event_post_type['post_type'], 
		'start_date_name' => 'event_date_started', 
		'end_date_name' => 'event_date_ended', 
		'duration_name' => 'event_duration'
		));	
}

//*************************************
//
// Add custom taxonomies
// http://codex.wordpress.org/Function_Reference/register_taxonomy
//

function add_custom_taxonomies() {
	// Add new "topics" taxonomy to Articles
	register_taxonomy('ps_topic', $article_post_type['post_type'], array(
		// Hierarchical taxonomy (like categories)
		'hierarchical' => true,
		// This array of options controls the labels displayed in the WordPress Admin UI
		'labels' => array(
			'name' => _x( 'Topics', 'taxonomy general name' ),
			'singular_name' => _x( 'Topic', 'taxonomy singular name' ),
			'search_items' =>  __( 'Search topics' ),
			'all_items' => __( 'All Topics' ),
			'parent_item' => __( 'Parent Topic' ),
			'parent_item_colon' => __( 'Parent Topic:' ),
			'edit_item' => __( 'Edit Topic' ),
			'update_item' => __( 'Update Topic' ),
			'add_new_item' => __( 'Add New Topic' ),
			'new_item_name' => __( 'New Topic Name' ),
			'menu_name' => __( 'Topics' )
		),
		// Control the slugs used for this taxonomy
		'rewrite' => array(
			'slug' => 'topics', // This controls the base slug that will display before each term
			'with_front' => false, // Don't display the category base before "/topics/"
			'hierarchical' => true // This will allow URL's like "/topics/boston/cambridge/"
		),
	));
}
add_action( 'init', 'add_custom_taxonomies', 0 );

//*************************************
//
// Display custom taxonomy
//
function add_topics(){
	if (!is_page('Topic Clusters')) return;
	
	/**
	 * Create an unordered list of links to active location archives
	 */
	echo '<ul class="topics-list">';
	wp_list_categories( array(
	  'taxonomy' => 'ps_topic',
	  'hide_empty' => 1,
	  'show_count' => 1,
	  'pad_counts' => 1,
	  'hierarchical' => 1,
	  'title_li' => ''
	) );
	echo '</ul>';
}

add_action( "hybrid_after_content", 'add_topics' );

?>