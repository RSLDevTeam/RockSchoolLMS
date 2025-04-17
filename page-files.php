<?php
/*
Template Name: Files page
Template Post Type: page
*/

get_header();
global $current_user;
wp_get_current_user();
$endpointPath = get_field('endpoint', 'option');  
$endpoint = 'https://' . $endpointPath; 

session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_view_mode'])) {
    $view = $_POST['set_view_mode'];
    if (in_array($view, ['large', 'list'])) {
        $_SESSION['file_view_mode'] = $view;
        exit; // End script if it's an AJAX call
    }
}

// Fallback default view
$currentView = $_SESSION['file_view_mode'] ?? 'large';

//Get Rockschool Directory or selected folder
$rawDirectory = isset($_GET['folder']) ? urldecode($_GET['folder']) : 'Rockschool';

// This is what the user clicked or sees
$fullDirectory = $displayDirectory = $rawDirectory;

$displayDirectory = basename($rawDirectory);

$rsl_folders_data = getUserWasabiFiles($fullDirectory);

//Get User Root Directory
$user_root_path = 'users/' . $current_user->ID;
$user_folders_data = getUserWasabiFiles($user_root_path); 
$user_rockschool_data = getUserWasabiFiles('Rockschool');
$franchise_folders_data = [];
$linked_franchises = get_field('linked_franchises', 'user_' . $current_user->ID) ?: [];

//wp_die('Linked franchises: ' . print_r($linked_franchises, true));
// Get all folders for Quick Access
$quick_access_folders = [
    [
        'folder' => 'Rockschool',
        'path' => 'Rockschool', 
        'is_parent' => true,
    ]
];
// Add user's Rockschool folder to the quick access folders list
foreach ($user_rockschool_data['folders'] as $rsl_folder) {
    $quick_access_folders[] = [
        'folder' => $rsl_folder['name'],  
        'path' => $rsl_folder['path'],
        'is_parent' => false,  
    ];
}


// Add linked franchises to the quick access folders
foreach ($linked_franchises as $franchise) {
    $franchise_folder = 'franchises/' . $franchise->post_title;
    $quick_access_folders[] = [
        'folder' => $franchise->post_title,  // Display name of the franchise
        'path' => $franchise_folder, 
        'is_parent' => true  // Full path to the franchise folder
    ];

    //add sunfolders to the quick access folders
    $franchise_folders_data[$franchise->post_title] = getUserWasabiFiles($franchise_folder);
}

//Add franchise folders to the quick access folders
foreach ($franchise_folders_data as $franchise => $data) {
    foreach ($data['folders'] as $folder) {
        $quick_access_folders[] = [
            'folder' => $folder['name'],  // Display name of the folder
            'path' => $folder['path'],    // Full path to the folder
            'is_parent' => false
        ];
    }
}

// Add user's root folder to the quick access folders list
$quick_access_folders[] = [
    'folder' => 'My Folder',  // Display name of the user's folder
    'path' => $user_root_path, // Full path to the user's folder
    'is_parent' => true
];

// Add user's subfolders to the quick access folders list
foreach ($user_folders_data['folders'] as $user_folder) {
    
    // Add the folder name and path to the quick access array
    $quick_access_folders[] = [
        'folder' => $user_folder['name'],  // Display name of the folder
        'path' => $user_folder['path']    // Full path to the folder
    ];
}

