<?php


remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
remove_action('woocommerce_after_shop_loop', 'woocommerce_result_count', 20);
//remove action order by
remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);




add_action( 'woocommerce_before_shop_loop', 'mnm_before_shop_loop' );

function mnm_before_shop_loop() {
    ?>
    <div class="container">
        <div class="row d-flex align-items-end g-2 ">
            <div class="col pt-2">
                <?php 
                    //woo breadcrumbs
                    woocommerce_breadcrumb();
                ?>
            </div>
            <div class="col text-center d-none d-lg-block ">
                <?php 
                    //woocommerce result count
                    woocommerce_result_count();
                ?>
            </div>
            <div class="col text-end">
                <?php 
                    //woocommerce catalog ordering
                    woocommerce_catalog_ordering();
                ?></div>
        </div>
        
    </div>
    
    <?php
    
}