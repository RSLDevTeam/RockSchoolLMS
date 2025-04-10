<?php
/*
Template Name: Files page
Template Post Type: page
*/

get_header();
global $current_user;
wp_get_current_user();

//Get Rockschool Directory or selected folder
$rawDirectory = isset($_GET['folder']) ? urldecode($_GET['folder']) : 'Rockschool';

// This is what the user clicked or sees
$fullDirectory = $displayDirectory = $rawDirectory;

$displayDirectory = basename($rawDirectory);

$rsl_folders_data = getUserWasabiFiles($fullDirectory);

//Get User Root Directory
$user_root_path = 'users/' . $current_user->ID;
$user_folders_data = getUserWasabiFiles($user_root_path); 
// Get all folders for Quick Access
$quick_access_folders = [
    ['folder' => 'Rockschool', 'path' => 'Rockschool'],
    ['folder' => 'My Folder', 'path' => $user_root_path]
];

// Add user's subfolders to the quick access folders list
foreach ($user_folders_data['folders'] as $user_folder) {
    
    // Add the folder name and path to the quick access array
    $quick_access_folders[] = [
        'folder' => $user_folder['name'],  // Display name of the folder
        'path' => $user_folder['path']    // Full path to the folder
    ];
}


// Handle "Create Folder" request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_folder'])) {
    $new_folder_name = sanitize_text_field($_POST['new_folder_name']);
    
    if (!empty($new_folder_name)) {
        $new_folder_path = rtrim($fullDirectory, '/') . '/' . $new_folder_name;

        // Create the folder in Wasabi
        createNewFolderInWasabi($new_folder_path);

        // Redirect to refresh folder view and prevent resubmission
        wp_redirect(add_query_arg('folder', $fullDirectory));
        exit;
    }
}


?>

<main id="primary" class="site-main files-page">

