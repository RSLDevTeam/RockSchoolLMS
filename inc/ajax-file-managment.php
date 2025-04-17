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

//Function to downlad the file from Wasabi
function downloadFileFromWasabi($file_name, $file_path) {
    $bucketName = get_field('bucket_name', 'option');
    $region = get_field('wasabi_region', 'option');
    $accessKey = get_field('wasabi_access_key', 'option');
    $secretKey = get_field('wasabi_secret_key', 'option');
    $endpointPath = get_field('endpoint', 'option');
    $endpoint = 'https://' . $endpointPath;

    // Set up Wasabi S3 client
    $s3Client = new Aws\S3\S3Client([
        'version'     => 'latest',
        'region'      => $region,
        'endpoint'    => $endpoint,
        'credentials' => [
            'key'    => $accessKey,
            'secret' => $secretKey,
        ],
        'use_path_style_endpoint' => true,
    ]);

    try {
        // Get the file object from Wasabi S3
        $result = $s3Client->getObject([
            'Bucket' => $bucketName,
            'Key'    => $file_path
        ]);

        // Get content type from the result
        $contentType = $result['ContentType'];

        // Output appropriate headers for the file download
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
        header('Content-Length: ' . $result['ContentLength']);
        header('Cache-Control: no-cache, no-store, must-revalidate'); // Prevent caching of the file

        // Clean any previous output to avoid corruption
        ob_clean();
        flush();

        // Send the file content to the browser
        echo $result['Body']; // Directly output the file content

        // End the script to prevent additional output
        exit;
    } catch (Aws\Exception\AwsException $e) {
        echo "Error downloading file: " . $e->getMessage();
    }
}

//Ajax Function to delete the file/folder
add_action('wp_ajax_delete_file_folder', 'delete_file_folder');
add_action('wp_ajax_nopriv_delete_file_folder', 'delete_file_folder');
function delete_file_folder() {
    if (!isset($_POST['type']) || !isset($_POST['path'])) {
        wp_send_json_error('Missing file name or folder');
        wp_die();
    }
    $type = sanitize_text_field($_POST['type']);
    $path = sanitize_text_field($_POST['path']);    
    if (!empty($type) && !empty($path)) {
        
        $response = deleteFolderFilesInWasabi($type, $path);

        if ($response) {
            wp_send_json_success('File/Folder deleted successfully');
        } else {
            wp_send_json_error('Failed to delete file/folder');
        }
    }
}