// Handle "Download" request
if (isset($_GET['download'])) {
    $file_name = urldecode($_GET['download']);
    $file_path = urldecode($_GET['path']);
    
    // Download the file from Wasabi
    downloadFileFromWasabi($file_name, $file_path);
    exit;
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
                        <?php
                        $last_parent_path = '';
                        $child_group = [];
                        
                        // First pass: group children under parents
                        foreach ($quick_access_folders as $folder) {
                            if (isset($folder['is_parent']) && $folder['is_parent']) {
                                $last_parent_path = rtrim($folder['path'], '/') . '/';
                            } else {
                                $child_group[$last_parent_path][] = $folder;
                            }
                        }

                        // Second pass: render items
                        foreach ($quick_access_folders as $folder):
                            $is_active = ($rawDirectory == $folder['path']) ? 'nav-active' : '';
                            $is_franchise = (strpos($folder['path'], 'franchises/') !== false);
                            $is_rockschool = (strpos($folder['path'], 'Rockschool') !== false);
                            $is_parent = isset($folder['is_parent']) && $folder['is_parent'];

                            if ($is_parent): 
                                $has_children = !empty($child_group[rtrim($folder['path'], '/') . '/']);
                        ?>
                                <!-- Parent Folder -->
                                <li class="list-group-item nav-item <?= $is_active ?> nav-parent no-border" data-path="<?= esc_attr($folder['path']) ?>">
                                    <a class="nav-link" href="?folder=<?= urlencode($folder['path']) ?>">
                                        <?php if ($is_franchise): ?>
                                            <i class="fa fa-building me-2"></i>
                                        <?php elseif ($is_rockschool): ?>
                                            <i class="fa fa-university me-2"></i>
                                        <?php else: ?>
                                            <i class="fa <?= $is_active ? 'fa-folder-open' : 'fa-folder' ?> me-2"></i>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($folder['folder']) ?>
                                    </a>
                                </li>

                                <?php if ($has_children):
                                    $children = $child_group[rtrim($folder['path'], '/') . '/'];
                                    $last_index = count($children) - 1;
                                    foreach ($children as $index => $child):
                                        $is_active = ($rawDirectory == $child['path']) ? 'nav-active' : '';
                                        $is_last = $index === $last_index;
                                ?>
                                    <li class="list-group-item nav-item <?= $is_active ?> nav-child no-border" data-path="<?= esc_attr($child['path']) ?>">
                                        <a class="nav-link ps-4" href="?folder=<?= urlencode($child['path']) ?>">
                                            <i class="fa <?= $is_active ? 'fa-folder-open' : 'fa-folder' ?> me-2"></i>
                                            <?= htmlspecialchars($child['folder']) ?>
                                        </a>
                                    </li>
                                <?php endforeach; endif; ?>
                        <?php endif; endforeach; ?>
                    </ul>

                </div>  
            </div>

            <div class="col-lg-9">
                <div class="dashboard-section">
                    <?php
                        $display_name = ($displayDirectory == $current_user->ID) ? 'My Folder' : htmlspecialchars($displayDirectory);
                    ?>
                    
                    <h3><?= $display_name ?></h3>
                    <?php
                        $topLevelFolder = getTopLevelFolder($fullDirectory);
                    ?>

                    <div class="mb-3 text-muted">
                        <?php if ($topLevelFolder === 'Rockschool'): ?>
                            Access files, resources and assets from Rockschool.
                        <?php elseif ($topLevelFolder === 'users'): ?>
                            Your secure, personal file storage area. You can upload files and create folders.
                        <?php else: ?>
                            File storage area for <strong><?= htmlspecialchars($topLevelFolder) ?></strong>. Files added here are available to all members of <strong><?= htmlspecialchars($topLevelFolder) ?></strong>.
                        <?php endif; ?>
                    </div>
                   
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="btn-groups">
                            <button id="btn-grid" data-view="large" class="<?= $currentView === 'large' ? 'active' : '' ?>">
                                <i class="fa fa-th-large" aria-hidden="true"></i>
                            </button>
                            <button id="btn-list" data-view="list" class="<?= $currentView === 'list' ? 'active' : '' ?>">
                                <i class="fa fa-th-list" aria-hidden="true"></i>
                            </button>
                        </div>

                        <? if ($displayDirectory !== 'Rockschool' && $topLevelFolder !== 'Rockschool' ) :?>
                            <div class="btn-groups">
                                <button data-bs-toggle="modal" data-bs-target="#newFolderModal">New Folder</button>
                                <button data-bs-toggle="modal" data-bs-target="#uploadFilesModal">Upload Files</button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class=" <?= $currentView === 'large' ? 'file-grid-view' : 'file-list-view' ?>" id=fileContainer>
                    <?php if (!$rsl_folders_data['files'] && !$rsl_folders_data['folders']) : ?>
                        <div class="col-12 text-center">No files/folders in the selected folder.</div>
                    <?php else: ?>
			            <?php
                            // Display files in grid view
                            set_query_var('rsl_folders_data', $rsl_folders_data);
                            set_query_var('currentView', $currentView);
                            get_template_part( 'template-parts/content', 'files-gridview' );

                            // Display files in list view
                            set_query_var('rsl_folders_data', $rsl_folders_data);
                            set_query_var('currentView', $currentView);
            			    get_template_part( 'template-parts/content', 'files-listview' );
                        ?>
                        
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

<!-- File Delete Confirmation -->
<div class="modal fade" id="deleteFileModal" tabindex="-1" aria-labelledby="deleteFileLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Confirm File Deletion</h5></div>
      <div class="modal-body">Are you sure you want to delete this file?</div>
      <div class="modal-footer">
        <button type="button" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="confirmDeleteFileBtn">Delete</button>
      </div>
    </div>
  </div>
</div>

<!-- Folder Delete Confirmation -->
<div class="modal fade" id="deleteFolderModal" tabindex="-1" aria-labelledby="deleteFolderLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Confirm Folder Deletion</h5></div>
      <div class="modal-body"> Are you sure you want to delete whole folder?</div>
      <div class="modal-footer">
        <button type="button" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="confirmDeleteFolderBtn">Delete All</button>
      </div>
    </div>
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
        const $btn = $(this);

        // Show spinner
        $btn.prop('disabled', true).html(`
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Uploading...
        `);
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
                            if (index + 1 === filesToUpload.length) {
                                $("#upload_status").text("All files uploaded successfully!");
                                location.reload();
                            }
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

    //function to toggle view
    $('#btn-grid, #btn-list').on('click', function () {
        let view = $(this).data('view');

        // Remove previous classes and set new
        $('#fileContainer')
            .removeClass('file-grid-view file-list-view')
            .addClass(view === 'large' ? 'file-grid-view' : 'file-list-view');

        // Toggle active class on buttons
        $('#btn-grid, #btn-list').removeClass('active');
        $(this).addClass('active');
        if (view === "large") {
            $("#file-grid").show();
            $("#file-table").hide();
        } else {
            $("#file-grid").hide();
            $("#file-table").show();
        }

        // Save view mode in session via AJAX
        $.post(window.location.href, { set_view_mode: view });
    });

    //function to delete file/folder
    let selectedPath = '';
    let selectedType = '';
    let originalBtnHTML = '';


    $('.delete-file-btn').on('click', function () {
        selectedPath = $(this).data('path');
        selectedType = 'file';
        originalBtnHTML = $('#confirmDeleteFileBtn').html();
        $('#deleteFileModal').modal('show');
    });

    $('.delete-folder-btn').on('click', function () {
        selectedPath = $(this).data('path');
        selectedType = 'folder';
        originalBtnHTML = $('#confirmDeleteFileBtn').html();
        $('#deleteFolderModal').modal('show');
    });

    $('#confirmDeleteFileBtn, #confirmDeleteFolderBtn').on('click', function () {
        const $btn = $(this);

        // Show spinner
        $btn.prop('disabled', true).html(`
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Deleting...
        `);

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'delete_file_folder',
                path: selectedPath,
                type: selectedType
            },
            success: function (res) {
                try {
                    console.log(res);
                    if (res.success) {
                        
                        $(`[data-path="${selectedPath}"]`).closest('.file-item, tr, li').fadeOut();
                        //close the modal based on type
                        if (selectedType === 'file') {
                            $('#deleteFileModal').modal('hide');
                        } else {
                            $('#deleteFolderModal').modal('hide');
                        }
                        // // Reload after a brief delay if needed
                        // setTimeout(() => {
                        //     window.location.href = window.location.href;
                        // }, 500);
                    } else {
                        alert('Error: ' + res.error);
                        $btn.prop('disabled', false).html(originalBtnHTML);
                    }
                } catch (err) {
                    alert('Unexpected response from server.');
                    $btn.prop('disabled', false).html(originalBtnHTML);
                }
            },
            error: function () {
                alert('Server error. Please try again.');
                $btn.prop('disabled', false).html(originalBtnHTML);
            }
        });
    });
});

</script>

<?php
get_sidebar();
get_footer();
