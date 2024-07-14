<?php
include 'includes/auth.php';

if (!isAuthenticated()) {
    header('Location: login.php');
    exit;
}

$uploadDir = 'uploads/';
$messages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['files'])) {
    foreach ($_FILES['files']['name'] as $key => $name) {
        $uploadFile = $uploadDir . basename($name);
        if (move_uploaded_file($_FILES['files']['tmp_name'][$key], $uploadFile)) {
            $messages[] = [
                'status' => 'success',
                'name' => $name,
                'url' => $uploadDir . basename($name),
                'previewable' => isImageFile($name)
            ];
        } else {
            $messages[] = [
                'status' => 'error',
                'name' => $name,
                'url' => '',
                'previewable' => false
            ];
        }
    }
}

function isImageFile($filename) {
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    return in_array(strtolower($ext), $imageExtensions);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload File</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #drop-area {
            border: 2px dashed #ccc;
            border-radius: 20px;
            width: 100%;
            padding: 20px;
            font-family: Arial;
            text-align: center;
            margin-top: 20px;
        }
        .highlight {
            border-color: purple;
        }
        .preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }
        .preview .file-item {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 10px;
            background-color: #f0f0f0;
            overflow: hidden;
            width: 150px;
            height: 220px; /* Adjust height to fit the delete button and file name */
            margin-bottom: 10px; /* Add margin between file items */
        }
        .preview .file-item .inner-file-item {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 150px; /* Adjust height to leave space for file name and delete button */
            background-color: rgba(0, 0, 0, 0.1);
            color: rgba(0, 0, 0, 0.5);
            font-size: 24px;
            font-weight: bold;
            border-radius: 10px;
        }
        .preview .file-item img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .preview .file-item .file-name {
            margin-top: 10px;
            font-size: 14px;
            width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .preview .file-item .delete-btn {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Upload File</h2>
        <?php if (!empty($messages)) { ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?php foreach ($messages as $message) { ?>
                    <?php if ($message['status'] == 'success') { ?>
                        <p>File: <?= $message['name']; ?> - <a href="<?= $message['url']; ?>" target="_blank">Access URL</a></p>
                    <?php } else { ?>
                        <p>File: <?= $message['name']; ?> - Upload failed</p>
                    <?php } ?>
                <?php } ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php } ?>
        <form id="uploadForm" method="POST" enctype="multipart/form-data" class="mb-3">
            <input type="file" name="files[]" id="fileElem" multiple class="d-none">
            <button type="submit" class="btn btn-primary">Upload</button>
            <a href="file_manager.php" class="btn btn-success ml-2">File Manager</a> <!-- Added green button -->
        </form>
        <div id="drop-area" class="border">
            <p>Drag and drop files here or click to select files</p>
            <label class="btn btn-secondary" for="fileElem">Select some files</label>
        </div>
        <div class="preview mt-3" id="preview"></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function () {
            let dropArea = $('#drop-area');
            let fileElem = $('#fileElem');
            let preview = $('#preview');
            let fileArray = [];

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropArea.on(eventName, preventDefaults);
            });

            dropArea.on('dragenter', () => dropArea.addClass('highlight'));
            dropArea.on('dragleave', () => dropArea.removeClass('highlight'));
            dropArea.on('dragover', () => dropArea.addClass('highlight'));
            dropArea.on('drop', (e) => {
                let dt = e.originalEvent.dataTransfer;
                let files = dt.files;
                handleFiles(files);
            });

            function handleFiles(files) {
                [...files].forEach(file => {
                    fileArray.push(file);
                    previewFile(file);
                });
                updateFileList();
            }

            fileElem.on('change', function () {
                handleFiles(this.files);
            });

            function previewFile(file) {
                let reader = new FileReader();
                let ext = file.name.split('.').pop().toLowerCase();
                let isImage = /\.(jpe?g|png|gif)$/i.test(file.name); // Check if file is an image

                reader.readAsDataURL(file);
                reader.onloadend = function () {
                    let div = $('<div>').addClass('file-item');
                    let innerDiv = $('<div>').addClass('inner-file-item');
                    div.append(innerDiv);
                    
                    if (isImage) {
                        let img = $('<img>').attr('src', reader.result);
                        innerDiv.append(img);
                    } else {
                        let fileBox = $('<div>').addClass('file-box').text(ext.toUpperCase());
                        innerDiv.append(fileBox);
                    }

                    let fileName = $('<div>').addClass('file-name').text(file.name);
                    let deleteButton = $('<button>').addClass('btn btn-danger btn-sm delete-btn').text('Delete');
                    
                    div.append(fileName);
                    div.append(deleteButton);
                    preview.append(div);
                    
                    deleteButton.on('click', function () {
                        removeFile(file);
                        div.remove();
                    });
                }
            }

            function removeFile(file) {
                let index = fileArray.indexOf(file);
                if (index > -1) {
                    fileArray.splice(index, 1);
                }
                updateFileList();
            }

            function updateFileList() {
                let dataTransfer = new DataTransfer();
                fileArray.forEach(file => {
                    dataTransfer.items.add(file);
                });
                fileElem[0].files = dataTransfer.files;
            }

            // Hide the alert after 5 seconds
            setTimeout(() => {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>
</body>
</html>
