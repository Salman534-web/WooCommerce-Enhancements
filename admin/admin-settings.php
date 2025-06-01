<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueues admin-specific styles and scripts for the plugin settings page.
 * This includes the WordPress color picker and custom JS for live preview.
 */
function my_wc_enh_admin_enqueue_scripts() {
    // Only enqueue scripts/styles on our plugin's settings page.
    if ( isset( $_GET['page'] ) && $_GET['page'] == 'my-wc-enhancements-settings' ) {
        wp_enqueue_style( 'my-wc-enh-admin-styles', MY_WC_ENH_PLUGIN_URL . 'admin/admin-styles.css', array(), '1.0.0' );
        // Enqueue wp-color-picker for color fields.
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'my-wc-enh-admin-script', MY_WC_ENH_PLUGIN_URL . 'admin/admin-script.js', array('jquery', 'wp-color-picker'), '1.0.0', true );
    }
}
add_action( 'admin_enqueue_scripts', 'my_wc_enh_admin_enqueue_scripts' );

/**
 * Adds the plugin's settings page to the WordPress admin menu.
 */
function my_wc_enh_add_admin_menu() {
    add_menu_page(
        __( 'WC Enhancements Settings', 'my-wc-enhancements' ), // Page title
        __( 'WC Enhancements', 'my-wc-enhancements' ),          // Menu title
        'manage_options',                                       // Capability required to access
        'my-wc-enhancements-settings',                          // Menu slug (this is the 'page' GET parameter, must be unique)
        'my_wc_enh_settings_page_callback',                     // Callback function to render the page
        'dashicons-cart',                                       // Icon URL or Dashicon class
        50                                                      // Position in the menu
    );
}
add_action( 'admin_menu', 'my_wc_enh_add_admin_menu' );

/**
 * Registers the plugin's settings, sections, and fields.
 * This uses the WordPress Settings API.
 */
