<?php
/**
 * Plugin Name: WooCommerce Enhancements
 * Plugin URI: https://themevaults.xyz/
 * Description: Enhances WooCommerce with custom price displays, COD management, and checkout urgency.
 * Version: 1.0.0
 * Author: Md. Salman
 * Author URI: https://www.facebook.com/mdsalman.bdofficial
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: wc-enhancements
 * Domain Path: /languages
 */

// Exit if accessed directly to prevent unauthorized access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants for easy path and URL referencing.
define( 'MY_WC_ENH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MY_WC_ENH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include all feature-specific files.
// These files contain the actual logic for price display, COD, and checkout enhancements.
require_once MY_WC_ENH_PLUGIN_DIR . 'includes/price-display-functions.php';
require_once MY_WC_ENH_PLUGIN_DIR . 'includes/cod-management-functions.php';
require_once MY_WC_ENH_PLUGIN_DIR . 'includes/checkout-enhancements.php';

// Include the admin settings file, which handles the plugin's options page.
require_once MY_WC_ENH_PLUGIN_DIR . 'admin/admin-settings.php';

/**
 * Enqueue public-facing styles for the plugin.
 * These styles are loaded on the frontend of the website.
 * Styles are only enqueued if the 'enable_custom_styles' option is active in the admin panel.
 */
function my_wc_enh_enqueue_public_styles() {
    // Retrieve plugin options.
    $options = get_option('my_wc_enh_settings');

    // Check if custom styles are enabled.
    if (isset($options['enable_custom_styles']) && $options['enable_custom_styles'] == '1') {
        // Enqueue the main public stylesheet.
        wp_enqueue_style( 'my-wc-enh-public-styles', MY_WC_ENH_PLUGIN_URL . 'public/public-styles.css', array(), '1.0.0', 'all' );
        // Add inline CSS generated from admin panel color settings.
        wp_add_inline_style( 'my-wc-enh-public-styles', my_wc_enh_generate_custom_css() );

        // Add user-defined custom CSS from the admin panel.
        if (isset($options['custom_css_code']) && !empty($options['custom_css_code'])) {
            wp_add_inline_style( 'my-wc-enh-public-styles', wp_strip_all_tags( $options['custom_css_code'] ) );
        }
    }
}
add_action( 'wp_enqueue_scripts', 'my_wc_enh_enqueue_public_styles' );

/**
 * Generates dynamic CSS based on the plugin's settings.
 * This function creates CSS variables that can be used in public-styles.css.
 * Default values are provided if a setting is not found or empty.
 *
 * @return string The generated CSS string.
 */
function my_wc_enh_generate_custom_css() {
    $options = get_option('my_wc_enh_settings');
    $custom_css = ':root {'; // Define CSS variables globally.

    // COD Badge Colors (Defaults matching your requested style)
    // For 'available' state
    $custom_css .= '--my-wc-enh-cod-badge-available-bg: ' . ( isset($options['cod_badge_available_bg']) && !empty($options['cod_badge_available_bg']) ? esc_attr($options['cod_badge_available_bg']) : '#e8f5e9' ) . ';';
    $custom_css .= '--my-wc-enh-cod-badge-available-color: ' . ( isset($options['cod_badge_available_color']) && !empty($options['cod_badge_available_color']) ? esc_attr($options['cod_badge_available_color']) : '#507d32' ) . ';';
    $custom_css .= '--my-wc-enh-cod-badge-available-border: ' . ( isset($options['cod_badge_available_border']) && !empty($options['cod_badge_available_border']) ? esc_attr($options['cod_badge_available_border']) : '#507d32' ) . ';';

    // For 'unavailable' state
    $custom_css .= '--my-wc-enh-cod-badge-unavailable-bg: ' . ( isset($options['cod_badge_unavailable_bg']) && !empty($options['cod_badge_unavailable_bg']) ? esc_attr($options['cod_badge_unavailable_bg']) : '#fff5f5' ) . ';';
    $custom_css .= '--my-wc-enh-cod-badge-unavailable-color: ' . ( isset($options['cod_badge_unavailable_color']) && !empty($options['cod_badge_unavailable_color']) ? esc_attr($options['cod_badge_unavailable_color']) : '#7f1d1d' ) . ';';
    $custom_css .= '--my-wc-enh-cod-badge-unavailable-border: ' . ( isset($options['cod_badge_unavailable_border']) && !empty($options['cod_badge_unavailable_border']) ? esc_attr($options['cod_badge_unavailable_border']) : '#ef4444' ) . ';';

    // Urgency Box Colors (Keeping sleek defaults for this, as not specified to change)
    $custom_css .= '--my-wc-enh-urgency-box-bg: ' . ( isset($options['urgency_box_bg']) && !empty($options['urgency_box_bg']) ? esc_attr($options['urgency_box_bg']) : '#FFFDE7' ) . ';';
    $custom_css .= '--my-wc-enh-urgency-box-color: ' . ( isset($options['urgency_box_color']) && !empty($options['urgency_box_color']) ? esc_attr($options['urgency_box_color']) : '#FF8F00' ) . ';';
    $custom_css .= '--my-wc-enh-urgency-box-border: ' . ( isset($options['urgency_box_border']) && !empty($options['urgency_box_border']) ? esc_attr($options['urgency_box_border']) : '#FFD54F' ) . ';';

    // Discount Breakdown Colors (Keeping sleek defaults for this, as not specified to change)
    $custom_css .= '--my-wc-enh-discount-amount-color: ' . ( isset($options['discount_amount_color']) && !empty($options['discount_amount_color']) ? esc_attr($options['discount_amount_color']) : '#D32F2F' ) . ';';
    $custom_css .= '--my-wc-enh-regular-price-color: ' . ( isset($options['regular_price_color']) && !empty($options['regular_price_color']) ? esc_attr($options['regular_price_color']) : '#757575' ) . ';';
    $custom_css .= '--my-wc-enh-product-discount-color: ' . ( isset($options['product_discount_color']) && !empty($options['product_discount_color']) ? esc_attr($options['product_discount_color']) : '#D32F2F' ) . ';';
    $custom_css .= '--my-wc-enh-coupon-discount-color: ' . ( isset($options['coupon_discount_color']) && !empty($options['coupon_discount_color']) ? esc_attr($options['coupon_discount_color']) : '#1976D2' ) . ';';
    $custom_css .= '--my-wc-enh-you-saved-color: ' . ( isset($options['you_saved_color']) && !empty($options['you_saved_color']) ? esc_attr($options['you_saved_color']) : '#388E3C' ) . ';';

    $custom_css .= '}'; // Close :root block.

    return $custom_css;
}

/**
 * Adds a "Settings" link to the plugin actions on the Plugins page.
 * This provides a quick way to access the plugin's settings.
 *
 * @param array $links An array of plugin action links.
 * @return array The modified array of links.
 */
function my_wc_enh_add_settings_link( $links ) {
    $settings_link = '<a href="admin.php?page=my-wc-enhancements-settings">' . __( 'Settings', 'my-wc-enhancements' ) . '</a>';
    array_unshift( $links, $settings_link ); // Add the settings link to the beginning of the array.
    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'my_wc_enh_add_settings_link' );
