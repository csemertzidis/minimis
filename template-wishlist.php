<?php
/*
Template Name: Wish List
*/



$context = Timber::context();
$timber_post = Timber::get_post();
$context['post'] = $timber_post;





//render the archive.twig template
Timber::render('wishlist.twig', $context);