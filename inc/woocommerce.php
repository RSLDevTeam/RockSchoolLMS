<?php
/**
 * Woocommerce Functions 
 */

defined( 'ABSPATH' ) || exit;

// Woo styles
function custom_enqueue_woocommerce_styles() {
    if (function_exists('is_woocommerce') && (is_woocommerce() || is_cart() || is_checkout() || is_account_page())) {
        wp_enqueue_style(
            'custom-woocommerce-style',
            get_stylesheet_directory_uri() . '/css/woocommerce-custom.min.css',
            array(),
            filemtime(get_stylesheet_directory() . '/css/woocommerce-custom.min.css')
        );
    }
}
add_action('wp_enqueue_scripts', 'custom_enqueue_woocommerce_styles');

// account nav
remove_action( 'woocommerce_account_navigation', 'woocommerce_account_navigation' );
add_action( 'woocommerce_account_navigation', function() {
    $items = wc_get_account_menu_items();
    $current_endpoint = WC()->query->get_current_endpoint();

    echo '<nav class="woocommerce-MyAccount-navigation" aria-label="Account pages">';
    echo '<ul class="nav nav-tabs">';

    foreach ( $items as $endpoint => $label ) {
        $url = esc_url( wc_get_account_endpoint_url( $endpoint ) );
        
        $is_active = ( $endpoint === $current_endpoint || ( $endpoint === 'dashboard' && ! is_wc_endpoint_url() ) );
        $active_class = $is_active ? 'active' : '';

        echo '<li class="nav-item">';
        echo '<a class="nav-link ' . $active_class . '" href="' . $url . '" aria-current="' . ( $is_active ? 'page' : 'false' ) . '">';
        echo esc_html( $label );
        echo '</a></li>';
    }

    echo '</ul>';
    echo '</nav>';
});

// modify account page wrapper
add_action('template_redirect', function() {
    ob_start(function($html) {
        if ( is_account_page() ) {
            $html = preg_replace(
                '/<div class="woocommerce(.*?)">/',
                '<div class="woocommerce dashboard-section$1">',
                $html
            );
        }
        return $html;
    });
});

// title in single product
function move_product_title_to_top() {
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
    add_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 1);
}
add_action('woocommerce_before_single_product', 'move_product_title_to_top');

// move decription on PDP
function move_product_excerpt_to_bottom() {
    remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs');
    add_action('woocommerce_after_add_to_cart_form', 'woocommerce_output_product_data_tabs', 10);
}
add_action('woocommerce_before_single_product', 'move_product_excerpt_to_bottom');

// move meta
function move_product_meta_below_title() {
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
    add_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 6);
}
add_action('woocommerce_before_single_product', 'move_product_meta_below_title');

// no zoom images
function disable_woocommerce_image_zoom() {
    remove_theme_support('wc-product-gallery-zoom');
}
add_action('after_setup_theme', 'disable_woocommerce_image_zoom');

// dashboard naming convention
function rename_my_account_dashboard_tab( $menu_items ) {
    $menu_items['dashboard'] = 'Overview';
    return $menu_items;
}
add_filter( 'woocommerce_account_menu_items', 'rename_my_account_dashboard_tab' );

// from price range tweak
function custom_variable_product_price_from_only( $price, $product ) {
    if ( $product->is_type( 'variable' ) ) {
        $prices = $product->get_variation_prices( true );
        $min_price = current( $prices['price'] );

        if ( $min_price ) {
            $price = wc_price( $min_price );
            $price = sprintf( /* translators: 'From' price label */ __( 'From %s', 'your-text-domain' ), $price );
        }
    }

    return $price;
}
add_filter( 'woocommerce_get_price_html', 'custom_variable_product_price_from_only', 10, 2 );

