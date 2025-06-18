<?php
/*
Template Name: My Account Template
*/



if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Check if user is logged in
if ( ! is_user_logged_in() ) {
    // Get the current URL to redirect back after login
    $redirect_to = isset( $_SERVER['REQUEST_URI'] ) ? home_url( $_SERVER['REQUEST_URI'] ) : '';
    // Redirect to login page with redirect parameter
    wp_redirect( wp_login_url( $redirect_to ) );
    exit;
}

$context = Timber::context();
$timber_post = Timber::get_post();
$context['post'] = $timber_post;




//render the archive.twig template
Timber::render('myaccount.twig', $context);