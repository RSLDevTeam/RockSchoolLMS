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

// move decriptions and related on PDP
function move_pdp_elements() {

    remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs');
    remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);

    add_action('woocommerce_after_add_to_cart_form', 'woocommerce_output_product_data_tabs', 10);
    add_action('woocommerce_after_single_product', 'woocommerce_output_related_products', 10);

}
add_action('woocommerce_before_single_product', 'move_pdp_elements');

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

// bye bye woo breadcrumbs
add_action( 'init', function() {
    remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
});

// wrap the woo tings in article
add_action( 'woocommerce_before_main_content', 'rsl_open_article_wrapper', 25 );
add_action( 'woocommerce_after_main_content', 'rsl_close_article_wrapper', 50 );

function rsl_open_article_wrapper() {
    if ( is_shop() || is_product() || is_product_category() || is_product_tag() ) {
        echo '<article id="' . get_the_ID() . '" class="page">';
    }
}

function rsl_close_article_wrapper() {
    if ( is_shop() || is_product() || is_product_category() || is_product_tag() ) {
        echo '</article>';
    }
}

// thumbs in woo
add_filter( 'single_product_archive_thumbnail_size', function( $size ) {
    return 'book-small'; 
});

// procuct archive redux
add_action( 'woocommerce_before_shop_loop', 'rsl_display_product_categories_above_grid', 5 );

function rsl_display_product_categories_above_grid() {
    // Only run on main shop page or product archives
    if ( ! is_shop() && ! is_product_taxonomy() ) {
        return;
    }

    $current_term = get_queried_object();
    $parent_id = 0;

    if ( is_a( $current_term, 'WP_Term' ) && $current_term->taxonomy === 'product_cat' ) {
        $parent_id = $current_term->term_id; // Show subcategories of current category
    }

    $product_categories = get_terms( [
        'taxonomy'   => 'product_cat',
        'hide_empty' => true,
        'parent'     => $parent_id,
    ] );

    if ( empty( $product_categories ) || is_wp_error( $product_categories ) ) {
        return;
    }

    echo '<div class="shop-category-grid">';
    echo '<div class="row">';

    foreach ( $product_categories as $category ) {
        $thumbnail_id = get_term_meta( $category->term_id, 'thumbnail_id', true );
        $image_url = wp_get_attachment_url( $thumbnail_id );
        $link = get_term_link( $category );

        echo '<div class="col-md-4">';
        echo '<a href="' . esc_url( $link ) . '">';
        echo '<div class="shop-category-item">';
            if ( $image_url ) {
                echo '<div class="image-holder"><img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $category->name ) . '" /></div>';
            }
            echo '<div class="shop-category-item-inner">';
            echo '<h3>' . esc_html( $category->name ) . '</h3>';
            echo '<p>' . esc_html( $category->description ) . '</p>';
            echo '</div>';
        echo '</div>';
        echo '</a>';
        echo '</div>';
    }

    echo '</div>';
    echo '</div>';
}

