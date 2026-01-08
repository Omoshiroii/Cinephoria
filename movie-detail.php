<?php
/* PAGE DÉTAILS D'UN FILM - CINEPHORIA */

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Récupérer l'ID du film
$movie_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($movie_id <= 0) {
    set_flash('Film non trouvé', 'error');
    redirect('movies.php');
}

// Récupérer les détails du film
$movie = db_select_one("SELECT * FROM movies WHERE id = ?", [$movie_id]);

if (!$movie) {
    set_flash('Film non trouvé', 'error');
    redirect('movies.php');
}

// Si l'utilisateur est connecté, récupérer sa note et vérifier la watchlist
$user_rating = null;
$in_watchlist = false;

if (is_logged_in()) {
    $user_rating = get_user_movie_rating(get_user_id(), $movie_id);
    $in_watchlist = is_in_watchlist(get_user_id(), $movie_id);
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && is_logged_in()) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        set_flash('Token de sécurité invalide.', 'error');
        redirect('movie-detail.php?id=' . $movie_id);
    }
    
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action == 'toggle_watchlist') {
        if ($in_watchlist) {
            db_execute("DELETE FROM watchlist WHERE user_id = ? AND movie_id = ?", [get_user_id(), $movie_id]);
            set_flash('Film retiré de votre liste', 'success');
        } else {
            db_execute("INSERT INTO watchlist (user_id, movie_id) VALUES (?, ?)", [get_user_id(), $movie_id]);
            set_flash('Film ajouté à votre liste', 'success');
        }
        redirect('movie-detail.php?id=' . $movie_id);
    }
    
    if ($action == 'rate_movie') {
        $rating = isset($_POST['rating']) ? floatval($_POST['rating']) : 0;
        $review = isset($_POST['review']) ? trim(strip_tags($_POST['review'])) : '';
        
        if ($rating < 0.5 || $rating > 10) {
            set_flash('La note doit être entre 0.5 et 10', 'error');
            redirect('movie-detail.php?id=' . $movie_id);
        }
        
        if ($user_rating) {
            db_execute(
                "UPDATE user_ratings SET rating = ?, review = ?, updated_at = NOW() WHERE user_id = ? AND movie_id = ?",
                [$rating, $review, get_user_id(), $movie_id]
            );
            set_flash('Votre note a été mise à jour!', 'success');
        } else {
            db_execute(
                "INSERT INTO user_ratings (user_id, movie_id, rating, review) VALUES (?, ?, ?, ?)",
                [get_user_id(), $movie_id, $rating, $review]
            );
            set_flash('Merci pour votre note!', 'success');
        }
        
        update_movie_rating($movie_id);
        redirect('movie-detail.php?id=' . $movie_id);
    }
    
    if ($action == 'delete_rating') {
        db_execute("DELETE FROM user_ratings WHERE user_id = ? AND movie_id = ?", [get_user_id(), $movie_id]);
        update_movie_rating($movie_id);
        set_flash('Votre note a été supprimée', 'success');
        redirect('movie-detail.php?id=' . $movie_id);
    }
}

