<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Calculates the total regular price of all items in the cart.
 *
 * @return float The total regular price.
 */
function get_total_regular_price() {
    $total = 0;
    foreach (WC()->cart->get_cart() as $item) {
        $product = $item['data'];
        $qty = $item['quantity'];
        $total += $product->get_regular_price() * $qty;
    }
    return $total;
}

/**
 * Calculates the total discount amount from products that are on sale.
 *
 * @return float The total product discount.
 */
function get_total_product_discount() {
    $discount = 0;
    foreach (WC()->cart->get_cart() as $item) {
        $product = $item['data'];
        $qty = $item['quantity'];
        if ($product->is_on_sale()) {
            $regular = $product->get_regular_price();
            $sale = $product->get_sale_price();
            $discount += ($regular - $sale) * $qty;
        }
    }
    return $discount;
}

/**
 * Displays a custom breakdown of savings in the cart and checkout totals.
 * This includes regular price, product discount, coupon discount, and total saved.
 * Enabled/disabled via the 'enable_discount_breakdown' setting.
 */
add_action('woocommerce_cart_totals_before_order_total', 'show_custom_discount_breakdown', 1);
add_action('woocommerce_review_order_before_order_total', 'show_custom_discount_breakdown', 1);

function show_custom_discount_breakdown() {
    $options = get_option('my_wc_enh_settings');
    // Check if discount breakdown is enabled.
    if ( ! isset($options['enable_discount_breakdown']) || $options['enable_discount_breakdown'] != '1' ) {
        return;
    }

    $regular_total = get_total_regular_price();
    $product_discount = get_total_product_discount();
    $coupon_discount = WC()->cart->get_discount_total();
    $total_saved = $product_discount + $coupon_discount;

    // Use CSS variables for styling if custom styles are enabled, otherwise use inline styles.
    $use_custom_styles = isset($options['enable_custom_styles']) && $options['enable_custom_styles'] == '1';

    // 🔥 Regular Price (strikethrough)
    echo '<tr>
        <th ' . ($use_custom_styles ? 'class="regular-price-total-label"' : 'style="color:#6c757d;"') . '>Regular Price Total</th>
        <td ' . ($use_custom_styles ? 'class="regular-price-total-value"' : 'style="color:#6c757d;"') . '><del>৳' . number_format($regular_total, 2) . '</del></td>
    </tr>';

    // 🟠 Product Discount
    if ($product_discount > 0) {
        echo '<tr>
            <th ' . ($use_custom_styles ? 'class="product-discount-label"' : 'style="color:#dc3545;"') . '>Product Discount</th>
            <td ' . ($use_custom_styles ? 'class="product-discount-value"' : 'style="color:#dc3545;"') . '>-৳' . number_format($product_discount, 2) . '</td>
        </tr>';
    }

    // 🔵 Coupon Discount
    if ($coupon_discount > 0) {
        echo '<tr>
            <th ' . ($use_custom_styles ? 'class="coupon-discount-label"' : 'style="color:#007bff;"') . '>Coupon Discount</th>
            <td ' . ($use_custom_styles ? 'class="coupon-discount-value"' : 'style="color:#007bff;"') . '>-৳' . number_format($coupon_discount, 2) . '</td>
        </tr>';
    }

    // ✅ You Saved
    if ($total_saved > 0) {
        echo '<tr>
            <th ' . ($use_custom_styles ? 'class="you-saved-label"' : 'style="color:#28a745;"') . '>🎉 You Saved</th>
            <td ' . ($use_custom_styles ? 'class="you-saved-value"' : 'style="color:#28a745;"') . '><strong>৳' . number_format($total_saved, 2) . '</strong></td>
        </tr>';
    }
}

/**
 * Shows an urgency message with a countdown timer on the checkout page.
 * Enabled/disabled via the 'enable_checkout_urgency' setting.
 * Timer duration is customizable via 'urgency_timer_duration'.
 */
add_action('woocommerce_review_order_before_payment', 'show_urgency_message_with_timer');
function show_urgency_message_with_timer() {
    $options = get_option('my_wc_enh_settings');
    // Check if checkout urgency is enabled.
    if ( ! isset($options['enable_checkout_urgency']) || $options['enable_checkout_urgency'] != '1' ) {
        return;
    }

    // Get timer duration from settings, default to 15 minutes if not set or invalid.
    $duration_minutes = isset($options['urgency_timer_duration']) ? intval($options['urgency_timer_duration']) : 15;
    if ($duration_minutes <= 0) {
        $duration_minutes = 15;
    }
    $duration_seconds = $duration_minutes * 60;

    // Use CSS classes for styling if custom styles are enabled, otherwise use inline styles.
    $use_custom_styles = isset($options['enable_custom_styles']) && $options['enable_custom_styles'] == '1';
    ?>
    <div id="urgency-box" <?php echo $use_custom_styles ? 'class="urgency-box-style"' : 'style="padding:10px;background:#fff3cd;color:#856404;border:1px solid #ffeeba;border-radius:6px;margin-bottom:15px;"'; ?>>
        ⚡ <strong>Hurry!</strong> This offer may expire soon. Complete your checkout now.<br>
        🕒 <strong>Offer ends in <span id="timer"></span></strong>
    </div>

    <script>
        (function(){
            // Get duration from PHP, converted to seconds.
            let duration = <?php echo esc_js($duration_seconds); ?>;
            let display = document.getElementById('timer');
            let urgencyBox = document.getElementById('urgency-box');

            function startTimer() {
                let timer = duration, minutes, seconds;
                let interval = setInterval(function () {
                    minutes = parseInt(timer / 60, 10);
                    seconds = parseInt(timer % 60, 10);

                    minutes = minutes < 10 ? "0" + minutes : minutes;
                    seconds = seconds < 10 ? "0" + seconds : seconds;

                    display.textContent = minutes + ":" + seconds;

                    if (--timer < 0) {
                        clearInterval(interval);
                        display.textContent = "Expired";
                        // Apply expired styles directly or via class if needed.
                        if (urgencyBox) {
                            <?php if ($use_custom_styles) : ?>
                                urgencyBox.classList.remove('urgency-box-style');
                                urgencyBox.classList.add('urgency-box-expired-style'); // New class for expired state
                            <?php else : ?>
                                urgencyBox.style.background = "#f8d7da";
                                urgencyBox.style.color = "#721c24";
                                urgencyBox.style.borderColor = "#f5c6cb";
                            <?php endif; ?>
                            urgencyBox.innerHTML = '⏰ <strong>Offer expired</strong>. Please proceed to checkout or refresh the page to try again.';
                        }
                    }
                }, 1000);
            }

            // Ensure the timer starts only when the DOM is ready.
            if (display && urgencyBox) {
                startTimer();
            }
        })();
    </script>
    <?php
}