// new woo sidebar (for filters)
function rsl_register_woocommerce_sidebar() {
    register_sidebar( [
        'name'          => __( 'WooCommerce Sidebar', 'your-theme-textdomain' ),
        'id'            => 'woocommerce_sidebar',
        'description'   => __( 'Widgets in this area will show on shop and product archive pages.', 'your-theme-textdomain' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s mb-4">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title mb-3">',
        'after_title'   => '</h3>',
    ] );
}
add_action( 'widgets_init', 'rsl_register_woocommerce_sidebar' );

// Add sidebar to archives (only if sidebar has widgets)
add_action( 'woocommerce_before_shop_loop', 'rsl_open_bootstrap_row_and_sidebar', 5 );
add_action( 'woocommerce_before_shop_loop', 'rsl_open_bootstrap_main_col', 6 );
add_action( 'woocommerce_after_shop_loop', 'rsl_close_bootstrap_row_layout', 20 );

function rsl_open_bootstrap_row_and_sidebar() {
    if ( is_shop() || is_product_taxonomy() ) {
        if ( is_active_sidebar( 'woocommerce_sidebar' ) ) {
            echo '<div class="row">';

            // Sidebar
            echo '<aside class="col-lg-3">';
            echo '<div class="dashboard-section filter-section">';
            dynamic_sidebar( 'woocommerce_sidebar' );
            echo '</div>';
            echo '</aside>';
        } else {
            // If no sidebar, open row + main content full width
            echo '<div class="container"><div class="row"><div class="col-12">';
        }
    }
}

function rsl_open_bootstrap_main_col() {
    if ( is_shop() || is_product_taxonomy() ) {
        if ( is_active_sidebar( 'woocommerce_sidebar' ) ) {
            echo '<div class="col-lg-9">';
        }
        // No else needed — full-width layout already opened above
    }
}

function rsl_close_bootstrap_row_layout() {
    if ( is_shop() || is_product_taxonomy() ) {
        echo '</div></div>'; 
    }
}

// category filter widget
class RSL_WC_Category_Filter_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'rsl_wc_category_filter',
            __('Filter by Category (Woo)', 'your-theme'),
            ['description' => __('Dropdown to filter products by category.', 'your-theme')]
        );
    }

    public function widget( $args, $instance ) {
        if ( ! is_shop() && ! is_product_taxonomy() ) return;

        $categories = get_terms([
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'parent'     => 0, // Or remove to include all
        ]);

        if ( empty( $categories ) || is_wp_error( $categories ) ) return;

        echo $args['before_widget'];
        echo $args['before_title'] . esc_html( $instance['title'] ?? 'Filter by Category' ) . $args['after_title'];

        echo '<form method="GET" action="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">';
        echo '<select name="product_cat" class="form-select mb-3" onchange="this.form.submit()">';
        echo '<option value="">' . esc_html__( 'All Categories', 'your-theme' ) . '</option>';

        foreach ( $categories as $category ) {
            $selected = isset($_GET['product_cat']) && $_GET['product_cat'] === $category->slug ? 'selected' : '';
            echo '<option value="' . esc_attr( $category->slug ) . '" ' . $selected . '>' . esc_html( $category->name ) . '</option>';
        }

        echo '</select>';
        echo '</form>';

        echo $args['after_widget'];
    }

    public function form( $instance ) {
        $title = $instance['title'] ?? 'Filter by Category';
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text"
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <?php
    }
}

function rsl_register_wc_category_filter_widget() {
    register_widget( 'RSL_WC_Category_Filter_Widget' );
}
add_action( 'widgets_init', 'rsl_register_wc_category_filter_widget' );

// sold out notice
function add_sold_out_badge() {
    global $product;
    if ( ! $product->is_in_stock() ) {
        echo '<div class="sold-out-badge"><div class="sold-out-badge-inner">' . esc_html__( 'Sold Out', 'rslfranchise' ) . '</div></div>';
    }
}
add_action( 'woocommerce_before_shop_loop_item_title', 'add_sold_out_badge', 10 ); 

// Woocommerce emails
add_action('init', function () {
    // Remove WooCommerce default email header and footer
    remove_action('woocommerce_email_header', 'woocommerce_email_header', 10);
    remove_action('woocommerce_email_footer', 'woocommerce_email_footer', 10);
});
add_filter('woocommerce_email_styles', '__return_empty_string');


// button in emails
add_action('woocommerce_email_after_order_table', 'add_course_access_link_to_all_emails', 10, 4);

function add_course_access_link_to_all_emails($order, $sent_to_admin, $plain_text, $email) {
    // Ensure we add the content after the order details in all customer emails
    if ($sent_to_admin) {
        return; // Skip admin emails
    }

    // Define the translated text for the button and link
    $login_text = __('Login to access course materials at:', 'your-text-domain');
    $button_text = __('Rockschool Backstage', 'your-text-domain');

    // Get the site URL for the login link
    $login_url = home_url('/');

    // Output the HTML content with translated strings
    echo '<p>' . esc_html($login_text) . '</p>';
    echo '<p><a href="' . esc_url($login_url) . '" style="text-decoration:none;">';
    echo '<button style="padding: 10px 20px; background-color: #26abe2; color: white; border: none; border-radius: 4px; cursor: pointer;">';
    echo esc_html($button_text);
    echo '</button></a></p>';
}
