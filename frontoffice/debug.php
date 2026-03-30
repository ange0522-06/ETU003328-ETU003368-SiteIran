<?php
require __DIR__ . '/app/db.php';

// Vérifier les articles
$articles = $pdo->query('
    SELECT a.id, a.titre, a.statut_id, s.nom AS statut 
    FROM article a 
    LEFT JOIN statut s ON a.statut_id = s.id
')->fetchAll();

echo '<h2>Articles dans la base :</h2>';
echo '<pre>';
print_r($articles);
echo '</pre>';

// Vérifier les statuts
$statuts = $pdo->query('SELECT * FROM statut')->fetchAll();
echo '<h2>Statuts disponibles :</h2>';
echo '<pre>';
print_r($statuts);
echo '</pre>';

// Compter les articles publiés
$publis = $pdo->query('SELECT COUNT(*) as count FROM article WHERE statut_id = 1')->fetch();
echo '<h2>Articles publiés (statut_id = 1) : ' . $publis['count'] . '</h2>';
?>
