<?php
require __DIR__ . '/app/db.php';

// Récupérer le terme de recherche
$search_term = $_GET['q'] ?? '';
$current_page = $_GET['p'] ?? 1;
$items_per_page = 12;

// Valider et sécuriser
$search_term = trim($search_term);
$search_safe = htmlspecialchars($search_term);

// Générer les balises meta
$meta_title = $search_term ? htmlspecialchars("Résultats pour « $search_term » - Site Iran") : 'Recherche - Site Iran';
$meta_description = $search_term ? htmlspecialchars("Résultats de recherche pour « $search_term » sur Site Iran") : 'Recherchez les articles sur Site Iran';
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
    <meta property="og:title" content="<?= $meta_title ?>">
    <meta property="og:description" content="<?= $meta_description ?>">
    <meta property="og:type" content="website">
    <link rel="canonical" href="<?= htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/recherche') ?>">
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
            --bg-light: #fafafa;
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
            background: var(--bg-light);
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
            transition: color 0.3s ease;
            font-weight: 500;
            position: relative;
            padding-bottom: 4px;
            border-bottom: 2px solid transparent;
        }

        nav a:hover, nav a.active {
            color: var(--accent-yellow);
            border-bottom-color: var(--accent-yellow);
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
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .search-section {
            background: var(--primary-light);
            padding: 3rem 2rem;
            border-radius: 8px;
            box-shadow: var(--shadow-lg);
            margin-bottom: 2rem;
        }

        .search-title {
            font-size: clamp(1.5rem, 4vw, 2rem);
            margin-bottom: 1.5rem;
            color: var(--text-dark);
            font-weight: 700;
        }

        .search-box {
            display: flex;
            gap: 1rem;
        }

        .search-box input {
            flex: 1;
            padding: 0.85rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.2s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--link-color);
        }

        .search-box button {
            padding: 0.85rem 2rem;
            background: var(--link-color);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .search-box button:hover {
            background: var(--link-hover);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .results-info {
            margin-bottom: 2rem;
            color: var(--text-light);
            font-size: 1rem;
            padding: 1rem;
            background: var(--primary-light);
            border-radius: 6px;
            border-left: 4px solid var(--accent-yellow);
        }

        .articles-grid {
            display: flex;
            flex-direction: column;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .article-card {
            background: var(--primary-light);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: row;
            gap: 1.5rem;
            padding: 1.5rem;
        }

        .article-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .article-card img {
            width: 200px;
            height: 150px;
            object-fit: cover;
            flex-shrink: 0;
            border-radius: 6px;
            loading: lazy;
        }

        .article-card-content {
            padding: 0;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .article-card h2 {
            font-size: 1.15rem;
            margin-bottom: 0.8rem;
            font-weight: 600;
            line-height: 1.4;
        }

        .article-card a {
            color: var(--text-dark);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .article-card a:hover {
            color: var(--link-color);
        }

        .article-card-excerpt {
            flex: 1;
            margin-bottom: 1rem;
            color: var(--text-light);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .article-card-meta {
            font-size: 0.85rem;
            color: var(--text-light);
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
            margin-top: auto;
        }

        .read-more {
            display: inline-block;
            color: var(--link-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
            font-size: 0.95rem;
            margin-top: 0.8rem;
        }

        .read-more:hover {
            color: var(--link-hover);
            text-decoration: underline;
        }

        .no-results {
            text-align: center;
            padding: 3rem 2rem;
            background: var(--primary-light);
            border-radius: 8px;
            color: var(--text-light);
            box-shadow: var(--shadow);
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 3rem;
            flex-wrap: wrap;
        }

        .pagination a, .pagination span {
            padding: 0.6rem 0.85rem;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            text-decoration: none;
            color: var(--link-color);
            font-weight: 500;
            transition: all 0.2s ease;
            background: var(--primary-light);
        }

        .pagination a:hover {
            background: var(--link-color);
            color: var(--primary-light);
            border-color: var(--link-color);
            transform: translateY(-2px);
        }

        .pagination span.active {
            background: var(--link-color);
            color: var(--primary-light);
            border-color: var(--link-color);
        }

        footer {
            background: var(--primary-dark);
            color: var(--primary-light);
            text-align: center;
            padding: 2.5rem 2rem;
            margin-top: 4rem;
            font-size: 0.9rem;
        }

        footer a {
            color: var(--primary-light);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        footer a:hover {
            color: var(--accent-yellow);
        }

        .highlight {
            background: var(--accent-yellow);
            padding: 0 3px;
            font-weight: 600;
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

            .article-card {
                flex-direction: column;
            }

            .article-card img {
                width: 100%;
                height: 200px;
            }

            .search-box {
                flex-direction: column;
            }

            .container {
                padding: 0 1.5rem;
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
                <li><a href="/recherche" class="active" aria-label="Rechercher des articles">Recherche</a></li>
            </ul>
        </nav>
    </header>

    <main id="main" class="container" role="main">
        <div class="search-section">
            <h1 class="search-title">Rechercher un article</h1>
            <form method="GET" action="/recherche" class="search-box">
                <input 
                    type="text" 
                    name="q" 
                    placeholder="Tapez votre recherche..." 
                    value="<?= $search_safe ?>"
                    required
                >
                <button type="submit">Rechercher</button>
            </form>
        </div>

        <?php
        if (!empty($search_term)):
            // Construire la requête de recherche
            $search_param = '%' . $search_term . '%';
            $sql = 'SELECT a.*, c.nom as categorie, au.nom as auteur, i.photo, i.alt as image_alt
                    FROM article a
                    LEFT JOIN categorie c ON a.categorie_id = c.id
                    LEFT JOIN auteur au ON a.auteur_id = au.id
                    LEFT JOIN image i ON a.image_id = i.id
                    WHERE a.statut_id = 1 AND (a.titre LIKE :search OR a.contenu LIKE :search OR c.nom LIKE :search OR au.nom LIKE :search)
                    ORDER BY a.date_publication DESC';

            // Compter les résultats
            $count_stmt = $pdo->prepare($sql);
            $count_stmt->execute([':search' => $search_param]);
            $total_results = $count_stmt->rowCount();
            $total_pages = ceil($total_results / $items_per_page);

            // Récupérer les résultats paginés
            $offset = ($current_page - 1) * $items_per_page;
            $sql .= " LIMIT $items_per_page OFFSET $offset";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([':search' => $search_param]);
            $results = $stmt->fetchAll();

            // Afficher les infos
            echo '<div class="results-info">';
            echo '<strong>' . $total_results . ' résultat' . ($total_results > 1 ? 's' : '') . '</strong> trouvé' . ($total_results > 1 ? 's' : '') . ' pour « ' . $search_safe . ' »';
            echo '</div>';

            if (empty($results)):
                echo '<div class="no-results">';
                echo '<h2>Aucun résultat trouvé</h2>';
                echo "<p>Essayez avec d'autres mots-clés</p>";
                echo '</div>';
            else:
                echo '<div class="articles-grid">';
                foreach ($results as $article):
                    $slug = strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9 ]/', '', $article['titre'])));
                    $article_url = "/articles/article-{$slug}-{$article['id']}.html";
                    $excerpt = substr(strip_tags($article['contenu']), 0, 150) . '...';
                    ?>
                    <article class="article-card">
                        <?php if ($article['photo']): ?>
                            <img 
                                src="<?= htmlspecialchars($article['photo']) ?>" 
                                alt="<?= htmlspecialchars($article['image_alt'] ?? $article['titre']) ?>"
                            >
                        <?php endif; ?>
                        <div class="article-card-content">
                            <h2>
                                <a href="<?= $article_url ?>">
                                    <?= htmlspecialchars(strip_tags($article['titre'])) ?>
                                </a>
                            </h2>
                            <p class="article-card-excerpt"><?= htmlspecialchars($excerpt) ?></p>
                            <a href="<?= $article_url ?>" class="read-more">Lire →</a>
                            <div class="article-card-meta" style="margin-top: auto;">
                                <div>Publié <?= date('\\l\\e d F Y \\à H\\hi', strtotime($article['date_publication'])) ?><?php if ($article['auteur']): ?>, par <?= htmlspecialchars($article['auteur']) ?><?php endif; ?></div>
                                <?php if ($article['categorie']): ?>
                                    <div>Catégorie : <a href="/categorie/<?= strtolower(str_replace(' ', '-', $article['categorie'])) ?>-<?= $article['categorie_id'] ?>" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($article['categorie']) ?></a></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                    <?php
                endforeach;
                echo '</div>';

                // Pagination
                if ($total_pages > 1):
                    echo '<div class="pagination">';
                    if ($current_page > 1):
                        echo '<a href="?q=' . urlencode($search_term) . '&p=1">← Première</a>';
                        echo '<a href="?q=' . urlencode($search_term) . '&p=' . ($current_page - 1) . '">← Précédent</a>';
                    endif;

                    for ($i = 1; $i <= $total_pages; $i++):
                        if ($i === $current_page):
                            echo '<span class="active">' . $i . '</span>';
                        else:
                            echo '<a href="?q=' . urlencode($search_term) . '&p=' . $i . '">' . $i . '</a>';
                        endif;
                    endfor;

                    if ($current_page < $total_pages):
                        echo '<a href="?q=' . urlencode($search_term) . '&p=' . ($current_page + 1) . '">Suivant →</a>';
                        echo '<a href="?q=' . urlencode($search_term) . '&p=' . $total_pages . '">Dernière →</a>';
                    endif;
                    echo '</div>';
                endif;
            endif;
        else:
            echo '<div class="results-info">';
            echo 'Utilisez la barre de recherche ci-dessus pour trouver des articles';
            echo '</div>';
        endif;
        ?>
    </main>

    <footer role="contentinfo">
        <p>&copy; <?= date('Y') ?> <strong>Site Iran</strong>. Tous droits réservés.</p>
        <p style="margin-top: 0.5rem; font-size: 0.85rem;">
            <a href="/mentions-legales" aria-label="Mentions légales">Mentions légales</a> | 
            <a href="/confidentialite" aria-label="Politique de confidentialité">Politique de confidentialité</a>
        </p>
    </footer>
</body>
</html>
