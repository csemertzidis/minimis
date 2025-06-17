<?php 


function get_my_user() {
    //get the user
    $user = wp_get_current_user();

    if ($user && !is_wp_error($user)) {
        // User is logged in
        $user_id = $user->ID;
        $user_roles = $user->roles;
        
        //check if the user is wholesaler
        if (in_array('Wholesaler', $user_roles)) {
            // User is a wholesaler
            $user_wholesaler = true;
        }
    } else {
        // User is not logged in
        $user_id = 0; // or handle as needed
    }

    $user=array();

    //add them in $user array
    $user['id'] = $user_id;
    $user['is_wholesaler'] = isset($user_wholesaler) ? $user_wholesaler : false;
    $user['roles'] = $user_roles ?? array();
    $user['is_logged_in'] = is_user_logged_in();
    $user['display_name'] = $user->display_name ?? '';  
    $user['email'] = $user->user_email ?? '';
    $user['first_name'] = $user->user_firstname ?? '';
    $user['last_name'] = $user->user_lastname ?? '';
    $user['is_admin'] = current_user_can('administrator') ? true : false;


    return $user;
}