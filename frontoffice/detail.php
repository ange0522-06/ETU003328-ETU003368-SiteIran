<?php
require __DIR__ . '/app/db.php';

// Récupérer l'article par ID
$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    header('Location: index.php');
    exit;
}

// Récupérer l'article avec toutes les informations
$query = $pdo->prepare('
    SELECT a.*, 
           c.nom as categorie,
           au.nom as auteur,
           s.nom as statut,
           i.photo, i.alt as image_alt, i.largeur, i.hauteur
    FROM article a
    LEFT JOIN categorie c ON a.categorie_id = c.id
    LEFT JOIN auteur au ON a.auteur_id = au.id
    LEFT JOIN statut s ON a.statut_id = s.id
    LEFT JOIN image i ON a.image_id = i.id
    WHERE a.id = ? AND a.statut_id = 1
');

$query->execute([$id]);
$article = $query->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    http_response_code(404);
    die('Article non trouvé');
}

// Générer le slug pour vérifier l'URL
$slug = strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9 ]/', '', $article['titre'])));
$expected_url = "/articles/article-{$slug}-{$id}.html";

// Générer les balises meta
$meta_title = htmlspecialchars($article['titre'] . ' - Site Iran');
$meta_description = htmlspecialchars(substr(strip_tags($article['contenu']), 0, 160));
$meta_image = $article['photo'] ? htmlspecialchars($article['photo']) : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $meta_title ?></title>
    <meta name="description" content="<?= $meta_description ?>">
    <meta name="author" content="<?= htmlspecialchars($article['auteur']) ?>">
    <meta property="og:title" content="<?= $meta_title ?>">
    <meta property="og:description" content="<?= $meta_description ?>">
    <?php if ($meta_image): ?>
    <meta property="og:image" content="<?= $meta_image ?>">
    <?php endif; ?>
    <meta property="og:type" content="article">
    <link rel="canonical" href="<?= htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $expected_url) ?>">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        header {
            background: #1a1a1a;
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        nav {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        nav h1 {
            font-size: 1.5rem;
        }
        nav ul {
            list-style: none;
            display: flex;
            gap: 2rem;
        }
        nav a {
            color: white;
            text-decoration: none;
            transition: color 0.3s;
        }
        nav a:hover {
            color: #ffc107;
        }
        .container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        .breadcrumb {
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }
        .breadcrumb a {
            color: #007bff;
            text-decoration: none;
        }
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        .article-header {
            margin-bottom: 2rem;
        }
        h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #1a1a1a;
        }
        .article-meta {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: #666;
        }
        .article-meta div {
            margin-bottom: 0.2rem;
        }
        .article-meta a {
            color: #007bff;
            text-decoration: none;
        }
        .article-meta a:hover {
            text-decoration: underline;
        }
        .article-image {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            margin-bottom: 2rem;
            border-radius: 8px;
        }
        .article-content {
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 2rem;
        }
        .article-content h2 {
            font-size: 1.5rem;
            margin-top: 2rem;
            margin-bottom: 1rem;
            color: #1a1a1a;
        }
        .article-content h3 {
            font-size: 1.2rem;
            margin-top: 1.5rem;
            margin-bottom: 0.5rem;
            color: #333;
        }
        .article-content p {
            margin-bottom: 1rem;
        }
        .category-tag {
            display: inline-block;
            background: #ffc107;
            color: #1a1a1a;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        footer {
            background: #1a1a1a;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 4rem;
        }
        @media (max-width: 768px) {
            nav ul {
                gap: 1rem;
            }
            h1 {
                font-size: 1.5rem;
            }
            .article-meta {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <h1>📰 Site Iran</h1>
            <ul>
                <li><a href="/">Accueil</a></li>
                <li><a href="/articles">Articles</a></li>
                <li><a href="/categories">Catégories</a></li>
                <li><a href="/recherche">Recherche</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <article class="article-header">
            <h1><?= htmlspecialchars(strip_tags($article['titre'])) ?></h1>
        </article>

        <article class="article-content">
            <?php if ($article['photo']): ?>
                <figure>
                    <img 
                        src="<?= htmlspecialchars($article['photo']) ?>" 
                        alt="<?= htmlspecialchars($article['image_alt'] ?? $article['titre']) ?>"
                        class="article-image"
                        <?php if ($article['largeur'] && $article['hauteur']): ?>
                            width="<?= $article['largeur'] ?>"
                            height="<?= $article['hauteur'] ?>"
                        <?php endif; ?>
                    >
                    <figcaption><?= htmlspecialchars($article['image_alt'] ?? '') ?></figcaption>
                </figure>
            <?php endif; ?>
            
            <?php 
            // Afficher le contenu sans les balises HTML
            echo strip_tags($article['contenu']);
            ?>

            <div class="article-meta" style="margin-top: 3rem; border-top: 1px solid #ddd; padding-top: 2rem;">
                <div>Publié <?= date('\\l\\e d F Y \\à H\\hi', strtotime($article['date_publication'])) ?><?php if ($article['auteur']): ?>, par <?= htmlspecialchars($article['auteur']) ?><?php endif; ?></div>
                <?php if ($article['categorie']): ?>
                    <div>Catégorie : <a href="/categorie/<?= strtolower(str_replace(' ', '-', $article['categorie'])) ?>-<?= $article['categorie_id'] ?>"><?= htmlspecialchars($article['categorie']) ?></a></div>
                <?php endif; ?>
            </div>
        </article>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> Site Iran. Tous droits réservés.</p>
    </footer>
</body>
</html>
