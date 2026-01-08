<?php
/* HELPER FUNCTIONS - CINEPHORIA */

// Valide qu'une adresse email est correcte
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Hash un mot de passe avec password_hash
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

// Vérifie qu'un mot de passe correspond à son hash
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

// Valide la force d'un mot de passe, retourne ['valid' => bool, 'message' => string]
function validate_password_strength($password) {
    if (strlen($password) < 8) {
        return [
            'valid' => false,
            'message' => 'Le mot de passe doit contenir au moins 8 caractères'
        ];
    }
    
    $has_uppercase = preg_match('/[A-Z]/', $password);
    $has_lowercase = preg_match('/[a-z]/', $password);
    $has_number = preg_match('/[0-9]/', $password);
    
    if (!($has_uppercase && $has_lowercase && $has_number)) {
        return [
            'valid' => false,
            'message' => 'Le mot de passe doit contenir des majuscules, minuscules et des chiffres'
        ];
    }
    
    return ['valid' => true, 'message' => 'Mot de passe valide'];
}

// Connecte un utilisateur en créant la session
function login_user($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['logged_in_at'] = time();
    
    // Regenerate session ID for security
    session_regenerate_id(true);
}

// Déconnecte l'utilisateur
function logout_user() {
    $_SESSION = array();    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

// Vérifie que l'utilisateur est connecté, sinon redirige vers login
function require_login() {
    if (!is_logged_in()) {
        set_flash('Vous devez être connecté pour accéder à cette page.', 'error');
        redirect('login.php');
    }
}

// Formate une date d'affichage (YYYY-MM-DD -> DD/MM/YYYY)
function format_date($date) {
    if (empty($date)) return '';
    return date('d/m/Y', strtotime($date));
}

// Formate une date en temps relatif (il y a X minutes/heures/jours)
function time_ago($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'À l\'instant';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return 'Il y a ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '');
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return 'Il y a ' . $hours . ' heure' . ($hours > 1 ? 's' : '');
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return 'Il y a ' . $days . ' jour' . ($days > 1 ? 's' : '');
    } elseif ($diff < 2592000) {
        $weeks = floor($diff / 604800);
        return 'Il y a ' . $weeks . ' semaine' . ($weeks > 1 ? 's' : '');
    } elseif ($diff < 31536000) {
        $months = floor($diff / 2592000);
        return 'Il y a ' . $months . ' mois';
    } else {
        $years = floor($diff / 31536000);
        return 'Il y a ' . $years . ' an' . ($years > 1 ? 's' : '');
    }
}

// Tronque un texte à une longueur donnée
function truncate_text($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

// Obtient les initiales d'un nom
function get_initials($name) {
    $parts = explode(' ', trim($name));
    $initials = '';
    foreach ($parts as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }
    return substr($initials, 0, 2);
}

// Génère un token CSRF
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Vérifie le token CSRF
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Affiche un champ CSRF caché pour les formulaires
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . generate_csrf_token() . '">';
}

// Calcule la moyenne des notes d'un film
function calculate_movie_average_rating($movie_id) {
    $result = db_select_one(
        "SELECT AVG(rating) as avg_rating, COUNT(*) as total_ratings FROM user_ratings WHERE movie_id = ?",
        [$movie_id]
    );
    
    return [
        'average' => $result['avg_rating'] ? round($result['avg_rating'], 1) : 0,
        'total' => $result['total_ratings'] ?? 0
    ];
}

// Met à jour la note moyenne d'un film dans la table movies
function update_movie_rating($movie_id) {
    $stats = calculate_movie_average_rating($movie_id);
    db_execute(
        "UPDATE movies SET rating = ? WHERE id = ?",
        [$stats['average'], $movie_id]
    );
}

// Génère des étoiles HTML basées sur une note
function render_stars($rating, $max = 10) {
    $stars_count = floor($rating / 2); // Convert 10-scale to 5-star
    $half_star = ($rating / 2) - $stars_count >= 0.5;
    $output = '';
    
    for ($i = 0; $i < 5; $i++) {
        if ($i < $stars_count) {
            $output .= '<i class="fas fa-star" style="color: #fbbf24;"></i>';
        } elseif ($i == $stars_count && $half_star) {
            $output .= '<i class="fas fa-star-half-alt" style="color: #fbbf24;"></i>';
        } else {
            $output .= '<i class="far fa-star" style="color: var(--text-muted);"></i>';
        }
    }
    
    return $output;
}

// Obtient les statistiques d'un utilisateur
function get_user_stats($user_id) {
    $ratings_count = db_count('user_ratings', 'user_id = ?', [$user_id]);
    $watchlist_count = db_count('watchlist', 'user_id = ?', [$user_id]);
    
    $avg_rating = db_select_one(
        "SELECT AVG(rating) as avg FROM user_ratings WHERE user_id = ?",
        [$user_id]
    );
    
    return [
        'total_ratings' => $ratings_count,
        'watchlist_count' => $watchlist_count,
        'average_rating' => $avg_rating['avg'] ? round($avg_rating['avg'], 1) : 0
    ];
}

// Vérifie si un film est dans la watchlist de l'utilisateur
function is_in_watchlist($user_id, $movie_id) {
    $result = db_select_one(
        "SELECT id FROM watchlist WHERE user_id = ? AND movie_id = ?",
        [$user_id, $movie_id]
    );
    return !empty($result);
}

// Obtient la note d'un utilisateur pour un film
function get_user_movie_rating($user_id, $movie_id) {
    return db_select_one(
        "SELECT * FROM user_ratings WHERE user_id = ? AND movie_id = ?",
        [$user_id, $movie_id]
    );
}

?>
