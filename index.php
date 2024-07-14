<?php
include 'includes/auth.php';

if (isAuthenticated()) {
    header('Location: upload.php');
    exit;
} else {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting...</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Redirecting...</h2>
        <p>Please wait while we redirect you to the appropriate page.</p>
    </div>
</body>
</html>
