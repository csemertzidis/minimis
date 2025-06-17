<?php


//disable coupon code in the cart
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 ); 


//this function prepares the content of the archive page



function archive_page_products_with_color($posts, $color)
{
    $colors_out = array();
    foreach ($color as $c) {
        $colors_out[] = get_term($c, 'pa_bottle-color')->slug;
    }

    // print_r($colors_out);
    // echo "<br>";


    $products_out = array();
   
    foreach ($posts as $post) {
        $product = wc_get_product($post->ID);

        $variations = $product->get_available_variations();
        foreach ($variations as $variation) {

            if (in_array($variation['attributes']['attribute_pa_bottle-color'], $colors_out)) {
               // echo $variation['variation_id'] .'-'.$variation['attributes']['attribute_pa_bottle-color']. "<br>";

                $p_out = array();
                $p_out['type'] = 'simple';
                $p_out['id'] = $product->get_id();
                //get variation name
                $p_out['name'] = $product->get_name() ;
                $p_out['price'] = $variation['display_price'] . '&nbsp;' . get_woocommerce_currency_symbol();
                $p_out['permalink'] = $product->get_permalink().'?attribute_pa_bottle-color='.$variation['attributes']['attribute_pa_bottle-color'];
                $p_out['image'] = get_the_post_thumbnail_url($variation['variation_id'], 'full') ?: wc_placeholder_img_src();
                $p_out['srcset'] = wp_get_attachment_image_srcset(get_post_thumbnail_id($variation['variation_id']), 'medium');

                //check if this product is in wishlist
                if (isset($_COOKIE['minimiswishlist'])) {
                    
                    $wishlist = json_decode(stripslashes($_COOKIE['minimiswishlist']), true);
                    if (in_array($product->get_id(), $wishlist)) {
                        //echo "found in wishlist<br>";
                        $p_out['in_wishlist'] = true;
                    }
                }

                $products_out[] = $p_out;
            }
        }
            
    }
    return $products_out;   
}


function archive_page_products($posts)
{

    $products_out = array();
    foreach ($posts as $post) {
        $product = wc_get_product($post->ID);

        $p_out = array();



        //simple product data

        if ($product->is_type('simple')) {

            $p_out['type'] = 'simple';
            $p_out['id'] = $product->get_id();
            //get variation name 
            $p_out['name'] = $product->get_name();
            $p_out['price'] = $product->get_price() . '&nbsp;' . get_woocommerce_currency_symbol();
            $p_out['permalink'] = $product->get_permalink();
            $p_out['image'] = get_the_post_thumbnail_url($product->get_id(), 'medium') ?: wc_placeholder_img_src();
            $p_out['srcset'] = wp_get_attachment_image_srcset(get_post_thumbnail_id($product->get_id()), 'medium');
            //category name
            $terms = get_the_terms($product->get_id(), 'product_cat');
            $p_out['category'] = $terms[0]->name;
        }

        //check if a product is variable product
        if ($product->is_type('variable')) {
            $variations = $product->get_available_variations();


            $v_out = array();

            //the amount of variations
            $p_out['type'] = 'variable';
            $p_out['id'] = $product->get_id();

            //get variation a

            $p_out['name'] = $product->get_name();
            $p_out['variations_number'] = count($variations);
            $p_out['permalink'] = $product->get_permalink();
            $terms = get_the_terms($product->get_id(), 'product_cat');
            $p_out['category'] = $terms[0]->name;


            //variation images

            foreach ($variations as $variation) {

                $v_out['id'] = $variation['variation_id'];
                $v_out['color'] = $variation['attributes']['attribute_pa_bottle-color'];
                //variation link
                $v_out['link'] = wc_get_product($variation['variation_id'])->get_permalink();
                //product name and variation name
                $v_out['name'] = $product->get_name() . ' ' . $variation['attributes']['attribute_pa_bottle-color'];

                $v_out['vdata'] = $variation;

                //variation image
                $v_out['image'] = get_the_post_thumbnail_url($variation['variation_id'], 'post-thumbnail') ?: wc_placeholder_img_src();

                //srcset
                $v_out['srcset'] = wp_get_attachment_image_srcset(get_post_thumbnail_id($variation['variation_id']), 'medium');

                //sizes
                $v_out['sizes'] = "(max-width:575) 600px,(min-width: 576) and (max-width:767) 50vw,(min-width:768) 33vw";

                //alternative text
                $v_out['alt'] = get_post_meta(get_post_thumbnail_id($variation['variation_id']), '_wp_attachment_image_alt', TRUE);




                $p_out['variations'][] = $v_out;
            }

            //if all variations have the same price
            if (count(array_unique(array_column($variations, 'display_price'))) === 1) {
                $p_out['price'] = $variations[0]['display_price'] . '&nbsp;' . get_woocommerce_currency_symbol();
            } else {
                $p_out['price'] = 'from ' . $product->get_price() . '&nbsp;' . get_woocommerce_currency_symbol();
            }

        }
        //check if this product is in wishlist
        if (isset($_COOKIE['minimiswishlist'])) {
            $wishlist = json_decode(stripslashes($_COOKIE['minimiswishlist']), true);
            if (in_array($product->get_id(), $wishlist)) {
                $p_out['in_wishlist'] = true;
            }
        }
        $products_out[] = $p_out;
        ;
    }

    return $products_out;
}

