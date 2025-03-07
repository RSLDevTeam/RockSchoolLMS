<?php
function custom_register_soundslice_block() {
    register_block_type('custom/soundslice', array(
        'editor_script' => 'custom-soundslice-block',
        'render_callback' => 'custom_render_soundslice_block'
    ));
}
add_action('init', 'custom_register_soundslice_block');

function custom_render_soundslice_block($attributes) {
    if (empty($attributes['soundsliceId'])) {
        return '<p>Please enter a Soundslice ID.</p>';
    }
    return '<iframe src="https://www.soundslice.com/slices/' . esc_attr($attributes['soundsliceId']) . '/embed-mini/" width="100%" height="293" frameBorder="0"></iframe>';
}