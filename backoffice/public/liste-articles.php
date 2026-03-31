<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../fonctions/liste_articles.php';
require_once __DIR__ . '/../app/db.php';

// Traitement des actions de statut
if (isset($_POST['action'], $_POST['article_id'])) {
    $id = (int)$_POST['article_id'];
    if ($_POST['action'] === 'publier') {
        // Statut publié (id=1)
        $pdo->prepare('UPDATE article SET statut_id = 1 WHERE id = ?')->execute([$id]);
    } elseif ($_POST['action'] === 'archiver') {
        // Statut archivé (id=3)
        $pdo->prepare('UPDATE article SET statut_id = 3 WHERE id = ?')->execute([$id]);
    }
}

$articles = get_articles();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des articles — Backoffice IranInfo</title>
    <meta name="description" content="Liste et gestion des articles du backoffice IranInfo. Filtrer, publier, archiver ou éditer les articles.">
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<a href="#main-content" class="skip-link" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;background:#fff;color:#222;z-index:1000;">Aller au contenu principal</a>
<div class="bo-layout">

    <!-- ── SIDEBAR ── -->
    <aside class="bo-sidebar">
        <div class="bo-sidebar-logo">
            <div class="bo-sidebar-logo-name">Iran<span>Info</span></div>
            <div class="bo-sidebar-logo-sub">Backoffice v1.0</div>
        </div>
        <div class="bo-sidebar-user">
            <div class="bo-user-avatar"><?= strtoupper(substr($_SESSION['user'], 0, 2)) ?></div>
            <div>
                <div class="bo-user-name"><?= htmlspecialchars($_SESSION['user']) ?></div>
                <div class="bo-user-info">Administrateur</div>
            </div>
        </div>

        <div class="bo-nav-section">Contenu</div>
        <a href="accueil.php" class="bo-nav-item">
            <div class="bo-nav-icon"></div>Tableau de bord
        </a>
        <a href="ajout-article.php" class="bo-nav-item">
            <div class="bo-nav-icon"></div>Ajouter un article
        </a>
        <a href="liste-articles.php" class="bo-nav-item active">
            <div class="bo-nav-icon"></div>Liste des articles
        </a>

        <div class="bo-nav-section">Système</div>
        <a href="index.php?logout=1" class="bo-nav-item danger">
            <div class="bo-nav-icon"></div>Déconnexion
        </a>
    </aside>

    <!-- ── MAIN ── -->
    <main id="main-content" class="bo-main" tabindex="-1" aria-label="Contenu principal">

        <div class="bo-topbar">
            <div>
                <div class="bo-page-title">Liste des articles</div>
                <div class="bo-breadcrumb">Backoffice › <span>Articles</span></div>
            </div>
            <div class="bo-topbar-actions">
                <div class="bo-search">
                    <input type="text" placeholder="Rechercher..." id="searchInput" oninput="filterTable()">
                    <button type="button">Chercher</button>
                </div>
                <a href="ajout-article.php" class="btn btn-primary">+ Nouvel article</a>
            </div>
        </div>

        <div class="bo-content">
            <div style="display:flex;flex-wrap:wrap;gap:24px;">
            <?php foreach ($articles as $article): ?>
                <div class="bo-card" style="width:420px;flex:0 0 420px;display:flex;flex-direction:column;justify-content:space-between;">
                    <div>
                        <div class="bo-card-header" style="font-size:1.2em;font-weight:bold;">
                            <?= htmlspecialchars(strip_tags($article['titre'])) ?>
                        </div>
                        <div style="margin:8px 0 8px 0;">
                            <span style="color:#888;font-size:13px;">Auteur :</span> <?= htmlspecialchars($article['auteur'] ?? '—') ?>
                            &nbsp;|&nbsp;
                            <span style="color:#888;font-size:13px;">Catégorie :</span> <?= htmlspecialchars($article['categorie'] ?? '—') ?>
                        </div>
                        <div style="color:#888;font-size:12px;">Date : <?= htmlspecialchars($article['date_publication'] ?? '—') ?></div>
                        <div style="margin:8px 0;">
                            <?php
                            $s = strtolower($article['statut'] ?? '');
                            if ($s === 'publié' || $s === 'publie') {
                                echo '<span class="badge badge-publie">Publié</span>';
                            } elseif ($s === 'brouillon') {
                                echo '<span class="badge badge-brouillon">Brouillon</span>';
                            } else {
                                echo '<span class="badge badge-archive">' . htmlspecialchars($article['statut'] ?? 'Inconnu') . '</span>';
                            }
                            ?>
                        </div>
                        <div style="margin-bottom:8px;">
                            <?php
                            if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $article['contenu'], $m)) {
                                echo '<img src="' . htmlspecialchars($m[1]) . '" alt="' . htmlspecialchars(strip_tags($article['titre'])) . '" style="width:100%;max-width:320px;height:120px;object-fit:cover;border-radius:4px;display:block;margin-bottom:8px;">';
                            }
                            ?>
                        </div>
                        <div style="background:#f8f8f8;border-radius:6px;padding:12px 14px;min-height:60px;max-height:120px;overflow:auto;">
                            <?= $article['contenu'] ?>
                        </div>
                    </div>
                    <form method="post" style="margin-top:14px;display:flex;gap:10px;">
                        <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                        <button type="submit" name="action" value="publier" class="btn btn-success btn-sm">Publier</button>
                        <button type="submit" name="action" value="archiver" class="btn btn-warning btn-sm">Archiver</button>
                        <a href="ajout-article.php?id=<?= $article['id'] ?? '' ?>" class="btn btn-ghost btn-sm">Éditer</a>
                    </form>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>

<script>
function filterTable() {
    var q = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#articlesTable tbody tr').forEach(function(row) {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
</script>

</body>
</html>