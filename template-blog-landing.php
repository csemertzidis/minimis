<?php
/*
Template Name: Archive Posts
*/



$context = Timber::context();
$timber_post = Timber::get_post();
$context['post'] = $timber_post;

//query the posts
$args = array(
    'post_type' => 'post',
    'posts_per_page' => 10,
    'paged' => get_query_var('paged') ? get_query_var('paged') : 1
);

$context['posts'] = get_posts($args);

$all_posts = array();

foreach ($context['posts'] as $post) {
    $p_out = array();

    $fields = get_fields($post->ID);

    $p_out['title'] = $post->post_title;
    $p_out['link'] = get_permalink($post->ID);
    $p_out['date'] = get_the_date('F j, Y', $post->ID);
    $p_out['short_description'] = $fields['short_description'];

    //get the featured image
    $featured_image = get_the_post_thumbnail_url($post->ID, 'medium');
    $p_out['featured_image'] = $featured_image;

    //srcset
    $p_out['srcset'] = wp_get_attachment_image_srcset(get_post_thumbnail_id($post->ID), 'medium');




    //push out
    array_push($all_posts, $p_out);
}

$context['all_posts'] = $all_posts;

//render the archive.twig template
Timber::render('posts-landing.twig', $context);