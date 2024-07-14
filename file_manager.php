<?php
include 'includes/auth.php';

if (!isAuthenticated()) {
    header('Location: login.php');
    exit;
}

$uploadDir = 'uploads/';
$uploadedFiles = [];

// Get all files in the upload directory
if ($handle = opendir($uploadDir)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            $uploadedFiles[] = [
                'name' => $entry,
                'url' => $uploadDir . $entry,
                'previewable' => isImageFile($entry) // Check if file is an image for preview
            ];
        }
    }
    closedir($handle);
}

// Function to check if file is an image (for preview)
function isImageFile($filename) {
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    return in_array(strtolower($ext), $imageExtensions);
}

// Handle file deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $filename = $_POST['filename'];
    $filePath = $uploadDir . $filename;
    if (file_exists($filePath)) {
        unlink($filePath); // Delete file from server
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Pagination
$perPage = 18;
$totalFiles = count($uploadedFiles);
$totalPages = ceil($totalFiles / $perPage);
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $perPage;
$paginatedFiles = array_slice($uploadedFiles, $start, $perPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Manager</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .file-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px; /* Increase gap between file items */
            margin-top: 50px;
        }
        .file-item {
            width: calc(100% / 7 - 20px);
            text-align: center;
        }
        .file-item .file-preview-container {
            position: relative;
            width: 150px;
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 10px;
            background-color: #f0f0f0;
            overflow: hidden;
        }
        .file-item img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .file-item .file-extension-preview {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            opacity: 0.5;
        }
        .file-name {
            margin-top: 10px;
            width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: center;
            font-size: 14px;
        }
        .delete-btn {
            margin-top: 5px;
        }
        .pagination {
            margin-top: 40px; /* Increase gap between file list and pagination */
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">File Manager</h2>
        <div class="file-list">
            <?php if (!empty($paginatedFiles)) { ?>
                <?php foreach ($paginatedFiles as $file) { ?>
                    <div class="file-item">
                        <a href="<?= $file['url']; ?>" target="_blank" class="file-preview-container">
                            <?php if ($file['previewable']) { ?>
                                <img src="<?= $file['url']; ?>" alt="<?= $file['name']; ?>" class="file-preview">
                            <?php } else { ?>
                                <div class="file-extension-preview"><?= strtoupper(pathinfo($file['name'], PATHINFO_EXTENSION)); ?></div>
                            <?php } ?>
                        </a>
                        <div class="file-name"><?= $file['name']; ?></div>
                        <form action="" method="POST" class="delete-form">
                            <input type="hidden" name="filename" value="<?= $file['name']; ?>">
                            <button type="button" class="btn btn-danger btn-sm delete-btn" data-toggle="modal" data-target="#deleteFileModal" data-filename="<?= $file['name']; ?>">Delete</button>
                        </form>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <p>No files uploaded yet.</p>
            <?php } ?>
        </div>

        <!-- Pagination -->
        <ul class="pagination">
            <?php if ($page > 1): ?>
                <li class="page-item"><a class="page-link" href="?page=<?= $page - 1; ?>">Previous</a></li>
            <?php else: ?>
                <li class="page-item disabled"><span class="page-link">Previous</span></li>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= ($i === $page) ? 'active' : ''; ?>"><a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a></li>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
                <li class="page-item"><a class="page-link" href="?page=<?= $page + 1; ?>">Next</a></li>
            <?php else: ?>
                <li class="page-item disabled"><span class="page-link">Next</span></li>
            <?php endif; ?>
        </ul>

    </div>

    <!-- Delete File Modal -->
    <div class="modal fade" id="deleteFileModal" tabindex="-1" role="dialog" aria-labelledby="deleteFileModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteFileModalLabel">Delete File</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this file?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="confirmDeleteForm" method="POST" action="">
                        <input type="hidden" name="filename" id="filenameToDelete">
                        <button type="submit" class="btn btn-danger" name="confirm_delete">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            let filenameToDelete;

            // Handle delete button click
            $('.delete-btn').click(function() {
                filenameToDelete = $(this).data('filename');
                $('#filenameToDelete').val(filenameToDelete);
            });

            // Handle confirm delete button click
            $('#confirmDelete').click(function() {
                if (filenameToDelete) {
                    $.post('', { confirm_delete: true, filename: filenameToDelete }, function() {
                        window.location.reload();
                    });
                }
            });
        });
    </script>
</body>
</html>
