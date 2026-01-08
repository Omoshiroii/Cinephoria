<?php
/* PAGE D'ACCUEIL - CINEPHORIA */

// Inclure les fichiers de configuration
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Récupérer les films les mieux notés (Top 8)
$top_movies = db_select(
    "SELECT * FROM movies ORDER BY rating DESC LIMIT 10"
);

// Récupérer les films récents (Top 8)
$recent_movies = db_select(
    "SELECT * FROM movies ORDER BY release_year DESC, created_at DESC LIMIT 8"
);

// Récupérer les films d'action
$action_movies = db_select(
    "SELECT * FROM movies WHERE genres LIKE '%Action%' ORDER BY rating DESC LIMIT 6"
);

// Récupérer les drames
$drama_movies = db_select(
    "SELECT * FROM movies WHERE genres LIKE '%Drame%' ORDER BY rating DESC LIMIT 6"
);

// Compter les statistiques
$total_movies = db_count('movies');
$total_users = db_count('users');
$total_reviews = db_count('user_ratings');

// Inclure l'en-tête
include 'includes/header.php';
?>

<!-- SECTION HERO -->
<section class="hero-section">
    <div class="hero-bg"></div>
    <div class="container">
        <div class="hero-content">
            
            <!-- Badge -->
            <div class="hero-badge">
                <i class="fas fa-film"></i>
                <span>Votre destination cinéma</span>
            </div>
            
            <!-- Titre principal -->
            <h1 class="hero-title">
                Découvrez et suivez
                <br>
                <span class="gradient-text">vos films préférés</span>
            </h1>
            
            <?php if (is_logged_in()): ?>
                <p class="hero-subtitle">
                    Bon retour parmi nous, <strong><?php echo clean(get_username()); ?></strong>! 
                    Prêt à découvrir de nouveaux chefs-d'œuvre?
                </p>
            <?php else: ?>
                <p class="hero-subtitle">
                    Rejoignez une communauté passionnée de cinéphiles. 
                    Notez, commentez et découvrez les meilleurs films du moment.
                </p>
            <?php endif; ?>
            
            <!-- Statistiques -->
            <div style="display: flex; justify-content: center; gap: 3rem; margin-bottom: 2rem; flex-wrap: wrap;">
                <div style="text-align: center;">
                    <div style="font-size: 2.5rem; font-weight: 800; color: var(--accent-cyan);"><?php echo $total_movies; ?></div>
                    <div style="color: var(--text-secondary); font-size: 0.9rem;">Films</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 2.5rem; font-weight: 800; color: var(--accent-blue);"><?php echo $total_users; ?></div>
                    <div style="color: var(--text-secondary); font-size: 0.9rem;">Membres</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 2.5rem; font-weight: 800; color: var(--accent-purple);"><?php echo $total_reviews; ?></div>
                    <div style="color: var(--text-secondary); font-size: 0.9rem;">Avis</div>
                </div>
            </div>
            
            <!-- Boutons CTA -->
            <div class="hero-buttons">
                <?php if (!is_logged_in()): ?>
                    <a href="<?php echo url('register.php'); ?>" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i>
                        Rejoindre Cinephoria
                    </a>
                    <a href="<?php echo url('movies.php'); ?>" class="btn btn-outline">
                        <i class="fas fa-compass"></i>
                        Explorer les films
                    </a>
                <?php else: ?>
                    <a href="<?php echo url('profile.php'); ?>" class="btn btn-primary">
                        <i class="fas fa-user"></i>
                        Mon Profil
                    </a>
                    <a href="<?php echo url('movies.php'); ?>" class="btn btn-outline">
                        <i class="fas fa-compass"></i>
                        Découvrir plus
                    </a>
                <?php endif; ?>
            </div>
            
        </div>
    </div>
</section>

