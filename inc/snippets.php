<?php
/**
 * Template snippets
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Buttons
function render_acf_button($button_text, $button_link, $button_class = 'primary-button') {
    if ($button_text && $button_link) {
        echo '<a href="' . esc_url($button_link) . '">';
        echo '<button class="' . esc_attr($button_class) . '">' . esc_html($button_text) . '</button>';
        echo '</a>';
    }
}

