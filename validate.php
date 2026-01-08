<?php
/* VALIDATION SCRIPT - CINEPHORIA */

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$errors = [];
$warnings = [];
$successes = [];

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinephoria - Validation du Système</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            color: #fff;
        }
        .container {
            background: #1e293b;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            border: 1px solid rgba(148, 163, 184, 0.2);
        }
        h1 { color: #06b6d4; text-align: center; margin-bottom: 30px; }
        h2 { color: #fff; border-bottom: 1px solid rgba(148, 163, 184, 0.2); padding-bottom: 10px; margin-top: 30px; }
        .check-item { padding: 10px 15px; margin: 8px 0; border-radius: 8px; display: flex; align-items: center; gap: 10px; }
        .success { background: rgba(16, 185, 129, 0.1); border-left: 4px solid #10b981; }
        .error { background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444; }
        .warning { background: rgba(245, 158, 11, 0.1); border-left: 4px solid #f59e0b; }
        .summary { background: rgba(6, 182, 212, 0.1); border: 1px solid rgba(6, 182, 212, 0.3); padding: 20px; border-radius: 12px; margin-top: 30px; }
        .stats { display: flex; gap: 30px; margin-bottom: 20px; }
        .stat { text-align: center; }
        .stat-number { font-size: 2rem; font-weight: 700; }
        .stat-label { color: #94a3b8; font-size: 0.9rem; }
        a { color: #06b6d4; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .btn { display: inline-block; padding: 12px 24px; background: linear-gradient(135deg, #06b6d4, #3b82f6); color: white; border-radius: 8px; font-weight: 600; margin-top: 15px; }
        code { background: #334155; padding: 2px 8px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎬 Cinephoria - Validation du Système</h1>

<?php

// 1. PHP Version
echo '<h2>📋 Version PHP</h2>';
if (version_compare(PHP_VERSION, '7.4', '>=')) {
    echo '<div class="check-item success">✓ PHP ' . PHP_VERSION . '</div>';
    $successes[] = "PHP OK";
} else {
    echo '<div class="check-item error">✗ PHP ' . PHP_VERSION . ' (7.4+ requis)</div>';
    $errors[] = "PHP too old";
}

// 2. Required Files
echo '<h2>📁 Fichiers requis</h2>';
$files = [
    'includes/config.php', 'includes/db.php', 'includes/functions.php',
    'includes/header.php', 'includes/footer.php', 'css/style.css',
    'index.php', 'login.php', 'register.php', 'logout.php',
    'movies.php', 'movie-detail.php', 'profile.php'
];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo '<div class="check-item success">✓ ' . $file . '</div>';
        $successes[] = $file;
    } else {
        echo '<div class="check-item error">✗ ' . $file . ' - MANQUANT</div>';
        $errors[] = $file;
    }
}

// 3. Required Functions
echo '<h2>🔧 Fonctions</h2>';
$functions = ['is_logged_in', 'hash_password', 'verify_password', 'login_user', 'logout_user', 'time_ago', 'generate_csrf_token', 'render_stars'];
foreach ($functions as $func) {
    if (function_exists($func)) {
        echo '<div class="check-item success">✓ ' . $func . '()</div>';
        $successes[] = $func;
    } else {
        echo '<div class="check-item error">✗ ' . $func . '() - MANQUANTE</div>';
        $errors[] = $func;
    }
}

// 4. Database
echo '<h2>🗄️ Base de données</h2>';
try {
    $db = get_db_connection();
    if ($db && $db->ping()) {
        echo '<div class="check-item success">✓ Connexion OK (' . DB_NAME . ')</div>';
        $successes[] = "DB connection";
        
        foreach (['users', 'movies', 'user_ratings', 'watchlist'] as $table) {
            $result = $db->query("SHOW TABLES LIKE '$table'");
            if ($result && $result->num_rows > 0) {
                $count = db_count($table);
                echo '<div class="check-item success">✓ Table ' . $table . ' (' . $count . ' lignes)</div>';
            } else {
                echo '<div class="check-item warning">⚠ Table ' . $table . ' manquante</div>';
                $warnings[] = $table;
            }
        }
    }
} catch (Exception $e) {
    echo '<div class="check-item error">✗ ' . $e->getMessage() . '</div>';
    $errors[] = "DB error";
}

// Summary
echo '<div class="summary">';
echo '<h3>📊 Résumé</h3>';
echo '<div class="stats">';
echo '<div class="stat"><div class="stat-number" style="color:#10b981">' . count($successes) . '</div><div class="stat-label">Succès</div></div>';
echo '<div class="stat"><div class="stat-number" style="color:#f59e0b">' . count($warnings) . '</div><div class="stat-label">Avertissements</div></div>';
echo '<div class="stat"><div class="stat-number" style="color:#ef4444">' . count($errors) . '</div><div class="stat-label">Erreurs</div></div>';
echo '</div>';

if (count($errors) === 0) {
    echo '<div class="check-item success" style="font-size:1.1rem">🎉 <strong>Système prêt!</strong></div>';
    echo '<p style="margin-top:15px">Identifiants test: <code>test@cinephoria.com</code> / <code>Test123</code></p>';
    echo '<a href="index.php" class="btn">🚀 Accéder au site</a>';
} else {
    echo '<div class="check-item error">❌ Corrections nécessaires</div>';
    if (!empty($warnings)) {
        echo '<p>👉 <a href="setup_database.php">Exécutez setup_database.php</a></p>';
    }
}
echo '</div>';

?>
    </div>
</body>
</html>
<?php close_db_connection(); ?>
