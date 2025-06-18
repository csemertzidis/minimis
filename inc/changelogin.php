<?php

/**
 * Customize WordPress login page logo
 */
function minimis_custom_login_logo() {
    ?>
    <style type="text/css">
        #login h1 a, .login h1 a {
            background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/assets/logo_center.png);
            height: 65px;
            width: 320px;
            background-size: contain;
            background-repeat: no-repeat;
            padding-bottom: 30px;
        }
    </style>
    <?php
}
add_action('login_enqueue_scripts', 'minimis_custom_login_logo');

// Change the login logo URL to point to the site's homepage
function minimis_login_logo_url() {
    return home_url();
}
add_filter('login_headerurl', 'minimis_login_logo_url');

// Change the login logo title
function minimis_login_logo_url_title() {
    return get_bloginfo('name');
}
add_filter('login_headertext', 'minimis_login_logo_url_title');