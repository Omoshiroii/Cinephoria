<?php
/* EN-TÊTE DU SITE - CINEPHORIA */

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Découvrez et suivez vos films préférés</title>
    <meta name="description" content="Cinephoria - Découvrez, notez et partagez vos films préférés avec une communauté de cinéphiles passionnés.">
    
    <link rel="stylesheet" href="<?php echo url('css/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <!-- Logo -->
                <div class="logo">
                    <a href="<?php echo url('index.php'); ?>">
                        <i class="fas fa-film"></i>
                        <span class="logo-text">Cinephoria</span>
                    </a>
                </div>
                
                <!-- Menu de navigation -->
                <ul class="nav-menu" id="navMenu">
                    <li>
                        <a href="<?php echo url('index.php'); ?>" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i> Accueil
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo url('movies.php'); ?>" class="<?php echo ($current_page == 'movies.php') ? 'active' : ''; ?>">
                            <i class="fas fa-video"></i> Films
                        </a>
                    </li>
                    <?php if (is_logged_in()): ?>
                        <li>
                            <a href="<?php echo url('profile.php'); ?>" class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
                                <i class="fas fa-user"></i> Mon Profil
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <!-- Barre de recherche -->
                <div class="search-bar">
                    <form action="<?php echo url('movies.php'); ?>" method="GET">
                        <div class="search-input-wrapper">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" placeholder="Rechercher un film..." class="search-input"
                                value="<?php echo isset($_GET['search']) ? clean($_GET['search']) : ''; ?>">
                        </div>
                    </form>
                </div>
                
                <!-- Boutons d'authentification -->
                <div class="auth-buttons">
                    <?php if (is_logged_in()): ?>
                        <div class="user-info">
                            <span class="welcome-text">
                                <i class="fas fa-user-circle"></i>
                                <strong><?php echo clean(get_username()); ?></strong>
                            </span>
                            <a href="<?php echo url('logout.php'); ?>" class="btn btn-ghost">
                                <i class="fas fa-sign-out-alt"></i> Déconnexion
                            </a>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo url('login.php'); ?>" class="btn btn-ghost">
                            <i class="fas fa-sign-in-alt"></i> Connexion
                        </a>
                        <a href="<?php echo url('register.php'); ?>" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Inscription
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Toggle menu mobile -->
                <button class="mobile-menu-toggle" id="mobileMenuToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </nav>
        </div>
    </header>
    
    <!-- Messages flash -->
    <?php
    $flash = get_flash();
    if ($flash):
    ?>
        <div class="flash-message flash-<?php echo $flash['type']; ?>">
            <div class="container">
                <div class="flash-content">
                    <?php if ($flash['type'] == 'success'): ?>
                        <i class="fas fa-check-circle"></i>
                    <?php elseif ($flash['type'] == 'error'): ?>
                        <i class="fas fa-exclamation-circle"></i>
                    <?php else: ?>
                        <i class="fas fa-info-circle"></i>
                    <?php endif; ?>
                    <span><?php echo clean($flash['message']); ?></span>
                    <button class="flash-close" onclick="this.parentElement.parentElement.parentElement.style.display='none'">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <main class="main-content">
