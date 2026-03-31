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
    <title><?= $meta_title ?></title>
    <meta name="description" content="<?= $meta_description ?>">
    <meta property="og:title" content="<?= $meta_title ?>">
    <meta property="og:description" content="<?= $meta_description ?>">
    <meta property="og:type" content="website">
    <link rel="canonical" href="<?= htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/recherche') ?>">
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
            background: #f5f5f5;
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
        nav a:hover, nav a.active {
            color: #ffc107;
        }
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        .search-section {
            background: white;
            padding: 3rem 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .search-title {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            color: #1a1a1a;
        }
        .search-box {
            display: flex;
            gap: 1rem;
        }
        .search-box input {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        .search-box button {
            padding: 0.75rem 2rem;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }
        .search-box button:hover {
            background: #0056b3;
        }
        .results-info {
            margin-bottom: 2rem;
            color: #666;
            font-size: 1.1rem;
        }
        .articles-grid {
            display: flex;
            flex-direction: column;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .article-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: row;
            gap: 1.5rem;
            padding: 1.5rem;
        }
        .article-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .article-card img {
            width: 200px;
            height: 150px;
            object-fit: cover;
            flex-shrink: 0;
            border-radius: 4px;
        }
        .article-card-content {
            padding: 0;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .article-card h2 {
            font-size: 1.1rem;
            margin-bottom: 0.8rem;
            font-weight: bold;
        }
        .article-card a {
            color: #1a1a1a;
            text-decoration: none;
            transition: color 0.3s;
        }
        .article-card a:hover {
            color: #007bff;
        }
        .article-card-excerpt {
            flex: 1;
            margin-bottom: 0.8rem;
            color: #555;
            font-size: 0.95rem;
        }
        .article-card-meta {
            font-size: 0.85rem;
            color: #666;
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
            margin-top: auto;
        }
        .read-more {
            display: inline-block;
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
            font-size: 0.95rem;
            margin-top: 0.8rem;
        }
        .read-more:hover {
            color: #0056b3;
        }
        .no-results {
            text-align: center;
            padding: 3rem 2rem;
            background: white;
            border-radius: 8px;
            color: #666;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }
        .pagination a, .pagination span {
            padding: 0.5rem 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #007bff;
        }
        .pagination a:hover {
            background: #007bff;
            color: white;
        }
        .pagination span.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        footer {
            background: #1a1a1a;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 4rem;
        }
        .highlight {
            background: #ffeb3b;
            padding: 0 2px;
        }
        @media (max-width: 768px) {
            nav ul {
                gap: 1rem;
            }
            .article-card {
                flex-direction: column;
            }
            .article-card img {
                width: 100%;
            }
            .search-box {
                flex-direction: column;
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
                <li><a href="/recherche" class="active">Recherche</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
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

    <footer>
        <p>&copy; <?= date('Y') ?> Site Iran. Tous droits réservés.</p>
    </footer>
</body>
</html>
