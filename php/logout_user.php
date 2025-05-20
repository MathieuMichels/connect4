<?php
session_start();

// Unset the pseudo session variable
if (isset($_SESSION['pseudo'])) {
    unset($_SESSION['pseudo']);
}

// Destroy the session
session_destroy();

// Redirect to the home page
header('Location: ../index.php');
exit;
?>