function my_wc_enh_settings_init() {
    // IMPORTANT: This 'option_group' MUST be consistent across register_setting(), settings_fields(), and do_settings_sections().
    $option_group = 'my_wc_enh_settings_group';
    // This is the single option name that will store all settings in the wp_options table.
    $option_name = 'my_wc_enh_settings';

    register_setting(
        $option_group, // The option group name.
        $option_name   // The name of the option to store all settings.
    );

    // --- General Settings Section ---
    $general_section_id = 'my_wc_enh_general_settings_section';
    $page_slug = 'my_wc_enh_settings_page'; // This must match the slug used in do_settings_sections()

    // Register the General Settings section.
    // It's crucial that sections are registered BEFORE fields are added to them.
    add_settings_section(
        $general_section_id,                      // ID for this section.
        __( 'General Settings', 'my-wc-enhancements' ), // Title of the section.
        null,                                     // Callback for section description (null if no description).
        $page_slug                                // The page slug where this section will appear.
    );

    // Field: Enable Custom Frontend Styles
    add_settings_field(
        'enable_custom_styles', // Field ID (unique within the section)
        __( 'Enable Custom Frontend Styles', 'my-wc-enhancements' ), // Field Title
        'my_wc_enh_toggle_callback', // Callback function to render the field.
        $page_slug, // The page slug this field belongs to.
        $general_section_id, // The section ID this field belongs to.
        array(
            'id'    => 'enable_custom_styles',
            'label' => 'Check to enable custom CSS for badges, urgency messages, etc. (uses colors below).',
            'option_name' => $option_name // Pass option name to callback for dynamic fetching
        )
    );
    // Field: Enable Single Product Discount Display
    add_settings_field(
        'enable_discount_display',
        __( 'Enable Single Product Discount Display', 'my-wc-enhancements' ),
        'my_wc_enh_toggle_callback',
        $page_slug,
        $general_section_id,
        array(
            'id'    => 'enable_discount_display',
            'label' => 'Show flat discount amount on single product page.',
            'option_name' => $option_name
        )
    );
    // Field: Enable Lowest Price Variation Selection
    add_settings_field(
        'enable_lowest_price_variation',
        __( 'Enable Lowest Price Variation Selection', 'my-wc-enhancements' ),
        'my_wc_enh_toggle_callback',
        $page_slug,
        $general_section_id,
        array(
            'id'    => 'enable_lowest_price_variation',
            'label' => 'Set and select the lowest price variation by default.',
            'option_name' => $option_name
        )
    );
    // Field: Enable Lowest Price on Category Page
    add_settings_field(
        'enable_lowest_price_category',
        __( 'Enable Lowest Price on Category Page', 'my-wc-enhancements' ),
        'my_wc_enh_toggle_callback',
        $page_slug,
        $general_section_id,
        array(
            'id'    => 'enable_lowest_price_category',
            'label' => 'Show only the lowest price for variable products on category pages.',
            'option_name' => $option_name
        )
    );
    // Field: Enable Cash on Delivery Management
    add_settings_field(
        'enable_cod_management',
        __( 'Enable Cash on Delivery Management', 'my-wc-enhancements' ),
        'my_wc_enh_toggle_callback',
        $page_slug,
        $general_section_id,
        array(
            'id'    => 'enable_cod_management',
            'label' => 'Manage COD availability based on shipping classes and display notices.',
            'option_name' => $option_name
        )
    );
    // Field: COD Disallowed Shipping Classes
    add_settings_field(
        'cod_disallowed_shipping_classes',
        __( 'COD Disallowed Shipping Classes', 'my-wc-enhancements' ),
        'my_wc_enh_textarea_callback', // Callback for a textarea.
        $page_slug,
        $general_section_id,
        array(
            'id'    => 'cod_disallowed_shipping_classes',
            'label' => 'Enter comma-separated slugs of shipping classes that disable COD (e.g., bouquets-balloon, cake).',
            'option_name' => $option_name
        )
    );
    // Field: Enable Checkout Urgency Timer
    add_settings_field(
        'enable_checkout_urgency',
        __( 'Enable Checkout Urgency Timer', 'my-wc-enhancements' ),
        'my_wc_enh_toggle_callback',
        $page_slug,
        $general_section_id,
        array(
            'id'    => 'enable_checkout_urgency',
            'label' => 'Show urgency message with a countdown timer on checkout.',
            'option_name' => $option_name
        )
    );
    // Field: Urgency Timer Duration
    add_settings_field(
        'urgency_timer_duration',
        __( 'Urgency Timer Duration (minutes)', 'my-wc-enhancements' ),
        'my_wc_enh_text_input_callback', // Callback for a text input.
        $page_slug,
        $general_section_id,
        array(
            'id'    => 'urgency_timer_duration',
            'label' => 'Set the duration for the checkout urgency timer in minutes (e.g., 15).',
            'option_name' => $option_name
        )
    );
    // Field: Enable Cart/Checkout Discount Breakdown
    add_settings_field(
        'enable_discount_breakdown',
        __( 'Enable Cart/Checkout Discount Breakdown', 'my-wc-enhancements' ),
        'my_wc_enh_toggle_callback',
        $page_slug,
        $general_section_id,
        array(
            'id'    => 'enable_discount_breakdown',
            'label' => 'Show detailed savings breakdown in cart and checkout.',
            'option_name' => $option_name
        )
    );

    // --- Custom CSS Settings Section ---
    $css_section_id = 'my_wc_enh_css_settings_section';
    add_settings_section(
        $css_section_id,
        __( 'Custom CSS Settings', 'my-wc-enhancements' ),
        'my_wc_enh_css_section_callback', // Callback for section description.
        $page_slug
    );

    // Color fields for COD Available Badge
    add_settings_field(
        'cod_badge_available_bg',
        __( 'COD Available Badge Background', 'my-wc-enhancements' ),
        'my_wc_enh_color_picker_callback', // Callback for color picker.
        $page_slug,
        $css_section_id,
        array(
            'id' => 'cod_badge_available_bg',
            'option_name' => $option_name
        )
    );
    add_settings_field(
        'cod_badge_available_color',
        __( 'COD Available Badge Text Color', 'my-wc-enhancements' ),
        'my_wc_enh_color_picker_callback',
        $page_slug,
        $css_section_id,
        array(
            'id' => 'cod_badge_available_color',
            'option_name' => $option_name
        )
    );
    add_settings_field(
        'cod_badge_available_border',
        __( 'COD Available Badge Border Color', 'my-wc-enhancements' ),
        'my_wc_enh_color_picker_callback',
        $page_slug,
        $css_section_id,
        array(
            'id' => 'cod_badge_available_border',
            'option_name' => $option_name
        )
    );

    // Color fields for COD Unavailable Badge
    add_settings_field(
        'cod_badge_unavailable_bg',
        __( 'COD Unavailable Badge Background', 'my-wc-enhancements' ),
        'my_wc_enh_color_picker_callback',
        $page_slug,
        $css_section_id,
        array(
            'id' => 'cod_badge_unavailable_bg',
            'option_name' => $option_name
        )
    );
    add_settings_field(
        'cod_badge_unavailable_color',
        __( 'COD Unavailable Badge Text Color', 'my-wc-enhancements' ),
        'my_wc_enh_color_picker_callback',
        $page_slug,
        $css_section_id,
        array(
            'id' => 'cod_badge_unavailable_color',
            'option_name' => $option_name
        )
    );
    add_settings_field(
        'cod_badge_unavailable_border',
        __( 'COD Unavailable Badge Border Color', 'my-wc-enhancements' ),
        'my_wc_enh_color_picker_callback',
        $page_slug,
        $css_section_id,
        array(
            'id' => 'cod_badge_unavailable_border',
            'option_name' => $option_name
        )
    );

    // Color fields for Urgency Box
    add_settings_field(
        'urgency_box_bg',
        __( 'Urgency Box Background', 'my-wc-enhancements' ),
        'my_wc_enh_color_picker_callback',
        $page_slug,
        $css_section_id,
        array(
            'id' => 'urgency_box_bg',
            'option_name' => $option_name
        )
    );
    add_settings_field(
        'urgency_box_color',
        __( 'Urgency Box Text Color', 'my-wc-enhancements' ),
        'my_wc_enh_color_picker_callback',
        $page_slug,
        $css_section_id,
        array(
            'id' => 'urgency_box_color',
            'option_name' => $option_name
        )
    );
    add_settings_field(
        'urgency_box_border',
        __( 'Urgency Box Border Color', 'my-wc-enhancements' ),
        'my_wc_enh_color_picker_callback',
        $page_slug,
        $css_section_id,
        array(
            'id' => 'urgency_box_border',
            'option_name' => $option_name
        )
    );

    // Color field for Single Product Discount Amount
    add_settings_field(
        'discount_amount_color',
        __( 'Discount Amount Text Color (Single Product)', 'my-wc-enhancements' ),
        'my_wc_enh_color_picker_callback',
        $page_slug,
        $css_section_id,
        array(
            'id' => 'discount_amount_color',
            'option_name' => $option_name
        )
    );

    // Color fields for Discount Breakdown
    add_settings_field(
        'regular_price_color',
        __( 'Regular Price Text Color (Breakdown)', 'my-wc-enhancements' ),
        'my_wc_enh_color_picker_callback',
        $page_slug,
        $css_section_id,
        array(
            'id' => 'regular_price_color',
            'option_name' => $option_name
        )
    );
    add_settings_field(
        'product_discount_color',
        __( 'Product Discount Text Color (Breakdown)', 'my-wc-enhancements' ),
        'my_wc_enh_color_picker_callback',
        $page_slug,
        $css_section_id,
        array(
            'id' => 'product_discount_color',
            'option_name' => $option_name
        )
    );
    add_settings_field(
        'coupon_discount_color',
        __( 'Coupon Discount Text Color (Breakdown)', 'my-wc-enhancements' ),
        'my_wc_enh_color_picker_callback',
        $page_slug,
        $css_section_id,
        array(
            'id' => 'coupon_discount_color',
            'option_name' => $option_name
        )
    );
    add_settings_field(
        'you_saved_color',
        __( 'You Saved Text Color (Breakdown)', 'my-wc-enhancements' ),
        'my_wc_enh_color_picker_callback',
        $page_slug,
        $css_section_id,
        array(
            'id' => 'you_saved_color',
            'option_name' => $option_name
        )
    );

    // --- Advanced Customizations Section ---
    $advanced_section_id = 'my_wc_enh_advanced_settings_section';
    add_settings_section(
        $advanced_section_id,
        __( 'Advanced Customizations', 'my-wc-enhancements' ),
        'my_wc_enh_advanced_section_callback',
        $page_slug
    );

    // Field: Custom CSS Code
    add_settings_field(
        'custom_css_code',
        __( 'Custom CSS Code', 'my-wc-enhancements' ),
        'my_wc_enh_textarea_callback',
        $page_slug,
        $advanced_section_id,
        array(
            'id'    => 'custom_css_code',
            'label' => 'Enter any custom CSS here. This will be applied to the frontend.',
            'option_name' => $option_name
        )
    );

    // --- Shortcode Information Section ---
    $shortcode_section_id = 'my_wc_enh_shortcode_info_section';
    add_settings_section(
        $shortcode_section_id,
        __( 'Shortcode Usage', 'my-wc-enhancements' ),
        'my_wc_enh_shortcode_info_callback', // Callback for section description.
        $page_slug
    );
}
add_action( 'admin_init', 'my_wc_enh_settings_init' );

