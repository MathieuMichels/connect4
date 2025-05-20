<?php require_once 'php/i18n_setup.php'; // Includes session_start() and loads translations ?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($current_lang); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('landing_page_title'); ?></title>
    <link rel="stylesheet" href="css/styles.css"> <!-- General styles -->
    <link rel="stylesheet" href="css/landing.css"> <!-- Landing page specific styles -->
</head>
<body class="landing-page"> <!-- Added class for landing-page specific body styles -->
    <div class="landing-container">
        <header class="landing-header">
            <!-- Simple header for landing page, can be different from main game header -->
            <div class="header-wrap">
                 <h1><?php echo t('main_title'); ?></h1> <!-- Re-use main_title or create a specific one -->
                 <nav class="landing-nav">
                    <a href="index.php?lang=<?php echo $current_lang; ?>#game-creation-lobby"><?php echo t('landing_play_now_button'); ?></a>
                    <!-- Add other navigation links if needed, e.g., to #help-window on index.php -->
                 </nav>
            </div>
        </header>

        <section class="hero-section">
            <div class="hero-content">
                <h1><?php echo t('landing_hero_title'); ?></h1>
                <p class="tagline"><?php echo t('landing_tagline'); ?></p>
                <a href="index.php?lang=<?php echo $current_lang; ?>" class="play-now-btn primary-action-btn"><?php echo t('landing_play_now_button'); ?></a>
            </div>
        </section>

        <section class="features-section">
            <div class="features-grid">
                <div class="feature-item">
                    <img src="assets/icons/multiplayer_icon.svg" alt="Multiplayer Icon" class="feature-icon" /> <!-- Placeholder icon -->
                    <h3><?php echo t('landing_feature_multiplayer_title'); ?></h3>
                    <p><?php echo t('landing_feature_multiplayer_desc'); ?></p>
                </div>
                <div class="feature-item">
                    <img src="assets/icons/ai_icon.svg" alt="AI Icon" class="feature-icon" /> <!-- Placeholder icon -->
                    <h3><?php echo t('landing_feature_ai_title'); ?></h3>
                    <p><?php echo t('landing_feature_ai_desc'); ?></p>
                </div>
                <div class="feature-item">
                     <img src="assets/icons/easy_icon.svg" alt="Easy to Learn Icon" class="feature-icon" /> <!-- Placeholder icon -->
                    <h3><?php echo t('landing_feature_easy_title'); ?></h3>
                    <p><?php echo t('landing_feature_easy_desc'); ?></p>
                </div>
            </div>
        </section>

        <footer class="landing-footer">
            <p>&copy; <?php echo date("Y"); ?> <?php echo t('main_title'); ?>. <?php /* echo t('all_rights_reserved'); */ // Optional: Add if key exists ?></p>
            <p><a href="index.php?lang=<?php echo $current_lang; ?>">Play Game</a> | <a href="https://github.com/MathieuMichels/connect4" target="_blank">GitHub</a></p>
        </footer>
    </div>
</body>
</html>
