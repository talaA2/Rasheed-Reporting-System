<?php
// Shared helpers, loaded by db.php so every page has them.

// Escape text before printing it into HTML (prevents cross-site scripting).
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Validate and save an uploaded report photo.
// Returns the saved file name, null if no photo was sent, or false if the file is not allowed.
function save_report_photo($file) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 5 * 1024 * 1024) {
        return false;                                   // upload failed or larger than 5 MB
    }

    // Check the real file content, not the name the user gave it
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($allowed[$mime]) || getimagesize($file['tmp_name']) === false) {
        return false;
    }

    // Random file name, so a user can never choose the name (or extension) on the server
    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/uploads/' . $name)) {
        return false;
    }
    return $name;
}
