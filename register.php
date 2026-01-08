<?php
/* PAGE D'INSCRIPTION - CINEPHORIA */

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Si déjà connecté, rediriger
if (is_logged_in()) {
    redirect('index.php');
}

// Traitement du formulaire
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = 'Token de sécurité invalide. Veuillez réessayer.';
    } else {
        $username = isset($_POST['username']) ? clean($_POST['username']) : '';
        $email = isset($_POST['email']) ? clean($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';
        
        // Validation du nom d'utilisateur
        if (empty($username)) {
            $errors[] = 'Le nom d\'utilisateur est requis';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Le nom d\'utilisateur doit contenir au moins 3 caractères';
        } elseif (strlen($username) > 50) {
            $errors[] = 'Le nom d\'utilisateur ne peut pas dépasser 50 caractères';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Le nom d\'utilisateur ne peut contenir que des lettres, chiffres et underscores';
        }
        
        // Validation de l'email
        if (empty($email)) {
            $errors[] = 'L\'email est requis';
        } elseif (!is_valid_email($email)) {
            $errors[] = 'L\'email n\'est pas valide';
        }
        
        // Validation du mot de passe
        if (empty($password)) {
            $errors[] = 'Le mot de passe est requis';
        } else {
            $password_check = validate_password_strength($password);
            if (!$password_check['valid']) {
                $errors[] = $password_check['message'];
            }
        }
        
        // Confirmation du mot de passe
        if ($password !== $password_confirm) {
            $errors[] = 'Les mots de passe ne correspondent pas';
        }
        
        // Vérifier si l'utilisateur existe déjà
        if (empty($errors)) {
            $existing_email = db_select_one(
                "SELECT id FROM users WHERE email = ?",
                [$email]
            );
            
            if ($existing_email) {
                $errors[] = 'Cette adresse email est déjà utilisée';
            }
            
            $existing_username = db_select_one(
                "SELECT id FROM users WHERE username = ?",
                [$username]
            );
            
            if ($existing_username) {
                $errors[] = 'Ce nom d\'utilisateur est déjà pris';
            }
        }
        
        // Si pas d'erreurs, créer le compte
        if (empty($errors)) {
            $hashed_password = hash_password($password);
            
            $user_id = db_execute(
                "INSERT INTO users (username, email, password) VALUES (?, ?, ?)",
                [$username, $email, $hashed_password]
            );
            
            if ($user_id) {
                $success = true;
                set_flash('Compte créé avec succès! Vous pouvez maintenant vous connecter.', 'success');
                redirect('login.php');
            } else {
                $errors[] = 'Une erreur est survenue lors de la création du compte';
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container" style="max-width: 500px; padding: 3rem 1rem;">
    
    <div style="background: var(--bg-secondary); padding: 2.5rem; border-radius: var(--radius-xl); border: 1px solid var(--border-color); box-shadow: var(--shadow-xl);">
        
        <!-- Titre -->
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--accent-cyan), var(--accent-blue)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <i class="fas fa-user-plus" style="font-size: 1.5rem; color: white;"></i>
            </div>
            <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">Inscription</h1>
            <p style="color: var(--text-secondary);">Rejoignez la communauté Cinephoria</p>
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
        <form method="POST" action="" id="registerForm">
            <?php echo csrf_field(); ?>
            
            <!-- Nom d'utilisateur -->
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                    <i class="fas fa-user"></i> Nom d'utilisateur
                </label>
                <input 
                    type="text" 
                    name="username" 
                    value="<?php echo isset($_POST['username']) ? clean($_POST['username']) : ''; ?>"
                    required
                    minlength="3"
                    maxlength="50"
                    pattern="[a-zA-Z0-9_]+"
                    autocomplete="username"
                    style="width: 100%; padding: 0.875rem; background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 1rem;"
                    placeholder="johndoe"
                >
                <small style="color: var(--text-muted); font-size: 0.8rem; display: block; margin-top: 0.3rem;">
                    Lettres, chiffres et underscores uniquement
                </small>
            </div>
            
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
                    placeholder="john@example.com"
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
                        minlength="8"
                        autocomplete="new-password"
                        style="width: 100%; padding: 0.875rem; background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 1rem; padding-right: 3rem;"
                        placeholder="••••••••"
                        oninput="checkPasswordStrength(this.value)"
                    >
                    <button type="button" onclick="togglePassword('password', this)" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer;">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                
                <!-- Indicateur de force du mot de passe -->
                <div id="passwordStrength" style="margin-top: 0.5rem;">
                    <div style="display: flex; gap: 0.25rem; margin-bottom: 0.25rem;">
                        <div id="strength1" style="flex: 1; height: 4px; background: var(--bg-tertiary); border-radius: 2px; transition: background 0.3s;"></div>
                        <div id="strength2" style="flex: 1; height: 4px; background: var(--bg-tertiary); border-radius: 2px; transition: background 0.3s;"></div>
                        <div id="strength3" style="flex: 1; height: 4px; background: var(--bg-tertiary); border-radius: 2px; transition: background 0.3s;"></div>
                        <div id="strength4" style="flex: 1; height: 4px; background: var(--bg-tertiary); border-radius: 2px; transition: background 0.3s;"></div>
                    </div>
                    <small id="strengthText" style="color: var(--text-muted); font-size: 0.8rem;">
                        Min. 8 caractères avec majuscule, minuscule et chiffre
                    </small>
                </div>
            </div>
            
            <!-- Confirmation mot de passe -->
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                    <i class="fas fa-lock"></i> Confirmer le mot de passe
                </label>
                <div style="position: relative;">
                    <input 
                        type="password" 
                        name="password_confirm" 
                        id="password_confirm"
                        required
                        autocomplete="new-password"
                        style="width: 100%; padding: 0.875rem; background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 1rem; padding-right: 3rem;"
                        placeholder="••••••••"
                        oninput="checkPasswordMatch()"
                    >
                    <button type="button" onclick="togglePassword('password_confirm', this)" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer;">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <small id="matchText" style="color: var(--text-muted); font-size: 0.8rem; display: none; margin-top: 0.3rem;"></small>
            </div>
            
            <!-- Accepter les conditions -->
            <div style="margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 0.5rem;">
                <input type="checkbox" name="terms" id="terms" required style="width: 18px; height: 18px; accent-color: var(--accent-cyan); margin-top: 0.2rem;">
                <label for="terms" style="color: var(--text-secondary); cursor: pointer; font-size: 0.9rem;">
                    J'accepte les <a href="#" style="color: var(--accent-cyan);">conditions d'utilisation</a> et la <a href="#" style="color: var(--accent-cyan);">politique de confidentialité</a>
                </label>
            </div>
            
            <!-- Bouton d'inscription -->
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 1rem; font-size: 1rem;">
                <i class="fas fa-user-plus"></i>
                Créer mon compte
            </button>
            
        </form>
        
        <!-- Lien vers connexion -->
        <div style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
            <p style="color: var(--text-secondary);">
                Déjà un compte?
                <a href="<?php echo url('login.php'); ?>" style="color: var(--accent-cyan); font-weight: 600;">
                    Connectez-vous
                </a>
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

function checkPasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]/)) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    
    const colors = ['#ef4444', '#f59e0b', '#fbbf24', '#10b981'];
    const texts = ['Faible', 'Moyen', 'Bon', 'Excellent'];
    
    for (let i = 1; i <= 4; i++) {
        const bar = document.getElementById('strength' + i);
        if (i <= strength) {
            bar.style.background = colors[strength - 1];
        } else {
            bar.style.background = 'var(--bg-tertiary)';
        }
    }
    
    const strengthText = document.getElementById('strengthText');
    if (password.length > 0) {
        strengthText.textContent = 'Force: ' + texts[strength - 1] || 'Très faible';
        strengthText.style.color = colors[strength - 1] || '#ef4444';
    } else {
        strengthText.textContent = 'Min. 8 caractères avec majuscule, minuscule et chiffre';
        strengthText.style.color = 'var(--text-muted)';
    }
    
    checkPasswordMatch();
}

function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('password_confirm').value;
    const matchText = document.getElementById('matchText');
    
    if (confirm.length > 0) {
        matchText.style.display = 'block';
        if (password === confirm) {
            matchText.textContent = '✓ Les mots de passe correspondent';
            matchText.style.color = '#10b981';
        } else {
            matchText.textContent = '✗ Les mots de passe ne correspondent pas';
            matchText.style.color = '#ef4444';
        }
    } else {
        matchText.style.display = 'none';
    }
}
</script>

<?php
include 'includes/footer.php';
?>