<!-- SECTION: FILMS LES MIEUX NOTÉS -->
<section class="movies-section">
    <div class="container">
        
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-star" style="color: #fbbf24;"></i>
                Films les mieux notés
            </h2>
            <a href="<?php echo url('movies.php?sort=rating'); ?>" class="btn btn-ghost">
                Voir tout <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        
        <div class="movies-grid">
            <?php if (!empty($top_movies)): ?>
                <?php foreach ($top_movies as $movie): ?>
                    <a href="<?php echo url('movie-detail.php?id=' . $movie['id']); ?>" class="movie-card">
                        <div class="movie-poster">
                            <?php if (!empty($movie['poster_url'])): ?>
                                <img src="<?php echo clean($movie['poster_url']); ?>" 
                                     alt="<?php echo clean($movie['title']); ?>"
                                     loading="lazy">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/300x450/1e293b/06b6d4?text=<?php echo urlencode($movie['title']); ?>" 
                                     alt="<?php echo clean($movie['title']); ?>">
                            <?php endif; ?>
                            
                            <div class="movie-rating">
                                <i class="fas fa-star"></i>
                                <?php echo number_format($movie['rating'], 1); ?>
                            </div>
                            
                            <!-- Overlay au hover -->
                            <div class="movie-overlay">
                                <span class="movie-overlay-text">Voir les détails</span>
                            </div>
                        </div>
                        
                        <div class="movie-info">
                            <h3 class="movie-title"><?php echo clean($movie['title']); ?></h3>
                            <p class="movie-year">
                                <i class="fas fa-calendar"></i>
                                <?php echo $movie['release_year']; ?>
                                <?php if (!empty($movie['genres'])): ?>
                                    <span style="margin-left: 0.5rem; padding-left: 0.5rem; border-left: 1px solid var(--border-color);">
                                        <?php echo clean(explode(',', $movie['genres'])[0]); ?>
                                    </span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="grid-column: 1/-1; text-align: center; color: var(--text-secondary);">
                    Aucun film disponible pour le moment.
                </p>
            <?php endif; ?>
        </div>
        
    </div>
</section>

<!-- SECTION: FILMS D'ACTION -->
<?php if (!empty($action_movies)): ?>
<section class="movies-section" style="padding-top: 0;">
    <div class="container">
        
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-bolt" style="color: var(--accent-cyan);"></i>
                Action & Aventure
            </h2>
            <a href="<?php echo url('movies.php?genre=Action'); ?>" class="btn btn-ghost">
                Voir tout <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        
        <div class="movies-grid">
            <?php foreach ($action_movies as $movie): ?>
                <a href="<?php echo url('movie-detail.php?id=' . $movie['id']); ?>" class="movie-card">
                    <div class="movie-poster">
                        <?php if (!empty($movie['poster_url'])): ?>
                            <img src="<?php echo clean($movie['poster_url']); ?>" 
                                 alt="<?php echo clean($movie['title']); ?>"
                                 loading="lazy">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/300x450/1e293b/06b6d4?text=<?php echo urlencode($movie['title']); ?>" 
                                 alt="<?php echo clean($movie['title']); ?>">
                        <?php endif; ?>
                        
                        <div class="movie-rating">
                            <i class="fas fa-star"></i>
                            <?php echo number_format($movie['rating'], 1); ?>
                        </div>
                        
                        <div class="movie-overlay">
                            <span class="movie-overlay-text">Voir les détails</span>
                        </div>
                    </div>
                    
                    <div class="movie-info">
                        <h3 class="movie-title"><?php echo clean($movie['title']); ?></h3>
                        <p class="movie-year">
                            <i class="fas fa-calendar"></i>
                            <?php echo $movie['release_year']; ?>
                        </p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        
    </div>
</section>
<?php endif; ?>

