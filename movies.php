<?php
/* PAGE LISTE DES FILMS - CINEPHORIA */

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Paramètres de pagination et filtres
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = MOVIES_PER_PAGE;
$offset = ($page - 1) * $per_page;

// Paramètres de recherche et filtres
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$genre = isset($_GET['genre']) ? trim($_GET['genre']) : '';
$year = isset($_GET['year']) ? intval($_GET['year']) : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'rating';

// Construction de la requête
$where_clauses = [];
$params = [];

if (!empty($search)) {
    $where_clauses[] = "(title LIKE ? OR original_title LIKE ? OR director LIKE ? OR cast LIKE ?)";
    $search_param = '%' . $search . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($genre)) {
    $where_clauses[] = "genres LIKE ?";
    $params[] = '%' . $genre . '%';
}

if ($year > 0) {
    $where_clauses[] = "release_year = ?";
    $params[] = $year;
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Tri
$order_sql = match($sort) {
    'title' => 'ORDER BY title ASC',
    'year' => 'ORDER BY release_year DESC',
    'recent' => 'ORDER BY created_at DESC',
    default => 'ORDER BY rating DESC'
};

// Compter le total
$count_query = "SELECT COUNT(*) as total FROM movies $where_sql";
$count_result = db_select_one($count_query, $params);
$total_movies = $count_result['total'];
$total_pages = ceil($total_movies / $per_page);

// Récupérer les films
$query = "SELECT * FROM movies $where_sql $order_sql LIMIT $per_page OFFSET $offset";
$movies = db_select($query, $params);

// Récupérer les genres uniques pour le filtre
$all_movies_for_genres = db_select("SELECT DISTINCT genres FROM movies WHERE genres IS NOT NULL AND genres != ''");
$genres_list = [];
foreach ($all_movies_for_genres as $m) {
    foreach (explode(',', $m['genres']) as $g) {
        $g = trim($g);
        if (!empty($g) && !in_array($g, $genres_list)) {
            $genres_list[] = $g;
        }
    }
}
sort($genres_list);

// Récupérer les années uniques
$years_result = db_select("SELECT DISTINCT release_year FROM movies ORDER BY release_year DESC");
$years_list = array_column($years_result, 'release_year');

include 'includes/header.php';
?>

<div class="container" style="padding: 2rem 1rem;">
    
    <!-- En-tête de la page -->
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem;">
            <i class="fas fa-video" style="color: var(--accent-cyan);"></i>
            Tous les films
        </h1>
        <p style="color: var(--text-secondary);">
<!--            <?php echo $total_movies; ?> film<?php echo $total_movies > 1 ? 's' : ''; ?> disponible<?php echo $total_movies > 1 ? 's' : ''; ?> -->
            <?php if (!empty($search)): ?>
                pour "<strong><?php echo clean($search); ?></strong>"
            <?php endif; ?>
        </p>
    </div>
    
    <!-- Filtres -->
    <div style="background: var(--bg-secondary); padding: 1.5rem; border-radius: var(--radius-lg); margin-bottom: 2rem; border: 1px solid var(--border-color);">
        <form method="GET" action="" style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end;">
            
            <!-- Recherche -->
            <div style="flex: 1; min-width: 200px;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; color: var(--text-secondary);">
                    <i class="fas fa-search"></i> Rechercher
                </label>
                <input type="text" name="search" value="<?php echo clean($search); ?>" 
                       placeholder="Titre, réalisateur, acteur..."
                       style="width: 100%; padding: 0.75rem; background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary);">
            </div>
            
            <!-- Genre -->
            <div style="min-width: 150px;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; color: var(--text-secondary);">
                    <i class="fas fa-tag"></i> Genre
                </label>
                <select name="genre" style="width: 100%; padding: 0.75rem; background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary);">
                    <option value="">Tous les genres</option>
                    <?php foreach ($genres_list as $g): ?>
                        <option value="<?php echo clean($g); ?>" <?php echo $genre === $g ? 'selected' : ''; ?>>
                            <?php echo clean($g); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Année -->
            <div style="min-width: 120px;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; color: var(--text-secondary);">
                    <i class="fas fa-calendar"></i> Année
                </label>
                <select name="year" style="width: 100%; padding: 0.75rem; background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary);">
                    <option value="">Toutes</option>
                    <?php foreach ($years_list as $y): ?>
                        <option value="<?php echo $y; ?>" <?php echo $year === $y ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Tri -->
            <div style="min-width: 150px;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; color: var(--text-secondary);">
                    <i class="fas fa-sort"></i> Trier par
                </label>
                <select name="sort" style="width: 100%; padding: 0.75rem; background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary);">
                    <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Mieux notés</option>
                    <option value="year" <?php echo $sort === 'year' ? 'selected' : ''; ?>>Plus récents</option>
                    <option value="title" <?php echo $sort === 'title' ? 'selected' : ''; ?>>Titre A-Z</option>
                    <option value="recent" <?php echo $sort === 'recent' ? 'selected' : ''; ?>>Ajoutés récemment</option>
                </select>
            </div>
            
            <!-- Boutons -->
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filtrer
                </button>
                <?php if (!empty($search) || !empty($genre) || $year > 0 || $sort !== 'rating'): ?>
                    <a href="<?php echo url('movies.php'); ?>" class="btn btn-ghost">
                        <i class="fas fa-times"></i> Réinitialiser
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- Grille de films -->
    <?php if (!empty($movies)): ?>
        <div class="movies-grid" style="margin-bottom: 2rem;">
            <?php foreach ($movies as $movie): ?>
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
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div style="display: flex; justify-content: center; gap: 0.5rem; flex-wrap: wrap;">
                
                <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                       class="btn btn-ghost">
                        <i class="fas fa-chevron-left"></i> Précédent
                    </a>
                <?php endif; ?>
                
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                if ($start_page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>" 
                       class="btn btn-ghost">1</a>
                    <?php if ($start_page > 2): ?>
                        <span style="padding: 0.5rem; color: var(--text-muted);">...</span>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                       class="btn <?php echo $i === $page ? 'btn-primary' : 'btn-ghost'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <span style="padding: 0.5rem; color: var(--text-muted);">...</span>
                    <?php endif; ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>" 
                       class="btn btn-ghost"><?php echo $total_pages; ?></a>
                <?php endif; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                       class="btn btn-ghost">
                        Suivant <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
                
            </div>
        <?php endif; ?>
        
    <?php else: ?>
        <div style="text-align: center; padding: 4rem 2rem;">
            <i class="fas fa-film" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
            <h2 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Aucun film trouvé</h2>
            <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                Essayez de modifier vos critères de recherche
            </p>
            <a href="<?php echo url('movies.php'); ?>" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Voir tous les films
            </a>
        </div>
    <?php endif; ?>
    
</div>

<?php
include 'includes/footer.php';
?>
