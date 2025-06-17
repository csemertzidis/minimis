<?php
/**
 * B2B Registration Block
 */

// Render callback - function name must match folder name for autoloader
function b2bregister($block, $content = '', $is_preview = false){

    $context = Timber::context();
    $context['block_id'] = uniqid('b2b-form-');
    $context['ajax_url'] = admin_url('admin-ajax.php');
    $context['nonce'] = wp_create_nonce('b2b_register_nonce');
    $context['recaptcha_site_key'] = get_option('b2b_recaptcha_site_key', '');
    
    // Debug: Add to context for troubleshooting
    $context['debug_info'] = [
        'has_site_key' => !empty($context['recaptcha_site_key']),
        'site_key_length' => strlen($context['recaptcha_site_key']),
        'has_secret_key' => !empty(get_option('b2b_recaptcha_secret_key', ''))
    ];


    $context['formplaceholders'] = [
        'business_name' => __('Business Name', 'minimis'),
        'contact_person' => __('Contact Person', 'minimis'),
        'business_type' => __('Business Type', 'minimis'),
        'vat' => __('VAT Number', 'minimis'),
        'address' => __('Address', 'minimis'),
        'country' => __('Country', 'minimis'),
        'email' => __('Email Address', 'minimis'),
        'phone' => __('Phone Number', 'minimis'),
        'website' => __('Website (optional)', 'minimis'),
        'social_media' => __('Social Media (optional)', 'minimis'),
        'notes' => __('Additional Notes (optional)', 'minimis'),
        'submit' => __('Submit Registration', 'minimis'),
    ];


   
    
    return Timber::render('/blocks/b2bregister/b2bregister.twig', $context);
}