<?php while ( have_posts() ) : the_post(); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

    <div class="entry-content files-folder-container">
        <h1><?php _e('Files', 'rslfranchise'); ?></h1>
        <?php the_content(); ?>

        <div class="row explorer">
            <div class="col-lg-3">
                <div class="dashboard-section">
                    <h3><?php _e('Quick Access', 'rslfranchise'); ?></h3>
                    <ul class="nav flex-column list-group forum-stats">
                        <?php foreach ($quick_access_folders as $folder): ?>
                            <li class="list-group-item nav-item">
                                <a class="nav-link " href="?folder=<?= urlencode($folder['path']) ?>">
                                    <i class="fa fa-folder"></i> <?= htmlspecialchars($folder['folder']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <li class="list-group-item nav-item">
                            <a class="nav-link " href="#">
                                <i class="fa fa-folder"></i>  Franchisee London
                            </a>
                        </li>
                    </ul>
                </div>  
            </div>

            <div class="col-lg-9">
                <div class="dashboard-section">
                    <?php
                        $display_name = ($displayDirectory == $current_user->ID) ? 'My Folder' : htmlspecialchars($displayDirectory);
                    ?>
                    
                    <h3><?= $display_name ?></h3>
                    <? if ($displayDirectory !== 'Rockschool' ) :?>
                    <div class="d-flex justify-content-end">
                        <div class="">
                            <button data-bs-toggle="modal" data-bs-target="#newFolderModal">New Folder</button>
                            <button data-bs-toggle="modal" data-bs-target="#uploadFilesModal">Upload Files</button>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="row file-grid">
                    <?php if (!$rsl_folders_data['files'] && !$rsl_folders_data['folders']) : ?>
                        <div class="col-12">No files/folders in the selected folder.</div>
                    <?php else: ?>
                        
                        <?php foreach ($rsl_folders_data['folders'] as $folder): 
                            $basename = $folder['name'];
                            $folder_url = '?folder=' . urlencode($folder['path']);
                        ?>
                        <div class="col-md-4 mb-4">
                            <a href="<?= esc_url($folder_url); ?>" class="text-decoration-none text-dark">
                                <div class="text-center p-4">
                                    <div class="mb-3">
                                        <i class="fa fa-folder-open-o fa-4x text-primary"></i>
                                    </div>
                                    <div class="file-name fw-semibold"><?= htmlspecialchars($basename); ?></div>
                                </div>
                            </a>
                        </div>
                        <?php endforeach; ?>
                        <?php foreach ($rsl_folders_data['files'] as $file): 
                            $basename = basename($file['name']);
                        ?>
                            <div class="col-md-4 mb-4">
                                <a href="<?= esc_url($file['path']); ?>" download class="text-decoration-none text-dark">
                                    <div class="text-center p-4">
                                        <div class="mb-3">
                                            <i class="fa <?= getFileIcon($basename); ?> fa-4x text-primary"></i>
                                        </div>
                                        <div class="file-name fw-semibold"><?= htmlspecialchars($basename); ?></div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                </div>  
            </div>
        </div>

    </div>
</article>

<?php endwhile; ?>


<!-- New Folder Modal -->
<div class="modal fade" id="newFolderModal" tabindex="-1" aria-labelledby="newFolderModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="width: max-content;">
    <form method="post" action="">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="newFolderModalLabel">Create New Folder</h5>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label for="folderName" class="form-label">Folder Name</label>
                <input type="text" class="form-control" id="folderName" name="new_folder_name" required>
            </div>
            <input type="hidden" name="create_folder" value="1">
        </div>
        <div class="modal-footer">
          <button type="button" data-bs-dismiss="modal">Cancel</button>
          <button type="submit">Create</button>
        </div>
      </div>
    </form>
  </div>
</div>


<!-- Upload Files Modal -->
<div class="modal fade" id="uploadFilesModal" tabindex="-1" aria-labelledby="uploadFilesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="width: max-content;">
    <form id="uploadFilesForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="uploadFilesModalLabel">Upload Files</h5>
        </div>
        <div class="modal-body">
          <input type="file" id="upload_files_input" multiple>
          <input type="hidden" id="current_folder_path" value="<?php echo $fullDirectory?>">
            <div id="upload_status_ajax"></div>
            
            <div class="upload-status-container">
                <div id="progress_bar_ajax" style="width: 0%; height: 20px; background-color: #4cadc2; margin-top: 0.5em;"></div>
                <div id="overall_progress_container" style="display: none; margin-top: 1em;">
                    <div id="overall_upload_status_ajax"><?php _e('Overall Progress', 'rslfranchise'); ?></div>
                    <div id="overall_progress_bar_ajax" style="width: 0%; height: 20px; background-color: #4cadc2; margin-top: 0.5em;"></div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" data-bs-dismiss="modal">Cancel</button>
          <button type="button" id="upload_files_btn">Upload</button>
        </div>
      </div>
    </form>
  </div>
</div>


</main>

<script>
const ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
jQuery(document).ready(function($) {
    let filesToUpload = [];
    var fileUrls = []; 

    $("#upload_files_btn").click(function() {
        const files = $("#upload_files_input")[0].files;
        const folder = $("#current_folder_path").val() || "Rockschool";

        if (files.length === 0) {
            alert("Please select at least one file.");
            return;
        }

        filesToUpload = Array.from(files);
        $("#upload_status_ajax").show();
        $("#progress_bar_ajax").show().width('0%');
        $("#overall_progress_bar_ajax").width('0%');
        $("#overall_progress_container").hide();
        $('.upload-status-container').addClass('visible');

        uploadNextFile(0, folder);
    });

    function uploadNextFile(index, folder) {
        console.log("Uploading file " + (index + 1) + " of " + filesToUpload.length);
        if (index >= filesToUpload.length) {
            $("#upload_status").text("All files uploaded successfully!");
            $("#upload_progress_bar").width("100%");
            return;
        }
        const file = filesToUpload[index];
        var updateOverallProgress = function(fileIndex, fileProgress) {
            var overallProgress = ((fileIndex + fileProgress) / filesToUpload.length) * 100;
            $("#overall_progress_bar_ajax").width(overallProgress + "%");
            $("#overall_upload_status_ajax").text("Overall Progress: " + Math.round(overallProgress) + "%");
        };

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'generate_presigned_url_callback',
                file_name: file.name,
                folder: folder
            },
            success: function(response) {
                if (response.success) {
                    const presignedUrl = response.data.url;
                    $.ajax({
                        url: presignedUrl,
                        type: 'PUT',
                        data: file,
                        contentType: file.type,
                        processData: false,
                        success: function() {
                            $("#upload_status").text(`Uploaded: ${file.name}`);
                            const progress = Math.round(((index + 1) / filesToUpload.length) * 100);
                            $("#upload_progress_bar").width(progress + "%");
                            $("#overall_progress_container").show();

                            updateOverallProgress(index, 1);
                             uploadNextFile(index + 1, folder);  // Upload next file
                        },
                        error: function() {
                            alert(`Failed to upload file: ${file.name}`);
                        },
                        xhr: function() {
                            const xhr = new window.XMLHttpRequest();
                            xhr.upload.addEventListener("progress", function(evt) {
                                if (evt.lengthComputable) {
                                    var percentComplete = evt.loaded / evt.total;
                                    var percentage = Math.round(percentComplete * 100) + "%";
                                    $("#progress_bar_ajax").width(percentage);
                                    $("#upload_status_ajax").text("Uploading: " + file.name + " (" + percentage + ")");
                                    if (filesToUpload.length > 1) {
                                        updateOverallProgress(index, percentComplete / 100);
                                    }
                                }
                            }, false);
                            return xhr;
                        }
                    });
                } else {
                    alert("Error generating upload URL for file: " + file.name);
                }
            },
            error: function() {
                alert("Error generating pre-signed URL. Try again.");
            }
        });
    }
});

</script>


<?php
get_sidebar();
get_footer();
