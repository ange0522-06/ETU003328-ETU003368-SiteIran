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
$article_date = date('Y-m-d', strtotime($article['date_publication']));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a1a1a">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= $meta_title ?></title>
    <meta name="description" content="<?= $meta_description ?>">
    <meta name="author" content="<?= htmlspecialchars($article['auteur'] ?? 'Site Iran') ?>">
    <meta name="keywords" content="<?= htmlspecialchars($article['categorie']) ?>, Iran, actualités">
    <meta property="og:title" content="<?= $meta_title ?>">
    <meta property="og:description" content="<?= $meta_description ?>">
    <meta property="og:type" content="article">
    <meta property="article:published_time" content="<?= $article_date ?>">
    <?php if ($meta_image): ?>
    <meta property="og:image" content="<?= $meta_image ?>">
    <meta property="og:image:alt" content="<?= htmlspecialchars($article['image_alt'] ?? $article['titre']) ?>">
    <?php endif; ?>
    <link rel="canonical" href="<?= htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $expected_url) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <style>
        :root {
            --primary-dark: #1a1a1a;
            --primary-light: #ffffff;
            --accent-yellow: #ffc107;
            --text-dark: #333333;
            --text-light: #666666;
            --link-color: #0066cc;
            --link-hover: #004999;
            --border-color: #e0e0e0;
            --shadow: 0 2px 8px rgba(0,0,0,0.1);
            --shadow-lg: 0 4px 12px rgba(0,0,0,0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background-color: #fafafa;
        }

        a:focus, button:focus {
            outline: 2px solid var(--accent-yellow);
            outline-offset: 2px;
        }

        header {
            background: var(--primary-dark);
            color: var(--primary-light);
            padding: 1.2rem 0;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        nav {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
        }

        .logo-section {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }

        nav h1 {
            font-size: 1.8rem;
            font-weight: 800;
            white-space: nowrap;
            color: var(--accent-yellow);
            letter-spacing: 0.5px;
            margin: 0;
        }

        .tagline {
            font-size: 0.8rem;
            color: #ccc;
            font-weight: 300;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin: 0;
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }

        nav a {
            color: var(--primary-light);
            text-decoration: none;
            transition: color 0.3s ease, text-decoration 0.3s ease;
            font-weight: 500;
            position: relative;
        }

        nav a:hover {
            color: var(--accent-yellow);
        }

        nav a:focus {
            outline: none;
        }

        .skip-link {
            position: absolute;
            top: -40px;
            left: 0;
            background: var(--accent-yellow);
            color: var(--primary-dark);
            padding: 8px;
            text-decoration: none;
            z-index: 100;
        }

        .skip-link:focus {
            top: 0;
        }

        .container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .breadcrumb {
            margin-bottom: 2rem;
            font-size: 0.95rem;
            color: var(--text-light);
        }

        .breadcrumb a {
            color: var(--link-color);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .breadcrumb a:hover {
            color: var(--link-hover);
            text-decoration: underline;
        }

        .article-header {
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--border-color);
        }

        h1 {
            font-size: clamp(1.75rem, 4vw, 2.5rem);
            margin-bottom: 1rem;
            color: var(--text-dark);
            font-weight: 700;
            line-height: 1.2;
        }

        h2 {
            font-size: 1.5rem;
            color: var(--text-dark);
            margin-top: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        h3 {
            font-size: 1.25rem;
            color: var(--text-dark);
            margin-top: 1.8rem;
            margin-bottom: 0.8rem;
            font-weight: 600;
        }

        .article-meta {
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
            font-size: 0.95rem;
            color: var(--text-light);
            margin-bottom: 1.5rem;
        }

        .article-meta a {
            color: var(--link-color);
            text-decoration: none;
            transition: color 0.2s ease;
            font-weight: 500;
        }

        .article-meta a:hover {
            color: var(--link-hover);
            text-decoration: underline;
        }

        figure {
            margin: 2rem 0;
            text-align: center;
        }

        .article-image {
            width: 100%;
            max-height: 500px;
            height: auto;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: var(--shadow-lg);
            display: block;
        }

        figcaption {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-top: 0.5rem;
            font-style: italic;
        }

        .article-content {
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 3rem;
            background: var(--primary-light);
            padding: 2rem;
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        .article-content p {
            margin-bottom: 1.2rem;
        }

        .article-content ul,
        .article-content ol {
            margin: 1.5rem 0 1.5rem 2rem;
        }

        .article-content li {
            margin-bottom: 0.5rem;
        }

        .article-footer {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 2px solid var(--border-color);
        }

        .category-tag {
            display: inline-block;
            background: var(--accent-yellow);
            color: var(--primary-dark);
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin-bottom: 1rem;
        }

        .category-tag:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        footer {
            background: var(--primary-dark);
            color: var(--primary-light);
            text-align: center;
            padding: 2.5rem 2rem;
            margin-top: 4rem;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            nav {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem 2rem;
            }

            nav ul {
                gap: 1rem;
                width: 100%;
                justify-content: space-around;
            }

            h1 {
                font-size: 1.5rem;
            }

            h2 {
                font-size: 1.3rem;
            }

            .container {
                padding: 0 1.5rem;
            }

            .article-content {
                padding: 1.5rem;
            }

            .article-meta {
                gap: 0.5rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <a href="#main" class="skip-link">Aller au contenu principal</a>
    
    <header role="banner">
        <nav role="navigation" aria-label="Navigation principale">
            <div class="logo-section">
                <h1>📰 SITE IRAN</h1>
                <p class="tagline">Actualités • Analyses • Reportages</p>
            </div>
            <ul>
                <li><a href="/" aria-label="Accueil">Accueil</a></li>
                <li><a href="/articles" aria-label="Tous les articles">Articles</a></li>
                <li><a href="/categories" aria-label="Parcourir par catégories">Catégories</a></li>
                <li><a href="/recherche" aria-label="Rechercher des articles">Recherche</a></li>
            </ul>
        </nav>
    </header>

    <main id="main" class="container" role="main">
        <article class="article-header">
            <h1><?= htmlspecialchars(strip_tags($article['titre'])) ?></h1>
            <div class="article-meta">
                <div><time datetime="<?= $article_date ?>">Publié le <?= date('d F Y \\à H\\hi', strtotime($article['date_publication'])) ?></time><?php if ($article['auteur']): ?> <span aria-label="par">, par</span> <strong><?= htmlspecialchars($article['auteur']) ?></strong><?php endif; ?></div>
                <?php if ($article['categorie']): ?>
                    <div>Catégorie : <a href="/categorie/<?= strtolower(str_replace(' ', '-', $article['categorie'])) ?>-<?= $article['categorie_id'] ?>" class="category-tag"><?= htmlspecialchars($article['categorie']) ?></a></div>
                <?php endif; ?>
            </div>
        </article>

        <article class="article-content">
            <?php if ($article['photo']): ?>
                <figure>
                    <img 
                        src="<?= htmlspecialchars($article['photo']) ?>" 
                        alt="<?= htmlspecialchars($article['image_alt'] ?? $article['titre']) ?>"
                        class="article-image"
                        loading="lazy"
                        decoding="async"
                        <?php if ($article['largeur'] && $article['hauteur']): ?>
                            width="<?= intval($article['largeur']) ?>"
                            height="<?= intval($article['hauteur']) ?>"
                        <?php endif; ?>
                    >
                    <?php if ($article['image_alt']): ?>
                        <figcaption><?= htmlspecialchars($article['image_alt']) ?></figcaption>
                    <?php endif; ?>
                </figure>
            <?php endif; ?>
            
            <?php 
            // Afficher le contenu sans les balises HTML
            echo strip_tags($article['contenu']);
            ?>
        </article>

        <div class="article-footer">
            <div class="article-meta">
                <div>
                    <strong>Partager :</strong>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $expected_url) ?>" target="_blank" rel="noopener noreferrer" aria-label="Partager sur Facebook">Facebook</a> | 
                    <a href="https://twitter.com/intent/tweet?url=<?= urlencode($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $expected_url) ?>&text=<?= urlencode($article['titre']) ?>" target="_blank" rel="noopener noreferrer" aria-label="Partager sur Twitter">Twitter</a>
                </div>
            </div>
        </div>
    </main>

    <footer role="contentinfo">
        <p>&copy; <?= date('Y') ?> <strong>Site Iran</strong>. Tous droits réservés.</p>
        <p style="margin-top: 0.5rem; font-size: 0.85rem;">
            <a href="/mentions-legales" style="color: inherit; text-decoration: none;">Mentions légales</a> | 
            <a href="/confidentialite" style="color: inherit; text-decoration: none;">Politique de confidentialité</a>
        </p>
    </footer>

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "NewsArticle",
        "headline": <?= json_encode($article['titre']) ?>,
        "description": <?= json_encode(htmlspecialchars(substr(strip_tags($article['contenu']), 0, 160))) ?>,
        "image": <?= json_encode($article['photo']) ?>,
        "datePublished": "<?= $article_date ?>",
        "author": {
            "@type": "Person",
            "name": <?= json_encode($article['auteur'] ?? 'Site Iran') ?>
        },
        "publisher": {
            "@type": "Organization",
            "name": "Site Iran",
            "logo": {
                "@type": "ImageObject",
                "url": "<?= $_SERVER['REQUEST_SCHEME'] ?>://<?= $_SERVER['HTTP_HOST'] ?>/logo.png"
            }
        }
    }
    </script>
</body>
</html>
