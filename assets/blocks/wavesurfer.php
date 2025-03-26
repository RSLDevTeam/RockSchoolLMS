<?php 
function custom_register_wavesurfer_block() {
    register_block_type('custom/wavesurfer', array(
        'editor_script' => 'custom-wavesurfer-block',
        'render_callback' => 'custom_render_wavesurfer_block'
    ));
}
add_action('init', 'custom_register_wavesurfer_block');

function custom_render_wavesurfer_block($attributes) {
    if (empty($attributes['audioUrl'])) {
        return '<p>Please select an audio file.</p>';
    }
    return do_shortcode('[wavesurfer audio_url="' . esc_url($attributes['audioUrl']) . '"]');
}