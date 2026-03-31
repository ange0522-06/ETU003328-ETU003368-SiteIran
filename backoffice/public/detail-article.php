<?php
require_once __DIR__ . '/../app/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    echo '<p>Article introuvable.</p>';
    exit;
}

$query = $pdo->prepare('SELECT a.*, c.nom as categorie, au.nom as auteur, s.nom as statut, i.photo, i.alt as image_alt, i.largeur, i.hauteur FROM article a LEFT JOIN categorie c ON a.categorie_id = c.id LEFT JOIN auteur au ON a.auteur_id = au.id LEFT JOIN statut s ON a.statut_id = s.id LEFT JOIN image i ON a.image_id = i.id WHERE a.id = ?');
$query->execute([$id]);
$article = $query->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    echo '<p>Article introuvable.</p>';
    exit;
}

// Récupérer la première image du contenu si pas d'image principale
$imgSrc = $article['photo'];
if (!$imgSrc && preg_match('/<img[^>]+src=["\\\']([^"\\\']+)["\\\']/i', $article['contenu'], $m)) {
    $imgSrc = $m[1];
}

session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars(strip_tags($article['titre'])) ?> - Détail article</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .detail-meta { color: #666; font-size: 1.05em; margin-bottom: 18px; }
        .detail-cat { background: #ffc107; color: #222; padding: 6px 18px; border-radius: 8px; font-weight: 600; font-size: 1em; display: inline-block; margin-left: 8px; }
        .detail-content { margin-top: 24px; font-size: 1.13em; line-height: 1.7; }
    </style>
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
        <a href="liste-articles.php" class="bo-nav-item">
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
                <div class="bo-page-title">Détail de l'article</div>
                <div class="bo-breadcrumb">Backoffice › <a href="liste-articles.php">Articles</a> › <span><?= htmlspecialchars(strip_tags($article['titre'])) ?></span></div>
            </div>
        </div>
        <div class="bo-content">
            <div class="bo-card" style="max-width:100%;margin:40px 0 0 0;">
                <h1><?= htmlspecialchars(strip_tags($article['titre'])) ?></h1>
                <div class="detail-meta">
                    Publié le <?= $article['date_publiee'] ? date('d/m/Y H:i', strtotime($article['date_publiee'])) : '—' ?>
                    <?php if ($article['auteur']): ?> | Auteur : <b><?= htmlspecialchars($article['auteur']) ?></b><?php endif; ?>
                    <?php if ($article['categorie']): ?> <span class="detail-cat">Catégorie : <?= htmlspecialchars($article['categorie']) ?></span><?php endif; ?>
                </div>
                <div class="detail-content">
                    <?= $article['contenu'] ?>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
