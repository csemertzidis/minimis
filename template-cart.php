<?php
/*
Template Name: Cart
*/



$context = Timber::context();
$timber_post = Timber::get_post();
$context['post'] = $timber_post;

//print_r($context);


//render the archive.twig template
Timber::render('cart.twig', $context);