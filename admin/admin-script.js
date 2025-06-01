jQuery(document).ready(function($){
    // Initialize all color pickers on the page.
    $('.my-wc-enh-color-field').wpColorPicker({
        // Callback function fired when the color changes.
        change: function(event, ui) {
            updatePreview(); // Update the live preview.
        },
        // Callback function fired when the color picker is cleared.
        clear: function() {
            updatePreview(); // Update the live preview.
        }
    });

    /**
     * Function to update the live preview section with current settings.
     * It reads values from the input fields and applies them to the preview elements.
     */
    function updatePreview() {
        // --- COD Badge Preview ---
        // Get current values from color picker inputs, with default fallbacks.
        var codAvailableBg = $('#cod_badge_available_bg').val() || '#E8F5E9'; // Light Green
        var codAvailableColor = $('#cod_badge_available_color').val() || '#2E7D32'; // Dark Green
        var codAvailableBorder = $('#cod_badge_available_border').val() || '#A5D6A7'; // Medium Green

        // Apply styles to the available COD badge preview.
        $('#cod-badge-preview-available').css({
            'background-color': codAvailableBg,
            'color': codAvailableColor,
            'border-color': codAvailableBorder
        });

        var codUnavailableBg = $('#cod_badge_unavailable_bg').val() || '#FFEBEE'; // Light Red
        var codUnavailableColor = $('#cod_badge_unavailable_color').val() || '#C62828'; // Dark Red
        var codUnavailableBorder = $('#cod_badge_unavailable_border').val() || '#EF9A9A'; // Medium Red

        // Apply styles to the unavailable COD badge preview.
        $('#cod-badge-preview-unavailable').css({
            'background-color': codUnavailableBg,
            'color': codUnavailableColor,
            'border-color': codUnavailableBorder
        });

        // --- Urgency Box Preview ---
        var urgencyBoxBg = $('#urgency_box_bg').val() || '#FFFDE7'; // Light Yellow
        var urgencyBoxColor = $('#urgency_box_color').val() || '#FF8F00'; // Orange
        var urgencyBoxBorder = $('#urgency_box_border').val() || '#FFD54F'; // Medium Yellow

        // Apply styles to the urgency box preview.
        $('#urgency-box-preview').css({
            'background-color': urgencyBoxBg,
            'color': urgencyBoxColor,
            'border-color': urgencyBoxBorder
        });

        // --- Discount Breakdown Preview & Single Product Discount ---
        var regularPriceColor = $('#regular_price_color').val() || '#757575'; // Grey
        var productDiscountColor = $('#product_discount_color').val() || '#D32F2F'; // Red
        var couponDiscountColor = $('#coupon_discount_color').val() || '#1976D2'; // Blue
        var youSavedColor = $('#you_saved_color').val() || '#388E3C'; // Green
        var singleDiscountAmountColor = $('#discount_amount_color').val() || '#D32F2F'; // Red


        // Apply colors to the discount breakdown table elements.
        $('#preview-regular-price-label, #preview-regular-price-value').css('color', regularPriceColor);
        $('#preview-product-discount-label, #preview-product-discount-value').css('color', productDiscountColor);
        $('#preview-coupon-discount-label, #preview-coupon-discount-value').css('color', couponDiscountColor);
        $('#preview-you-saved-label, #preview-you-saved-value').css('color', youSavedColor);

        // Apply color to the single product discount amount.
        $('#preview-discount-amount-single').css('color', singleDiscountAmountColor);

        // Note: The custom CSS textarea's content itself is not directly applied to the JS preview,
        // as it's meant for arbitrary CSS. The PHP handles its enqueueing.
    }

    // Initial preview update when the page loads to reflect saved settings.
    updatePreview();

    // Attach change/keyup event listeners to all relevant input fields
    // to update the preview in real-time as the user types or selects.
    // This includes the new custom CSS textarea.
    $('.my-wc-enh-color-field, input[type="text"], input[type="number"], textarea').on('change keyup', function() {
        updatePreview();
    });
});
