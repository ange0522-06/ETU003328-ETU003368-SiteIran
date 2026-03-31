<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../app/db.php';

// Stats rapides
$nb_articles  = $pdo->query("SELECT COUNT(*) FROM article")->fetchColumn();
$nb_publies   = $pdo->query("SELECT COUNT(*) FROM article WHERE statut_id = 1")->fetchColumn();
$nb_brouillon = $pdo->query("SELECT COUNT(*) FROM article WHERE statut_id = 2")->fetchColumn();
$nb_categories= $pdo->query("SELECT COUNT(*) FROM categorie")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil — Backoffice IranInfo</title>
    <meta name="description" content="Tableau de bord du backoffice IranInfo. Gérer les articles, catégories et utilisateurs.">
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
        <a href="accueil.php" class="bo-nav-item active">
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
                <div class="bo-page-title">Tableau de bord</div>
                <div class="bo-breadcrumb">Backoffice › Accueil</div>
            </div>
            <div class="bo-topbar-actions">
                <a href="ajout-article.php" class="btn btn-primary">+ Nouvel article</a>
            </div>
        </div>

        <div class="bo-content">

            <!-- STATS -->
            <div class="bo-stats-grid">
                <div class="bo-stat-card">
                    <div class="bo-stat-val"><?= $nb_articles ?></div>
                    <div class="bo-stat-label">Total articles</div>
                </div>
                <div class="bo-stat-card">
                    <div class="bo-stat-val"><?= $nb_publies ?></div>
                    <div class="bo-stat-label">Publiés</div>
                    <div class="bo-stat-delta up">En ligne</div>
                </div>
                <div class="bo-stat-card">
                    <div class="bo-stat-val"><?= $nb_brouillon ?></div>
                    <div class="bo-stat-label">Brouillons</div>
                    <div class="bo-stat-delta" style="color:var(--bo-ambre)">En attente</div>
                </div>
                <div class="bo-stat-card">
                    <div class="bo-stat-val"><?= $nb_categories ?></div>
                    <div class="bo-stat-label">Catégories</div>
                </div>
            </div>

            <!-- ACTIONS RAPIDES -->
            <div class="bo-card">
                <div class="bo-card-header">
                    <div class="bo-card-title">Actions rapides</div>
                </div>
                <div class="bo-card-body" style="display:flex;gap:12px;flex-wrap:wrap;">
                    <a href="ajout-article.php" class="btn btn-primary">+ Nouvel article</a>
                    <a href="liste-articles.php" class="btn btn-ghost">Voir tous les articles</a>
                </div>
            </div>

            <!-- DERNIERS ARTICLES -->
            <?php
            $derniers = $pdo->query(
                "SELECT a.titre, a.date_publication, c.nom AS categorie,
                        s.nom AS statut
                 FROM article a
                 LEFT JOIN categorie c ON a.categorie_id = c.id
                 LEFT JOIN statut s    ON a.statut_id = s.id
                 ORDER BY a.date_publication DESC LIMIT 5"
            )->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <div class="bo-card">
                <div class="bo-card-header">
                    <div class="bo-card-title">Derniers articles</div>
                    <a href="liste-articles.php" class="btn btn-ghost btn-sm">Voir tout</a>
                </div>
                <div class="bo-table-wrap">
                    <table class="bo-table">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Catégorie</th>
                                <th>Date</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($derniers as $a): ?>
                            <tr>
                                <td><?= htmlspecialchars(strip_tags($a['titre'])) ?></td>
                                <td><?= htmlspecialchars($a['categorie'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($a['date_publication'] ?? '—') ?></td>
                                <td>
                                    <?php
                                    $s = strtolower($a['statut'] ?? '');
                                    $cls = $s === 'publié' ? 'badge-publie' : ($s === 'brouillon' ? 'badge-brouillon' : 'badge-archive');
                                    ?>
                                    <span class="badge <?= $cls ?>"><?= htmlspecialchars($a['statut'] ?? '—') ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div><!-- /bo-content -->
    </main><!-- /bo-main -->
</div><!-- /bo-layout -->

</body>
</html>