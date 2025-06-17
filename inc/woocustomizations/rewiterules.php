<?php


add_filter( 'rewrite_rules_array', function( $rules ) {
    $new_rules = array(
        'product/([^/]+)/([^/]+)/?$' => 'index.php?post_type=product&name=$matches[1]&attribute_pa_bottle_color=$matches[2]',
    );
    return $new_rules + $rules;
}, 10, 1 );

add_filter( 'query_vars', 'add_query_vars_filter' );
function add_query_vars_filter( $vars ) {
    $vars[] = "attribute_pa_bottle_color"; 
    return $vars;
}

flush_rewrite_rules();

add_filter('woocommerce_product_variation_get_default_attributes', 'pre_select_variation_by_attribute', 10, 2);
function pre_select_variation_by_attribute($default_attributes, $product) {
    if (isset($_GET['product_attribute'])) {
        $attribute_value = sanitize_text_field($_GET['product_attribute']); 

        // Get all product attributes
        $attributes = $product->get_attributes();

        // Check if product has attributes
        if (empty($attributes)) {
            error_log('Product has no attributes.'); 
            return $default_attributes;
        }

        foreach ($attributes as $attribute_name => $attribute) {
            // Check if attribute has slugs
            if (!$attribute->get_slugs()) {
                error_log('Attribute ' . $attribute_name . ' has no slugs.');
                continue; 
            }

            // Find the matching attribute (case-insensitive search)
            $attribute_slug = wc_attribute_taxonomy_name($attribute_name); // Get the correct attribute slug
            if (in_array(strtolower($attribute_value), array_map('strtolower', $attribute->get_slugs()))) {
                $default_attributes[$attribute_slug] = $attribute_value;
                error_log('Attribute ' . $attribute_slug . ' matched with value ' . $attribute_value);
                break;
            }
        }
    }
    return $default_attributes;
}