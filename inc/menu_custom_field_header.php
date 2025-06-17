<?php
// Add the custom field to the menu item edit screen
add_action( 'wp_nav_menu_item_custom_fields', 'add_custom_menu_item_checkbox', 10, 4 );
function add_custom_menu_item_checkbox( $item_id, $item, $depth, $args ) {
    $is_header = get_post_meta( $item_id, '_menu_item_is_header', true );
    ?>
    <p class="field-is-header description description-wide">
        <label for="edit-menu-item-is-header-<?php echo $item_id; ?>">
            <input type="checkbox" id="edit-menu-item-is-header-<?php echo $item_id; ?>" class="widefat code edit-menu-item-is-header" name="menu-item-is-header[<?php echo $item_id; ?>]" value="1" <?php checked( $is_header, 1 ); ?> />
            <?php _e( 'Is menu header', 'your-text-domain' ); ?>
        </label>
    </p>
    <?php
}

// Save the custom field value when the menu item is updated
add_action( 'wp_update_nav_menu_item', 'update_custom_menu_item_checkbox', 10, 3 );
function update_custom_menu_item_checkbox( $menu_id, $menu_item_db_id, $args ) {
    if ( isset( $_REQUEST['menu-item-is-header'][$menu_item_db_id] ) ) {
        update_post_meta( $menu_item_db_id, '_menu_item_is_header', 1 );
    } else {
        delete_post_meta( $menu_item_db_id, '_menu_item_is_header' );
    }
}