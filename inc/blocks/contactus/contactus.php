<?php
/**
 * Contact Us Block
 */

// Render callback - function name must match folder name for autoloader
function contactus($block, $content = '', $is_preview = false){

    $context = Timber::context();
    $context['block_id'] = uniqid('b2b-form-');
    $context['ajax_url'] = admin_url('admin-ajax.php');
    $context['nonce'] = wp_create_nonce('b2b_register_nonce');
    $context['recaptcha_site_key'] = get_option('b2b_recaptcha_site_key', '');


    $context['title'] = __('Contact us', 'minimis');
    $context['emailline'] = __('An email is always a good idea.', 'minimis');
    $context['phoneline'] = __('Reach us through phone.', 'minimis');


    $context['social'] = array(
        array(
            'link' => 'https://www.facebook.com/minimisgr',
            'name' => 'Facebook',
        ),
        array(
            'link' => 'https://www.instagram.com/minimisgr/',
            'name' => 'Instagram',
        ),
        array(
            'link' => 'https://www.tiktok.com/@minimisgr',
            'name' => 'TikTok',
        ),
    );


    $context['form']=array();
    $context['form']['placeholders'] = array(
        'name' => __('need your name here', 'minimis'),
        'email' => __('need your email here', 'minimis'),
        'phone' => __('need your phone here', 'minimis'),
        'subject' => __('need your subject here', 'minimis'),
        'message' => __('need your message here', 'minimis'),
        'submit' => __('Send', 'minimis'),
    );




     Timber::render('/blocks/contactus/contactus.twig', $context);
}

// Enqueue scripts for the contact form block
function enqueue_contact_form_scripts() {
    // Check if reCAPTCHA is configured
    $recaptcha_site_key = get_option('b2b_recaptcha_site_key', '');
    
    if (!empty($recaptcha_site_key)) {
        // Enqueue reCAPTCHA script - load it globally since we can't easily detect the block
        wp_enqueue_script(
            'google-recaptcha-contact',
            'https://www.google.com/recaptcha/api.js',
            [],
            null,
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'enqueue_contact_form_scripts');

// AJAX handler for contact form submission
add_action('wp_ajax_contact_form_submission', 'handle_contact_form_submission');
add_action('wp_ajax_nopriv_contact_form_submission', 'handle_contact_form_submission');

function handle_contact_form_submission() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'b2b_register_nonce')) {
        wp_send_json_error(array('message' => 'Security verification failed.'));
        return;
    }
    
    // Sanitize and validate form data
    $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $subject = isset($_POST['subject']) ? sanitize_text_field($_POST['subject']) : '';
    $message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';
    
    // Validate required fields
    if (empty($name) || empty($phone) || empty($email)) {
        wp_send_json_error(array('message' => 'Please fill in all required fields (Name, Phone, Email).'));
        return;
    }
    
    // Validate email format
    if (!is_email($email)) {
        wp_send_json_error(array('message' => 'Please enter a valid email address.'));
        return;
    }
    
    // Verify reCAPTCHA if configured
    $recaptcha_site_key = get_option('b2b_recaptcha_site_key', '');
    $recaptcha_secret_key = get_option('b2b_recaptcha_secret_key', '');
    
    if (!empty($recaptcha_site_key) && !empty($recaptcha_secret_key)) {
        $recaptcha_response = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
        
        if (empty($recaptcha_response)) {
            wp_send_json_error(array('message' => 'Please complete the reCAPTCHA verification.'));
            return;
        }
        
        // Verify reCAPTCHA with Google
        $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
        $verify_data = array(
            'secret' => $recaptcha_secret_key,
            'response' => $recaptcha_response,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        );
        
        $response = wp_remote_post($verify_url, array(
            'body' => $verify_data,
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => 'reCAPTCHA verification failed. Please try again.'));
            return;
        }
        
        $response_body = wp_remote_retrieve_body($response);
        $result = json_decode($response_body, true);
        
        if (!$result['success']) {
            wp_send_json_error(array('message' => 'reCAPTCHA verification failed. Please try again.'));
            return;
        }
    }
    
    // Prepare email content
    $to = 'christos.semertzidis@gmail.com,info@minimis.shop';
    $email_subject = 'Contact Form Submission: ' . (!empty($subject) ? $subject : 'No Subject');
    
    $email_message = "New contact form submission:\n\n";
    $email_message .= "Name: " . $name . "\n";
    $email_message .= "Email: " . $email . "\n";
    $email_message .= "Phone: " . $phone . "\n";
    $email_message .= "Subject: " . (!empty($subject) ? $subject : 'No Subject') . "\n";
    $email_message .= "Message: " . (!empty($message) ? $message : 'No Message') . "\n\n";
    $email_message .= "Submitted on: " . current_time('mysql') . "\n";
    $email_message .= "IP Address: " . $_SERVER['REMOTE_ADDR'] . "\n";
    
    // Get the site domain for From header
    $site_url = parse_url(home_url(), PHP_URL_HOST);
    $from_email = 'noreply@' . $site_url;
    
    // Email headers - fix the format
    $headers = array();
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    $headers[] = 'From: ' . get_bloginfo('name') . ' <' . $from_email . '>';
    $headers[] = 'Reply-To: ' . $name . ' <' . $email . '>';
    
    // Log email attempt for debugging
    error_log('Contact form attempting to send email to: ' . $to);
    error_log('Email subject: ' . $email_subject);
    error_log('From email: ' . $from_email);
    
    // Send email
    $email_sent = wp_mail($to, $email_subject, $email_message, $headers);
    
    // Log the result
    if ($email_sent) {
        error_log('Contact form email sent successfully');
        wp_send_json_success(array('message' => 'Thank you! Your message has been sent successfully.'));
    } else {
        error_log('Contact form email failed to send');
        // Get the last error for debugging
        global $phpmailer;
        if (isset($phpmailer)) {
            error_log('PHPMailer error: ' . $phpmailer->ErrorInfo);
        }
        wp_send_json_error(array('message' => 'Sorry, there was an error sending your message. Please try again.'));
    }
}

// Add email debugging for contact form
add_action('wp_mail_failed', 'contact_form_mail_failed');
function contact_form_mail_failed($wp_error) {
    error_log('Contact form wp_mail failed: ' . $wp_error->get_error_message());
}

// Add action to ensure PHPMailer errors are logged
add_action('phpmailer_init', 'contact_form_phpmailer_init');
function contact_form_phpmailer_init($phpmailer) {
    // Enable SMTP debugging (only for contact form submissions)
    if (isset($_POST['action']) && $_POST['action'] === 'contact_form_submission') {
        $phpmailer->SMTPDebug = 1; // Enable verbose debug output
        $phpmailer->Debugoutput = function($str, $level) {
            error_log("PHPMailer debug level $level: $str");
        };
    }
}


