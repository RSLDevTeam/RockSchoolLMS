<?php

add_action('wp_ajax_generate_presigned_url_callback', 'generate_presigned_url_callback');
add_action('wp_ajax_nopriv_generate_presigned_url_callback', 'generate_presigned_url_callback');
defined( 'ABSPATH' ) || exit;
function generate_presigned_url_callback() {
    if (!isset($_POST['file_name']) || !isset($_POST['folder'])) {
        wp_send_json_error('Missing file name or folder');
        wp_die();
    }
    $file_name = sanitize_file_name($_POST['file_name']);
    $folder = sanitize_text_field($_POST['folder']);

    $bucket = get_field('bucket_name', 'option');
    $region = get_field('wasabi_region', 'option');
    $accessKey = get_field('wasabi_access_key', 'option');
    $secretKey = get_field('wasabi_secret_key', 'option');
    $endpointPath = get_field('endpoint', 'option');
    $endpoint = 'https://' . $endpointPath;


    $s3Client = new Aws\S3\S3Client([
        'version'     => 'latest',
        'region'      => $region,
        'endpoint'    => $endpoint,
        'use_path_style_endpoint' => true,
        'credentials' => [
            'key'    => $accessKey,
            'secret' => $secretKey,
        ],
    ]);

    $key = rtrim($folder, '/') . '/' . $file_name;

    try {
        $cmd = $s3Client->getCommand('PutObject', [
            'Bucket' => $bucket,
            'Key'    => $key,
        ]);

        $request = $s3Client->createPresignedRequest($cmd, '+10 minutes');
        $presignedUrl = (string) $request->getUri();

        wp_send_json_success(['url' => $presignedUrl]);
    } catch (Exception $e) {
      error_log($e->getMessage());
        wp_send_json_error($e->getMessage());
    }
    wp_die();
}
