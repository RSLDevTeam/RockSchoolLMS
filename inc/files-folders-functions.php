<?php

use Aws\S3\S3Client;

function getWasabiClient() {
    $region = get_field('wasabi_region', 'option');
    $accessKey = get_field('wasabi_access_key', 'option');
    $secretKey = get_field('wasabi_secret_key', 'option');
    $endpointPath = get_field('endpoint', 'option');
    $endpoint = 'https://' . $endpointPath;

    return new S3Client([
        'version'     => 'latest',
        'region'      => $region,
        'endpoint'    => $endpoint,
        'credentials' => [
            'key'    => $accessKey,
            'secret' => $secretKey,
        ],
        'use_path_style_endpoint' => true
    ]);
}


function getUserWasabiFiles($folder = 'Rockschool') {
    $bucket = get_field('bucket_name', 'option');
    $folder = urldecode($folder);
    $prefix = rtrim(trim($folder, '/'), '/') . '/';

    $s3 = getWasabiClient();

    try {
        $result = $s3->listObjectsV2([
            'Bucket'    => $bucket,
            'Prefix'    => $prefix,
            'Delimiter' => '/',
        ]);

        $folders = [];
        $files = [];

        // Loop through subfolders
        if (isset($result['CommonPrefixes'])) {
            foreach ($result['CommonPrefixes'] as $f) {
                $folderPrefix = $f['Prefix'];
                $folderName = basename($folderPrefix);

                // Optional: get size and last modified time for folders
                $folderMeta = $s3->listObjectsV2([
                    'Bucket' => $bucket,
                    'Prefix' => $folderPrefix,
                ]);

                $totalSize = 0;
                $lastModified = null;

                if (isset($folderMeta['Contents'])) {
                    foreach ($folderMeta['Contents'] as $item) {
                        $totalSize += $item['Size'];
                        if (!$lastModified || strtotime($item['LastModified']) > strtotime($lastModified)) {
                            $lastModified = $item['LastModified'];
                        }
                    }
                }

                $folders[] = [
                    'name' => $folderName,
                    'path' => $folderPrefix,
                    'size' => $totalSize,
                    'last_modified' => $lastModified,
                ];
            }
        }

        // Loop through files
        if (isset($result['Contents'])) {
            foreach ($result['Contents'] as $object) {
                $fileName = basename($object['Key']);
                $fullFilePath = $object['Key'];

                if ($fileName && $object['Key'] !== $prefix) {
                    $files[] = [
                        'name' => $fileName,
                        'path' => $fullFilePath,
                        'size' => $object['Size'],
                        'last_modified' => $object['LastModified'],
                    ];
                }
            }
        }

        return [
            'folders' => $folders,
            'files'   => $files,
        ];

    } catch (Aws\Exception\AwsException $e) {
        error_log("Wasabi Error: " . $e->getMessage());
        return [
            'folders' => [],
            'files'   => [],
            'error'   => $e->getMessage(),
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

    try {
        $s3Client = getWasabiClient();

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


function getTopLevelFolder($path) {
    $segments = explode('/', trim($path, '/'));
    if($segments[0] == 'franchises') {
        array_shift($segments); 
    }
    return $segments[0] ?? '';
}
