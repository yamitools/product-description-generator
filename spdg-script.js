jQuery(document).ready(function($) {
    $('#spdg_generate_description').click(function(e) {
        e.preventDefault();
        
        var productName = $('#spdg_product_name').val();
        var productFeatures = $('#spdg_product_features').val();

        // Replace with your API call to generate a description
        var description = 'Generated description for ' + productName + ' with features: ' + productFeatures;

        $('#spdg_generated_description').val(description);
    });
});