// Enqueue scripts for the block
function enqueue_b2b_registration_scripts() {
    if (has_block('minimis-blocks/b2bregister')) {
        // Enqueue reCAPTCHA script
        wp_enqueue_script(
            'google-recaptcha',
            'https://www.google.com/recaptcha/api.js',
            [],
            null,
            true
        );
        
        wp_enqueue_script(
            'b2b-registration-script',
            get_template_directory_uri() . '/inc/blocks/b2bregister/b2bregister.js',
            ['jquery', 'google-recaptcha'],
            '1.0.0',
            true
        );
        wp_localize_script('b2b-registration-script', 'b2bAjax', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('b2b_register_nonce')
        ]);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_b2b_registration_scripts');

// AJAX handler for logged-in users
add_action('wp_ajax_b2b_register_user', 'handle_b2b_registration');
// AJAX handler for non-logged-in users
add_action('wp_ajax_nopriv_b2b_register_user', 'handle_b2b_registration');

// AJAX handler for email validation
add_action('wp_ajax_b2b_validate_email', 'handle_b2b_email_validation');
add_action('wp_ajax_nopriv_b2b_validate_email', 'handle_b2b_email_validation');

function handle_b2b_registration() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'b2b_register_nonce')) {
        wp_die('Security check failed');
    }
    
    // Verify reCAPTCHA (only if configured)
    $recaptcha_response = sanitize_text_field($_POST['g-recaptcha-response'] ?? '');
    $recaptcha_secret = get_option('b2b_recaptcha_secret_key', '');
    $recaptcha_site_key = get_option('b2b_recaptcha_site_key', '');
    
    // Only require reCAPTCHA if both keys are configured
    if (!empty($recaptcha_site_key) && !empty($recaptcha_secret)) {
        if (empty($recaptcha_response)) {
            wp_send_json_error(['message' => 'Please complete the reCAPTCHA verification']);
        }
        
        // Verify reCAPTCHA with Google
        $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
        $verify_data = [
            'secret' => $recaptcha_secret,
            'response' => $recaptcha_response,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];
        
        $verify_response = wp_remote_post($verify_url, [
            'body' => $verify_data,
            'timeout' => 10
        ]);
        
        if (is_wp_error($verify_response)) {
            wp_send_json_error(['message' => 'reCAPTCHA verification failed. Please try again.']);
        }
        
        $verify_body = wp_remote_retrieve_body($verify_response);
        $verify_result = json_decode($verify_body, true);
        
        if (!$verify_result['success']) {
            wp_send_json_error(['message' => 'reCAPTCHA verification failed. Please try again.']);
        }
    }
    
    // Sanitize and validate input data
    $business_name = sanitize_text_field($_POST['business_name']);
    $contact_person = sanitize_text_field($_POST['contact_person']);
    $business_type = sanitize_text_field($_POST['business_type']);
    $vat = sanitize_text_field($_POST['vat']);
    $address = sanitize_textarea_field($_POST['address']);
    $country = sanitize_text_field($_POST['country']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    $website = sanitize_url($_POST['website']);
    $social_media = sanitize_text_field($_POST['social_media']);
    $notes = sanitize_textarea_field($_POST['notes']);
    
    // Validation
    $errors = [];
    
    if (empty($business_name)) {
        $errors[] = 'Business name is required';
    }
    
    if (empty($business_type)) {
        $errors[] = 'Business type is required';
    }
    
    if (empty($vat)) {
        $errors[] = 'VAT number is required';
    }
    
    if (empty($address)) {
        $errors[] = 'Address is required';
    }
    
    if (empty($country)) {
        $errors[] = 'Country is required';
    }
    
    if (empty($email) || !is_email($email)) {
        $errors[] = 'Valid email is required';
    }
    
    if (empty($phone)) {
        $errors[] = 'Phone number is required';
    }
    
    // Check if phone contains only numbers, spaces, +, -, (, )
    if (!empty($phone) && !preg_match('/^[0-9\s\+\-\(\)]+$/', $phone)) {
        $errors[] = 'Phone number contains invalid characters';
    }
    
    // Check if email already exists
    if (email_exists($email)) {
        $errors[] = 'Email address is already registered';
    }
    
    if (!empty($errors)) {
        wp_send_json_error(['message' => implode('<br>', $errors)]);
    }
    
    // Create username from business name (sanitized)
    $username = sanitize_user(str_replace(' ', '_', strtolower($business_name)));
    
    // Make sure username is unique
    $original_username = $username;
    $counter = 1;
    while (username_exists($username)) {
        $username = $original_username . '_' . $counter;
        $counter++;
    }
    
    // Generate random password
    $password = wp_generate_password(12, false);
    
    // Create user
    $user_id = wp_create_user($username, $password, $email);
    
    if (is_wp_error($user_id)) {
        wp_send_json_error(['message' => 'Failed to create user: ' . $user_id->get_error_message()]);
    }
    
    // Add user role
    $user = new WP_User($user_id);
    $user->set_role('pendingwholesaler');
    
    // Save additional user meta
    update_user_meta($user_id, 'business_name', $business_name);
    update_user_meta($user_id, 'contact_person', $contact_person);
    update_user_meta($user_id, 'business_type', $business_type);
    update_user_meta($user_id, 'vat_number', $vat);
    update_user_meta($user_id, 'business_address', $address);
    update_user_meta($user_id, 'business_country', $country);
    update_user_meta($user_id, 'business_phone', $phone);
    update_user_meta($user_id, 'business_website', $website);
    update_user_meta($user_id, 'social_media', $social_media);
    update_user_meta($user_id, 'registration_notes', $notes);
    
    // Send notification email to admin (optional)
    $admin_email = get_option('admin_email');
    $subject = 'New B2B Registration: ' . $business_name;
    $message = "A new B2B client has registered:\n\n";
    $message .= "Business Name: {$business_name}\n";
    $message .= "Contact Person: {$contact_person}\n";
    $message .= "Email: {$email}\n";
    $message .= "Business Type: {$business_type}\n";
    $message .= "VAT: {$vat}\n";
    $message .= "Country: {$country}\n";
    $message .= "Phone: {$phone}\n";
    if (!empty($website)) {
        $message .= "Website: {$website}\n";
    }
    if (!empty($social_media)) {
        $message .= "Social Media: {$social_media}\n";
    }
    if (!empty($notes)) {
        $message .= "Notes: {$notes}\n";
    }
    
    wp_mail($admin_email, $subject, $message);
    
    // Send welcome email to user
    $user_subject = 'Welcome to ' . get_bloginfo('name') . ' - B2B Account Created';
    $user_message = "Dear {$contact_person},\n\n";
    $user_message .= "Your B2B account has been successfully created! We will get in touch before activating your account!\n\n";
    $user_message .= "Business Name: {$business_name}\n";
    $user_message .= "Username: {$username}\n";
    $user_message .= "Password: {$password}\n\n";
    //$user_message .= "Please keep this information safe and log in to your account to get started.\n\n";
    $user_message .= "Best regards,\n" . get_bloginfo('name');
    
    wp_mail($email, $user_subject, $user_message);
    
    wp_send_json_success(['message' => 'Registration successful! Check your email for login credentials.']);
}

