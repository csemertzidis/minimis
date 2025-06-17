<?php
function my_search_function()
{
    $search_term = sanitize_text_field($_POST['search_term']);

    $args = array(
        's' => $search_term,
        'posts_per_page' => 10, // Limit the number of results
        //posts and products
        'post_type' => array('post', 'product')
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            ?>
            <a href="<?php the_permalink(); ?>">
                <h3><?php the_title(); ?></h3>
                <p><?php the_excerpt(); ?></p>
            </a>
            <?php
        }
        wp_reset_postdata();
    } else {
        echo "<p>No results found.</p>";
    }

    die(); // Always use die() to end AJAX functions
}

add_action('wp_ajax_my_search_function', 'my_search_function');
add_action('wp_ajax_nopriv_my_search_function', 'my_search_function');