/**
 * Helper to get plugin options and ensure it's an array.
 * @param string $option_name The name of the option.
 * @return array The options array.
 */
function my_wc_enh_get_options($option_name) {
    $options = get_option($option_name);
    return is_array($options) ? $options : array();
}

/**
 * Callback function to render a toggle (checkbox) setting field.
 *
 * @param array $args Arguments passed to the field (id, label, option_name).
 */
function my_wc_enh_toggle_callback( $args ) {
    $option_name = isset($args['option_name']) ? $args['option_name'] : 'my_wc_enh_settings';
    $options = my_wc_enh_get_options($option_name);
    $id = esc_attr( $args['id'] );
    $label = esc_html( $args['label'] );
    // Check if the option is set and if its value is '1'.
    $checked = isset( $options[$id] ) ? checked( '1', $options[$id], false ) : '';
    echo '<label><input type="checkbox" name="' . esc_attr($option_name) . '[' . $id . ']" value="1" ' . $checked . ' /> ' . $label . '</label>';
}

/**
 * Callback function to render a standard text input setting field.
 *
 * @param array $args Arguments passed to the field (id, label, option_name).
 */
function my_wc_enh_text_input_callback( $args ) {
    $option_name = isset($args['option_name']) ? $args['option_name'] : 'my_wc_enh_settings';
    $options = my_wc_enh_get_options($option_name);
    $id = esc_attr( $args['id'] );
    $label = esc_html( $args['label'] );
    $value = isset( $options[$id] ) ? esc_attr( $options[$id] ) : '';
    echo '<input type="text" id="' . $id . '" name="' . esc_attr($option_name) . '[' . $id . ']" value="' . $value . '" class="regular-text" />';
    if ( ! empty( $label ) ) {
        echo '<p class="description">' . $label . '</p>';
    }
}

