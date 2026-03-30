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

// Déconnexion
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
    <title>Connexion Backoffice</title>
    <meta name="robots" content="noindex, nofollow">
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7f7; }
        .login-box { max-width: 350px; margin: 60px auto; background: #fff; border-radius: 8px; box-shadow: 0 4px 24px #0001; padding: 32px 28px; }
        h1 { text-align: center; font-size: 1.5rem; margin-bottom: 24px; }
        label { display: block; margin-bottom: 6px; font-weight: bold; }
        input[type=text], input[type=password] { width: 100%; padding: 8px 10px; margin-bottom: 18px; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 10px; background: #0066cc; color: #fff; border: none; border-radius: 4px; font-size: 1rem; cursor: pointer; }
        .error { color: #c00; margin-bottom: 12px; text-align: center; }
        .logout { text-align: right; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>Connexion Backoffice</h1>
        <?php if ($error): ?>
            <div class="error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <label for="username">Utilisateur</label>
            <input type="text" id="username" name="username" required autofocus placeholder="admin">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required placeholder="Mot de passe">
            <button type="submit">Se connecter</button>
        </form>
        <div style="margin-top:18px; font-size:0.95em; color:#666; text-align:center;">
            <strong>Démo :</strong> admin / admin123
        </div>
    </div>
</body>
</html>