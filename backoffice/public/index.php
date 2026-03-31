<?php
session_start();
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../fonctions/auth.php';

$error = '';
if (isset($_POST['username'], $_POST['password'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $admin = get_admin_by_username($pdo, $username);
    if ($admin && verify_admin_password($admin, $password)) {
        $_SESSION['user'] = $admin['username'];
        header('Location: accueil.php');
        exit;
    } else {
        $error = 'Identifiants incorrects.';
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Backoffice IranInfo</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<div class="bo-login-page">
    <div class="bo-login-card">

        <div class="bo-login-logo">
            Iran<span>Info</span>
        </div>
        <div class="bo-login-sub">Administration — Connexion requise</div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <span>⚠</span>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" autocomplete="off">
            <div class="bo-field">
                <label class="bo-label" for="username">Utilisateur</label>
                <input class="bo-input" type="text" id="username" name="username"
                       required autofocus placeholder="admin">
            </div>
            <div class="bo-field">
                <label class="bo-label" for="password">Mot de passe</label>
                <input class="bo-input" type="password" id="password" name="password"
                       required placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                Se connecter
            </button>
        </form>

        <div style="margin-top:18px;font-size:12px;color:#888;text-align:center;border-top:1px solid #eee;padding-top:14px;">
            <strong>Démo :</strong> admin / admin123
        </div>
    </div>
</div>

</body>
</html>