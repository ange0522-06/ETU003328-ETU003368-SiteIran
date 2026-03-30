<?php
require_once __DIR__ . '/../fonctions/liste_articles.php';
$articles = get_articles();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des articles</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-top: 24px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f0f0f0; }
        img.article-thumb { max-width: 80px; max-height: 60px; display: block; }
        .actions button { margin-right: 6px; }
    </style>
</head>
<body>
    <h1>Liste des articles</h1>
    <ul>
        <li><a href="accueil.php">Accueil</a></li>
        <li><a href="ajout-article.php">Ajouter un article</a></li>
    </ul>
    <table>
        <thead>
            <tr>
                <th>Titre</th>
                <th>Auteur</th>
                <th>Catégorie</th>
                <th>Date</th>
                <th>Statut</th>
                <th>Image principale</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($articles as $article): ?>
            <tr>
                <td><?= htmlspecialchars(strip_tags($article['titre'])) ?></td>
                <td><?= htmlspecialchars($article['auteur']) ?></td>
                <td><?= htmlspecialchars($article['categorie']) ?></td>
                <td><?= htmlspecialchars($article['date_publication']) ?></td>
                <td><?= htmlspecialchars($article['statut'] ?? 'Inconnu') ?></td>
                <td>
                    <?php
                    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $article['contenu'], $m)) {
                        echo '<img src="' . htmlspecialchars($m[1]) . '" class="article-thumb" alt="Image">';
                    } else {
                        echo '<span style="color:#888">Aucune</span>';
                    }
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
