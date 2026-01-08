<?php
/* DÉCONNEXION - CINEPHORIA */

require_once 'includes/config.php';
require_once 'includes/functions.php';

// Déconnecter l'utilisateur
logout_user();

// Message de confirmation
set_flash('Vous avez été déconnecté avec succès. À bientôt!', 'success');

// Rediriger vers l'accueil
redirect('index.php');
?>