// Email validation AJAX handler
function handle_b2b_email_validation() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'b2b_register_nonce')) {
        wp_send_json_error(['message' => 'Security check failed']);
    }
    
    $email = sanitize_email($_POST['email']);
    $errors = [];
    
    // Check email format first (priority 1)
    if (empty($email) || !is_email($email)) {
        $errors[] = 'Please enter a valid email address';
    } else {
        // Check if email already exists (priority 2)
        if (email_exists($email)) {
            $errors[] = 'This email address is already registered';
        }
    }
    
    if (!empty($errors)) {
        wp_send_json_error([
            'message' => $errors[0], // Return first error (format has priority)
            'valid' => false
        ]);
    }
    
    wp_send_json_success([
        'message' => 'Email is valid and available',
        'valid' => true
    ]);
}

// Make sure 'wholesaler' role exists
function create_wholesaler_role() {
    if (!get_role('wholesaler')) {
        add_role('wholesaler', 'Wholesaler', [
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
        ]);

        //add 'pendingwholesaler' role for pending registrations
        add_role('pendingwholesaler', 'Pending Wholesaler', [
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
        ]);
    }
}
add_action('init', 'create_wholesaler_role');

// Add reCAPTCHA settings to admin
function add_b2b_recaptcha_admin_menu() {
    add_options_page(
        'B2B reCAPTCHA Settings',
        'B2B reCAPTCHA',
        'manage_options',
        'b2b-recaptcha-settings',
        'b2b_recaptcha_settings_page'
    );
}
add_action('admin_menu', 'add_b2b_recaptcha_admin_menu');

function b2b_recaptcha_settings_page() {
    if (isset($_POST['submit'])) {
        update_option('b2b_recaptcha_site_key', sanitize_text_field($_POST['site_key']));
        update_option('b2b_recaptcha_secret_key', sanitize_text_field($_POST['secret_key']));
        echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
    }
    
    $site_key = get_option('b2b_recaptcha_site_key', '');
    $secret_key = get_option('b2b_recaptcha_secret_key', '');
    ?>
    <div class="wrap">
        <h1>B2B reCAPTCHA Settings</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row">reCAPTCHA Site Key</th>
                    <td>
                        <input type="text" name="site_key" value="<?php echo esc_attr($site_key); ?>" class="regular-text" />
                        <p class="description">Your reCAPTCHA site key from Google</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">reCAPTCHA Secret Key</th>
                    <td>
                        <input type="text" name="secret_key" value="<?php echo esc_attr($secret_key); ?>" class="regular-text" />
                        <p class="description">Your reCAPTCHA secret key from Google</p>
                    </td>
                </tr>
            </table>
            <p class="description">
                To get your reCAPTCHA keys, visit <a href="https://www.google.com/recaptcha/admin" target="_blank">Google reCAPTCHA Admin Console</a> and create a new site with reCAPTCHA v2 "I'm not a robot" checkbox.
            </p>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}



