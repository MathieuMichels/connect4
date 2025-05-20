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
        file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));
        // error_log("User registered: " . $new_user['pseudo'] . " | users.json content: " . json_encode($users)); // Debug log removed
        $_SESSION['pseudo'] = $pseudo;
    } else {
        // Pseudo already exists, just set the session for login
        $_SESSION['pseudo'] = $pseudo; 
    }

    // Handle response type based on source_request
    if (isset($_POST['source_request']) && $_POST['source_request'] == 'js_quick_join') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'pseudo' => $_SESSION['pseudo']]);
        exit;
    } else {
        header('Location: ../index.php');
        exit;
    }

} else { // Not a POST request or pseudo not set
    if (isset($_POST['source_request']) && $_POST['source_request'] == 'js_quick_join') {
        header('Content-Type: application/json');
        // Check if it's just a missing pseudo for a quick_join request
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['pseudo'])) {
             echo json_encode(['success' => false, 'message' => 'Pseudo not provided for quick join.']);
        } else {
             echo json_encode(['success' => false, 'message' => 'Invalid request for quick join.']);
        }
        exit;
    } else {
        // Standard redirect for other cases
        header('Location: ../index.php');
        exit;
    }
}
?>
