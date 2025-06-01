<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Displays a custom Cash on Delivery (COD) notice on the checkout page.
 * The notice changes based on whether specific shipping classes are in the cart.
 * Enabled/disabled via the 'enable_cod_management' setting.
 */
add_action('woocommerce_review_order_before_payment', 'custom_cod_notice_checkout_flatsome');

function custom_cod_notice_checkout_flatsome() {
    $options = get_option('my_wc_enh_settings');
    // Check if COD management is enabled.
    if ( ! isset($options['enable_cod_management']) || $options['enable_cod_management'] != '1' ) {
        return;
    }

    // Get disallowed shipping classes from settings, default to hardcoded if not set.
    $disallowed_classes_string = isset($options['cod_disallowed_shipping_classes']) ? $options['cod_disallowed_shipping_classes'] : 'bouquets-balloon,cake,cheese-frozen,craft-items,basket-gift,baking-items-high-weight';
    $disallowed_classes = array_map('trim', explode(',', $disallowed_classes_string));
    $disallowed_classes = array_filter($disallowed_classes); // Remove any empty entries.

    $cod_available = true;

    // Iterate through cart items to check for disallowed shipping classes.
    foreach (WC()->cart->get_cart() as $cart_item) {
        $product = $cart_item['data'];
        $shipping_class = $product->get_shipping_class();

        if (in_array($shipping_class, $disallowed_classes)) {
            $cod_available = false;
            break; // Found a disallowed item, no need to check further.
        }
    }

    // Use CSS variables for styling if custom styles are enabled, otherwise use inline styles.
    $use_custom_styles = isset($options['enable_custom_styles']) && $options['enable_custom_styles'] == '1';

    if ($cod_available) {
        if ($use_custom_styles) {
            echo '<div class="cod-notice-available">'; // Class defined in public-styles.css
        } else {
            echo '<div style="
                background-color: #e6ffec;
                color: #14532d;
                padding: 15px 20px;
                border-radius: 8px;
                border-left: 6px solid #22c55e;
                margin-bottom: 20px;
                font-size: 14px;
                line-height: 1.5;
            ">';
        }
        echo '<strong>✅ Cash on Delivery is available in Dhaka city only.</strong><br>
              For deliveries outside Dhaka, a partial advance payment is required to confirm your order.
            </div>';
    } else {
        if ($use_custom_styles) {
            echo '<div class="cod-notice-unavailable">'; // Class defined in public-styles.css
        } else {
            echo '<div style="
                background-color: #fff5f5;
                color: #7f1d1d;
                padding: 15px 20px;
                border-radius: 8px;
                border-left: 6px solid #ef4444;
                margin-bottom: 20px;
                font-size: 14px;
                line-height: 1.5;
            ">';
        }
        echo '<strong>❌ Cash on Delivery is unavailable for the selected item(s).</strong><br>
              To proceed, please pay a minimum delivery fee in advance.<br>
              For gift deliveries, full payment is required. The product will be delivered without an invoice for surprise gifting.
            </div>';
    }
}

/**
 * Disables the Cash on Delivery payment gateway if disallowed shipping classes are in the cart.
 * Enabled/disabled via the 'enable_cod_management' setting.
 *
 * @param array $available_gateways Array of available payment gateways.
 * @return array The modified array of payment gateways.
 */
add_filter('woocommerce_available_payment_gateways', 'disable_cod_for_disallowed_shipping_classes');
function disable_cod_for_disallowed_shipping_classes($available_gateways) {
    if (is_admin()) return $available_gateways; // Don't run in admin.

    $options = get_option('my_wc_enh_settings');
    // Check if COD management is enabled.
    if ( ! isset($options['enable_cod_management']) || $options['enable_cod_management'] != '1' ) {
        return $available_gateways;
    }

    $disallowed_classes_string = isset($options['cod_disallowed_shipping_classes']) ? $options['cod_disallowed_shipping_classes'] : 'bouquets-balloon,cake,cheese-frozen,craft-items,basket-gift,baking-items-high-weight';
    $disallowed_classes = array_map('trim', explode(',', $disallowed_classes_string));
    $disallowed_classes = array_filter($disallowed_classes);

    foreach (WC()->cart->get_cart() as $cart_item) {
        $product = $cart_item['data'];
        if (in_array($product->get_shipping_class(), $disallowed_classes)) {
            unset($available_gateways['cod']); // Remove COD gateway.
            break;
        }
    }

    return $available_gateways;
}

/**
 * Shows a COD availability badge on the single product summary page.
 * Enabled/disabled via the 'enable_cod_management' setting.
 */
add_action('woocommerce_single_product_summary', 'show_cod_badge_on_product_page', 25);

