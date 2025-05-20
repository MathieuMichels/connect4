<?php
session_start();

$users_file = '../users.json';
$users = [];

if (file_exists($users_file) && filesize($users_file) > 0) {
    $users = json_decode(file_get_contents($users_file), true);
} else {
    // Initialize with an empty array if the file doesn't exist or is empty
    file_put_contents($users_file, json_encode([]));
    $users = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pseudo'])) {
    $pseudo = trim($_POST['pseudo']);

    if (empty($pseudo)) {
        // Handle empty pseudo if necessary, maybe redirect with an error
        header('Location: ../index.php?error=empty_pseudo');
        exit;
    }

    $pseudo_exists = false;
    foreach ($users as $user) {
        if (isset($user['pseudo']) && $user['pseudo'] === $pseudo) {
            $pseudo_exists = true;
            break;
        }
    }

    if (!$pseudo_exists) {
        $new_user = [
            'id' => count($users) + 1, // Simple incrementing ID
            'pseudo' => $pseudo,
            'wins' => 0,
            'defeats' => 0,
            'nulls' => 0
        ];
        $users[] = $new_user;
        if (file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT))) {
            error_log("User registered: " . $new_user['pseudo'] . " | users.json content: " . json_encode($users));
        }
        $_SESSION['pseudo'] = $pseudo;
    } else {
        // Optionally, handle pseudo already exists error, e.g., redirect with error
        // For now, we'll just redirect to index. If pseudo exists, login should be used.
        // Or, we can log them in if pseudo exists
        $_SESSION['pseudo'] = $pseudo; // Log in the user if pseudo already exists
    }
    header('Location: ../index.php');
    exit;
} else {
    // Redirect if not a POST request or pseudo is not set
    header('Location: ../index.php');
    exit;
}
?>
