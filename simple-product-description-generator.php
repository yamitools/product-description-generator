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

