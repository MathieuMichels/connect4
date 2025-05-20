<?php
session_start();

$users_file = '../users.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pseudo'])) {
    $pseudo = trim($_POST['pseudo']);

    if (empty($pseudo)) {
        // Handle empty pseudo if necessary
        header('Location: ../index.php?error=empty_pseudo_login');
        exit;
    }

    $users = [];
    if (file_exists($users_file) && filesize($users_file) > 0) {
        $users = json_decode(file_get_contents($users_file), true);
    } else {
        // If users.json doesn't exist or is empty, no user can log in
        header('Location: ../index.php?error=no_users');
        exit;
    }

    $pseudo_found = false;
    foreach ($users as $user) {
        if (isset($user['pseudo']) && $user['pseudo'] === $pseudo) {
            $pseudo_found = true;
            break;
        }
    }

    if ($pseudo_found) {
        $_SESSION['pseudo'] = $pseudo;
        header('Location: ../index.php');
        exit;
    } else {
        // Pseudo not found
        header('Location: ../index.php?error=pseudo_not_found');
        exit;
    }
} else {
    // Redirect if not a POST request or pseudo is not set
    header('Location: ../index.php');
    exit;
}
?>
