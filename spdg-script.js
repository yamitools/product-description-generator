jQuery(document).ready(function($) {
    $('#spdg_generate_description').click(function(e) {
        e.preventDefault();
        
        var productName = $('#spdg_product_name').val();
        var productFeatures = $('#spdg_product_features').val();

        if (!productName || !productFeatures) {
            alert('Please enter a product name and features.');
            return;
        }

        $.ajax({
            url: spdg_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'generate_product_description',
                product_name: productName,
                product_features: productFeatures,
                nonce: spdg_ajax.nonce
            },
            success: function(response) {
                console.log(response);  // Debug code to see the output of the response
                if (response.success) {
                    $('#spdg_generated_description').val(response.data);
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('AJAX Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });
});
