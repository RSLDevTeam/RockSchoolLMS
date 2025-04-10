<?php
/*
Template Name: Files page
Template Post Type: page
*/

get_header();
global $current_user;
wp_get_current_user();

//Get Rockschool Directory or selected folder
$directory = isset($_GET['folder']) ? $_GET['folder'] : 'Rockschool';
$rsl_folders_data = getUserWasabiFiles($directory);

//Get User Root Directory
$user_root_path = 'users/' . $current_user->ID;
$user_folders_data = getUserWasabiFiles($user_root_path); 

// Get all folders for Quick Access
$quick_access_folders = [
    ['folder' => 'Rockschool', 'path' => 'Rockschool'],
    ['folder' => 'My Folder', 'path' => $user_root_path]
];

// Add user's subfolders
foreach ($user_folders_data['folders'] as $user_folder) {
    $folder_name = trim(str_replace($user_root_path . '/', '', $user_folder), '/');
    $quick_access_folders[] = [
        'folder' => $folder_name,
        'path' => $user_folder 
    ];
}


// Handle "Create Folder" request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_folder'])) {
    $new_folder_name = sanitize_text_field($_POST['new_folder_name']);
    
    if (!empty($new_folder_name)) {
        $new_folder_path = rtrim($directory, '/') . '/' . $new_folder_name;

        // Create the folder in Wasabi
        createNewFolderInWasabi($new_folder_path);

        // Redirect to refresh folder view and prevent resubmission
        wp_redirect(add_query_arg('folder', $directory));
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
                        $display_name = ($directory === $user_root_path) ? 'My Folder' : htmlspecialchars($directory);
                    ?>
                    
                    <h3><?= $display_name ?></h3>
                    <? if ($directory !== 'Rockschool' ) :?>
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
                            $basename = basename($folder);
                            $folder_url = add_query_arg('folder', urlencode($folder));
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
                            $basename = basename($file);
                        ?>
                            <div class="col-md-4 mb-4">
                                <a href="<?= esc_url($file); ?>" download class="text-decoration-none text-dark">
                                    <div class="text-center p-4 border rounded shadow-sm h-100">
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
  <div class="modal-dialog  modal-dialog-centered" style="width: max-content;">
    <form method="post" action="" enctype="multipart/form-data">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="uploadFilesModalLabel">Upload Files</h5>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label for="fileUpload" class="form-label">Choose files</label>
                <input type="file" class="form-control" id="fileUpload" name="upload_files[]" multiple required>
            </div>
            <input type="hidden" name="upload_action" value="1">
        </div>
        <div class="modal-footer">
          <button type="button" data-bs-dismiss="modal">Cancel</button>
          <button type="submit">Upload</button>
        </div>
      </div>
    </form>
  </div>
</div>


</main>

<?php
get_sidebar();
get_footer();
