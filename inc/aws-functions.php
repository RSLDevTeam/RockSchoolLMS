<?php
/**
 * AWS Functions 
 */

defined( 'ABSPATH' ) || exit;

function generate_presigned_url() {
    if (!defined('DOING_AJAX') || !DOING_AJAX) wp_die('No direct access allowed.');

    $fileName = isset($_POST['file_name']) ? sanitize_file_name($_POST['file_name']) : '';
    $homework_id = isset($_POST['homework_id']) ? intval($_POST['homework_id']) : 0;
    if (empty($fileName) || !$homework_id) {
        wp_send_json_error('Invalid request.');
        wp_die();
    }

    $bucketName = get_field('bucket_name', 'option');
    $keyName = 'homework/' . $homework_id . '/' . $fileName;
    $region = get_field('wasabi_region', 'option');
    $accessKey = get_field('wasabi_access_key', 'option');
    $secretKey = get_field('wasabi_secret_key', 'option');
    $endpointPath = get_field('endpoint', 'option');
    $endpoint = 'https://' . $endpointPath; // Wasabi's S3-compatible endpoint

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


