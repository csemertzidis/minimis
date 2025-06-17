<?php
// Debug script to check reCAPTCHA configuration
// Run this from WordPress admin or add to functions.php temporarily

function debug_recaptcha_config() {
    $site_key = get_option('b2b_recaptcha_site_key', '');
    $secret_key = get_option('b2b_recaptcha_secret_key', '');
    
    echo "<h3>reCAPTCHA Configuration Status:</h3>";
    echo "<p><strong>Site Key:</strong> " . ($site_key ? 'Configured (' . substr($site_key, 0, 10) . '...)' : 'NOT CONFIGURED') . "</p>";
    echo "<p><strong>Secret Key:</strong> " . ($secret_key ? 'Configured (' . substr($secret_key, 0, 10) . '...)' : 'NOT CONFIGURED') . "</p>";
    
    if (empty($site_key) || empty($secret_key)) {
        echo "<p style='color: red;'><strong>Issue:</strong> reCAPTCHA keys are not configured. This is why the reCAPTCHA widget is not showing.</p>";
        echo "<p><strong>Solution:</strong> Go to WordPress Admin > Settings > B2B reCAPTCHA to configure your keys.</p>";
    } else {
        echo "<p style='color: green;'><strong>Status:</strong> reCAPTCHA keys are configured.</p>";
    }
}

// Uncomment the line below to run the debug function
// add_action('wp_footer', 'debug_recaptcha_config');
?>
