<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$DEFAULT_LANG = 'en';
$supported_langs = ['en', 'fr'];
$current_lang = $DEFAULT_LANG;
$translations = [];

// Determine language: 1. URL param, 2. Session, 3. Browser (simplified), 4. Default
if (isset($_GET['lang']) && in_array($_GET['lang'], $supported_langs)) {
    $current_lang = $_GET['lang'];
    $_SESSION['lang'] = $current_lang;
} elseif (isset($_SESSION['lang']) && in_array($_SESSION['lang'], $supported_langs)) {
    $current_lang = $_SESSION['lang'];
} elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $browser_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    if (in_array($browser_lang, $supported_langs)) {
        $current_lang = $browser_lang;
    }
}

// Construct the path relative to the directory of i18n_setup.php
$lang_file_path = __DIR__ . '/../languages/' . $current_lang . '.json';

if (file_exists($lang_file_path)) {
    $json_content = file_get_contents($lang_file_path);
    if ($json_content === false) {
        error_log("Failed to read language file: " . $lang_file_path);
        $translations = [];
    } else {
        $translations = json_decode($json_content, true);
        if ($translations === null) { // Handle JSON decode error
            $translations = []; // Fallback to empty array
            error_log("Failed to decode JSON for language: " . $current_lang . ". Error: " . json_last_error_msg());
        }
    }
} else {
    error_log("Language file not found: " . $lang_file_path);
    // Optionally, try to load default language if current one fails
    if ($current_lang !== $DEFAULT_LANG) {
        $default_lang_file_path = __DIR__ . '/../languages/' . $DEFAULT_LANG . '.json';
        if (file_exists($default_lang_file_path)) {
            $json_content_default = file_get_contents($default_lang_file_path);
            if ($json_content_default !== false) {
                $translations = json_decode($json_content_default, true);
                if ($translations === null) {
                     $translations = [];
                     error_log("Failed to decode JSON for default language: " . $DEFAULT_LANG . ". Error: " . json_last_error_msg());
                }
            } else {
                 error_log("Failed to read default language file: " . $default_lang_file_path);
                 $translations = [];
            }
        } else {
             error_log("Default language file not found: " . $default_lang_file_path);
             $translations = [];
        }
    } else {
        $translations = []; // If default lang file itself is not found
    }
}

function t($key, ...$args) {
    global $translations, $DEFAULT_LANG; // Use $DEFAULT_LANG for fallback key format
    
    // Check if key exists in translations
    if (isset($translations[$key])) {
        $text = $translations[$key];
    } else {
        // Fallback: Convert key to a more readable format
        // Example: 'site_title' becomes 'Site Title'
        // This is a basic fallback, might need more sophisticated handling for complex keys
        $text = str_replace('_', ' ', $key);
        $text = ucwords($text); // Capitalize each word
        error_log("Translation missing for key: " . $key . " in language: " . $GLOBALS['current_lang']);
    }
    
    // If arguments are provided, use sprintf for replacement
    if (!empty($args) && strpos($text, '%') !== false) { // Check if text contains sprintf placeholders
        return sprintf($text, ...$args);
    }
    return $text;
}
?>
