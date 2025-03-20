<?php
/**
 * AWS Functions 
 */

defined( 'ABSPATH' ) || exit;

// Global options panel for AWS credentials
function aws_options_menu() {
    add_menu_page(
        'Wasabi / AWS Settings',  // Page title
        'Wasabi / AWS Settings',  // Menu title
        'manage_options',         // Capability
        'aws-settings',           // Menu slug
        'aws_settings_page_html', // Callback function
        'dashicons-cloud',        // Icon
        99                        // Position
    );
}
add_action('admin_menu', 'aws_options_menu');

function aws_settings_init() {
    register_setting('aws-settings', 'aws_options');

    add_settings_section(
        'aws_settings_section',
        'AWS API Settings',
        'aws_settings_section_cb',
        'aws-settings'
    );

    add_settings_field('aws_bucket_name', 'Bucket Name', 'aws_field_bucket_name_cb', 'aws-settings', 'aws_settings_section');
    add_settings_field('aws_region', 'Region', 'aws_field_region_cb', 'aws-settings', 'aws_settings_section');
    add_settings_field('aws_access_key', 'Access Key', 'aws_field_access_key_cb', 'aws-settings', 'aws_settings_section');
    add_settings_field('aws_secret_key', 'Secret Key', 'aws_field_secret_key_cb', 'aws-settings', 'aws_settings_section');
}
add_action('admin_init', 'aws_settings_init');

function aws_settings_section_cb() {
    echo '<p>Enter your Wasabi / AWS API settings below. These are used for homework files uploaded.</p>';
}

function aws_field_bucket_name_cb() {
    $options = get_option('aws_options');
    echo '<input type="text" name="aws_options[bucket_name]" value="' . esc_attr($options['bucket_name'] ?? '') . '">';
}

function aws_field_region_cb() {
    $options = get_option('aws_options');
    echo '<input type="text" name="aws_options[aws_region]" value="' . esc_attr($options['aws_region'] ?? '') . '">';
}

function aws_field_access_key_cb() {
    $options = get_option('aws_options');
    echo '<input type="password" name="aws_options[access_key]" value="' . esc_attr($options['access_key'] ?? '') . '">';
}

function aws_field_secret_key_cb() {
    $options = get_option('aws_options');
    echo '<input type="password" name="aws_options[secret_key]" value="' . esc_attr($options['secret_key'] ?? '') . '">';
}

function aws_settings_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?= esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('aws-settings');
            do_settings_sections('aws-settings');
            submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php
}

// Wasabi-compatible pre-signed URL generation
function generate_presigned_url() {
    if (!defined('DOING_AJAX') || !DOING_AJAX) wp_die('No direct access allowed.');

    $fileName = isset($_POST['file_name']) ? sanitize_file_name($_POST['file_name']) : '';
    $homework_id = isset($_POST['homework_id']) ? intval($_POST['homework_id']) : 0;
    if (empty($fileName) || !$homework_id) {
        wp_send_json_error('Invalid request.');
        wp_die();
    }

    $aws_options = get_option('aws_options');
    $bucketName = $aws_options['bucket_name'];
    $keyName = 'homework/' . $homework_id . '/' . $fileName;
    $region = $aws_options['aws_region'];
    $accessKey = $aws_options['access_key'];
    $secretKey = $aws_options['secret_key'];
    $endpoint = 'https://s3.' . $region . '.wasabisys.com'; // Wasabi's S3-compatible endpoint

    try {
        $s3Client = new Aws\S3\S3Client([
            'version' => 'latest',
            'region' => $region,
            'endpoint' => $endpoint,
            'use_path_style_endpoint' => true, // Required for Wasabi
            'credentials' => ['key' => $accessKey, 'secret' => $secretKey],
        ]);

        $cmd = $s3Client->getCommand('PutObject', ['Bucket' => $bucketName, 'Key' => $keyName]);
        $request = $s3Client->createPresignedRequest($cmd, '+20 minutes');
        wp_send_json_success(['url' => (string) $request->getUri()]);
    } catch (Exception $e) {
        error_log($e->getMessage());
        wp_send_json_error('Error generating pre-signed URL.');
    }
    wp_die();
}
add_action('wp_ajax_generate_presigned_url', 'generate_presigned_url');
add_action('wp_ajax_nopriv_generate_presigned_url', 'generate_presigned_url');

// Upload handler
function project_ajax_s3_file_upload_handler() {
    if (empty($_POST['homework_id'])) {
        wp_send_json_error('Invalid submission. Missing homework ID.');
        wp_die();
    }
    
    $homework_id = intval($_POST['homework_id']);
    $uploaded_files = $_POST['file_urls'] ?? [];
    $response_text = $_POST['response_text'] ?? '';

    // Save response if provided
    if (!empty($response_text)) {
        update_field('response', $response_text, $homework_id); 
    }

    // Save uploaded files if provided
    if (!empty($uploaded_files) && is_array($uploaded_files)) {
        foreach ($uploaded_files as $file_url) {
            add_post_meta($homework_id, 'project_file_url', $file_url);
        }
    }

    send_homework_response_notification($homework_id);

    wp_send_json_success([
        'message' => 'Submission successful.',
        'uploaded_files' => $uploaded_files,
        'response_text' => $response_text
    ]);

    

    wp_die();
}
add_action('wp_ajax_project_ajax_s3_file_upload', 'project_ajax_s3_file_upload_handler');


// pre-signed view file URL
function generate_presigned_view_url($file_key) {
    $aws_options = get_option('aws_options');
    $bucketName = $aws_options['bucket_name'];
    $region = $aws_options['aws_region'];
    $accessKey = $aws_options['access_key'];
    $secretKey = $aws_options['secret_key'];
    $endpoint = 'https://s3.' . $region . '.wasabisys.com'; 

    try {
        $s3Client = new Aws\S3\S3Client([
            'version' => 'latest',
            'region' => $region,
            'endpoint' => $endpoint,
            'use_path_style_endpoint' => true, // Required for Wasabi
            'credentials' => ['key' => $accessKey, 'secret' => $secretKey],
        ]);

        $cmd = $s3Client->getCommand('GetObject', [
            'Bucket' => $bucketName,
            'Key' => $file_key
        ]);

        // Manually set headers 
        $request = $s3Client->createPresignedRequest($cmd, '+15 minutes', [
            'headers' => [
                'x-amz-content-sha256' => 'UNSIGNED-PAYLOAD',
                'x-amz-storage-class' => 'STANDARD'
            ]
        ]);

        $presigned_url = (string) $request->getUri();

        // Debug Output
        error_log("Generated Pre-Signed URL: " . $presigned_url);
        return $presigned_url;

    } catch (Exception $e) {
        error_log("Error generating pre-signed URL: " . $e->getMessage());
        return false;
    }
}


