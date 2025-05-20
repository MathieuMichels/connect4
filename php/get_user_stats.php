<?php
session_start();
header('Content-Type: application/json');

$users_file = '../users.json';
$response = ['error' => 'User not found or not logged in'];

if (isset($_GET['pseudo'])) {
    $pseudo = trim($_GET['pseudo']);
    
    if (file_exists($users_file) && filesize($users_file) > 0) {
        $users = json_decode(file_get_contents($users_file), true);
        $user_found = false;
        foreach ($users as $user) {
            if (isset($user['pseudo']) && $user['pseudo'] === $pseudo) {
                $response = [
                    'pseudo' => $user['pseudo'],
                    'wins' => $user['wins'],
                    'defeats' => $user['defeats'],
                    'nulls' => $user['nulls']
                ];
                $user_found = true;
                break;
            }
        }
        if (!$user_found) {
             $response = ['error' => 'User data not found in users.json'];
        }
    } else {
        $response = ['error' => 'users.json is missing or empty.'];
    }
} else {
    $response = ['error' => 'Pseudo not provided.'];
}

echo json_encode($response);
?>
