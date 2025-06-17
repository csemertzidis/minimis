<?php
/**
 * The main template file
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since   Timber 0.1
 */


$context          = Timber::context();
$context['posts'] = Timber::get_posts();
$templates        = array( 'posts.twig' );

//get page title
$context['title'] = 'blog';
$blogposts = array(); 
foreach ($context['posts'] as $post) {
	$p_out = array();
	
	$p_out['title'] = $post->title;
	$p_out['link'] = $post->link;
	$p_out['image'] = wp_get_attachment_image_url($post->thumbnail->ID, 'large');
	$p_out['srcset'] = wp_get_attachment_image_srcset($post->thumbnail->ID, 'large');
	$p_out['alt'] = get_post_meta($post->thumbnail->ID, '_wp_attachment_image_alt', true);
	$p_out['date'] = $post->date;

	$blogposts[] = $p_out;

}

$context['blogposts'] = $blogposts;


//add to context the categories
$context['categories'] = get_terms(array(
	'taxonomy' => 'category',
	'hide_empty' => false,
));

//print_r($context['categories']);

foreach ($context['categories'] as $category) {

	$c_out = array();

	$c_out['name'] = $category->name;
	$c_out['slug'] = $category->slug;

	//append the category to the context

	if ($c_out['slug'] == 'uncategorized' or $c_out['slug'] == 'mi-katigoriopoiimeno') {
		//skip uncategorized category
		continue;
	}
	else
	{
		$context['categories_out'][] = $c_out;
	}
	
}

//reorder the categories by name
usort($context['categories_out'], function($a, $b) {
	return strcmp($a['name'], $b['name']);
});

//put first the "All" category
$all_category = array(
	'name' => __('All', 'minimis'),
	'slug' => 'all',
);
array_unshift($context['categories_out'], $all_category);



Timber::render( $templates, $context );

// function enqueue_masonry() {
// 	if (is_home()) {
// 		wp_enqueue_script('masonry', 'https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js', array(), null, true);
// 		echo "include masonry";
// 	}
// }
// add_action('wp_enqueue_scripts', 'enqueue_masonry');
