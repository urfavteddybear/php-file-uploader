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
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
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
$perPage = 10;
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
            margin-top: 50px;
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
        }
        .file-item {
            position: relative;
            width: 150px;
            margin-right: 20px;
            margin-bottom: 20px;
            text-align: center;
        }
        .file-item a {
            display: block;
        }
        .file-item img {
            max-width: 100%;
            height: 100px; /* Set a fixed height for consistent size */
            object-fit: cover; /* Ensure images maintain aspect ratio */
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .file-name {
            margin-top: 10px;
        }
        .file-actions {
            margin-top: 5px;
        }
        .file-actions button {
            margin-right: 5px;
        }
        .pagination {
            margin-top: 20px;
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
                        <a href="<?= $file['url']; ?>" target="_blank">
                            <?php if ($file['previewable']) { ?>
                                <img src="<?= $file['url']; ?>" alt="<?= $file['name']; ?>" class="file-preview">
                            <?php } else { ?>
                                <img src="https://via.placeholder.com/150" alt="Placeholder" class="file-preview">
                            <?php } ?>
                        </a>
                        <div class="file-name"><?= $file['name']; ?></div>
                        <div class="file-actions">
                            <form action="" method="POST" class="delete-form">
                                <input type="hidden" name="filename" value="<?= $file['name']; ?>">
                                <button type="button" class="btn btn-danger delete-button" data-toggle="modal" data-target="#deleteFileModal" data-filename="<?= $file['name']; ?>">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <p>No files uploaded yet.</p>
            <?php } ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?= $page - 1; ?>">Previous</a></li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i === $page) ? 'active' : ''; ?>"><a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a></li>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?= $page + 1; ?>">Next</a></li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>
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
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
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
            $('.delete-button').click(function() {
                filenameToDelete = $(this).data('filename');
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
