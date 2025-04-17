<div class="table-responsive">
    <table class="table file-table table" id="file-table" style="display: <?= $currentView === 'large' ? 'none' : 'table'; ?>;">
        <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Size</th>
                <th>Last Modified</th>
                <?php if ($is_rockschool_directory) :?>
                    <th>Action</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <!-- FOLDERS -->
            <?php foreach ($rsl_folders_data['folders'] as $folder): ?>
                <tr>
                    <td>
                        <a href="?folder=<?= urlencode($folder['path']); ?>" class="text-decoration-none">
                            <i class="fa fa-folder-o me-2"></i><?= htmlspecialchars($folder['name']); ?>
                        </a>
                    </td>
                    <td>Folder</td>
                    <td><?= $folder['size'] > 0 ? size_format($folder['size']) : '—'; ?></td>
                    <td><?= $folder['last_modified'] ? date("Y-m-d H:i", strtotime($folder['last_modified'])) : '—'; ?></td>
                    <?php if ($is_rockschool_directory) :?>
                    <td>
                        <button class="btn btn-danger delete-folder-btn" data-path="<?= $folder['path']; ?>">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>

            <!-- FILES -->
            <?php foreach ($rsl_folders_data['files'] as $file): ?>
                <tr>
                    <td>
                        <a href="?download=<?= urlencode($file['name']); ?>&path=<?= urlencode($file['path']); ?>" class="text-decoration-none">
                            <i class="fa <?= getFileIcon($file['name']); ?> me-2"></i><?= htmlspecialchars($file['name']); ?>
                        </a>
                    </td>
                    <td><?= strtoupper(pathinfo($file['name'], PATHINFO_EXTENSION)) ?: 'File'; ?></td>
                    <td><?= size_format($file['size']); ?></td>
                    <td><?= date("Y-m-d H:i", strtotime($file['last_modified'])); ?></td>
                    <?php if ($is_rockschool_directory) :?>
                        <td>
                            <button class="btn btn-danger delete-file-btn" data-path="<?= $file['path']; ?>">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
