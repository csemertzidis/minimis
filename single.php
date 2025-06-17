<?php
/**
 * The Template for displaying all single posts
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

$context = Timber::context();
$timber_post = Timber::get_post();
$context['post'] = $timber_post;

$context['fields'] = get_fields();

foreach ($context['fields']['related_posts'] as $related_post) {
	$context['related_posts'][] = array(
		'id' => $related_post->ID,
		'title' => $related_post->post_title,
		'link' => get_permalink($related_post->ID),
		'image' => wp_get_attachment_image_src(get_post_thumbnail_id($related_post->ID), 'medium')[0],
	);
}
	

if (post_password_required($timber_post->ID)) {
	Timber::render('single-password.twig', $context);
} else {
	Timber::render(array('single-' . $timber_post->ID . '.twig', 'single-' . $timber_post->post_type . '.twig', 'single-' . $timber_post->slug . '.twig', 'single.twig'), $context);
}
