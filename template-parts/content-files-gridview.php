<div class="file-grid flex-wrap gap-3" id="file-grid" style="display: <?= $currentView === 'large' ? 'flex' : 'none'; ?>;">
  <?php foreach ($rsl_folders_data['folders'] as $folder): 
      $basename = $folder['name'];
      $folder_url = '?folder=' . urlencode($folder['path']);
  ?>
  <div class="file-item">
      <a href="<?= esc_url($folder_url); ?>" class="text-decoration-none text-dark">
          <div class="text-center p-4">
              <div class="mb-3">
                  <i class="fa fa-folder-o fa-4x fa-rsl"></i>
              </div>
              <div class="file-name fw-semibold"><?= htmlspecialchars($basename); ?></div>
          </div>
      </a>
  </div>
  <?php endforeach; ?>
  <?php foreach ($rsl_folders_data['files'] as $file): 
      $basename = basename($file['name']);
      $file_path = $file['path']; 
  ?>
      <div class="file-item">
          <a href="?download=<?= urlencode($basename) ?>&path=<?= urlencode($file_path); ?>" download class="text-decoration-none">
              <div class="text-center p-4">
                  <div class="mb-3">
                      <i class="fa <?= getFileIcon($basename); ?> fa-4x fa-rsl"></i>
                  </div>
                  <div class="file-name fw-semibold"><?= htmlspecialchars($basename); ?></div>
              </div>
          </a>
      </div>
  <?php endforeach; ?>
</div>