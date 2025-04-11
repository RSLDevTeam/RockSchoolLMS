<?php

use Aws\S3\S3Client;

function getUserWasabiFiles($folder = 'Rockschool') {
    $bucket = get_field('bucket_name', 'option');
    $region = get_field('wasabi_region', 'option');
    $accessKey = get_field('wasabi_access_key', 'option');
    $secretKey = get_field('wasabi_secret_key', 'option');
    $endpointPath = get_field('endpoint', 'option');
    $endpoint = 'https://' . $endpointPath;

    // Decode the folder name and ensure it's properly formatted
    $folder = urldecode($folder);  // Ensure it's URL-decoded
    $prefix = rtrim(trim($folder, '/'), '/') . '/';  // Ensure trailing slash for folder

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

        $folders = [];
        $files = [];
        
        // Loop through subfolders (CommonPrefixes)
        if (isset($result['CommonPrefixes'])) {
            foreach ($result['CommonPrefixes'] as $f) {
                $folderName = basename($f['Prefix']);
                $fullFolderPath = $f['Prefix'];  // Full path of the folder
                $folders[] = [
                    'name' => $folderName,
                    'path' => $fullFolderPath
                ];
            }
        }
        
        // Loop through files (Contents)
        if (isset($result['Contents'])) {
            foreach ($result['Contents'] as $object) {
                $fileName = basename($object['Key']);
                $fullFilePath = $object['Key'];  // Full path of the file
                if ($fileName && $object['Key'] !== $prefix) {
                    $files[] = [
                        'name' => $fileName,
                        'path' => $fullFilePath
                    ];
                }
            }
        }
        

        return [
            'folders'    => $folders,
            'files'      => $files,
        ];

    } catch (Aws\Exception\AwsException $e) {
        error_log("Wasabi Error: " . $e->getMessage());
        return [
            'folders'    => [],
            'files'      => [],
            'error'      => $e->getMessage()
        ];
    }
}



// Function to get file icon class
function getFileIcon($file) {
  $ext = pathinfo($file, PATHINFO_EXTENSION);
  $icons = [
      'pdf' => 'fa-file-pdf-o',
      'txt' => 'fa-file-text-o',
      'jpg' => 'fa-file-image-o',
      'jpeg'=> 'fa-file-image-o',
      'png' => 'fa-file-image-o',
      'doc' => 'fa-file-word-o',
      'docx' => 'fa-file-word-o',
      'xlsx' => 'fa-file-excel-o',
      'csv' => 'fa-file-excel-o',
      'zip' => 'fa-file-archive-o'
  ];
  return $icons[$ext] ?? 'fa-file';
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