function show_cod_badge_on_product_page() {
    $options = get_option('my_wc_enh_settings');
    // Check if COD management is enabled.
    if ( ! isset($options['enable_cod_management']) || $options['enable_cod_management'] != '1' ) {
        return;
    }

    global $product;

    if ( ! $product || ! method_exists( $product, 'get_shipping_class' ) ) {
        return;
    }

    $disallowed_classes_string = isset($options['cod_disallowed_shipping_classes']) ? $options['cod_disallowed_shipping_classes'] : 'bouquets-balloon,cake,cheese-frozen,craft-items,basket-gift,baking-items-high-weight';
    $disallowed_classes = array_map('trim', explode(',', $disallowed_classes_string));
    $disallowed_classes = array_filter($disallowed_classes);

    $shipping_class = $product->get_shipping_class();

    // Use CSS classes for styling if custom styles are enabled, otherwise use inline styles.
    $use_custom_styles = isset($options['enable_custom_styles']) && $options['enable_custom_styles'] == '1';

    // Common badge styles (if not using custom styles)
    $badge_common_style = '
        display: inline-flex;
        align-items: flex-start;
        gap: 10px;
        border-radius: 10px;
        padding: 14px 18px;
        font-size: 14px;
        line-height: 1.4;
        margin-top: 20px;
    ';

    // ❌ COD not available
    if (in_array($shipping_class, $disallowed_classes)) {
        if ($use_custom_styles) {
            echo '<div class="cod-badge-unavailable">'; // Class defined in public-styles.css
        } else {
            echo '<div style="background-color: #ffecec; color: #b30000; border: 1px solid #f5c2c2; ' . $badge_common_style . '">';
        }
        echo '<span style="font-size: 18px;">❌</span>
            <div>
                <strong>Cash on Delivery is unavailable for this item.</strong><br>
                A minimum delivery fee must be paid in advance.<br>
                For gift deliveries, full payment is required without a product invoice.
            </div>
        </div>';
    }
    // ✅ COD available
    else {
        if ($use_custom_styles) {
            echo '<div class="cod-badge-available">'; // Class defined in public-styles.css
        } else {
            echo '<div style="background-color: #e6ffec; color: #1a7f37; border: 1px solid #cce5cc; ' . $badge_common_style . '">';
        }
        echo '<span style="font-size: 18px;">✅</span>
            <div>
                <strong>Cash on Delivery available in Dhaka city only.</strong><br>
                For outside Dhaka, a partial advance payment is required to confirm your order.
            </div>
        </div>';
    }
}

/**
 * Registers a shortcode to display the COD status badge.
 * This allows users to place the badge anywhere using `[cod_status_badge]`.
 * Enabled/disabled via the 'enable_cod_management' setting.
 */
add_shortcode('cod_status_badge', 'custom_cod_badge_shortcode');

function custom_cod_badge_shortcode() {
    $options = get_option('my_wc_enh_settings');
    // Check if COD management is enabled.
    if ( ! isset($options['enable_cod_management']) || $options['enable_cod_management'] != '1' ) {
        return '';
    }

    global $product;

    if ( ! $product || ! method_exists($product, 'get_shipping_class') ) {
        return '';
    }

    $disallowed_classes_string = isset($options['cod_disallowed_shipping_classes']) ? $options['cod_disallowed_shipping_classes'] : 'bouquets-balloon,cake,cheese-frozen,craft-items,basket-gift,baking-items-high-weight';
    $disallowed_classes = array_map('trim', explode(',', $disallowed_classes_string));
    $disallowed_classes = array_filter($disallowed_classes);

    $shipping_class = $product->get_shipping_class();

    // Use CSS classes for styling if custom styles are enabled, otherwise use inline styles.
    $use_custom_styles = isset($options['enable_custom_styles']) && $options['enable_custom_styles'] == '1';

    $badge_common_style = '
        display: inline-flex;
        align-items: flex-start;
        gap: 10px;
        border-radius: 10px;
        padding: 14px 18px;
        font-size: 14px;
        line-height: 1.4;
        margin-top: 20px;
    ';

    ob_start(); // Start output buffering to capture the HTML.

    if (in_array($shipping_class, $disallowed_classes)) {
        if ($use_custom_styles) {
            echo '<div class="cod-badge-unavailable">';
        } else {
            echo '<div style="background-color: #ffecec; color: #b30000; border: 1px solid #f5c2c2; ' . $badge_common_style . '">';
        }
        echo '<span style="font-size: 18px;">❌</span>
            <div>
                <strong>Cash on Delivery not available for this item.</strong><br>
                Please pay delivery fee in advance to confirm your order.
            </div>
        </div>';
    } else {
        if ($use_custom_styles) {
            echo '<div class="cod-badge-available">';
        } else {
            echo '<div style="background-color: #e6ffec; color: #1a7f37; border: 1px solid #cce5cc; ' . $badge_common_style . '">';
        }
        echo '<span style="font-size: 18px;">✅</span>
            <div>
                <strong>Cash on Delivery available in Dhaka..</strong><br>
                For outside Dhaka, Delivery fee advance is required..
            </div>
        </div>';
    }

    return ob_get_clean(); // Return the captured HTML.
}