/**
 * Callback function to render a textarea setting field.
 *
 * @param array $args Arguments passed to the field (id, label, option_name).
 */
function my_wc_enh_textarea_callback( $args ) {
    $option_name = isset($args['option_name']) ? $args['option_name'] : 'my_wc_enh_settings';
    $options = my_wc_enh_get_options($option_name);
    $id = esc_attr( $args['id'] );
    $label = esc_html( $args['label'] );
    $value = isset( $options[$id] ) ? esc_textarea( $options[$id] ) : '';
    echo '<textarea id="' . $id . '" name="' . esc_attr($option_name) . '[' . $id . ']" rows="10" cols="70" class="large-text code">' . $value . '</textarea>';
    if ( ! empty( $label ) ) {
        echo '<p class="description">' . $label . '</p>';
    }
}

/**
 * Callback function to render a color picker setting field.
 * Uses WordPress's built-in `wp-color-picker`.
 *
 * @param array $args Arguments passed to the field (id, option_name).
 */
function my_wc_enh_color_picker_callback( $args ) {
    $option_name = isset($args['option_name']) ? $args['option_name'] : 'my_wc_enh_settings';
    $options = my_wc_enh_get_options($option_name);
    $id = esc_attr( $args['id'] );
    $value = isset( $options[$id] ) ? esc_attr( $options[$id] ) : '';
    // The 'my-wc-enh-color-field' class is used by admin-script.js to initialize the color picker.
    echo '<input type="text" class="my-wc-enh-color-field" id="' . $id . '" name="' . esc_attr($option_name) . '[' . $id . ']" value="' . $value . '" data-default-color="" />';
}

