<?php
require_once __DIR__ . '/../app/db.php';

function get_articles() {
    global $pdo;
    return $pdo->query('
        SELECT a.*, s.nom AS statut, au.nom AS auteur, c.nom AS categorie, a.date_publiee
        FROM article a
        LEFT JOIN statut s ON a.statut_id = s.id
        LEFT JOIN auteur au ON a.auteur_id = au.id
        LEFT JOIN categorie c ON a.categorie_id = c.id
        ORDER BY a.date_publication DESC
    ')->fetchAll(PDO::FETCH_ASSOC);
}
