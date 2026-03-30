<?php
function upload_image_tinymce($file, $alt = 'Image de l\'article') {
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowed)) {
        return ['error' => 'Format non supporté'];
    }

    // Utiliser un chemin absolu depuis la racine du projet
    $uploadDir = '/var/www/html/public/uploads/';
    
    // Vérifier et créer le dossier avec les bonnes permissions
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filename = uniqid('img_', true) . '.' . $ext;
    $target   = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $target)) {
        // Chemin web pour TinyMCE
        $webPath = '/public/uploads/' . $filename;
        return [
            'location' => $webPath,
            'filename' => $filename
        ];
    } else {
        // Ajouter plus d'informations sur l'erreur
        $error = error_get_last();
        return ['error' => 'Erreur upload : ' . ($error['message'] ?? 'vérifiez les permissions du dossier uploads/')];
    }
}