<?php
/*
Plugin Name: Simple Product Description Generator
Description: A simple plugin to generate product descriptions for WooCommerce.
Version: 1.0
Author: YamiTools
*/

// Enqueue scripts and styles
function spdg_enqueue_scripts() {
    wp_enqueue_script('spdg-script', plugin_dir_url(__FILE__) . 'spdg-script.js', array('jquery'), '1.0', true);
}
add_action('admin_enqueue_scripts', 'spdg_enqueue_scripts');

// Add a meta box in the product edit page
function spdg_add_meta_box() {
    add_meta_box('spdg_meta_box', 'Product Description Generator', 'spdg_meta_box_callback', 'product', 'side', 'high');
}
add_action('add_meta_boxes', 'spdg_add_meta_box');

function spdg_meta_box_callback($post) {
    echo '<label for="spdg_product_name">Product Name:</label>';
    echo '<input type="text" id="spdg_product_name" value="' . esc_attr(get_the_title($post->ID)) . '" style="width:100%;"><br><br>';
    
    echo '<label for="spdg_product_features">Product Features:</label>';
    echo '<textarea id="spdg_product_features" rows="4" style="width:100%;"></textarea><br><br>';
    
    echo '<button id="spdg_generate_description" class="button">Generate Description</button>';
    echo '<h4>Generated Description:</h4>';
    echo '<textarea id="spdg_generated_description" rows="6" style="width:100%;"></textarea>';
}

add_action('wp_ajax_generate_product_description', 'spdg_generate_product_description');
add_action('wp_ajax_nopriv_generate_product_description', 'spdg_generate_product_description');

function spdg_generate_product_description() {
    $product_name = sanitize_text_field($_POST['product_name']);
    $product_features = sanitize_text_field($_POST['product_features']);

    // Prepare the prompt for the OpenAI API
    $prompt = "Write a product description for a product called '$product_name' with the following features: $product_features";

    // Make the API call to OpenAI
    $api_key = 'sk-k8q9fBP5u8akez5JuZXbPCccJKy4g7UPerL5GckSldT3BlbkFJgSrgBSvbzWdT_HefJl6pmfwUHFeyDsJyTYV1w2pv4A';  // Replace with your OpenAI API key
    $endpoint = 'https://api.openai.com/v1/completions';
    $headers = array(
        'Authorization: Bearer ' . $api_key,
        'Content-Type: application/json'
    );
    
    $data = array(
        'model' => 'text-davinci-003',  // Use a specific GPT model
        'prompt' => $prompt,
        'max_tokens' => 200
    );

    $response = wp_remote_post($endpoint, array(
        'headers' => $headers,
        'body' => json_encode($data),
        'timeout' => 60
    ));

    if (is_wp_error($response)) {
        wp_send_json_error('Error in API call.');
    } else {
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        $generated_description = $result['choices'][0]['text'];

        wp_send_json($generated_description);
    }

    wp_die();
}

