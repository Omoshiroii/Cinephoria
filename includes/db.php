<?php
/* DATABASE HELPER - CINEPHORIA */

// Connexion globale
$GLOBALS['db_connection'] = null;

// Obtient la connexion à la base de données
function get_db_connection() {
    if ($GLOBALS['db_connection'] === null) {
        try {
            $GLOBALS['db_connection'] = new mysqli(
                DB_HOST,
                DB_USER,
                DB_PASS,
                DB_NAME
            );
            
            if ($GLOBALS['db_connection']->connect_error) {
                throw new Exception('Erreur de connexion: ' . $GLOBALS['db_connection']->connect_error);
            }
            
            $GLOBALS['db_connection']->set_charset('utf8mb4');
        } catch (Exception $e) {
            // Log the detailed error for administrators and show a generic message to users
            error_log('DB connection error: ' . $e->getMessage());
            if (DEBUG_MODE) {
                die('Une erreur est survenue. Voir les logs pour plus de détails.');
            } else {
                die('Une erreur est survenue. Veuillez réessayer plus tard.');
            }
        }
    }
    return $GLOBALS['db_connection'];
}

// Ferme la connexion à la base de données
function close_db_connection() {
    if ($GLOBALS['db_connection'] !== null) {
        $GLOBALS['db_connection']->close();
        $GLOBALS['db_connection'] = null;
    }
}

// Exécute une requête SELECT retournant un seul enregistrement
function db_select_one($query, $params = []) {
    $db = get_db_connection();
    $stmt = $db->prepare($query);
    
    if (!$stmt) {
        // Log details for administrators; avoid exposing query or DB error to users
        error_log('DB prepare error: ' . $db->error . ' - Query: ' . $query);
        if (DEBUG_MODE) {
            die('Une erreur est survenue lors de la préparation de la requête. Voir les logs pour détails.');
        }
        return null;
    }
    
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row;
}

// Exécute une requête SELECT retournant plusieurs enregistrements
function db_select($query, $params = []) {
    $db = get_db_connection();
    $stmt = $db->prepare($query);
    
    if (!$stmt) {
        error_log('DB prepare error: ' . $db->error . ' - Query: ' . $query);
        if (DEBUG_MODE) {
            die('Une erreur est survenue lors de la préparation de la requête. Voir les logs pour détails.');
        }
        return [];
    }
    
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    
    $stmt->close();
    return $rows;
}

// Exécute une requête INSERT/UPDATE/DELETE et retourne l'ID inséré ou true/false
function db_execute($query, $params = []) {
    $db = get_db_connection();
    $stmt = $db->prepare($query);
    
    if (!$stmt) {
        error_log('DB prepare error: ' . $db->error . ' - Query: ' . $query);
        if (DEBUG_MODE) {
            die('Une erreur est survenue lors de la préparation de la requête. Voir les logs pour détails.');
        }
        return false;
    }
    
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    $success = $stmt->execute();
    $insert_id = $stmt->insert_id;
    $affected_rows = $stmt->affected_rows;
    $stmt->close();
    
    // Pour INSERT, retourne l'ID inséré
    if ($insert_id > 0) {
        return $insert_id;
    }
    
    // Pour UPDATE/DELETE, retourne le nombre de lignes affectées ou true
    return $success ? ($affected_rows > 0 ? $affected_rows : true) : false;
}

// Compte le nombre d'enregistrements correspondant aux critères
function db_count($table, $where = '', $params = []) {
    $query = "SELECT COUNT(*) as count FROM $table";
    if (!empty($where)) {
        $query .= " WHERE $where";
    }
    
    $result = db_select_one($query, $params);
    return $result ? (int)$result['count'] : 0;
}

// Vérifie si un enregistrement existe
function db_exists($table, $where, $params = []) {
    return db_count($table, $where, $params) > 0;
}

// Échappe une chaîne pour une utilisation sûre dans les requêtes
function db_escape($string) {
    $db = get_db_connection();
    return $db->real_escape_string($string);
}

?>
