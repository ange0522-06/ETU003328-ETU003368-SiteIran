<?php

function get_admin_by_username($pdo, $username) {
    $stmt = $pdo->prepare('SELECT * FROM admin WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function verify_admin_password($admin, $password) {
    if (isset($admin['password'])) {
        if (strlen($admin['password']) === 60 && preg_match('/^\$2y\$/', $admin['password'])) {
            return password_verify($password, $admin['password']);
        } else {
            return $admin['password'] === $password;
        }
    }
    return false;
}
function hash_all_admin_passwords($pdo) {
    $admins = $pdo->query('SELECT id, password FROM admin')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($admins as $admin) {
        $plain = $admin['password'];
        if (strlen($plain) !== 60 || !preg_match('/^\$2y\$/', $plain)) {
            $hash = password_hash($plain, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('UPDATE admin SET password = ? WHERE id = ?');
            $stmt->execute([$hash, $admin['id']]);
        }
    }
}

?>