function get_woocommerce_cart_count()
{
    return WC()->cart->get_cart_contents_count();
}

function custom_minicart_count_fragment($fragments)
{
    ob_start();
    ?>
    <span class="cart-count"><?php echo get_woocommerce_cart_count(); ?></span>
    <?php
    $fragments['span.cart-count'] = ob_get_clean();
    return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'custom_minicart_count_fragment');

add_action('woocommerce_add_to_cart', 'refresh_mini_cart_after_add_to_cart');
function refresh_mini_cart_after_add_to_cart()
{
    // Don't output script during AJAX requests to avoid corrupting JSON response
    if (wp_doing_ajax()) {
        return;
    }
    
    ?>
    <script>
        jQuery(document).ready(function ($) {
            // Wrap your code in a DOMContentLoaded event listener
            $(function () {
                $('#minicartmodal .modal-body').load(wc_add_to_cart_params.ajax_url + '?action=woocommerce_get_refreshed_fragments');
            });
        });
    </script>
    <?php
}

//set default product archive page to 15 products per page
add_filter('loop_shop_per_page', 'new_loop_shop_per_page', 20);

function new_loop_shop_per_page($cols)
{
    $cols = 15;
    return $cols;
}





add_filter('wp_calculate_image_srcset', 'my_custom_image_srcset', 10, 5);
function my_custom_image_srcset($sources, $size_array, $image_src, $image_meta, $attachment_id)
{
    // Remove the original size from the array
    unset($sources[$image_meta['width']]);
    return $sources;
}

// AJAX handler for adding products to cart
add_action('wp_ajax_ajax_add_to_cart', 'ajax_add_to_cart_handler');
add_action('wp_ajax_nopriv_ajax_add_to_cart', 'ajax_add_to_cart_handler');

