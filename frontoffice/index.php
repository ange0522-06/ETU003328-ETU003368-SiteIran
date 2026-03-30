<?php
require __DIR__ . '/app/db.php';

// Déterminer la page demandée
$page = $_GET['page'] ?? 'accueil';
$categorie_id = $_GET['categorie'] ?? null;
$current_page = $_GET['p'] ?? 1;
$items_per_page = 12;

// Générer les balises meta selon la page
$meta_title = 'Site Iran - Actualités et Analyses';
$meta_description = 'Découvrez les dernières actualités sur l\'Iran avec nos articles détaillés et analyses en profondeur.';

switch ($page) {
    case 'articles':
        $meta_title = 'Articles - Site Iran';
        break;
    case 'categories':
        $meta_title = 'Catégories - Site Iran';
        break;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($meta_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($meta_description) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($meta_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($meta_description) ?>">
    <meta property="og:type" content="website">
    <link rel="canonical" href="<?= htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>">
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
        h1 {
            font-size: 2rem;
            margin-bottom: 2rem;
            color: #1a1a1a;
        }
        .articles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
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
            flex-direction: column;
        }
        .article-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .article-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .article-card-content {
            padding: 1.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .article-card h2 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }
        .article-card a {
            color: #1a1a1a;
            text-decoration: none;
            transition: color 0.3s;
        }
        .article-card a:hover {
            color: #007bff;
        }
        .article-card-meta {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 1rem;
        }
        .article-card-excerpt {
            flex: 1;
            margin-bottom: 1rem;
            color: #555;
            font-size: 0.95rem;
        }
        .article-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .read-more {
            display: inline-block;
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }
        .read-more:hover {
            color: #0056b3;
        }
        .category-tag {
            display: inline-block;
            background: #ffc107;
            color: #1a1a1a;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-size: 0.85rem;
            text-decoration: none;
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
        .no-articles {
            text-align: center;
            padding: 2rem;
            color: #666;
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
            .articles-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <h1>📰 Site Iran</h1>
            <ul>
                <li><a href="/" class="<?= $page === 'accueil' ? 'active' : '' ?>">Accueil</a></li>
                <li><a href="/articles" class="<?= $page === 'articles' ? 'active' : '' ?>">Articles</a></li>
                <li><a href="/categories" class="<?= $page === 'categories' ? 'active' : '' ?>">Catégories</a></li>
                <li><a href="/contact">Contact</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <?php if ($page === 'accueil'): ?>
            <h1>Bienvenue sur Site Iran</h1>
            <p style="font-size: 1.1rem; margin-bottom: 2rem;">
                Découvrez les dernières actualités, analyses et reportages sur l'Iran. Notre équipe vous propose 
                des contenus en profondeur pour mieux comprendre les enjeux du pays.
            </p>
            
            <h2 style="font-size: 1.5rem; margin: 3rem 0 1.5rem 0;">Articles récents</h2>

        <?php elseif ($page === 'articles' || $categorie_id): ?>
            <?php if ($categorie_id): ?>
                <?php
                $cat_query = $pdo->query("SELECT nom FROM categorie WHERE id = $categorie_id");
                $categorie = $cat_query->fetch();
                if ($categorie) {
                    echo '<h1>' . htmlspecialchars($categorie['nom']) . '</h1>';
                }
                ?>
            <?php else: ?>
                <h1>Tous les articles</h1>
            <?php endif; ?>

        <?php elseif ($page === 'categories'): ?>
            <h1>Catégories</h1>
            <?php
            $cat_query = $pdo->query("SELECT * FROM categorie");
            $categories = $cat_query->fetchAll();
            ?>
            <div class="articles-grid">
                <?php foreach ($categories as $cat): ?>
                    <?php
                    $count_query = $pdo->query("SELECT COUNT(*) as total FROM article WHERE categorie_id = {$cat['id']} AND statut_id = 1");
                    $count = $count_query->fetch()['total'];
                    ?>
                    <a href="/categorie/<?= strtolower(str_replace(' ', '-', $cat['nom'])) ?>-<?= $cat['id'] ?>" style="text-decoration: none;">
                        <div style="background: white; padding: 2rem; border-radius: 8px; text-align: center; cursor: pointer;">
                            <h2 style="color: #1a1a1a; margin-bottom: 0.5rem;"><?= htmlspecialchars($cat['nom']) ?></h2>
                            <p style="color: #666;"><?= $count ?> article<?= $count > 1 ? 's' : '' ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php exit; ?>
        <?php endif; ?>

        <!-- Afficher les articles -->
        <?php
        // Construire la requête
        $sql = 'SELECT a.*, c.nom as categorie, au.nom as auteur, i.photo, i.alt as image_alt
                FROM article a
                LEFT JOIN categorie c ON a.categorie_id = c.id
                LEFT JOIN auteur au ON a.auteur_id = au.id
                LEFT JOIN image i ON a.image_id = i.id
                WHERE a.statut_id = 1';

        if ($categorie_id) {
            $sql .= ' AND a.categorie_id = ' . intval($categorie_id);
        }

        $sql .= ' ORDER BY a.date_publication DESC';

        // Récupérer le total pour la pagination
        $count_query = $pdo->query($sql);
        $total_articles = $count_query->rowCount();
        $total_pages = ceil($total_articles / $items_per_page);

        // Ajouter LIMIT et OFFSET
        $offset = ($current_page - 1) * $items_per_page;
        $sql .= " LIMIT $items_per_page OFFSET $offset";

        $articles_query = $pdo->query($sql);
        $articles = $articles_query->fetchAll();

        if (empty($articles)): ?>
            <div class="no-articles">
                <p>Aucun article trouvé.</p>
            </div>
        <?php else: ?>
            <div class="articles-grid">
                <?php foreach ($articles as $article): 
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
                                    <?= htmlspecialchars($article['titre']) ?>
                                </a>
                            </h2>
                            <div class="article-card-meta">
                                <?= date('d F Y', strtotime($article['date_publication'])) ?> • 
                                <?= htmlspecialchars($article['auteur']) ?>
                            </div>
                            <p class="article-card-excerpt"><?= htmlspecialchars($excerpt) ?></p>
                            <div class="article-card-footer">
                                <?php if ($article['categorie']): ?>
                                    <a href="/categorie/<?= strtolower(str_replace(' ', '-', $article['categorie'])) ?>-<?= $article['categorie_id'] ?>" class="category-tag">
                                        <?= htmlspecialchars($article['categorie']) ?>
                                    </a>
                                <?php endif; ?>
                                <a href="<?= $article_url ?>" class="read-more">Lire →</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($current_page > 1): ?>
                        <a href="?page=<?= $page ?><?= $categorie_id ? '&categorie=' . $categorie_id : '' ?>&p=<?= $current_page - 1 ?>">← Précédent</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i === $current_page): ?>
                            <span class="active"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?page=<?= $page ?><?= $categorie_id ? '&categorie=' . $categorie_id : '' ?>&p=<?= $i ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <a href="?page=<?= $page ?><?= $categorie_id ? '&categorie=' . $categorie_id : '' ?>&p=<?= $current_page + 1 ?>">Suivant →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> Site Iran. Tous droits réservés.</p>
    </footer>
</body>
</html>
