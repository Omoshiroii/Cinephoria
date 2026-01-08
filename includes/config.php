<?php
/* FICHIER DE CONFIGURATION - CINEPHORIA */

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* CONFIGURATION DU SITE */

// Nom du site
define('SITE_NAME', 'Cinephoria');

// URL de base du site (à modifier selon votre configuration)
define('BASE_URL', 'http://localhost/cinephoria');

// Chemin absolu vers le dossier du projet
define('ROOT_PATH', __DIR__ . '/..');

/* CONFIGURATION DE LA BASE DE DONNÉES */

define('DB_HOST', 'localhost');
define('DB_NAME', 'cinephoria_db');
define('DB_USER', 'root');
define('DB_PASS', '');

/* PARAMÈTRES DE SÉCURITÉ */

define('SECRET_KEY', getenv('CINEPHORIA_SECRET_KEY') ?: 'cinephoria_secret_key_2025_change_me');
define('SESSION_LIFETIME', 86400); // 24 heures

/* PARAMÈTRES DE L'APPLICATION */

define('MOVIES_PER_PAGE', 12);
define('DEBUG_MODE', getenv('CINEPHORIA_DEBUG') ? filter_var(getenv('CINEPHORIA_DEBUG'), FILTER_VALIDATE_BOOL) : true);

/* TIMEZONE */

date_default_timezone_set('Africa/Casablanca');

/* GESTION DES ERREURS */

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

/* FONCTIONS UTILITAIRES DE BASE */

// Génère une URL complète
function url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

// Redirige vers une URL
function redirect($path) {
    header('Location: ' . url($path));
    exit();
}

// Vérifie si l'utilisateur est connecté
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Retourne l'ID de l'utilisateur connecté
function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

// Retourne le nom d'utilisateur connecté
function get_username() {
    return $_SESSION['username'] ?? null;
}

// Nettoie les données pour éviter les attaques XSS
function clean($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Définit un message flash
function set_flash($message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

// Récupère et supprime le message flash
function get_flash() {
    if (isset($_SESSION['flash_message'])) {
        $flash = [
            'message' => $_SESSION['flash_message'],
            'type' => $_SESSION['flash_type']
        ];
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return $flash;
    }
    return null;
}

require_once 'setup_database.php';

?>