function ajax_add_to_cart_handler() {
    // Early exit if not AJAX
    if (!wp_doing_ajax()) {
        wp_die('Direct access not allowed');
    }
    
    // More aggressive output cleaning
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Start fresh output buffer
    ob_start();
    
    // Set proper headers
    header('Content-Type: application/json');
    
    // Prevent any caching
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    
    try {
        // Properly initialize WooCommerce for AJAX requests
        if (!did_action('woocommerce_init')) {
            WC()->frontend_includes();
            WC()->session = WC()->session ?? new WC_Session_Handler();
            WC()->session->init();
            WC()->customer = WC()->customer ?? new WC_Customer();
            WC()->cart = WC()->cart ?? new WC_Cart();
            
            // Trigger WooCommerce init without output
            do_action('woocommerce_init');
        }
        
        // Ensure current user context is properly set
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            wp_set_current_user($current_user->ID);
            
            // Reinitialize customer with current user data
            WC()->customer = new WC_Customer($current_user->ID);
            
            // Ensure WordPress user globals are properly set for role checks
            global $current_user;
            $current_user = wp_get_current_user();
            
            error_log('AJAX Add to Cart - Logged in user ID: ' . $current_user->ID);
            error_log('AJAX Add to Cart - User roles: ' . implode(', ', $current_user->roles));
        } else {
            error_log('AJAX Add to Cart - Guest user');
        }
        
        // Ensure WC is loaded
        if (!function_exists('WC')) {
            throw new Exception('WooCommerce not loaded');
        }
        
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['_wpnonce'], 'minimis_ajax_nonce')) {
            throw new Exception('Security check failed. Please refresh the page and try again.');
        }
        
        // Log for debugging
        error_log('AJAX Add to Cart called with POST data: ' . print_r($_POST, true));

        if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
            throw new Exception('Invalid product ID');
        }

        $product_id = intval($_POST['product_id']);
        $quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;
        $variation_id = 0;
        $variation = array();

        // Get the product
        $product = wc_get_product($product_id);
        if (!$product) {
            throw new Exception('Product not found for ID: ' . $product_id);
        }

        error_log('Product found: ' . $product->get_name() . ' (Type: ' . $product->get_type() . ')');

        // Handle variable products
        if ($product->is_type('variable')) {
            // Get variation attributes from POST data
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'attribute_') === 0 && !empty($value)) {
                    $variation[$key] = sanitize_text_field($value);
                }
            }

            error_log('Found variation attributes: ' . print_r($variation, true));

            // Find variation ID based on attributes
            if (!empty($variation)) {
                $data_store = WC_Data_Store::load('product');
                $variation_id = $data_store->find_matching_product_variation($product, $variation);
                
                error_log('Found variation ID: ' . $variation_id);
                
                if (!$variation_id) {
                    throw new Exception('Please select product options. Selected attributes: ' . print_r($variation, true));
                }
            } else {
                throw new Exception('Please select product options for this variable product');
            }
        }

        // Add to cart
        $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variation);
        
        if ($passed_validation) {
            $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation);
            
            error_log('Cart item key: ' . $cart_item_key);
            
            if ($cart_item_key) {
                do_action('woocommerce_ajax_added_to_cart', $product_id);
                
                // Get updated cart count
                $cart_count = WC()->cart->get_cart_contents_count();
                
                error_log('Cart count after adding: ' . $cart_count);
                
                // Clean any output that might have been generated
                ob_clean();
                
                wp_send_json_success(array(
                    'message' => 'Product added to cart successfully!',
                    'cart_count' => $cart_count,
                    'cart_hash' => WC()->cart->get_cart_hash(),
                    'cart_item_key' => $cart_item_key,
                    'fragments' => apply_filters('woocommerce_add_to_cart_fragments', array())
                ));
            } else {
                throw new Exception('Failed to add product to cart - no cart item key returned');
            }
        } else {
            throw new Exception('Product validation failed');
        }
        
    } catch (Exception $e) {
        error_log('AJAX Add to Cart Error: ' . $e->getMessage());
        
        // Clean any output that might have been generated
        ob_clean();
        
        wp_send_json_error(array('message' => $e->getMessage()));
    }
    
    wp_die();
}

// AJAX handler for color variation changes
add_action('wp_ajax_ajax_color_variation', 'ajax_color_variation_handler');
add_action('wp_ajax_nopriv_ajax_color_variation', 'ajax_color_variation_handler');

