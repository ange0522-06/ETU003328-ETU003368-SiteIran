<?php
require_once __DIR__ . '/../fonctions/upload.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $alt = isset($_POST['alt']) ? htmlspecialchars($_POST['alt']) : "Image de l'article";
    $result = upload_image_tinymce($_FILES['file'], $alt);

    if (!isset($result['error'])) {
        // ✅ Insérer le chemin dans la table image et récupérer l'id
        try {
            require_once __DIR__ . '/../app/db.php';
            $stmt = $pdo->prepare('INSERT INTO image (photo) VALUES (?)');
            $stmt->execute([$result['location']]);
            $result['image_id'] = $pdo->lastInsertId();
        } catch (Exception $e) {
            // Upload réussi mais insert BDD échoué — on continue quand même
            $result['db_warning'] = 'Image uploadée mais non enregistrée en BDD : ' . $e->getMessage();
        }
    }

    echo json_encode($result);
    exit;
}
echo json_encode(['error' => 'Aucun fichier reçu']);