<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Displays the flat discount amount on the single product page.
 * This function modifies the WooCommerce price HTML.
 * It is enabled/disabled via the 'enable_discount_display' setting.
 *
 * @param string   $price   The product price HTML.
 * @param WC_Product $product The product object.
 * @return string The modified price HTML.
 */
add_filter('woocommerce_get_price_html', 'custom_discount_amount_display', 1553, 200);

function custom_discount_amount_display($price, $product) {
    $options = get_option('my_wc_enh_settings');
    // Check if this feature is enabled in plugin settings.
    if ( ! isset($options['enable_discount_display']) || $options['enable_discount_display'] != '1' ) {
        return $price; // Return original price if disabled.
    }

    // Check if we are on the single product page and the product is on sale.
    if (is_product() && $product->is_on_sale()) {
        $regular_price = (float) $product->get_regular_price();
        $sale_price = (float) $product->get_sale_price();
        $discount_amount = $regular_price - $sale_price;

        // Display sale price with discount amount.
        // The 'discount-amount' class will be styled by public-styles.css using CSS variables.
        return '<del>' . wc_price($regular_price) . '</del> ' .
               '<ins>' . wc_price($sale_price) . '</ins>' .
               ' <span class="discount-amount">' . wc_price($discount_amount) . ' off</span>';
    }
    return $price;
}

/**
 * Sets the lowest priced variation as the default for variable products.
 * This ensures the lowest price is pre-selected on product pages.
 * Enabled/disabled via the 'enable_lowest_price_variation' setting.
 *
 * @param array      $prices      Array of variation prices.
 * @param WC_Product $product     The product object.
 * @param bool       $for_display Whether prices are for display.
 * @return array The modified array of prices.
 */
add_filter('woocommerce_variation_prices', 'set_lowest_price_variation_default', 9999, 3);

function set_lowest_price_variation_default($prices, $product, $for_display) {
    $options = get_option('my_wc_enh_settings');
    // Check if this feature is enabled.
    if ( ! isset($options['enable_lowest_price_variation']) || $options['enable_lowest_price_variation'] != '1' ) {
        return $prices;
    }

    if ($product->is_type('variable')) {
        $variation_prices = $prices['price'];
        if (!empty($variation_prices)) {
            $lowest_price = min($variation_prices);
            // Set all variations to the lowest price for default selection logic.
            $prices['price'] = array_fill_keys(array_keys($prices['price']), $lowest_price);
        }
    }
    return $prices;
}

/**
 * Selects the lowest priced variation on the single product page.
 * This works in conjunction with `set_lowest_price_variation_default`.
 * Enabled/disabled via the 'enable_lowest_price_variation' setting.
 *
 * @param array $variation_data Array of variation data.
 * @return array The modified variation data.
 */
add_filter('woocommerce_available_variation', 'select_lowest_price_variation');

function select_lowest_price_variation($variation_data) {
    $options = get_option('my_wc_enh_settings');
    // Check if this feature is enabled.
    if ( ! isset($options['enable_lowest_price_variation']) || $options['enable_lowest_price_variation'] != '1' ) {
        return $variation_data;
    }

    static $lowest_price = null; // Use static to keep track of the lowest price across variations.

    if ($lowest_price === null || $variation_data['display_price'] < $lowest_price) {
        $lowest_price = $variation_data['display_price'];
        $variation_data['is_selected'] = true; // Mark the current lowest as selected.
    } else {
        $variation_data['is_selected'] = false; // Deselect others.
    }

    return $variation_data;
}

/**
 * Shows only the lowest price for variable products on product category pages.
 * Enabled/disabled via the 'enable_lowest_price_category' setting.
 *
 * @param string   $price   The product price HTML.
 * @param WC_Product $product The product object.
 * @return string The modified price HTML.
 */
add_filter('woocommerce_get_price_html', 'show_lowest_variation_price', 9999, 2);

function show_lowest_variation_price($price, $product) {
    $options = get_option('my_wc_enh_settings');
    // Check if this feature is enabled.
    if ( ! isset($options['enable_lowest_price_category']) || $options['enable_lowest_price_category'] != '1' ) {
        return $price;
    }

    if ($product->is_type('variable')) {
        $variation_prices = $product->get_variation_prices();
        if (!empty($variation_prices['price'])) {
            $lowest_price = min($variation_prices['price']);
            // Format the price to display only the lowest amount.
            $price = '<span class="woocommerce-Price-amount amount">' . wc_price($lowest_price) . '</span>';
        }
    }
    return $price;
}
