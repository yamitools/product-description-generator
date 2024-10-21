jQuery(document).ready(function($) {
    $('#spdg_generate_description').click(function(e) {
        e.preventDefault();
        
        var productName = $('#spdg_product_name').val();
        var productFeatures = $('#spdg_product_features').val();

        $.ajax({
            url: ajaxurl,  // WordPress-specific variable that points to admin-ajax.php
            type: 'POST',
            data: {
                action: 'generate_product_description',
                product_name: productName,
                product_features: productFeatures
            },
            success: function(response) {
                $('#spdg_generated_description').val(response);
            },
            error: function() {
                alert('Failed to generate description. Please try again.');
            }
        });
    });
});
