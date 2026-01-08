<?php
/* PAGE DE CONNEXION - CINEPHORIA */

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Si déjà connecté, rediriger vers l'accueil
if (is_logged_in()) {
    redirect('index.php');
}

// Traitement du formulaire
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = 'Token de sécurité invalide. Veuillez réessayer.';
    } else {
        $email = isset($_POST['email']) ? clean($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $remember = isset($_POST['remember']) ? true : false;
        
        // Validation
        if (empty($email)) {
            $errors[] = 'L\'email est requis';
        } elseif (!is_valid_email($email)) {
            $errors[] = 'L\'email n\'est pas valide';
        }
        
        if (empty($password)) {
            $errors[] = 'Le mot de passe est requis';
        }
        
        // Si pas d'erreurs, tenter la connexion
        if (empty($errors)) {
            // Chercher l'utilisateur
            $user = db_select_one(
                "SELECT * FROM users WHERE email = ?",
                [$email]
            );
            
            if ($user && verify_password($password, $user['password'])) {
                // Connexion réussie
                login_user($user);
                
                // Si "Se souvenir de moi" est coché, prolonger la session
                if ($remember) {
                    ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30); // 30 jours
                }
                
                set_flash('Bienvenue ' . $user['username'] . '!', 'success');
                
                // Rediriger vers la page demandée ou l'accueil
                $redirect_to = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : 'index.php';
                unset($_SESSION['redirect_after_login']);
                redirect($redirect_to);
            } else {
                $errors[] = 'Email ou mot de passe incorrect';
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container" style="max-width: 500px; padding: 3rem 1rem;">
    
    <!-- Carte de connexion -->
    <div style="background: var(--bg-secondary); padding: 2.5rem; border-radius: var(--radius-xl); border: 1px solid var(--border-color); box-shadow: var(--shadow-xl);">
        
        <!-- Titre -->
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--accent-cyan), var(--accent-blue)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <i class="fas fa-sign-in-alt" style="font-size: 1.5rem; color: white;"></i>
            </div>
            <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">Connexion</h1>
            <p style="color: var(--text-secondary);">Bon retour sur Cinephoria!</p>
        </div>
        
        <!-- Afficher les erreurs -->
        <?php if (!empty($errors)): ?>
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid var(--error); border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1.5rem;">
                <?php foreach ($errors as $error): ?>
                    <p style="color: var(--error); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Formulaire -->
        <form method="POST" action="">
            <?php echo csrf_field(); ?>
            
            <!-- Email -->
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                    <i class="fas fa-envelope"></i> Email
                </label>
                <input 
                    type="email" 
                    name="email" 
                    value="<?php echo isset($_POST['email']) ? clean($_POST['email']) : ''; ?>"
                    required
                    autocomplete="email"
                    style="width: 100%; padding: 0.875rem; background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 1rem;"
                    placeholder="votre@email.com"
                >
            </div>
            
            <!-- Mot de passe -->
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                    <i class="fas fa-lock"></i> Mot de passe
                </label>
                <div style="position: relative;">
                    <input 
                        type="password" 
                        name="password" 
                        id="password"
                        required
                        autocomplete="current-password"
                        style="width: 100%; padding: 0.875rem; background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 1rem; padding-right: 3rem;"
                        placeholder="••••••••"
                    >
                    <button type="button" onclick="togglePassword('password', this)" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer;">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <!-- Se souvenir de moi -->
            <div style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" name="remember" id="remember" style="width: 18px; height: 18px; accent-color: var(--accent-cyan);">
                <label for="remember" style="color: var(--text-secondary); cursor: pointer;">Se souvenir de moi</label>
            </div>
            
            <!-- Bouton de connexion -->
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 1rem; font-size: 1rem;">
                <i class="fas fa-sign-in-alt"></i>
                Se connecter
            </button>
            
        </form>
        
        <!-- Lien vers inscription -->
        <div style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
            <p style="color: var(--text-secondary);">
                Pas encore de compte?
                <a href="<?php echo url('register.php'); ?>" style="color: var(--accent-cyan); font-weight: 600;">
                    Inscrivez-vous
                </a>
            </p>
        </div>
        
        <!-- Info de test -->
        <div style="margin-top: 1.5rem; padding: 1rem; background: rgba(6, 182, 212, 0.1); border: 1px solid rgba(6, 182, 212, 0.3); border-radius: var(--radius-md);">
            <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                <i class="fas fa-info-circle"></i> <strong>Compte de test:</strong>
            </p>
            <p style="font-size: 0.85rem; color: var(--text-muted);">
                Email: <code style="background: var(--bg-tertiary); padding: 0.2rem 0.5rem; border-radius: 0.25rem;">test@cinephoria.com</code><br>
                Mot de passe: <code style="background: var(--bg-tertiary); padding: 0.2rem 0.5rem; border-radius: 0.25rem;">Test123</code>
            </p>
        </div>
        
    </div>
    
</div>

<script>
function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

<?php
include 'includes/footer.php';
?>
