<?php
function upload_image_tinymce($file, $alt = 'Image de l\'article') {
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowed)) {
        return ['error' => 'Format non supporté'];
    }

    $uploadDir = __DIR__ . '/../public/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = uniqid('img_', true) . '.' . $ext;
    $target   = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $target)) {
        // Chemin web pour TinyMCE (fonctionne partout)
        $webPath = '/backoffice/public/uploads/' . $filename;
        return [
            'location' => $webPath,
            'filename' => $filename
        ];
    } else {
        return ['error' => 'Erreur upload : vérifiez les permissions du dossier uploads/'];
    }
}