function ajax_color_variation_handler() {
    // Early exit if not AJAX
    if (!wp_doing_ajax()) {
        wp_die('Direct access not allowed');
    }
    
    // More aggressive output cleaning
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Start fresh output buffer
    ob_start();
    
    // Set proper headers
    header('Content-Type: application/json');
    
    // Prevent any caching
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    
    try {
        // Properly initialize WooCommerce for AJAX requests
        if (!did_action('woocommerce_init')) {
            WC()->frontend_includes();
            WC()->session = WC()->session ?? new WC_Session_Handler();
            WC()->session->init();
            WC()->customer = WC()->customer ?? new WC_Customer();
            WC()->cart = WC()->cart ?? new WC_Cart();
            
            // Trigger WooCommerce init without output
            do_action('woocommerce_init');
        }
        
        // Ensure current user context is properly set
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            wp_set_current_user($current_user->ID);
            
            // Reinitialize customer with current user data
            WC()->customer = new WC_Customer($current_user->ID);
            
            // Ensure WordPress user globals are properly set for role checks
            global $current_user;
            $current_user = wp_get_current_user();
            
            error_log('AJAX Color Variation - Logged in user ID: ' . $current_user->ID);
            error_log('AJAX Color Variation - User roles: ' . implode(', ', $current_user->roles));
            error_log('AJAX Color Variation - Customer ID: ' . WC()->customer->get_id());
        } else {
            error_log('AJAX Color Variation - Guest user');
        }
        
        // Ensure WC is loaded
        if (!function_exists('WC')) {
            throw new Exception('WooCommerce not loaded');
        }
        
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['_wpnonce'], 'minimis_ajax_nonce')) {
            throw new Exception('Security check failed. Please refresh the page and try again.');
        }
        
        // Log for debugging
        error_log('AJAX Color Variation called with POST data: ' . print_r($_POST, true));
        
        if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
            throw new Exception('Invalid product ID');
        }

        if (!isset($_POST['color'])) {
            throw new Exception('Color not specified');
        }

        $product_id = intval($_POST['product_id']);
        $selected_color = sanitize_text_field($_POST['color']);
        
        error_log('Looking for product ID: ' . $product_id . ' with color: ' . $selected_color);
    
        $product = wc_get_product($product_id);
        if (!$product || !$product->is_type('variable')) {
            throw new Exception('Product not found or not variable');
        }

        $variable_product = new WC_Product_Variable($product_id);
        
        $available_variations = $variable_product->get_available_variations();
        
        // Ensure variations are calculated with current user pricing
        foreach ($available_variations as &$variation_data_ref) {
            $temp_variation = wc_get_product($variation_data_ref['variation_id']);
            if ($temp_variation) {
                // This will recalculate prices with current user context
                $variation_data_ref['display_price'] = $temp_variation->get_price();
                $variation_data_ref['display_regular_price'] = $temp_variation->get_regular_price();
                $variation_data_ref['display_sale_price'] = $temp_variation->get_sale_price();
            }
        }
        
        $variation_data = null;
        
        // Find the variation that matches the selected color
        foreach ($available_variations as $variation) {
            if ($variation['attributes']['attribute_pa_bottle-color'] == $selected_color) {
                $variation_obj = new WC_Product_Variation($variation['variation_id']);
                
                // Ensure the variation object has the proper user context for pricing
                if (is_user_logged_in()) {
                    // Force the variation to use current user's context for pricing
                    $variation_obj->read_meta_data(true);
                    
                    // Apply any user/role-based pricing filters
                    if (has_filter('woocommerce_product_get_price') || 
                        has_filter('woocommerce_variation_get_price') ||
                        has_filter('woocommerce_product_variation_get_price')) {
                        
                        // Trigger pricing recalculation with current user context
                        $variation_obj = wc_get_product($variation['variation_id']);
                    }
                }
                
                // Force pricing refresh by clearing any cached prices
                if (method_exists($variation_obj, 'clear_cache')) {
                    $variation_obj->clear_cache();
                }
                
                // Apply all WooCommerce pricing filters that might affect the price
                $price_filters_applied = false;
                
                // Check for common role-based pricing filter hooks
                $common_price_filters = [
                    'woocommerce_product_get_price',
                    'woocommerce_variation_get_price', 
                    'woocommerce_product_variation_get_price',
                    'woocommerce_get_price_html',
                    'woocommerce_variation_get_price_html'
                ];
                
                foreach ($common_price_filters as $filter_name) {
                    if (has_filter($filter_name)) {
                        $price_filters_applied = true;
                        error_log('Found active price filter: ' . $filter_name);
                    }
                }
                
                if ($price_filters_applied) {
                    // Get a completely fresh instance to ensure all filters are applied
                    $fresh_variation = wc_get_product($variation['variation_id']);
                    if ($fresh_variation) {
                        $variation_obj = $fresh_variation;
                        error_log('Using fresh variation instance due to active price filters');
                    }
                }
                
                // Get price exactly the same way as single_product_summary.php
                $price_html = $variation_obj->get_price_html();
                
                // Get other price formats for debugging and comparison
                $price_raw = $variation_obj->get_price();
                $display_price = $variation['display_price'];
                $regular_price = $variation_obj->get_regular_price();
                $sale_price = $variation_obj->get_sale_price();
                
                // Double-check with one more fresh instance if prices seem inconsistent
                $double_check_variation = wc_get_product($variation['variation_id']);
                if ($double_check_variation) {
                    $double_check_price_html = $double_check_variation->get_price_html();
                    $double_check_price_raw = $double_check_variation->get_price();
                    
                    // Use the most recent pricing if it differs
                    if ($double_check_price_html !== $price_html || $double_check_price_raw !== $price_raw) {
                        $price_html = $double_check_price_html;
                        $price_raw = $double_check_price_raw;
                        $regular_price = $double_check_variation->get_regular_price();
                        $sale_price = $double_check_variation->get_sale_price();
                        error_log('Updated pricing after double-check - Role-based pricing likely active');
                    }
                }
                
                error_log('Found variation ID: ' . $variation['variation_id']);
                error_log('Price HTML (matching single_product_summary.php): ' . $price_html);
                error_log('Raw price: ' . $price_raw);
                error_log('Display price: ' . $display_price);
                error_log('Regular price: ' . $regular_price);
                error_log('Sale price: ' . $sale_price);
                error_log('User ID for pricing: ' . get_current_user_id());
                
                $variation_data = array(
                    'price_html' => $price_html,  // This should match single_product_summary.php exactly
                    'price_raw' => $price_raw,
                    'display_price' => $display_price,
                    'price_formatted' => wc_price($price_raw),
                    'regular_price' => $regular_price,
                    'sale_price' => $sale_price,
                    'sku' => $variation_obj->get_sku(),
                    'description' => $variation_obj->get_description(),
                    'image_url' => wp_get_attachment_image_url($variation_obj->get_image_id(), 'full'),
                    'srcset' => wp_get_attachment_image_srcset($variation_obj->get_image_id(), 'full'),
                    'image_alt' => get_post_meta($variation_obj->get_image_id(), '_wp_attachment_image_alt', true),
                    'dimensions' => array(
                        1 => array(
                            'label' => 'length',
                            'value' => $variation_obj->get_length() . " cm"
                        ),
                        2 => array(
                            'label' => 'width',
                            'value' => $variation_obj->get_width() . " cm"
                        ),
                        3 => array(
                            'label' => 'height',
                            'value' => $variation_obj->get_height() . " cm"
                        ),
                        4 => array(
                            'label' => 'weight',
                            'value' => $variation_obj->get_weight() . " kg"
                        ),
                    )
                );
                break;
            }
        }
        
        if ($variation_data) {
            error_log('Sending variation data: ' . print_r($variation_data, true));
            
            // Clean any output that might have been generated
            ob_clean();
            
            wp_send_json_success($variation_data);
        } else {
            error_log('No variation found for color: ' . $selected_color);
            
            // Clean any output that might have been generated
            ob_clean();
            
            wp_send_json_error(array('message' => 'Variation not found for selected color'));
        }
        
    } catch (Exception $e) {
        error_log('AJAX Color Variation Error: ' . $e->getMessage());
        
        // Clean any output that might have been generated
        ob_clean();
        
        wp_send_json_error(array('message' => $e->getMessage()));
    }
    
    wp_die();
}