// Statistiques et avis
$rating_stats = calculate_movie_average_rating($movie_id);
$recent_reviews = db_select("
    SELECT ur.*, u.username
    FROM user_ratings ur
    INNER JOIN users u ON ur.user_id = u.id
    WHERE ur.movie_id = ?
    ORDER BY ur.created_at DESC
    LIMIT 10
", [$movie_id]);

// Films similaires
$similar_movies = [];
if (!empty($movie['genres'])) {
    $first_genre = trim(explode(',', $movie['genres'])[0]);
    $similar_movies = db_select(
        "SELECT * FROM movies WHERE genres LIKE ? AND id != ? ORDER BY rating DESC LIMIT 6",
        ['%' . $first_genre . '%', $movie_id]
    );
}

include 'includes/header.php';
?>

<!-- Backdrop -->
<?php if (!empty($movie['backdrop_url'])): ?>
<div style="position: absolute; top: 0; left: 0; right: 0; height: 500px; z-index: -1; overflow: hidden;">
    <img src="<?php echo clean($movie['backdrop_url']); ?>" alt="" 
         style="width: 100%; height: 100%; object-fit: cover; opacity: 0.2; filter: blur(2px);">
    <div style="position: absolute; inset: 0; background: linear-gradient(to bottom, transparent, var(--bg-primary));"></div>
</div>
<?php endif; ?>

<div style="padding: 2rem 0 4rem;">
    <div class="container">
        
        <!-- Section principale -->
        <div style="display: grid; grid-template-columns: 300px 1fr; gap: 2.5rem; margin-bottom: 3rem;">
            
            <!-- Poster -->
            <div>
                <?php if (!empty($movie['poster_url'])): ?>
                    <img src="<?php echo clean($movie['poster_url']); ?>" 
                         alt="<?php echo clean($movie['title']); ?>"
                         style="width: 100%; border-radius: var(--radius-xl); box-shadow: var(--shadow-xl); border: 1px solid var(--border-color);">
                <?php else: ?>
                    <div style="width: 100%; aspect-ratio: 2/3; background: var(--bg-secondary); border-radius: var(--radius-xl); display: flex; align-items: center; justify-content: center; border: 1px solid var(--border-color);">
                        <i class="fas fa-film" style="font-size: 4rem; color: var(--text-muted);"></i>
                    </div>
                <?php endif; ?>
                
                <!-- Boutons d'action -->
                <div style="margin-top: 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
                    <?php if (is_logged_in()): ?>
                        <form method="POST">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="toggle_watchlist">
                            <button type="submit" class="btn <?php echo $in_watchlist ? 'btn-primary' : 'btn-outline'; ?>" style="width: 100%; justify-content: center;">
                                <i class="fas fa-<?php echo $in_watchlist ? 'check' : 'plus'; ?>"></i>
                                <?php echo $in_watchlist ? 'Dans ma liste' : 'Ajouter à ma liste'; ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="<?php echo url('login.php'); ?>" class="btn btn-outline" style="width: 100%; justify-content: center;">
                            <i class="fas fa-plus"></i>
                            Ajouter à ma liste
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Informations -->
            <div>
                <h1 style="font-size: 2.75rem; font-weight: 800; margin-bottom: 0.5rem; line-height: 1.2;">
                    <?php echo clean($movie['title']); ?>
                </h1>
                
                <?php if (!empty($movie['original_title']) && $movie['original_title'] != $movie['title']): ?>
                    <p style="color: var(--text-secondary); font-size: 1.1rem; margin-bottom: 1rem; font-style: italic;">
                        <?php echo clean($movie['original_title']); ?>
                    </p>
                <?php endif; ?>
                
                <!-- Métadonnées -->
                <div style="display: flex; gap: 1.5rem; flex-wrap: wrap; margin-bottom: 1.5rem; color: var(--text-secondary);">
                    <span><i class="fas fa-calendar"></i> <?php echo $movie['release_year']; ?></span>
                    <?php if (!empty($movie['duration'])): ?>
                        <span><i class="fas fa-clock"></i> <?php echo floor($movie['duration'] / 60); ?>h <?php echo $movie['duration'] % 60; ?>min</span>
                    <?php endif; ?>
                    <?php if (!empty($movie['language'])): ?>
                        <span><i class="fas fa-globe"></i> <?php echo strtoupper($movie['language']); ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Genres -->
                <?php if (!empty($movie['genres'])): ?>
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
                        <?php foreach (explode(',', $movie['genres']) as $genre): ?>
                            <span style="padding: 0.4rem 1rem; background: var(--bg-tertiary); border-radius: 9999px; font-size: 0.85rem; border: 1px solid var(--border-color);">
                                <?php echo clean(trim($genre)); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Note moyenne -->
                <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 2rem; padding: 1.5rem; background: var(--bg-secondary); border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
                    <div style="text-align: center;">
                        <div style="font-size: 3rem; font-weight: 800; color: var(--accent-cyan); line-height: 1;">
                            <?php echo number_format($rating_stats['average'], 1); ?>
                        </div>
                        <div style="color: var(--text-muted); font-size: 0.85rem;">/10</div>
                    </div>
                    <div style="flex: 1;">
                        <div style="display: flex; gap: 0.25rem; margin-bottom: 0.5rem;">
                            <?php echo render_stars($rating_stats['average']); ?>
                        </div>
                        <p style="color: var(--text-secondary); font-size: 0.9rem;">
                            <i class="fas fa-users"></i> <?php echo $rating_stats['total']; ?> avis
                        </p>
                    </div>
                    
                    <?php if ($user_rating): ?>
                        <div style="text-align: center; padding-left: 1.5rem; border-left: 1px solid var(--border-color);">
                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.25rem;">VOTRE NOTE</div>
                            <div style="font-size: 1.75rem; font-weight: 700; color: #fbbf24;">
                                <?php echo number_format($user_rating['rating'], 1); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Synopsis -->
                <?php if (!empty($movie['description'])): ?>
                    <div style="margin-bottom: 2rem;">
                        <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.75rem;">Synopsis</h2>
                        <p style="color: var(--text-secondary); line-height: 1.8; font-size: 1.05rem;">
                            <?php echo clean($movie['description']); ?>
                        </p>
                    </div>
                <?php endif; ?>
                
                <!-- Équipe -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                    <?php if (!empty($movie['director'])): ?>
                        <div>
                            <h3 style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;">Réalisateur</h3>
                            <p style="font-weight: 600;"><?php echo clean($movie['director']); ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($movie['cast'])): ?>
                        <div>
                            <h3 style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;">Casting</h3>
                            <p style="color: var(--text-secondary);"><?php echo clean($movie['cast']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
        
        <!-- Section de notation -->
        <?php if (is_logged_in()): ?>
            <div style="background: var(--bg-secondary); padding: 2rem; border-radius: var(--radius-xl); margin-bottom: 3rem; border: 1px solid var(--border-color);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h2 style="font-size: 1.5rem; font-weight: 700;">
                        <i class="fas fa-star" style="color: #fbbf24;"></i>
                        <?php echo $user_rating ? 'Modifier ma note' : 'Noter ce film'; ?>
                    </h2>
                    
                    <?php if ($user_rating): ?>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Voulez-vous vraiment supprimer votre note?')">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="delete_rating">
                            <button type="submit" class="btn btn-ghost" style="color: var(--error);">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                
                <form method="POST" id="ratingForm">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="rate_movie">
                    <input type="hidden" name="rating" id="ratingInput" value="<?php echo $user_rating ? $user_rating['rating'] : ''; ?>">
                    
                    <!-- Sélecteur d'étoiles interactif -->
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.75rem; font-weight: 500;">Votre note</label>
                        <div id="starRating" style="display: flex; gap: 0.5rem; align-items: center;">
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <button type="button" class="star-btn" data-value="<?php echo $i; ?>" 
                                        style="background: none; border: none; cursor: pointer; font-size: 1.75rem; color: var(--text-muted); transition: all 0.2s; padding: 0.25rem;"
                                        onmouseover="hoverStars(<?php echo $i; ?>)" 
                                        onmouseout="resetStars()"
                                        onclick="setRating(<?php echo $i; ?>)">
                                    <i class="fas fa-star"></i>
                                </button>
                            <?php endfor; ?>
                            <span id="ratingDisplay" style="margin-left: 1rem; font-size: 1.5rem; font-weight: 700; color: var(--accent-cyan); min-width: 60px;">
                                <?php echo $user_rating ? $user_rating['rating'] . '/10' : '?/10'; ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Commentaire -->
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.75rem; font-weight: 500;">
                            Votre avis <span style="color: var(--text-muted); font-weight: normal;">(optionnel)</span>
                        </label>
                        <textarea 
                            name="review" 
                            rows="4"
                            maxlength="2000"
                            style="width: 100%; padding: 1rem; background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 1rem; resize: vertical; font-family: inherit;"
                            placeholder="Partagez votre opinion sur ce film... Qu'avez-vous aimé ou pas?"
                        ><?php echo $user_rating && $user_rating['review'] ? clean($user_rating['review']) : ''; ?></textarea>
                        <div style="display: flex; justify-content: flex-end; margin-top: 0.25rem;">
                            <small style="color: var(--text-muted);">Max 2000 caractères</small>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" id="submitBtn" <?php echo !$user_rating ? 'disabled' : ''; ?>>
                        <i class="fas fa-check"></i>
                        <?php echo $user_rating ? 'Mettre à jour' : 'Publier ma note'; ?>
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div style="background: var(--bg-secondary); padding: 2rem; border-radius: var(--radius-xl); margin-bottom: 3rem; border: 1px solid var(--border-color); text-align: center;">
                <i class="fas fa-user-lock" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Connectez-vous pour noter ce film</h3>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">Partagez votre avis avec la communauté Cinephoria</p>
                <a href="<?php echo url('login.php'); ?>" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Se connecter
                </a>
                <span style="margin: 0 1rem; color: var(--text-muted);">ou</span>
                <a href="<?php echo url('register.php'); ?>" class="btn btn-outline">
                    <i class="fas fa-user-plus"></i> Créer un compte
                </a>
            </div>
        <?php endif; ?>
        
        <!-- Avis des utilisateurs -->
        <?php if (!empty($recent_reviews)): ?>
            <div style="margin-bottom: 3rem;">
                <h2 style="font-size: 1.75rem; font-weight: 700; margin-bottom: 1.5rem;">
                    <i class="fas fa-comments"></i>
                    Avis de la communauté
                    <span style="font-size: 1rem; color: var(--text-muted); font-weight: normal;">(<?php echo count($recent_reviews); ?>)</span>
                </h2>
                
                <div style="display: grid; gap: 1rem;">
                    <?php foreach ($recent_reviews as $review): ?>
                        <div style="background: var(--bg-secondary); padding: 1.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <div style="width: 45px; height: 45px; background: linear-gradient(135deg, var(--accent-cyan), var(--accent-blue)); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: white;">
                                        <?php echo strtoupper(substr($review['username'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <p style="font-weight: 600; margin-bottom: 0.2rem;">
                                            <?php echo clean($review['username']); ?>
                                            <?php if (is_logged_in() && $review['user_id'] == get_user_id()): ?>
                                                <span style="font-size: 0.75rem; background: var(--accent-cyan); color: white; padding: 0.15rem 0.5rem; border-radius: 9999px; margin-left: 0.5rem;">Vous</span>
                                            <?php endif; ?>
                                        </p>
                                        <p style="color: var(--text-muted); font-size: 0.85rem;">
                                            <?php echo time_ago($review['created_at']); ?>
                                        </p>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.5rem; background: var(--bg-tertiary); padding: 0.5rem 1rem; border-radius: var(--radius-md);">
                                    <i class="fas fa-star" style="color: #fbbf24;"></i>
                                    <span style="font-weight: 700;"><?php echo number_format($review['rating'], 1); ?></span>
                                    <span style="color: var(--text-muted);">/10</span>
                                </div>
                            </div>
                            
                            <?php if (!empty($review['review'])): ?>
                                <p style="color: var(--text-secondary); line-height: 1.7;">
                                    <?php echo nl2br(clean($review['review'])); ?>
                                </p>
                            <?php else: ?>
                                <p style="color: var(--text-muted); font-style: italic;">
                                    Pas de commentaire
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Films similaires -->
        <?php if (!empty($similar_movies)): ?>
            <div>
                <h2 style="font-size: 1.75rem; font-weight: 700; margin-bottom: 1.5rem;">
                    <i class="fas fa-film"></i>
                    Films similaires
                </h2>
                
                <div class="movies-grid">
                    <?php foreach ($similar_movies as $sim_movie): ?>
                        <a href="<?php echo url('movie-detail.php?id=' . $sim_movie['id']); ?>" class="movie-card">
                            <div class="movie-poster">
                                <?php if (!empty($sim_movie['poster_url'])): ?>
                                    <img src="<?php echo clean($sim_movie['poster_url']); ?>" 
                                         alt="<?php echo clean($sim_movie['title']); ?>"
                                         loading="lazy">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/300x450/1e293b/06b6d4?text=<?php echo urlencode($sim_movie['title']); ?>" 
                                         alt="<?php echo clean($sim_movie['title']); ?>">
                                <?php endif; ?>
                                <div class="movie-rating">
                                    <i class="fas fa-star"></i>
                                    <?php echo number_format($sim_movie['rating'], 1); ?>
                                </div>
                            </div>
                            <div class="movie-info">
                                <h3 class="movie-title"><?php echo clean($sim_movie['title']); ?></h3>
                                <p class="movie-year">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo $sim_movie['release_year']; ?>
                                </p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<style>
    @media (max-width: 768px) {
        div[style*="grid-template-columns: 300px"] {
            grid-template-columns: 1fr !important;
        }
        .movie-detail-poster {
            max-width: 250px;
            margin: 0 auto;
        }
    }
    
    .star-btn:hover i,
    .star-btn.active i {
        color: #fbbf24 !important;
        transform: scale(1.1);
    }
</style>

<script>
let currentRating = <?php echo $user_rating ? $user_rating['rating'] : 0; ?>;

function hoverStars(rating) {
    const stars = document.querySelectorAll('.star-btn');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.querySelector('i').style.color = '#fbbf24';
        } else {
            star.querySelector('i').style.color = 'var(--text-muted)';
        }
    });
    document.getElementById('ratingDisplay').textContent = rating + '/10';
}

function resetStars() {
    const stars = document.querySelectorAll('.star-btn');
    stars.forEach((star, index) => {
        if (index < currentRating) {
            star.querySelector('i').style.color = '#fbbf24';
            star.classList.add('active');
        } else {
            star.querySelector('i').style.color = 'var(--text-muted)';
            star.classList.remove('active');
        }
    });
    document.getElementById('ratingDisplay').textContent = currentRating ? currentRating + '/10' : '?/10';
}

function setRating(rating) {
    currentRating = rating;
    document.getElementById('ratingInput').value = rating;
    document.getElementById('submitBtn').disabled = false;
    resetStars();
}

// Initialize stars on page load
document.addEventListener('DOMContentLoaded', function() {
    resetStars();
});
</script>

<?php
include 'includes/footer.php';
?>