<!-- SECTION: DRAMES -->
<?php if (!empty($drama_movies)): ?>
<section class="movies-section" style="padding-top: 0;">
    <div class="container">
        
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-theater-masks" style="color: var(--accent-purple);"></i>
                Drames
            </h2>
            <a href="<?php echo url('movies.php?genre=Drame'); ?>" class="btn btn-ghost">
                Voir tout <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        
        <div class="movies-grid">
            <?php foreach ($drama_movies as $movie): ?>
                <a href="<?php echo url('movie-detail.php?id=' . $movie['id']); ?>" class="movie-card">
                    <div class="movie-poster">
                        <?php if (!empty($movie['poster_url'])): ?>
                            <img src="<?php echo clean($movie['poster_url']); ?>" 
                                 alt="<?php echo clean($movie['title']); ?>"
                                 loading="lazy">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/300x450/1e293b/06b6d4?text=<?php echo urlencode($movie['title']); ?>" 
                                 alt="<?php echo clean($movie['title']); ?>">
                        <?php endif; ?>
                        
                        <div class="movie-rating">
                            <i class="fas fa-star"></i>
                            <?php echo number_format($movie['rating'], 1); ?>
                        </div>
                        
                        <div class="movie-overlay">
                            <span class="movie-overlay-text">Voir les détails</span>
                        </div>
                    </div>
                    
                    <div class="movie-info">
                        <h3 class="movie-title"><?php echo clean($movie['title']); ?></h3>
                        <p class="movie-year">
                            <i class="fas fa-calendar"></i>
                            <?php echo $movie['release_year']; ?>
                        </p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        
    </div>
</section>
<?php endif; ?>

<!-- SECTION: FILMS RÉCENTS -->
<section class="movies-section" style="padding-top: 0;">
    <div class="container">
        
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-clock"></i>
                Ajoutés récemment
            </h2>
            <a href="<?php echo url('movies.php?sort=recent'); ?>" class="btn btn-ghost">
                Voir tout <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        
        <div class="movies-grid">
            <?php if (!empty($recent_movies)): ?>
                <?php foreach ($recent_movies as $movie): ?>
                    <a href="<?php echo url('movie-detail.php?id=' . $movie['id']); ?>" class="movie-card">
                        <div class="movie-poster">
                            <?php if (!empty($movie['poster_url'])): ?>
                                <img src="<?php echo clean($movie['poster_url']); ?>" 
                                     alt="<?php echo clean($movie['title']); ?>"
                                     loading="lazy">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/300x450/1e293b/06b6d4?text=<?php echo urlencode($movie['title']); ?>" 
                                     alt="<?php echo clean($movie['title']); ?>">
                            <?php endif; ?>
                            
                            <div class="movie-rating">
                                <i class="fas fa-star"></i>
                                <?php echo number_format($movie['rating'], 1); ?>
                            </div>
                            
                            <div class="movie-overlay">
                                <span class="movie-overlay-text">Voir les détails</span>
                            </div>
                        </div>
                        
                        <div class="movie-info">
                            <h3 class="movie-title"><?php echo clean($movie['title']); ?></h3>
                            <p class="movie-year">
                                <i class="fas fa-calendar"></i>
                                <?php echo $movie['release_year']; ?>
                            </p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="grid-column: 1/-1; text-align: center; color: var(--text-secondary);">
                    Aucun film récent disponible.
                </p>
            <?php endif; ?>
        </div>
        
    </div>
</section>

<!-- SECTION: CTA pour inscription -->
<?php if (!is_logged_in()): ?>
<section style="padding: 4rem 0; background: linear-gradient(135deg, rgba(6, 182, 212, 0.1), rgba(139, 92, 246, 0.1));">
    <div class="container">
        <div style="text-align: center; max-width: 700px; margin: 0 auto;">
            <h2 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 1rem;">
                Prêt à rejoindre la communauté?
            </h2>
            <p style="color: var(--text-secondary); font-size: 1.1rem; margin-bottom: 2rem;">
                Créez votre compte gratuit et commencez à noter vos films préférés, 
                créer des listes et partager vos avis avec des milliers de cinéphiles.
            </p>
            <a href="<?php echo url('register.php'); ?>" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">
                <i class="fas fa-rocket"></i>
                Créer mon compte gratuit
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<style>
.movie-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.9), transparent);
    display: flex;
    align-items: flex-end;
    justify-content: center;
    padding-bottom: 1.5rem;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.movie-card:hover .movie-overlay {
    opacity: 1;
}

.movie-overlay-text {
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
    background: var(--accent-cyan);
    border-radius: var(--radius-md);
}
</style>

<?php include 'includes/footer.php'; ?>