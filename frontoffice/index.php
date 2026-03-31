<?php
require __DIR__ . '/app/db.php';

// Déterminer la page demandée
$page = $_GET['page'] ?? 'accueil';
$categorie_id = $_GET['categorie'] ?? null;
$current_page = $_GET['p'] ?? 1;
$items_per_page = 12;

// Initialiser les variables de filtre de date AVANT toute utilisation
$date_min = $_GET['date_min'] ?? '';
$date_max = $_GET['date_max'] ?? '';

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
    <meta name="theme-color" content="#1a1a1a">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= htmlspecialchars($meta_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($meta_description) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($meta_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($meta_description) ?>">
    <meta property="og:type" content="website">
    <link rel="canonical" href="<?= htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>">
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

        h1 {
            font-size: clamp(1.5rem, 4vw, 2.5rem);
            margin-bottom: 2rem;
            color: var(--text-dark);
            font-weight: 700;
        }

        h2 {
            font-size: 1.25rem;
            color: var(--text-dark);
            font-weight: 600;
        }

        .hero {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #2d2d2d 100%);
            color: var(--primary-light);
            padding: 3rem 2rem;
            border-radius: 8px;
            margin-bottom: 3rem;
            text-align: center;
        }

        .hero h1 {
            color: var(--primary-light);
            margin-bottom: 1rem;
        }

        .hero p {
            font-size: 1.1rem;
            opacity: 0.9;
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

        .article-card-meta {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        .article-card-meta div {
            margin-bottom: 0.4rem;
        }

        .article-card-excerpt {
            flex: 1;
            margin-bottom: 1rem;
            color: var(--text-light);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .read-more {
            display: inline-block;
            color: var(--link-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
            font-size: 0.95rem;
        }

        .read-more:hover {
            color: var(--link-hover);
            text-decoration: underline;
        }

        .category-tag {
            display: inline-block;
            background: var(--accent-yellow);
            color: var(--primary-dark);
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            transition: transform 0.2s ease;
            margin-right: 0.5rem;
        }

        .category-tag:hover {
            transform: scale(1.05);
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

        .no-articles {
            text-align: center;
            padding: 3rem 2rem;
            background: var(--primary-light);
            border-radius: 8px;
            color: var(--text-light);
            box-shadow: var(--shadow);
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .category-card {
            background: var(--primary-light);
            padding: 2rem;
            border-radius: 8px;
            text-align: center;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-decoration: none;
            color: inherit;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 200px;
        }

        .category-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .category-card h2 {
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .category-card p {
            color: var(--text-light);
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

            .container {
                padding: 0 1.5rem;
            }

            .hero {
                padding: 2rem 1.5rem;
            }

            .category-grid {
                grid-template-columns: 1fr;
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
                <div style="font-size:1.7rem;font-weight:700;color:#fff;line-height:1.1;">
                    Iran<span style="color:#ffc107;">Info</span>
                </div>
                <div style="font-size:0.95rem;color:#bdbdbd;margin-top:2px;">FrontOffice</div>
            </div>
            <ul>
                <li><a href="/" class="<?= $page === 'accueil' ? 'active' : '' ?>" aria-label="Accueil">Accueil</a></li>
                <li><a href="/articles" class="<?= $page === 'articles' ? 'active' : '' ?>" aria-label="Tous les articles">Articles</a></li>
                <li><a href="/categories" class="<?= $page === 'categories' ? 'active' : '' ?>" aria-label="Parcourir par catégories">Catégories</a></li>
                <li><a href="/recherche" aria-label="Rechercher des articles">Recherche</a></li>
            </ul>
        </nav>
    </header>

    <main id="main" class="container" role="main">
        <?php if ($page === 'accueil'): ?>
            <section class="hero">
                <h1>Bienvenue sur Site Iran</h1>
                <p>
                    Découvrez les dernières actualités, analyses et reportages sur l'Iran. Notre équipe vous propose 
                    des contenus en profondeur pour mieux comprendre les enjeux du pays.
                </p>
            </section>
            
            <section>
                <h2 style="font-size: 1.5rem; margin: 2rem 0 1.5rem 0;">📰 Articles récents</h2>

        <?php elseif ($page === 'articles' || $categorie_id): ?>
            <section>
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

                <!-- Formulaire filtre date publication -->
                <form method="get" style="margin-bottom:2.5rem;display:flex;gap:32px;align-items:end;background:#fff;padding:18px 28px;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,0.06);max-width:700px;">
                    <input type="hidden" name="page" value="articles">
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <label for="date_min" style="font-size:1em;font-weight:500;color:#222;">Date de publication (min)</label>
                        <input type="date" name="date_min" id="date_min" value="<?= htmlspecialchars($date_min) ?>" style="padding:7px 12px;border-radius:6px;border:1px solid #ccc;font-size:1em;">
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <label for="date_max" style="font-size:1em;font-weight:500;color:#222;">Date de publication (max)</label>
                        <input type="date" name="date_max" id="date_max" value="<?= htmlspecialchars($date_max) ?>" style="padding:7px 12px;border-radius:6px;border:1px solid #ccc;font-size:1em;">
                    </div>
                    <button type="submit" style="padding:10px 24px;font-size:1em;background:#ffc107;color:#222;border:none;border-radius:6px;font-weight:700;box-shadow:0 1px 4px rgba(0,0,0,0.07);cursor:pointer;transition:background 0.2s;">Filtrer</button>
                </form>

        <?php elseif ($page === 'categories'): ?>
            <section>
                <h1>Explorez nos catégories</h1>
                <?php
                $cat_query = $pdo->query("SELECT * FROM categorie");
                $categories = $cat_query->fetchAll();
                ?>
                <div class="category-grid">
                    <?php foreach ($categories as $cat): ?>
                        <?php
                        $count_query = $pdo->query("SELECT COUNT(*) as total FROM article WHERE categorie_id = {$cat['id']} AND statut_id = 1");
                        $count = $count_query->fetch()['total'];
                        ?>
                        <a href="/categorie/<?= strtolower(str_replace(' ', '-', $cat['nom'])) ?>-<?= $cat['id'] ?>" class="category-card" role="button" aria-label="<?= htmlspecialchars($cat['nom']) ?> - <?= $count ?> article<?= $count > 1 ? 's' : '' ?>">
                            <h2><?= htmlspecialchars($cat['nom']) ?></h2>
                            <p><?= $count ?> article<?= $count > 1 ? 's' : '' ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
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
        if ($date_min) {
            $sql .= " AND a.date_publiee >= '" . addslashes($date_min) . " 00:00:00'";
        }
        if ($date_max) {
            $sql .= " AND a.date_publiee <= '" . addslashes($date_max) . " 23:59:59'";
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
            <div class="no-articles" role="status" aria-live="polite">
                <p>Aucun article trouvé. Veuillez vérifier vos critères de recherche.</p>
            </div>
        <?php else: ?>
            <div class="articles-grid">
                <?php foreach ($articles as $article): 
                    $slug = strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9 ]/', '', $article['titre'])));
                    $article_url = "/articles/article-{$slug}-{$article['id']}.html";
                    $excerpt = substr(strip_tags($article['contenu']), 0, 150) . '...';
                    $article_date = date('d F Y \\à H\\hi', strtotime($article['date_publication']));
                ?>
                    <article class="article-card">
                        <?php if ($article['photo']): ?>
                            <img 
                                src="<?= htmlspecialchars($article['photo']) ?>" 
                                alt="<?= htmlspecialchars($article['image_alt'] ?? $article['titre']) ?>"
                                loading="lazy"
                                decoding="async"
                            >
                        <?php else: ?>
                            <div style="width: 200px; height: 150px; background: #e0e0e0; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #999;">Sans image</div>
                        <?php endif; ?>
                        <div class="article-card-content">
                            <h2>
                                <a href="<?= $article_url ?>" aria-label="<?= htmlspecialchars($article['titre']) ?>">
                                    <?= htmlspecialchars(strip_tags($article['titre'])) ?>
                                </a>
                            </h2>
                            <p class="article-card-excerpt"><?= htmlspecialchars($excerpt) ?></p>
                            <a href="<?= $article_url ?>" class="read-more" aria-label="Lire l'article: <?= htmlspecialchars(strip_tags($article['titre'])) ?>">Lire l'article →</a>
                            <div class="article-card-meta" style="margin-top: auto; margin-bottom: 0;">
                                <div style="margin-top: 1rem;">
                                    <time datetime="<?= $article['date_publiee'] ? date('Y-m-d\TH:i', strtotime($article['date_publiee'])) : '' ?>">
                                        Publié le <?= $article['date_publiee'] ? date('d/m/Y H:i', strtotime($article['date_publiee'])) : '—' ?>
                                    </time><?php if ($article['auteur']): ?> <span aria-label="par">, par</span> <strong><?= htmlspecialchars($article['auteur']) ?></strong><?php endif; ?>
                                </div>
                                <?php if ($article['categorie']): ?>
                                    <div>Catégorie : <a href="/categorie/<?= strtolower(str_replace(' ', '-', $article['categorie'])) ?>-<?= $article['categorie_id'] ?>" class="category-tag"><?= htmlspecialchars($article['categorie']) ?></a></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Pagination" class="pagination">
                    <?php if ($current_page > 1): ?>
                        <a href="?page=<?= $page ?><?= $categorie_id ? '&categorie=' . $categorie_id : '' ?>&p=<?= $current_page - 1 ?>" aria-label="Page précédente">← Précédent</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i === $current_page): ?>
                            <span class="active" aria-current="page"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?page=<?= $page ?><?= $categorie_id ? '&categorie=' . $categorie_id : '' ?>&p=<?= $i ?>" aria-label="Page <?= $i ?>">Page <?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <a href="?page=<?= $page ?><?= $categorie_id ? '&categorie=' . $categorie_id : '' ?>&p=<?= $current_page + 1 ?>" aria-label="Page suivante">Suivant →</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
            </section>
    </main>

    <footer role="contentinfo">
        <p>&copy; <?= date('Y') ?> <strong>Site Iran</strong>. Tous droits réservés.</p>
        <p style="margin-top: 0.5rem; font-size: 0.85rem;">
            <a href="/mentions-legales" aria-label="Mentions légales">Mentions légales</a> | 
            <a href="/confidentialite" aria-label="Politique de confidentialité">Politique de confidentialité</a>
        </p>
    </footer>

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "Site Iran",
        "description": "<?= htmlspecialchars($meta_description) ?>",
        "url": "<?= $_SERVER['REQUEST_SCHEME'] ?>://<?= $_SERVER['HTTP_HOST'] ?>"
    }
    </script>
</body>
</html>
