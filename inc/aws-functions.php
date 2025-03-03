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
    echo '<input type="text" name="aws_options[access_key]" value="' . esc_attr($options['access_key'] ?? '') . '">';
}

function aws_field_secret_key_cb() {
    $options = get_option('aws_options');
    echo '<input type="text" name="aws_options[secret_key]" value="' . esc_attr($options['secret_key'] ?? '') . '">';
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


function project_ajax_s3_file_upload_handler() {
    if (empty($_POST['file_urls']) || empty($_POST['homework_id'])) {
        wp_send_json_error('Invalid submission.');
        wp_die();
    }
    
    $homework_id = intval($_POST['homework_id']);
    $uploaded_files = $_POST['file_urls'];

    foreach ($uploaded_files as $file_url) {
        add_post_meta($homework_id, 'project_file_url', $file_url);
    }

    wp_send_json_success('<ul>' . implode('', array_map(fn($url) => '<li><a href="' . esc_url($url) . '" target="_blank">' . basename($url) . '</a></li>', $uploaded_files)) . '</ul>');
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

// Project submission shortcode
function project_submission_ajax_s3_shortcode() {
    ob_start();
    global $post;
    $homework_id = get_the_ID();
    $submitted_files = get_post_meta($homework_id, 'project_file_url');
    $aws_options = get_option('aws_options');
    ?>
    <div id="project-upload-form-ajax">

        <?php if (!empty($submitted_files)): ?>

            <h2><?php _e('Submitted files', 'rslfranchise'); ?></h2>
            <ul>
                <?php foreach ($submitted_files as $file_url): ?>

                    <?php
                    $file_key = str_replace('https://s3.eu-west-1.wasabisys.com/' . $aws_options['bucket_name'] . '/', '', $file_url);
                    $presigned_url = generate_presigned_view_url($file_key);
                    ?>
                    <li><a href="<?php echo esc_url($presigned_url); ?>" target="_blank"><?php echo basename($file_url); ?></a></li>


                <?php endforeach; ?>
            </ul>

        <?php else: ?>

            <h2><?php _e('Upload files', 'rslfranchise'); ?></h2>

            <p><?php _e('Use the form below to select video, audio or other files from your device to submit to your instructor as part of this assignment.', 'rslfranchise'); ?></p>

            <input type="file" id="project_files_ajax" multiple>
            <input type="hidden" id="homework_id_ajax" value="<?php echo esc_attr($homework_id); ?>">
            <button id="upload_files_btn_ajax"><?php _e('Upload', 'rslfranchise'); ?></button>
            <div id="upload_status_ajax"></div>

            <div class="upload-status-container">
                <div id="upload_status_ajax"></div>
                <div id="progress_bar_ajax" style="width: 0%; height: 20px; background-color: #4cadc2; margin-top: 0.5em;"></div>

                <div id="overall_progress_container" style="display: none; margin-top: 1em;">
                    <div id="overall_upload_status_ajax"><?php _e('Overall Progress', 'rslfranchise'); ?></div>
                    <div id="overall_progress_bar_ajax" style="width: 0%; height: 20px; background-color: #4cadc2; margin-top: 0.5em;"></div>
                </div>
            </div>
            <div id="uploaded_files_list_ajax"></div>

        <?php endif; ?>

    </div>

    <script type="text/javascript">
        jQuery(document).ready(function($) {

            var homeworkID = $("#homework_id_ajax").val();
            var fileUrls = []; // Store URLs and names of uploaded files
            var filesToUpload = []; // Store files selected to upload

            // Event handler for 'Submit files' button
            $("#upload_files_btn_ajax").click(function() {

                console.log("Submit files button clicked");

                var files = $("#project_files_ajax")[0].files;

                if (files.length === 0) {
                    alert('Please select one or more files to upload.');
                    return; // Stop the function from continuing if no files are selected
                }

                // Add selected files to filesToUpload array
                for (var i = 0; i < files.length; i++) {
                    filesToUpload.push(files[i]);
                }

                // Clear the file input
                $("#project_files_ajax").val('');

                // Start uploading files
                uploadFiles();
            });

            // Function to upload files in filesToUpload array
            var uploadFiles = function() {

                console.log("Starting to upload files");

                // Make a copy of filesToUpload and clear it for future files
                var filesToProcess = filesToUpload.slice();
                filesToUpload = [];

                var totalFiles = filesToProcess.length;
                var filesUploaded = 0;

                var overallProgress = 0;

                // **Show the upload status elements and reset progress bars**
                $("#upload_status_ajax").show();
                $("#progress_bar_ajax").show().width('0%'); // Reset individual progress bar
                $("#overall_progress_bar_ajax").width('0%'); // Reset overall progress bar
                $("#overall_progress_container").hide(); // Hide overall progress initially

                // **Add 'visible' class to '.upload-status-container'**
                $('.upload-status-container').addClass('visible');

                // Show the overall progress bar if more than one file
                if (totalFiles > 1) {
                    $("#overall_progress_container").show();
                }

                var updateOverallProgress = function(fileIndex, fileProgress) {
                    overallProgress = ((fileIndex + fileProgress) / totalFiles) * 100;
                    $("#overall_progress_bar_ajax").width(overallProgress + "%");
                    $("#overall_upload_status_ajax").text("Overall Progress: " + Math.round(overallProgress) + "%");
                };

                var uploadFile = function(fileIndex) {
                    if (fileIndex >= totalFiles) {
                        // All files have been uploaded

                        // **Hide the upload status elements and remove 'visible' class**
                        $("#upload_status_ajax").hide();
                        $("#progress_bar_ajax").hide();
                        $("#overall_progress_container").hide();
                        $('.upload-status-container').removeClass('visible');

                        showFilesAndSubmitButton();
                        return;
                    }

                    var file = filesToProcess[fileIndex];
                    var fileName = file.name;

                    console.log("Uploading file: " + fileName);

                    // Update upload status
                    $("#upload_status_ajax").text("Uploading: " + fileName);

                    // Get the pre-signed URL for each file
                    $.ajax({
                        url: "<?php echo admin_url('admin-ajax.php'); ?>",
                        type: 'POST',
                        data: {
                            'action': 'generate_presigned_url',
                            'file_name': file.name,
                            'homework_id': homeworkID
                        },
                        success: function(response) {
                            if(response.success) {
                                var presignedUrl = response.data.url;

                                // Perform the actual upload to S3
                                $.ajax({
                                    url: presignedUrl,
                                    type: 'PUT',
                                    data: file,
                                    contentType: file.type,
                                    processData: false,
                                    success: function() {
                                        console.log("File uploaded successfully: " + fileName);

                                        fileUrls.push({
                                            url: presignedUrl.split('?')[0], // Store the file URL without query parameters
                                            name: fileName
                                        });
                                        filesUploaded++;
                                        uploadFile(fileIndex + 1); // Upload next file
                                    },
                                    error: function() {
                                        alert('Error uploading file to S3.');
                                    },
                                    xhr: function() {
                                        var xhr = new window.XMLHttpRequest();
                                        xhr.upload.addEventListener("progress", function(evt) {
                                            if (evt.lengthComputable) {
                                                var percentComplete = evt.loaded / evt.total;
                                                var percentage = Math.round(percentComplete * 100) + "%";
                                                $("#progress_bar_ajax").width(percentage);
                                                $("#upload_status_ajax").text("Uploading: " + fileName + " (" + percentage + ")");
                                                if (totalFiles > 1) {
                                                    updateOverallProgress(fileIndex, percentComplete / 100);
                                                }
                                            }
                                        }, false);
                                        return xhr;
                                    }
                                });
                            } else {
                                alert('Error generating pre-signed URL.');
                            }
                        },
                        error: function() {
                            alert('Error generating pre-signed URL.');
                        }
                    });
                };

                // Start uploading the first file
                uploadFile(0);
            };

            var showFilesAndSubmitButton = function() {
                console.log("All files uploaded, showing files and submit button");

                // Show the list of uploaded files and 'Submit' button
                updateFilesList();

                // Add the 'visible' class to show the files list
                $("#uploaded_files_list_ajax").addClass("visible");
            };

            // Function to update the displayed list of files
            var updateFilesList = function() {
                console.log("Updating files list");

                var filesListHtml = '<div class="review-files"><h3 class="ld-file-upload-heading"><?php _e('Review your files', 'rslfranchise'); ?></h3><div><?php _e('Please check the files you have uploaded. You can remove files from the list below or upload more files if you need.', 'rslfranchise'); ?></div><ul>';

                for (var i = 0; i < fileUrls.length; i++) {
                    var fileName = fileUrls[i].name;
                    filesListHtml += '<li>' + fileName + ' <a href="#" class="remove-file" data-index="' + i + '">[x]</a></li>';
                }

                filesListHtml += '</ul></div>';

                // Check if there are any files left
                if (fileUrls.length > 0) {
                    filesListHtml += '<button id="submit_uploaded_files_btn"><?php _e('Submit', 'rslfranchise'); ?></button><div class="upload-form-introduction"><?php _e('Once you click submit you will not be able to modify your submission', 'rslfranchise'); ?></div>';
                } else {
                    // No files left, hide the files list
                    $("#uploaded_files_list_ajax").removeClass("visible");
                }

                $("#uploaded_files_list_ajax").html(filesListHtml);

                // Add click handler for remove file links
                $(".remove-file").off('click').on('click', function(e) {
                    e.preventDefault();

                    var index = $(this).data('index');
                    console.log("Removing file at index: " + index);

                    fileUrls.splice(index, 1);

                    updateFilesList();
                });

                // Add click handler for the 'Submit' button if it exists
                if ($("#submit_uploaded_files_btn").length > 0) {
                    $("#submit_uploaded_files_btn").off('click').on('click', function() {
                        console.log("Submit button clicked");

                        callPhpHandler();
                    });
                }
            };

            var callPhpHandler = function() {
                // Prepare the array of file URLs to send to the server
                var fileUrlArray = fileUrls.map(function(item) {
                    return item.url;
                });

                if (fileUrlArray.length === 0) {
                    alert('No files to submit.');
                    return;
                }

                console.log("Sending files to server for project creation");

                // Call PHP handler
                $.ajax({
                    url: "<?php echo admin_url('admin-ajax.php'); ?>",
                    type: "POST",
                    data: {
                        'action': 'project_ajax_s3_file_upload',
                        'file_urls': fileUrlArray,
                        'homework_id': homeworkID
                    },
                    success: function(response) {
                        console.log("Server response received");

                        var container = $("#project-upload-form-ajax");
                        container.html("<div class='thank-you-notice'><?php _e('Thank you for submitting your files.', 'rslfranchise'); ?></div>");
                        container.append(response.data);

                        // Refresh page to show updated status
                        location.reload();

                    },
                    error: function() {
                        alert('Error in post creation.');
                    }
                });
            };

        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('project_submission_ajax_s3', 'project_submission_ajax_s3_shortcode');
