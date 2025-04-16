<?php
/**
 * AWS Shortcode 
 */

defined( 'ABSPATH' ) || exit;

// Project submission shortcode
function project_submission_ajax_s3_shortcode() {
    ob_start();
    global $post;
    $homework_id = get_the_ID();
    $submitted_files = get_post_meta($homework_id, 'project_file_url');
    ?>

    <div id="project-upload-form-ajax">

        <p><?php _e('Use the form below to send your reponse. You can select video, audio or other files from your device to submit to your instructor as part of this assignment.', 'rslfranchise'); ?></p>
        <input type="file" id="project_files_ajax" multiple>
        <input type="hidden" id="homework_id_ajax" value="<?php echo esc_attr($homework_id); ?>">

        <textarea id="response_text_ajax" rows="4" cols="50"></textarea>
        <button id="submit_files_response_btn_ajax"><?php _e('Submit', 'rslfranchise'); ?></button>
        <div id="upload_status_ajax"></div>
        
        <div class="upload-status-container">
            <div id="progress_bar_ajax" style="width: 0%; height: 20px; background-color: #4cadc2; margin-top: 0.5em;"></div>
            <div id="overall_progress_container" style="display: none; margin-top: 1em;">
                <div id="overall_upload_status_ajax"><?php _e('Overall Progress', 'rslfranchise'); ?></div>
                <div id="overall_progress_bar_ajax" style="width: 0%; height: 20px; background-color: #4cadc2; margin-top: 0.5em;"></div>
            </div>
        </div>

    </div>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var homeworkID = $("#homework_id_ajax").val();
            var fileUrls = []; 
            var filesToUpload = []; 

            $("#submit_files_response_btn_ajax").click(function() {
                var responseText = $("#response_text_ajax").val().trim();
                var files = $("#project_files_ajax")[0].files;
                
                if (files.length === 0 && responseText === "") {
                    alert('Please upload at least one file or provide a response.');
                    return;
                }
                
                // Add selected files to filesToUpload array
                for (var i = 0; i < files.length; i++) {
                    filesToUpload.push(files[i]);
                }
                
                // Clear the file input
                $("#project_files_ajax").val('');
                
                // Start uploading files if any
                if (filesToUpload.length > 0) {
                    uploadFiles(responseText);
                } else {
                    saveResponse(responseText);
                }
            });

            var uploadFiles = function(responseText) {
                var filesToProcess = filesToUpload.slice();
                filesToUpload = [];
                var totalFiles = filesToProcess.length;
                var filesUploaded = 0;
                
                $("#upload_status_ajax").show();
                $("#progress_bar_ajax").show().width('0%');
                $("#overall_progress_bar_ajax").width('0%');
                $("#overall_progress_container").hide();
                $('.upload-status-container').addClass('visible');

                if (totalFiles > 1) {
                    $("#overall_progress_container").show();
                }

                var updateOverallProgress = function(fileIndex, fileProgress) {
                    var overallProgress = ((fileIndex + fileProgress) / totalFiles) * 100;
                    $("#overall_progress_bar_ajax").width(overallProgress + "%");
                    $("#overall_upload_status_ajax").text("Overall Progress: " + Math.round(overallProgress) + "%");
                };

                var uploadFile = function(fileIndex) {
                    if (fileIndex >= totalFiles) {
                        saveResponse(responseText);
                        return;
                    }

                    var file = filesToProcess[fileIndex];
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
                                $.ajax({
                                    url: presignedUrl,
                                    type: 'PUT',
                                    data: file,
                                    contentType: file.type,
                                    processData: false,
                                    success: function() {
                                        fileUrls.push(presignedUrl.split('?')[0]);
                                        filesUploaded++;
                                        uploadFile(fileIndex + 1);
                                    },
                                    error: function() {
                                        alert('Error uploading file to Wasabi.');
                                    },
                                    xhr: function() {
                                        var xhr = new window.XMLHttpRequest();
                                        xhr.upload.addEventListener("progress", function(evt) {
                                            if (evt.lengthComputable) {
                                                var percentComplete = evt.loaded / evt.total;
                                                var percentage = Math.round(percentComplete * 100) + "%";
                                                $("#progress_bar_ajax").width(percentage);
                                                $("#upload_status_ajax").text("Uploading: " + file.name + " (" + percentage + ")");
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
                uploadFile(0);
            };

            var saveResponse = function(responseText) {
                $.ajax({
                    url: "<?php echo admin_url('admin-ajax.php'); ?>",
                    type: "POST",
                    data: {
                        'action': 'project_ajax_s3_file_upload',
                        'file_urls': fileUrls,
                        'homework_id': homeworkID,
                        'response_text': responseText
                    },
                    success: function(response) {
                        if(response.success) {
                            // alert("Submission successful!");
                            location.reload();
                        } else {
                            alert("Error saving submission.");
                        }
                    },
                    error: function() {
                        alert("Error communicating with server.");
                    }
                });
            };
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('project_submission_ajax_s3', 'project_submission_ajax_s3_shortcode');
