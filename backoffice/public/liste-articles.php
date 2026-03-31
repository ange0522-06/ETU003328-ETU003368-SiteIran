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
        $pdo->prepare('UPDATE article SET statut_id = 1, date_publiee = NOW() WHERE id = ?')->execute([$id]);
    } elseif ($_POST['action'] === 'archiver') {
        // Statut archivé (id=3)
        $pdo->prepare('UPDATE article SET statut_id = 3 WHERE id = ?')->execute([$id]);
    }
}

$articles = get_articles();

// Traitement de l'édition
if (isset($_POST['edit_id'])) {
    $edit_id = (int)$_POST['edit_id'];
    $edit_titre = trim($_POST['edit_titre'] ?? '');
    $edit_categorie = (int)($_POST['edit_categorie'] ?? 0);
    $edit_contenu = trim($_POST['edit_contenu'] ?? '');
    $stmt = $pdo->prepare('UPDATE article SET titre = ?, categorie_id = ?, contenu = ? WHERE id = ?');
    $stmt->execute([$edit_titre, $edit_categorie, $edit_contenu, $edit_id]);
    // Rafraîchir les articles après édition
    $articles = get_articles();
}

// Récupérer les catégories pour l'édition
$categories = $pdo->query('SELECT id, nom FROM categorie')->fetchAll(PDO::FETCH_ASSOC);
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
                <form class="bo-search" style="width:350px;max-width:100%;display:flex;gap:8px;" onsubmit="event.preventDefault();filterCards();">
                    <input type="text" placeholder="Rechercher..." id="searchInput" style="width:100%;padding:10px 14px;font-size:15px;border-radius:6px;border:1px solid #ccc;" oninput="filterCards()">
                    <button type="submit" class="btn btn-primary" style="padding:10px 18px;font-size:15px;">Rechercher</button>
                </form>
                <a href="ajout-article.php" class="btn btn-primary">+ Nouvel article</a>
            </div>
        </div>

        <div class="bo-content">
            <div id="articlesGrid" style="display:flex;flex-direction:column;gap:40px;align-items:center;">
            <?php foreach ($articles as $article): ?>
                <div class="article-card bo-card" data-search="<?= htmlspecialchars(strtolower($article['titre'].' '.$article['auteur'].' '.$article['categorie'].' '.$article['contenu'])) ?>" style="display:flex;flex-direction:row;align-items:center;gap:32px;width:90%;max-width:1100px;min-height:180px;padding:32px 40px;box-shadow:0 2px 16px rgba(0,0,0,0.07);">
                    <?php if (isset($_POST['edit']) && $_POST['edit'] == $article['id']): ?>
                        <form method="post" style="display:flex;flex-direction:column;gap:10px;width:100%;">
                            <input type="hidden" name="edit_id" value="<?= $article['id'] ?>">
                            <label>Titre : <input type="text" name="edit_titre" value="<?= htmlspecialchars($article['titre']) ?>" required style="width:100%;padding:8px;"></label>
                            <label>Catégorie :
                                <select name="edit_categorie" style="width:100%;padding:8px;">
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $article['categorie_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>Contenu :
                                <textarea name="edit_contenu" rows="5" style="width:100%;padding:8px;resize:vertical;"><?= htmlspecialchars($article['contenu']) ?></textarea>
                            </label>
                            <div style="display:flex;gap:10px;justify-content:flex-end;">
                                <button type="submit" class="btn btn-success btn-sm">Enregistrer</button>
                                <button type="button" class="btn btn-ghost btn-sm" onclick="window.location.href=window.location.href">Annuler</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <?php
                        $img = '';
                        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $article['contenu'], $m)) {
                            $img = '<img src="' . htmlspecialchars($m[1]) . '" alt="image1" style="width:120px;height:90px;object-fit:cover;border-radius:6px;box-shadow:0 1px 6px rgba(0,0,0,0.08);margin-right:24px;">';
                        } else {
                            $img = '<div style="width:120px;height:90px;background:#f0f0f0;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#aaa;font-size:1.1em;margin-right:24px;">image1</div>';
                        }
                        ?>
                        <?= $img ?>
                        <div style="flex:1;display:flex;flex-direction:column;gap:10px;">
                            <div style="font-size:1.1em;font-weight:600;color:#444;"><?= htmlspecialchars(strip_tags($article['titre'])) ?> <?= mb_strlen(strip_tags($article['contenu'])) > 20 ? '...' : '' ?></div>
                            <a href="detail-article.php?id=<?= $article['id'] ?>" target="_blank" style="font-weight:700;color:#222;text-decoration:none;font-size:1.05em;">Lire l'article →</a>
                            <div style="font-size:0.98em;color:#666;">
                                Publié le <?= htmlspecialchars($article['date_publiee'] ? date('d/m/Y H:i', strtotime($article['date_publiee'])) : '—') ?>
                            </div>
                            <div style="margin-top:4px;">Catégorie : <span style="background:#ffc107;color:#222;padding:6px 18px;border-radius:8px;font-weight:600;font-size:0.98em;display:inline-block;"><?= htmlspecialchars($article['categorie'] ?? '—') ?></span></div>
                            <div style="margin-top:8px;">
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
                            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px;">
                                <form method="post" style="margin:0;">
                                    <input type="hidden" name="edit" value="<?= $article['id'] ?>">
                                    <button type="submit" class="btn btn-ghost btn-sm">Éditer</button>
                                </form>
                                <form method="post" style="margin:0;">
                                    <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                                    <?php
                                    $s = strtolower($article['statut'] ?? '');
                                    if ($s === 'brouillon') {
                                        echo '<button type="submit" name="action" value="publier" class="btn btn-success btn-sm">Publier</button>';
                                        echo '<button type="submit" name="action" value="archiver" class="btn btn-warning btn-sm">Archiver</button>';
                                    }
                                    ?>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>

<script>
function filterCards() {
    var q = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('.article-card').forEach(function(card) {
        card.style.display = card.getAttribute('data-search').includes(q) ? '' : 'none';
    });
}
</script>

</body>
</html>