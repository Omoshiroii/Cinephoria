<?php
/* PAGE PROFIL UTILISATEUR - CINEPHORIA */

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Vérifier que l'utilisateur est connecté
require_login();

$user_id = get_user_id();

// Récupérer les infos de l'utilisateur
$user = db_select_one("SELECT * FROM users WHERE id = ?", [$user_id]);

if (!$user) {
    logout_user();
    redirect('login.php');
}

// Récupérer les statistiques
$stats = get_user_stats($user_id);

// Récupérer les films notés par l'utilisateur
$rated_movies = db_select("
    SELECT m.*, ur.rating as user_rating, ur.review, ur.created_at as rated_at
    FROM user_ratings ur
    INNER JOIN movies m ON ur.movie_id = m.id
    WHERE ur.user_id = ?
    ORDER BY ur.created_at DESC
", [$user_id]);

// Récupérer la watchlist
$watchlist = db_select("
    SELECT m.*, w.created_at as added_at
    FROM watchlist w
    INNER JOIN movies m ON w.movie_id = m.id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
", [$user_id]);

// Onglet actif
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'ratings';

include 'includes/header.php';
?>

<div class="container" style="padding: 2rem 1rem;">
    
    <!-- En-tête du profil -->
    <div style="background: var(--bg-secondary); border-radius: var(--radius-xl); padding: 2rem; margin-bottom: 2rem; border: 1px solid var(--border-color);">
        <div style="display: flex; gap: 2rem; align-items: center; flex-wrap: wrap;">
            
            <!-- Avatar -->
            <div style="width: 120px; height: 120px; background: linear-gradient(135deg, var(--accent-cyan), var(--accent-blue)); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: 700; color: white; flex-shrink: 0;">
                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
            </div>
            
            <!-- Infos -->
            <div style="flex: 1;">
                <h1 style="font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem;">
                    <?php echo clean($user['username']); ?>
                </h1>
                <p style="color: var(--text-secondary); margin-bottom: 1rem;">
                    <i class="fas fa-envelope"></i> <?php echo clean($user['email']); ?>
                    <span style="margin-left: 1rem;">
                        <i class="fas fa-calendar-alt"></i> Membre depuis <?php echo format_date($user['created_at']); ?>
                    </span>
                </p>
                
                <!-- Statistiques -->
                <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
                    <div style="text-align: center;">
                        <div style="font-size: 2rem; font-weight: 800; color: var(--accent-cyan);"><?php echo $stats['total_ratings']; ?></div>
                        <div style="color: var(--text-muted); font-size: 0.85rem;">Films notés</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 2rem; font-weight: 800; color: var(--accent-blue);"><?php echo $stats['watchlist_count']; ?></div>
                        <div style="color: var(--text-muted); font-size: 0.85rem;">Dans ma liste</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 2rem; font-weight: 800; color: #fbbf24;">
                            <?php echo $stats['average_rating'] > 0 ? number_format($stats['average_rating'], 1) : '-'; ?>
                        </div>
                        <div style="color: var(--text-muted); font-size: 0.85rem;">Note moyenne</div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    
    <!-- Onglets -->
    <div style="display: flex; gap: 0.5rem; margin-bottom: 2rem; border-bottom: 2px solid var(--border-color); padding-bottom: 0.5rem;">
        <a href="?tab=ratings" class="btn <?php echo $tab === 'ratings' ? 'btn-primary' : 'btn-ghost'; ?>">
            <i class="fas fa-star"></i> Mes notes (<?php echo count($rated_movies); ?>)
        </a>
        <a href="?tab=watchlist" class="btn <?php echo $tab === 'watchlist' ? 'btn-primary' : 'btn-ghost'; ?>">
            <i class="fas fa-bookmark"></i> Ma liste (<?php echo count($watchlist); ?>)
        </a>
    </div>
    
    <!-- Contenu des onglets -->
    <?php if ($tab === 'ratings'): ?>
        
        <!-- MES NOTES -->
        <?php if (!empty($rated_movies)): ?>
            <div style="display: grid; gap: 1rem;">
                <?php foreach ($rated_movies as $movie): ?>
                    <div style="background: var(--bg-secondary); border-radius: var(--radius-lg); padding: 1.5rem; border: 1px solid var(--border-color); display: flex; gap: 1.5rem;">
                        
                        <!-- Poster -->
                        <a href="<?php echo url('movie-detail.php?id=' . $movie['id']); ?>" style="flex-shrink: 0;">
                            <?php if (!empty($movie['poster_url'])): ?>
                                <img src="<?php echo clean($movie['poster_url']); ?>" 
                                     alt="<?php echo clean($movie['title']); ?>"
                                     style="width: 100px; border-radius: var(--radius-md);">
                            <?php else: ?>
                                <div style="width: 100px; aspect-ratio: 2/3; background: var(--bg-tertiary); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-film" style="color: var(--text-muted);"></i>
                                </div>
                            <?php endif; ?>
                        </a>
                        
                        <!-- Infos -->
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                                <div>
                                    <a href="<?php echo url('movie-detail.php?id=' . $movie['id']); ?>" 
                                       style="font-size: 1.25rem; font-weight: 700; color: var(--text-primary);">
                                        <?php echo clean($movie['title']); ?>
                                    </a>
                                    <p style="color: var(--text-muted); font-size: 0.9rem;">
                                        <?php echo $movie['release_year']; ?>
                                        <?php if (!empty($movie['director'])): ?>
                                            • <?php echo clean($movie['director']); ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                
                                <!-- Ma note -->
                                <div style="display: flex; align-items: center; gap: 0.5rem; background: rgba(251, 191, 36, 0.1); padding: 0.5rem 1rem; border-radius: var(--radius-md); border: 1px solid rgba(251, 191, 36, 0.3);">
                                    <i class="fas fa-star" style="color: #fbbf24;"></i>
                                    <span style="font-weight: 700; font-size: 1.1rem;"><?php echo number_format($movie['user_rating'], 1); ?></span>
                                    <span style="color: var(--text-muted);">/10</span>
                                </div>
                            </div>
                            
                            <?php if (!empty($movie['review'])): ?>
                                <p style="color: var(--text-secondary); line-height: 1.6; margin-bottom: 0.5rem;">
                                    <?php echo truncate_text(clean($movie['review']), 200); ?>
                                </p>
                            <?php endif; ?>
                            
                            <p style="color: var(--text-muted); font-size: 0.85rem;">
                                <i class="fas fa-clock"></i> Noté <?php echo time_ago($movie['rated_at']); ?>
                            </p>
                        </div>
                        
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 4rem 2rem; background: var(--bg-secondary); border-radius: var(--radius-xl); border: 1px solid var(--border-color);">
                <i class="fas fa-star" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                <h2 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Aucune note pour le moment</h2>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    Commencez à noter des films pour voir votre historique ici!
                </p>
                <a href="<?php echo url('movies.php'); ?>" class="btn btn-primary">
                    <i class="fas fa-compass"></i> Découvrir des films
                </a>
            </div>
        <?php endif; ?>
        
    <?php else: ?>
        
        <!-- MA WATCHLIST -->
        <?php if (!empty($watchlist)): ?>
            <div class="movies-grid">
                <?php foreach ($watchlist as $movie): ?>
                    <div class="movie-card" style="position: relative;">
                        <a href="<?php echo url('movie-detail.php?id=' . $movie['id']); ?>">
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
                            </div>
                            
                            <div class="movie-info">
                                <h3 class="movie-title"><?php echo clean($movie['title']); ?></h3>
                                <p class="movie-year">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo $movie['release_year']; ?>
                                </p>
                            </div>
                        </a>
                        
                        <!-- Bouton retirer -->
                        <form method="POST" action="<?php echo url('movie-detail.php?id=' . $movie['id']); ?>" 
                              style="position: absolute; top: 0.5rem; left: 0.5rem;"
                              onsubmit="return confirm('Retirer de votre liste?')">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="toggle_watchlist">
                            <button type="submit" style="width: 32px; height: 32px; border-radius: 50%; background: rgba(239, 68, 68, 0.9); border: none; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center;" title="Retirer de ma liste">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 4rem 2rem; background: var(--bg-secondary); border-radius: var(--radius-xl); border: 1px solid var(--border-color);">
                <i class="fas fa-bookmark" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                <h2 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Votre liste est vide</h2>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    Ajoutez des films à votre liste pour les retrouver facilement!
                </p>
                <a href="<?php echo url('movies.php'); ?>" class="btn btn-primary">
                    <i class="fas fa-compass"></i> Découvrir des films
                </a>
            </div>
        <?php endif; ?>
        
    <?php endif; ?>
    
</div>

<?php
include 'includes/footer.php';
?>
