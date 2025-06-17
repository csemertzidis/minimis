<?php


add_filter( 'woocommerce_available_payment_gateways', 'bbloomer_paypal_enable_manager' );
  
function bbloomer_paypal_enable_manager( $available_gateways ) {
  

    //get user role
    $user = wp_get_current_user();
    $user_roles = $user->roles;
    $is_wholesaler = in_array('Wholesaler', $user_roles);

    //if user is wholesaler, remove paypal gateway
    if ( $is_wholesaler ) {
        unset( $available_gateways['eurobank_gateway'] );
        unset( $available_gateways['cod'] );
        unset( $available_gateways['paypal'] );
    }
  

    //disable cash on deli


   return $available_gateways;
}



//modify shipping methods accorfing to user role
add_filter( 'woocommerce_package_rates', 'bbloomer_modify_shipping_methods', 10, 2 );
function bbloomer_modify_shipping_methods( $rates, $package ) {

    //get user role
    $user = wp_get_current_user();
    $user_roles = $user->roles;
    $is_wholesaler = in_array('Wholesaler', $user_roles);

    //if user is wholesaler, remove all shipping methods
    if ( $is_wholesaler ) {
        foreach ( $rates as $rate_id => $rate ) {
            // Check if the rate is 'free_shipping'
            if ( 'flexible_shipping_single' === $rate->method_id ) {
                // If it is, remove it
                unset( $rates[ $rate_id ] );
            }
        }
    }

    //if is not wholesaler
    if ( ! $is_wholesaler ) {
        // Loop through each shipping rate
        foreach ( $rates as $rate_id => $rate ) {
            // Check if the rate is 'free_shipping'
            if ( 'flat_rate' === $rate->method_id ) {
                // If it is, remove it
                unset( $rates[ $rate_id ] );
            }
            //free_shipping
            if ( 'free_shipping' === $rate->method_id ) {
                // If it is, remove it
                unset( $rates[ $rate_id ] );
            }
        }
    }

    return $rates;
}




//list all shipping methods
//get all shipping methods from wc

$shipping_methods = WC()->shipping->get_shipping_methods();

foreach ($shipping_methods as $method) {
    // Check if the method is enabled
    if ($method->enabled === 'yes') {
        // Output the method ID and title
       //echo 'Shipping Method ID: ' . $method->id . '<br>';
       //echo 'Shipping Method Title: ' . $method->get_method_title() . '<br>';
    }
}



//COD extra fee 3
// Add a fee to COD payment method
add_action('woocommerce_cart_calculate_fees', 'add_cod_fee', 10, 1);
function add_cod_fee($cart) {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }
    
    // Check if COD is selected
    $chosen_payment_method = WC()->session->get('chosen_payment_method');
    
    if ($chosen_payment_method === 'cod') {
        $fee = 3; // 3 Euro fee
        $cart->add_fee(__('Cash on Delivery Fee', 'woocommerce'), $fee, true);
    }
}

// Add JavaScript to update cart when payment method changes
add_action('wp_footer', 'cod_fee_js');
function cod_fee_js() {
    if (is_checkout()) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('body').on('change', 'input[name="payment_method"]', function() {
                    $('body').trigger('update_checkout');
                });
                
                // Also update on document ready for initial load
                $(document.body).trigger('update_checkout');
            });
        </script>
        <?php
    }
}





