<?php
function upload_image_tinymce($file, $alt = 'Image de l\'article', $width = null, $height = null, $quality = null) {
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowed)) {
        return ['error' => 'Format non supporté'];
    }

    $uploadDir = '/var/www/html/public/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filename = uniqid('img_', true) . '.' . $ext;
    $target   = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $target)) {
        // Récupérer les dimensions de l'image
        $imgInfo = getimagesize($target);
        $imgWidth = $imgInfo[0] ?? null;
        $imgHeight = $imgInfo[1] ?? null;

        // Enregistrer dans la base de données
        require __DIR__ . '/../app/db.php';
            $stmt = $pdo->prepare('INSERT INTO image (photo, largeur, hauteur, alt) VALUES (?, ?, ?, ?)');
            $stmt->execute([
                '/public/uploads/' . $filename,
                $imgWidth,
                $imgHeight,
                $alt
            ]);
        $image_id = $pdo->lastInsertId();

        $webPath = '/public/uploads/' . $filename;
        return [
            'location' => $webPath,
            'filename' => $filename,
            'image_id' => $image_id,
            'largeur' => $imgWidth,
            'hauteur' => $imgHeight,
            'alt' => $alt
        ];
    } else {
        $error = error_get_last();
        return ['error' => 'Erreur upload : ' . ($error['message'] ?? 'vérifiez les permissions du dossier uploads/')];
    }
}