//show the collected data in the admin
function b2b_registration_data_page() {
    $users = get_users([
        'role' => 'pendingwholesaler',
        'fields' => ['ID', 'display_name', 'user_email']
    ]);
    
    echo '<div class="wrap"><h1>B2B Registration Data</h1>';
    
    if (empty($users)) {
        echo '<p>No pending B2B registrations found.</p>';
    } else {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>User ID</th><th>Name</th><th>Email</th><th>Profile</th><th>Registration date</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($users as $user) {
            echo '<tr>';
            echo '<td>' . esc_html($user->ID) . '</td>';
            echo '<td>' . esc_html($user->display_name) . '</td>';
            echo '<td>' . esc_html($user->user_email) . '</td>';
            // link to user profile
            echo '<td><a href="' . esc_url(get_edit_user_link($user->ID)) . '">View Profile</a></td>';
            //registation date
            echo '<td>' .date("d-m-Y H:m:s",($user->user_registered))  . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }
    
    echo '</div>';
}
add_action('admin_menu', function() {
    add_menu_page(
        'B2B Registration Data',
        'B2B Registrations',
        'manage_options',
        'b2b-registration-data',
        'b2b_registration_data_page'
    );
});
// Add a link to the B2B registration data page in the admin bar
add_action('admin_bar_menu', function($wp_admin_bar) {
    if (current_user_can('manage_options')) {
        $wp_admin_bar->add_node([
            'id' => 'b2b-registration-data',
            'title' => 'B2B Registrations',
            'href' => admin_url('admin.php?page=b2b-registration-data'),
            'meta' => ['title' => 'View B2B Registration Data']
        ]);
    }
}, 100);   

//in the user admin panel show the additional fields
add_action('show_user_profile', 'b2b_show_additional_fields');
add_action('edit_user_profile', 'b2b_show_additional_fields');
function b2b_show_additional_fields($user) {
    if (in_array('pendingwholesaler', $user->roles)) {
        ?>
        <hr/>
        <h3>B2B Registration Details</h3>
        <table class="form-table" style="background-color:rgb(255, 255, 255); padding: 20px; border-radius: 5px;">
            <tr>
                <th><label for="business_name">Business Name</label></th>
                <td><input type="text" name="business_name" id="business_name" value="<?php echo esc_attr(get_user_meta($user->ID, 'business_name', true)); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="contact_person">Contact Person</label></th>
                <td><input type="text" name="contact_person" id="contact_person" value="<?php echo esc_attr(get_user_meta($user->ID, 'contact_person', true)); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="business_type">Business Type</label></th>
                <td><input type="text" name="business_type" id="business_type" value="<?php echo esc_attr(get_user_meta($user->ID, 'business_type', true)); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="vat_number">VAT Number</label></th>
                <td><input type="text" name="vat_number" id="vat_number" value="<?php echo esc_attr(get_user_meta($user->ID, 'vat_number', true)); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="business_address">Business Address</label></th>
                <td><textarea name="business_address" id="business_address" class="large-text"><?php echo esc_textarea(get_user_meta($user->ID, 'business_address', true)); ?></textarea></td>
            </tr>
            <tr>
                <th><label for="business_country">Business Country</label></th>
                <td><input type="text" name="business_country" id="business_country" value="<?php echo esc_attr(get_user_meta($user->ID, 'business_country', true)); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="business_phone">Business Phone</label></th>
                <td><input type="text" name="business_phone" id="business_phone" value="<?php echo esc_attr(get_user_meta($user->ID, 'business_phone', true)); ?>" class="regular-text
" /></td>
            </tr>
            <tr>
                <th><label for="business_website">Business Website</label></th>
                <td><input type="url" name="business_website" id="business_website" value="<?php echo esc_url(get_user_meta($user->ID, 'business_website', true)); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="social_media">Social Media</label></th>
                <td><input type="text" name="social_media" id="social_media" value="<?php echo esc_attr(get_user_meta($user->ID, 'social_media', true)); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="registration_notes">Registration Notes</label></th>
                <td><textarea name="registration_notes" id="registration_notes" class="large-text"><?php echo esc_textarea(get_user_meta($user->ID, 'registration_notes', true)); ?></textarea></td>
            </tr>
        </table>
        <hr/>
        <?php
    }
}
// Save additional fields when user profile is updated
add_action('personal_options_update', 'b2b_save_additional_fields');
add_action('edit_user_profile_update', 'b2b_save_additional_fields');  
function b2b_save_additional_fields($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return;
    }
    
    update_user_meta($user_id, 'business_name', sanitize_text_field($_POST['business_name']));
    update_user_meta($user_id, 'contact_person', sanitize_text_field($_POST['contact_person']));
    update_user_meta($user_id, 'business_type', sanitize_text_field($_POST['business_type']));
    update_user_meta($user_id, 'vat_number', sanitize_text_field($_POST['vat_number']));
    update_user_meta($user_id, 'business_address', sanitize_textarea_field($_POST['business_address']));
    update_user_meta($user_id, 'business_country', sanitize_text_field($_POST['business_country']));
    update_user_meta($user_id, 'business_phone', sanitize_text_field($_POST['business_phone']));
    update_user_meta($user_id, 'business_website', esc_url_raw($_POST['business_website']));
    update_user_meta($user_id, 'social_media', sanitize_text_field($_POST['social_media']));
    update_user_meta($user_id, 'registration_notes', sanitize_textarea_field($_POST['registration_notes']));
}





add_action('show_user_profile', 'extra_user_profile_fields');
add_action('edit_user_profile', 'extra_user_profile_fields');

