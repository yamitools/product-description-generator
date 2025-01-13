<?php
/*
Plugin Name: Simple Product Description Generator
Description: A simple plugin to generate product descriptions for WooCommerce using OpenAI.
Version: 1.0
Author: YamiTools
Text Domain: spdg
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Load text domain for localization
function spdg_load_textdomain() {
    load_plugin_textdomain('spdg', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'spdg_load_textdomain');

// Enqueue scripts and styles
function spdg_enqueue_scripts($hook) {
    if ($hook === 'post.php' || $hook === 'post-new.php') {
        wp_enqueue_script('spdg-script', plugin_dir_url(__FILE__) . 'spdg-script.js', array('jquery'), '1.0', true);
        wp_localize_script('spdg-script', 'spdg_ajax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('spdg_generate_description_nonce')
        ));
    }
}
add_action('admin_enqueue_scripts', 'spdg_enqueue_scripts');

// Add a meta box in the product edit page
function spdg_add_meta_box() {
    add_meta_box('spdg_meta_box', __('Product Description Generator', 'spdg'), 'spdg_meta_box_callback', 'product', 'side', 'high');
}
add_action('add_meta_boxes', 'spdg_add_meta_box');

function spdg_meta_box_callback($post) {
    echo '<label for="spdg_product_name">' . __('Product Name:', 'spdg') . '</label>';
    echo '<input type="text" id="spdg_product_name" value="' . esc_attr(get_the_title($post->ID)) . '" style="width:100%;"><br><br>';
    
    echo '<label for="spdg_product_features">' . __('Product Features:', 'spdg') . '</label>';
    echo '<textarea id="spdg_product_features" rows="4" style="width:100%;"></textarea><br><br>';
    
    echo '<button id="spdg_generate_description" class="button">' . __('Generate Description', 'spdg') . '</button>';
    echo '<h4>' . __('Generated Description:', 'spdg') . '</h4>';
    echo '<textarea id="spdg_generated_description" rows="6" style="width:100%;"></textarea>';
}

// Handle AJAX request for generating description
function spdg_generate_product_description() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'spdg_generate_description_nonce')) {
        wp_send_json_error(__('Invalid nonce.', 'spdg'));
    }

    // Sanitize inputs
    $product_name = sanitize_text_field($_POST['product_name']);
    $product_features = sanitize_text_field($_POST['product_features']);

    if (empty($product_name) || empty($product_features)) {
        wp_send_json_error(__('Product name and features are required.', 'spdg'));
    }

    // Get API key from settings
    $api_key = get_option('spdg_openai_api_key');
    if (empty($api_key)) {
        wp_send_json_error(__('OpenAI API key is not configured.', 'spdg'));
    }

    // Prepare the prompt for the OpenAI API
    $prompt = sprintf(__('Write a product description for a product called "%s" with the following features: %s', 'spdg'), $product_name, $product_features);

    // Make the API call to OpenAI
    $endpoint = 'https://api.openai.com/v1/completions';
    $headers = array(
        'Authorization: Bearer ' . $api_key,
        'Content-Type: application/json'
    );
    
    $data = array(
        'model' => get_option('spdg_openai_model', 'text-davinci-003'),  // Default to text-davinci-003
        'prompt' => $prompt,
        'max_tokens' => 200
    );

    $response = wp_remote_post($endpoint, array(
        'headers' => $headers,
        'body' => json_encode($data),
        'timeout' => 60
    ));

    if (is_wp_error($response)) {
        wp_send_json_error(__('Error in API call.', 'spdg'));
    }

    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);

    if (isset($result['choices'][0]['text'])) {
        $generated_description = sanitize_text_field($result['choices'][0]['text']);
        wp_send_json_success($generated_description);
    } else {
        wp_send_json_error(__('Failed to generate description.', 'spdg'));
    }
}
add_action('wp_ajax_generate_product_description', 'spdg_generate_product_description');

// Add settings page
function spdg_add_settings_page() {
    add_options_page(
        __('Product Description Generator Settings', 'spdg'),
        __('Description Generator', 'spdg'),
        'manage_options',
        'spdg-settings',
        'spdg_render_settings_page'
    );
}
add_action('admin_menu', 'spdg_add_settings_page');

function spdg_render_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['spdg_settings_nonce']) && wp_verify_nonce($_POST['spdg_settings_nonce'], 'spdg_save_settings')) {
        update_option('spdg_openai_api_key', sanitize_text_field($_POST['spdg_openai_api_key']));
        update_option('spdg_openai_model', sanitize_text_field($_POST['spdg_openai_model']));
        echo '<div class="notice notice-success"><p>' . __('Settings saved.', 'spdg') . '</p></div>';
    }

    $api_key = get_option('spdg_openai_api_key', '');
    $model = get_option('spdg_openai_model', 'text-davinci-003');

    ?>
    <div class="wrap">
        <h1><?php _e('Product Description Generator Settings', 'spdg'); ?></h1>
        <form method="post" action="">
            <?php wp_nonce_field('spdg_save_settings', 'spdg_settings_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="spdg_openai_api_key"><?php _e('OpenAI API Key', 'spdg'); ?></label></th>
                    <td>
                        <input type="text" name="spdg_openai_api_key" id="spdg_openai_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text">
                        <p class="description"><?php _e('Enter your OpenAI API key.', 'spdg'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="spdg_openai_model"><?php _e('OpenAI Model', 'spdg'); ?></label></th>
                    <td>
                        <input type="text" name="spdg_openai_model" id="spdg_openai_model" value="<?php echo esc_attr($model); ?>" class="regular-text">
                        <p class="description"><?php _e('Enter the OpenAI model to use (e.g., text-davinci-003).', 'spdg'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Save Settings', 'spdg')); ?>
        </form>
    </div>
    <?php
}
