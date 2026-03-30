<?php
require_once __DIR__ . '/../fonctions/upload.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Activer l'affichage des erreurs pour le débogage (à désactiver en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {

    $alt = isset($_POST['alt']) ? htmlspecialchars($_POST['alt']) : "Image de l'article";
    $width = isset($_POST['width']) ? intval($_POST['width']) : null;
    $height = isset($_POST['height']) ? intval($_POST['height']) : null;
    $quality = isset($_POST['quality']) ? intval($_POST['quality']) : null;
    $result = upload_image_tinymce($_FILES['file'], $alt, $width, $height, $quality);

    echo json_encode($result);
    exit;
}
echo json_encode(['error' => 'Aucun fichier reçu']);