function extra_user_profile_fields($user)
{
?>
    <hr>
<h2>ΠΡΟΣΟΧΗ ΠΑΛΙΑ ΦΟΡΜΑ ΣΤΟΙΧΕΙΩΝ ΧΟΝΔΡΙΚΗΣ ΠΕΛΑΤΗ</h2>
    <h3 style="text-decoration:underline;"><?php _e("Στοιχεία τιμολόγησης πελάτη χονδρικής", "blank"); ?></h3>

    <table class="form-table">
        <tr>
            <th><label for="company_name"><?php _e("Επωνυμία"); ?></label></th>
            <td>
                <input type="text" name="company_name" id="company_name" value="<?php echo get_user_meta($user->ID)['company_name'][0] ?>" class="regular-text" /><br />
                <span class="description"><?php _e("Επωνυμία επιχείρησης"); ?></span>
            </td>
        </tr>
        <tr>
            <th><label for="company_title"><?php _e("Διακριτικό Τίτλος"); ?></label></th>
            <td>
                <input type="text" name="company_title" id="company_title" value="<?php echo get_user_meta($user->ID)['company_title'][0] ?>" class="regular-text" /><br />
                <span class="description"><?php _e("Διακριτικό Τίτλος"); ?></span>
            </td>
        </tr>
        <tr>
            <th><label for="company_name"><?php _e("Επάγγελμα"); ?></label></th>
            <td>
                <input type="text" name="epagelma" id="epagelma" value="<?php echo get_user_meta($user->ID)['epagelma'][0] ?>" class="regular-text" /><br />
                <span class="description"><?php _e("Επάγγελμα"); ?></span>
            </td>
        </tr>
        <tr>
            <th><label for="company_name"><?php _e("Διεύθυνση"); ?></label></th>
            <td>
                <input type="text" name="diefthinsi" id="diefthinsi" value="<?php echo get_user_meta($user->ID)['diefthinsi'][0] ?>" class="regular-text" /><br />
                <span class="description"><?php _e("Διεύθυνση"); ?></span>
            </td>
        </tr>
        <tr>
            <th><label for="company_name"><?php _e("Τηλέφωνο"); ?></label></th>
            <td>
                <input type="text" name="tilefono" id="tilefono" value="<?php echo get_user_meta($user->ID)['tilefono'][0] ?>" class="regular-text" /><br />
                <span class="description"><?php _e("Τηλέφωνο"); ?></span>
            </td>
        </tr>
        <tr>
            <th><label for="company_name"><?php _e("Α.Φ.Μ"); ?></label></th>
            <td>
                <input type="text" name="afm" id="afm" value="<?php echo get_user_meta($user->ID)['afm'][0] ?>" class="regular-text" /><br />
                <span class="description"><?php _e("Α.Φ.Μ."); ?></span>
            </td>
        </tr>
        <tr>
            <th><label for="company_name"><?php _e("Δ.O.Y."); ?></label></th>
            <td>
                <input type="text" name="doy" id="doy" value="<?php echo get_user_meta($user->ID)['doy'][0] ?>" class="regular-text" /><br />
                <span class="description"><?php _e("Δ.O.Y."); ?></span>
            </td>
        </tr>
    </table>
    <hr>
    <table class="form-table">
        <tr>
            <th>Πρώτη παραγγελία εκτός συστήματος</th>
            <td><input type="checkbox" name="first_order" id="first_order" value="1" <?php echo get_user_meta($user->ID)['first_order'][0] == 1 ? 'checked' : '' ?> /></td>
        </tr>
    </table>

    <hr>

    <div style="font-weight:bold;">
        <?php echo $user->display_name ?><br />
        <?php echo get_user_meta($user->ID)['company_name'][0] ?><br />
        <?php echo get_user_meta($user->ID)['company_title'][0] ?><br />
        <?php echo get_user_meta($user->ID)['epagelma'][0] ?><br />
        <?php echo get_user_meta($user->ID)['diefthinsi'][0] ?><br />
        <?php echo get_user_meta($user->ID)['tilefono'][0] ?><br />
        <?php echo get_user_meta($user->ID)['afm'][0] ?><br />
        <?php echo get_user_meta($user->ID)['doy'][0] ?><br />
    </div>

    <hr>
<?php }

add_action('personal_options_update', 'save_extra_user_profile_fields');
add_action('edit_user_profile_update', 'save_extra_user_profile_fields');

function save_extra_user_profile_fields($user_id)
{

    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    update_user_meta($user_id, 'company_name', $_POST['company_name']);
    update_user_meta($user_id, 'company_title', $_POST['company_title']);
    update_user_meta($user_id, 'epagelma', $_POST['epagelma']);
    update_user_meta($user_id, 'diefthinsi', $_POST['diefthinsi']);
    update_user_meta($user_id, 'tilefono', $_POST['tilefono']);
    update_user_meta($user_id, 'afm', $_POST['afm']);
    update_user_meta($user_id, 'doy', $_POST['doy']);
    update_user_meta($user_id, 'first_order', $_POST['first_order']);
}
