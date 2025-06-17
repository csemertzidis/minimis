<?php

function dropup_share(){

    $url_to_share = urlencode(get_permalink());

    //timber render
    Timber::render('views/woo/dropup-share.twig', array(
        'url_to_share' => $url_to_share
    ));
}

//hook it to the product summary
add_action('woocommerce_single_product_summary', 'dropup_share', 30);



/**
 * Create a custom action hook for single product pages
 * This can be used to add custom content in templates
 */
function minimis_init_single_product_hook() {
    do_action('minimis-single-product');
}


// add woocommerce_single_variation to the custom action hook
//add_action('minimis-single-product', 'woocommerce_variable_add_to_cart', 20);


// add to the custom action a simple echo hell
add_action('minimis-single-product', function() {
    //echo '<div class="minimis-single-product-hook">This is a custom hook for single product pages.</div>';
});

function minimis_product() {



    //get the user
    $user = get_my_user();


    // Check if we are on a single product page
    if (is_product()) {
        
        $product = wc_get_product(get_the_ID());

        //type of product
        $product_type = $product->get_type();
       // echo '<div class="minimis-product-type">Product Type: ' . esc_html($product_type) . '</div>';

        //check if the product is in whishlist
        //echo is_in_wishlist(get_the_ID()) ? '<div class="minimis-product-wishlist">This product is in your wishlist.</div>' : '<div class="minimis-product-wishlist">This product is not in your wishlist.</div>';
        
        



        $product_out = array();

        $product_out['id'] = $product->get_id();
        $product_out['name'] = $product->get_name();
        $product_out['is_in_whishlist'] = is_in_wishlist($product->get_id());
        $product_out['is_variable'] = $product->is_type('variable');


        //if product is variable and no variation is selected, get the first variation
        if ($product->is_type('variable') && !isset($_GET['attribute_pa_bottle-color'])) {
            $variable_product = new WC_Product_Variable($product->get_id());
            $available_variations = $variable_product->get_available_variations();
            if (!empty($available_variations)) {
                $first_variation = $available_variations[0];
                $variation_obj = new WC_Product_Variation($first_variation['variation_id']);
                $_GET['attribute_pa_bottle-color'] = $first_variation['attributes']['attribute_pa_bottle-color'];
            }
        }

        $fields = get_fields($product->get_id());

        $gallery = array();
        if (isset($fields['gallery']) && is_array($fields['gallery'])) {
            foreach ($fields['gallery'] as $image) {
                $im_out = array();
                $im_out['id'] = $image['ID'];
                $im_out['url'] = wp_get_attachment_image_url($image['ID'], 'full');
                $im_out['srcset'] = wp_get_attachment_image_srcset($image['ID'], 'full');
                $im_out['alt'] = get_post_meta($image['ID'], '_wp_attachment_image_alt', true);
                $gallery[] = $im_out;
            }
        }
        $product_out['gallery'] = $gallery;


        //product terms
        $product_out['terms'] = array();
        $terms = get_the_terms($product->get_id(), 'product_cat');
        if ($terms && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                $product_out['terms'][] = array(
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                );
            }
        }

        //if product is variable, get the variation that is selected
        if ($product->is_type('variable')) {

           // echo $_GET['attribute_pa_bottle_color'];

            $variable_product = new WC_Product_Variable($product->get_id());
            $available_variations = $variable_product->get_available_variations();

            $colors = array();
            $attributes = $variable_product->get_variation_attributes();
            if (isset($attributes['pa_bottle-color'])) {
                $atts = $attributes['pa_bottle-color'];

                //foreach attributes get the name and not the slug
                foreach ($atts as $key => $att) {
                    $term = get_term_by('slug', $att, 'pa_bottle-color');
                    if ($term) {
                        $atts[$key] = $term->slug; // replace slug with name
                    }
                }

                foreach ($atts as $att){
                   // print_r($_GET['']);
                   // echo $att." ".$_GET['attribute_pa_bottle-color']."<br>";
                    

                    if (isset($_GET['attribute_pa_bottle-color']) && $_GET['attribute_pa_bottle-color'] == $att) {
                        $colors[] = array(
                            'name' => get_term_by('slug', $att, 'pa_bottle-color')->name,
                            'value' => $att,
                            'selected' => true,
                        );
                    } else {
                        $colors[] = array(
                            'name' => get_term_by('slug', $att, 'pa_bottle-color')->name,
                            'value' => $att,
                            'selected' => false,
                        );
                    }
                }

            }
            //print_r($colors);
            $product_out['colors'] = $colors;

           //ß$product_out['htmlprice'] = $variable_product->get_variation_price('max', true);

            foreach ($available_variations as $variation) {

                if (isset($_GET['attribute_pa_bottle-color']) && $_GET['attribute_pa_bottle-color'] == $variation['attributes']['attribute_pa_bottle-color']) {
                    $variation_obj = new WC_Product_Variation($variation['variation_id']);
                    //echo $variation_obj->get_price_html()."---<br>";
                    $product_out['price'] = $variation_obj->get_price_html();   
                    
                    //sku
                    $product_out['sku'] = $variation_obj->get_sku();

                    //description
                    $product_out['description'] = $variation_obj->get_description();

                    $product_out['image_url'] = wp_get_attachment_image_url($variation_obj->get_image_id(), 'full');
                    $product_out['srcset'] = wp_get_attachment_image_srcset($variation_obj->get_image_id(), 'full');
                    $product_out['image_alt'] = get_post_meta($variation_obj->get_image_id(), '_wp_attachment_image_alt', true);



                   

                    //dimensions
                    $product_out['dimensions'] = array(
                       1 => array(
                            'label' => 'length',
                            'value' => $variation_obj->get_length()." cm"
                        ),
                        2 => array(
                            'label' => 'width',
                            'value' => $variation_obj->get_width()." cm"
                        ),
                        3 => array(
                            'label' => 'height',
                            'value' => $variation_obj->get_height()." cm"
                        ),
                        4 => array(
                            'label' => 'weight',
                            'value' => $variation_obj->get_weight(). " kg"
                        ),
                    );
                    
                    //print_r($variation_obj);
                }   

                
            }
        }
        else {

        }

        
       

            



        

        Timber::render('views/woo/single_product_entry.twig', array(
            'product' => $product,
            'user' => $user,
            'p_out' => $product_out,
            'product_type' => $product_type,
            'ajax_nonce' => wp_create_nonce('minimis_ajax_nonce'),
        ));
    } 

}
add_action('minimis-single-product', 'minimis_product', 5);


function enqueue_masonry() {
	if (is_home()) {
		wp_enqueue_script('masonry', 'https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js', array(), null, true);
		
	}
}
add_action('wp_enqueue_scripts', 'enqueue_masonry');