// AJAX handler for load more archive products
add_action('wp_ajax_mnm_loadmore_archive', 'mnm_loadmore_archive');
add_action('wp_ajax_nopriv_mnm_loadmore_archive', 'mnm_loadmore_archive');

function mnm_loadmore_archive() {
  

    if (!wp_doing_ajax()) {
        wp_die('Direct access not allowed');
    }

   // print_r($_POST);
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
    $paged = isset($_POST['current_page']) ? intval($_POST['current_page']) : 1;
    $color = isset($_POST['color']) ? sanitize_text_field($_POST['color']) : '';

    $attributes = get_field('colors', 'option');
    
    // echo"<pre>";
    // print_r($attributes);
    


    $args = array(
        'post_type' => 'product',
        'posts_per_page' => 12,
        'paged' => $paged,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
    );

    if ($category && $category != 'all') {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $category,
            ),
        );
    }

   // print_r($args);

    $posts = Timber::get_posts($args);

    if (isset($color) && $color != '') {
       // echo "<hr> Color: " . $color;
        
        foreach ($attributes as $c){
                if ($c['title'] == $color){
                    $bottles = $c['bottle_colors'];
                }
        }
        $bottles_terms = array();
        foreach ($bottles as $bottle){
            $bottles_terms[] = $bottle->term_id;
        }
        // echo "<hr> Bottles terms: ";
        // print_r($bottles_terms);

        $products = archive_page_products_with_color($posts, $bottles_terms);

    }
    else{
        $products = archive_page_products($posts);
    }

    
    $out = Timber::compile('views/partial/ajax_products.twig', array(
        'products' => $products,
    ));
    echo $out;
    
    wp_die(); // This is re
}