/**
 * Callback function for the Custom CSS Settings section description.
 */
function my_wc_enh_css_section_callback() {
    echo '<p>Customize the colors for various elements of the plugin. Changes will be reflected in the live preview below.</p>';
}

/**
 * Callback function for the Advanced Customizations section description.
 */
function my_wc_enh_advanced_section_callback() {
    echo '<p>Add custom CSS code to further style the plugin\'s elements or other parts of your site.</p>';
}

/**
 * Callback function for the Shortcode Information section description.
 */
function my_wc_enh_shortcode_info_callback() {
    ?>
    <p>Use the following shortcode to display the Cash on Delivery status badge anywhere on your site (e.g., in product descriptions, pages, or posts):</p>
    <pre><code>[cod_status_badge]</code></pre>
    <p>This shortcode will dynamically show whether Cash on Delivery is available for the current product (if on a product page) or based on cart contents (if on a page like cart/checkout where product context is available).</p>
    <?php
}

/**
 * Renders the main HTML for the plugin's settings page.
 * This includes the form for settings and the live CSS preview.
 */
function my_wc_enh_settings_page_callback() {
    $option_group = 'my_wc_enh_settings_group'; // Must match the one in register_setting()
    $page_slug = 'my_wc_enh_settings_page';     // Must match the page slug used in add_settings_section()
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields( $option_group ); // Output hidden form fields for the settings group.
            do_settings_sections( $page_slug ); // Output all sections and fields for this page.
            submit_button( 'Save Changes' );    // Output save changes button.
            ?>
        </form>

        <hr>

        <h2>CSS Live Preview</h2>
        <div id="css-preview-container">
            <h3>COD Badge Preview:</h3>
            <div id="cod-badge-preview-available" class="cod-badge-preview-common">
                <span style="font-size: 18px;">✅</span>
                <div>
                    <strong>Cash on Delivery available in Dhaka city only.</strong><br>
                    For outside Dhaka, a partial advance payment is required to confirm your order.
                </div>
            </div>
            <div id="cod-badge-preview-unavailable" class="cod-badge-preview-common" style="margin-top: 10px;">
                <span style="font-size: 18px;">❌</span>
                <div>
                    <strong>Cash on Delivery is unavailable for this item.</strong><br>
                    A minimum delivery fee must be paid in advance.
                </div>
            </div>

            <h3 style="margin-top: 20px;">Urgency Box Preview:</h3>
            <div id="urgency-box-preview" style="padding:10px;border-radius:6px;margin-bottom:15px;">
                ⚡ <strong>Hurry!</strong> This offer may expire soon. Complete your checkout now.<br>
                🕒 <strong>Offer ends in <span>15:00</span></strong>
            </div>

            <h3 style="margin-top: 20px;">Discount Breakdown Preview:</h3>
            <table class="wc-enh-preview-table">
                <tr>
                    <th id="preview-regular-price-label">Regular Price Total</th>
                    <td id="preview-regular-price-value"><del>৳1200.00</del></td>
                </tr>
                <tr>
                    <th id="preview-product-discount-label">Product Discount</th>
                    <td id="preview-product-discount-value">-৳200.00</td>
                </tr>
                <tr>
                    <th id="preview-coupon-discount-label">Coupon Discount</th>
                    <td id="preview-coupon-discount-value">-৳50.00</td>
                </tr>
                <tr>
                    <th id="preview-you-saved-label">🎉 You Saved</th>
                    <td id="preview-you-saved-value"><strong>৳250.00</strong></td>
                </tr>
            </table>

            <h3 style="margin-top: 20px;">Single Product Discount Preview:</h3>
            <p>
                <del id="preview-regular-price-single">৳100.00</del>
                <ins id="preview-sale-price-single">৳75.00</ins>
                <span class="discount-amount" id="preview-discount-amount-single">৳25.00 off</span>
            </p>

        </div>
    </div>
    <?php
}
