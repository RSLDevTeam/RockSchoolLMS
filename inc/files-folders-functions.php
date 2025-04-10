<?php

use Aws\S3\S3Client;

function getUserWasabiFiles($folder = 'Rockschool') {
    $bucket = get_field('bucket_name', 'option');
    $region = get_field('wasabi_region', 'option');
    $accessKey = get_field('wasabi_access_key', 'option');
    $secretKey = get_field('wasabi_secret_key', 'option');
    $endpointPath = get_field('endpoint', 'option');
    $endpoint = 'https://' . $endpointPath;

    // Construct prefix (path inside S3)
    $prefix = trim('/' . ltrim($folder, '/'), '/') . '/';

    // Create S3 client
    $s3 = new S3Client([
        'version'     => 'latest',
        'region'      => $region,
        'endpoint'    => $endpoint,
        'credentials' => [
            'key'    => $accessKey,
            'secret' => $secretKey,
        ],
        'use_path_style_endpoint' => true
    ]);

    try {
        $result = $s3->listObjectsV2([
            'Bucket'    => $bucket,
            'Prefix'    => $prefix,
            'Delimiter' => '/',
        ]);

        $folders = [];
        $files = [];

        if (isset($result['CommonPrefixes'])) {
            foreach ($result['CommonPrefixes'] as $f) {
                $folders[] = basename($f['Prefix']);
            }
        }

        if (isset($result['Contents'])) {
            foreach ($result['Contents'] as $object) {
                $fileName = basename($object['Key']);
                if ($fileName && $object['Key'] !== $prefix) {
                    $files[] = $fileName;
                }
            }
        }

        // Generate breadcrumbs
        $breadcrumbs = [];
        $parts = explode('/', trim($folder, '/'));
        $path = '';
        foreach ($parts as $part) {
            $path .= $part . '/';
            $breadcrumbs[] = [
                'label' => $part,
                'path' => $path
            ];
        }

        return [
            'folders'    => $folders,
            'files'      => $files,
            'breadcrumbs' => $breadcrumbs,
        ];

    } catch (Aws\Exception\AwsException $e) {
        error_log("Wasabi Error: " . $e->getMessage());
        return [
            'folders'    => [],
            'files'      => [],
            'breadcrumbs' => [],
            'error'      => $e->getMessage()
        ];
    }
}


// Function to get file icon class
function getFileIcon($file) {
  $ext = pathinfo($file, PATHINFO_EXTENSION);
  $icons = [
      'pdf' => 'fa-file-pdf-o text-danger',
      'txt' => 'fa-file-text-o text-primary',
      'jpg' => 'fa-file-image-o text-success',
      'jpeg'=> 'fa-file-image-o text-success',
      'png' => 'fa-file-image-o text-success',
      'doc' => 'fa-file-word-o text-info',
      'docx' => 'fa-file-word-o text-info',
      'xlsx' => 'fa-file-excel-o text-success',
      'csv' => 'fa-file-excel-o text-success',
      'zip' => 'fa-file-archive-0 text-warning'
  ];
  return $icons[$ext] ?? 'fa-file text-secondary';
}


function createNewFolderInWasabi($folder_path) {
    $bucket = get_field('bucket_name', 'option');
    $region = get_field('wasabi_region', 'option');
    $accessKey = get_field('wasabi_access_key', 'option');
    $secretKey = get_field('wasabi_secret_key', 'option');
    $endpointPath = get_field('endpoint', 'option');
    $endpoint = 'https://' . $endpointPath;

    try {
        $s3Client = new S3Client([
            'version'     => 'latest',
            'region'      => $region,
            'endpoint'    => $endpoint,
            'credentials' => [
                'key'    => $accessKey,
                'secret' => $secretKey,
            ],
            'use_path_style_endpoint' => true,
        ]);

        // Ensure folder_path ends with `/`
        $folder_key = rtrim($folder_path, '/') . '/';

        // Create "folder" by uploading empty object with trailing slash
        $s3Client->putObject([
            'Bucket' => $bucket,
            'Key'    => $folder_key,
            'Body'   => '',
        ]);

    } catch (Exception $e) {
        error_log('Error creating folder in Wasabi: ' . $e->getMessage());
    }
}
