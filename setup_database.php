<?php
/* CRÉATION DE BASE DE DONNÉES - CINEPHORIA */

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

if ($conn->connect_error) {
    echo '<script>console.log("✗ Erreur de connexion: ' . $conn->connect_error . '");</script>';
    die();
}

echo '<script>console.log("✓ Connexion établie au serveur MySQL");</script>';

if($conn->query("SHOW DATABASES LIKE 'cinephoria_db'")->num_rows > 0)
    echo '<script> console.log("- La base de donnée cinephoria_db déjà existe.");</script>';
else {
    $sql_statements = [
        "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci",
        
        "USE " . DB_NAME,
        
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            avatar_url VARCHAR(500) DEFAULT NULL,
            bio TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_username (username),
            INDEX idx_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        "CREATE TABLE IF NOT EXISTS movies (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            original_title VARCHAR(255),
            description TEXT,
            release_year INT,
            poster_url VARCHAR(500),
            backdrop_url VARCHAR(500),
            rating DECIMAL(3,1) DEFAULT 0.0,
            duration INT COMMENT 'Durée en minutes',
            language VARCHAR(10) DEFAULT 'en',
            genres VARCHAR(255),
            director VARCHAR(255),
            cast TEXT,
            tmdb_id INT UNIQUE COMMENT 'ID de The Movie Database',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_title (title),
            INDEX idx_release_year (release_year),
            INDEX idx_rating (rating),
            INDEX idx_tmdb_id (tmdb_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        "CREATE TABLE IF NOT EXISTS user_ratings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            movie_id INT NOT NULL,
            rating DECIMAL(3,1) NOT NULL,
            review TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_movie (user_id, movie_id),
            INDEX idx_user_id (user_id),
            INDEX idx_movie_id (movie_id),
            INDEX idx_rating (rating)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        "CREATE TABLE IF NOT EXISTS watchlist (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            movie_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_movie_watchlist (user_id, movie_id),
            INDEX idx_user_id (user_id),
            INDEX idx_movie_id (movie_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];

    foreach ($sql_statements as $stmt) {
        if ($conn->query($stmt) === TRUE) {
            if (strpos($stmt, 'DATABASE') !== false) {
                echo '<script>console.log("✓ Base de données créée: ' . DB_NAME . '");</script>';
            } elseif (preg_match('/CREATE TABLE.*?(\w+)\s*\(/i', $stmt, $m)) {
                echo '<script>console.log("✓ Table créée: ' . $m[1] . '");</script>';
            }
        } else {
            if (strpos($conn->error, 'already exists') === false) {
                echo '<script>console.log("⚠ Avertissement: ' . $conn->error . '");</script>';
            }
        }
    }


    $movies_query = "INSERT IGNORE INTO movies (title, original_title, description, release_year, poster_url, backdrop_url, rating, duration, genres, director, cast) VALUES

    ('Inception', 'Inception', 'Un voleur qui s\\'infiltre dans les rêves de ses cibles pour voler leurs secrets se voit confier une mission impossible : implanter une idée dans l\\'esprit de quelqu\\'un.', 2010, 'https://image.tmdb.org/t/p/w500/ljsZTbVsrQSqZgWeep2B1QiDKuh.jpg', 'https://image.tmdb.org/t/p/w1280/s3TBrffO37wglode0J3DynjjO7h.jpg', 8.8, 148, 'Action, Science-Fiction, Thriller', 'Christopher Nolan', 'Leonardo DiCaprio, Joseph Gordon-Levitt, Ellen Page'),

    ('The Shawshank Redemption', 'The Shawshank Redemption', 'Condamné à perpétuité, Andy Dufresne est incarcéré à Shawshank. Il y développe une amitié improbable avec Red, un autre détenu.', 1994, 'https://image.tmdb.org/t/p/w500/q6y0Go1tsGEsmtFryDOJo3dEmqu.jpg', 'https://image.tmdb.org/t/p/w1280/kXfqcdQKsToO0OUXHcrrNCHDBzO.jpg', 9.3, 142, 'Drame', 'Frank Darabont', 'Tim Robbins, Morgan Freeman'),

    ('The Dark Knight', 'The Dark Knight', 'Batman doit accepter l\\'une des plus grandes épreuves psychologiques et physiques de sa capacité à combattre l\\'injustice lorsque le Joker sème le chaos dans Gotham.', 2008, 'https://image.tmdb.org/t/p/w500/qJ2tW6WMUDux911r6m7haRef0WH.jpg', 'https://image.tmdb.org/t/p/w1280/nMKdUUepR0i5zn0y1T4CsSB5chy.jpg', 9.0, 152, 'Action, Crime, Drame', 'Christopher Nolan', 'Christian Bale, Heath Ledger, Aaron Eckhart'),

    ('Pulp Fiction', 'Pulp Fiction', 'L\\'odyssée sanglante et burlesque de petits malfrats dans la jungle de Hollywood.', 1994, 'https://image.tmdb.org/t/p/w500/d5iIlFn5s0ImszYzBPb8JPIfbXD.jpg', 'https://image.tmdb.org/t/p/w1280/suaEOtk1N1sgg2MTM7oZd2cfVp3.jpg', 8.9, 154, 'Crime, Thriller', 'Quentin Tarantino', 'John Travolta, Uma Thurman, Samuel L. Jackson'),

    ('Forrest Gump', 'Forrest Gump', 'L\\'histoire d\\'un homme au grand cœur et au faible QI qui traverse les moments clés de l\\'histoire américaine.', 1994, 'https://image.tmdb.org/t/p/w500/arw2vcBveWOVZr6pxd9XTd1TdQa.jpg', 'https://image.tmdb.org/t/p/w1280/3h1JZGDhZ8nzxdgvkxha0qBqi05.jpg', 8.8, 142, 'Drame, Romance', 'Robert Zemeckis', 'Tom Hanks, Robin Wright, Gary Sinise'),

    ('Interstellar', 'Interstellar', 'Une équipe d\\'explorateurs voyage à travers un trou de ver dans l\\'espace pour assurer la survie de l\\'humanité.', 2014, 'https://image.tmdb.org/t/p/w500/gEU2QniE6E77NI6lCU6MxlNBvIx.jpg', 'https://image.tmdb.org/t/p/w1280/xJHokMbljvjADYdit5fK5VQsXEG.jpg', 8.6, 169, 'Aventure, Drame, Science-Fiction', 'Christopher Nolan', 'Matthew McConaughey, Anne Hathaway, Jessica Chastain'),

    ('The Matrix', 'The Matrix', 'Un programmeur découvre la véritable nature de sa réalité et son rôle dans la guerre contre ses contrôleurs.', 1999, 'https://image.tmdb.org/t/p/w500/f89U3ADr1oiB1s9GkdPOEpXUk5H.jpg', 'https://image.tmdb.org/t/p/w1280/fNG7i7RqMErkcqhohV2a6cV1Ehy.jpg', 8.7, 136, 'Action, Science-Fiction', 'Lana Wachowski, Lilly Wachowski', 'Keanu Reeves, Laurence Fishburne, Carrie-Anne Moss'),

    ('The Godfather', 'The Godfather', 'La saga d\\'une famille mafieuse new-yorkaise et de la transformation de son plus jeune fils en un impitoyable parrain.', 1972, 'https://image.tmdb.org/t/p/w500/3bhkrj58Vtu7enYsRolD1fZdja1.jpg', 'https://image.tmdb.org/t/p/w1280/tmU7GeKVybMWFButWEGl2M4GeiP.jpg', 9.2, 175, 'Crime, Drame', 'Francis Ford Coppola', 'Marlon Brando, Al Pacino, James Caan'),

    ('Fight Club', 'Fight Club', 'Un employé de bureau insomniaque forme un club de combat clandestin avec un fabricant de savon.', 1999, 'https://image.tmdb.org/t/p/w500/pB8BM7pdSp6B6Ih7QZ4DrQ3PmJK.jpg', 'https://image.tmdb.org/t/p/w1280/hZkgoQYus5vegHoetLkCJzb17zJ.jpg', 8.8, 139, 'Drame', 'David Fincher', 'Brad Pitt, Edward Norton, Helena Bonham Carter'),

    ('Goodfellas', 'Goodfellas', 'L\\'histoire de Henry Hill et de sa vie dans la mafia, couvrant son ascension et sa chute.', 1990, 'https://image.tmdb.org/t/p/w500/aKuFiU82s5ISJpGZp7YkIr3kCUd.jpg', 'https://image.tmdb.org/t/p/w1280/sw7mordbZxgITU877yTpZCud90M.jpg', 8.7, 145, 'Crime, Drame', 'Martin Scorsese', 'Robert De Niro, Ray Liotta, Joe Pesci'),

    ('The Lord of the Rings: The Fellowship of the Ring', 'The Lord of the Rings: The Fellowship of the Ring', 'Un hobbit se lance dans une quête pour détruire un anneau maléfique.', 2001, 'https://image.tmdb.org/t/p/w500/6oom5QYQ2yQTMJIbnvbkBL9cHo6.jpg', 'https://image.tmdb.org/t/p/w1280/x2RS3uTcsJJ9IfjNPcgDmukoEcQ.jpg', 8.8, 178, 'Aventure, Fantastique', 'Peter Jackson', 'Elijah Wood, Ian McKellen, Viggo Mortensen'),

    ('Star Wars: Episode IV - A New Hope', 'Star Wars', 'Luke Skywalker rejoint les forces rebelles pour sauver la Princesse Leia et combattre l\\'Empire galactique.', 1977, 'https://image.tmdb.org/t/p/w500/6FfCtAuVAW8XJjZ7eWeLibRLWTw.jpg', 'https://image.tmdb.org/t/p/w1280/zqkmTXzjkAgXmEWLRsY4UpTWCeo.jpg', 8.6, 121, 'Aventure, Action, Science-Fiction', 'George Lucas', 'Mark Hamill, Harrison Ford, Carrie Fisher'),

    ('Parasite', 'Gisaengchung', 'Toute la famille de Ki-taek est au chômage. Elle s\\'intéresse particulièrement au train de vie de la richissime famille Park.', 2019, 'https://image.tmdb.org/t/p/w500/7IiTTgloJzvGI1TAYymCfbfl3vT.jpg', 'https://image.tmdb.org/t/p/w1280/TU9NIjwzjoKPwQHoHshkFcQUCG.jpg', 8.5, 132, 'Thriller, Drame, Comédie', 'Bong Joon-ho', 'Song Kang-ho, Lee Sun-kyun, Cho Yeo-jeong'),

    ('Spirited Away', 'Sen to Chihiro no kamikakushi', 'Chihiro, une fillette de 10 ans, est en route vers sa nouvelle demeure lorsque son père décide de faire un détour.', 2001, 'https://image.tmdb.org/t/p/w500/39wmItIWsg5sZMyRUHLkWBcuVCM.jpg', 'https://image.tmdb.org/t/p/w1280/Ab8mkHmkYADjU7wQiOkia9BzGvS.jpg', 8.6, 125, 'Animation, Famille, Fantastique', 'Hayao Miyazaki', 'Rumi Hiiragi, Miyu Irino, Mari Natsuki'),

    ('The Silence of the Lambs', 'The Silence of the Lambs', 'Une jeune stagiaire du FBI doit recueillir l\\'aide d\\'un tueur en série incarcéré pour appréhender un autre psychopathe.', 1991, 'https://image.tmdb.org/t/p/w500/rplLJ2hPcOQmkFhTqUte0MkEaO2.jpg', 'https://image.tmdb.org/t/p/w1280/mfwq2nMBzArzQ7Y9RKE8SKeeTkg.jpg', 8.6, 118, 'Thriller, Crime, Horreur', 'Jonathan Demme', 'Jodie Foster, Anthony Hopkins, Scott Glenn'),

    ('Schindler\\'s List', 'Schindler\\'s List', 'En Pologne durant la Seconde Guerre mondiale, Oskar Schindler sauve progressivement des Juifs de la mort.', 1993, 'https://image.tmdb.org/t/p/w500/sF1U4EUQS8YHUYjNl3pMGNIQyr0.jpg', 'https://image.tmdb.org/t/p/w1280/zb6fM1CX41D9rF9hdgclu0peUmy.jpg', 9.0, 195, 'Drame, Histoire, Guerre', 'Steven Spielberg', 'Liam Neeson, Ben Kingsley, Ralph Fiennes'),

    ('Whiplash', 'Whiplash', 'Un jeune batteur de jazz intègre l\\'un des meilleurs conservatoires de musique du pays où il va être poussé à bout par un professeur tyrannique.', 2014, 'https://image.tmdb.org/t/p/w500/7fn624j5lj3xTme2SgiLCeuedmO.jpg', 'https://image.tmdb.org/t/p/w1280/fRGxZuo7jJUWQsVg9PREb98Aclp.jpg', 8.5, 107, 'Drame, Musique', 'Damien Chazelle', 'Miles Teller, J.K. Simmons'),

    ('The Prestige', 'The Prestige', 'À la fin du XIXe siècle, deux magiciens rivaux s\\'affrontent dans une compétition mortelle pour créer la meilleure illusion.', 2006, 'https://image.tmdb.org/t/p/w500/bdN3gXuIZYaJP7ftKK2sU0nPtEA.jpg', 'https://image.tmdb.org/t/p/w1280/aWjlpc9dFMqhgtssYGr0mzj52Kd.jpg', 8.5, 130, 'Drame, Mystère, Thriller', 'Christopher Nolan', 'Christian Bale, Hugh Jackman, Scarlett Johansson'),

    ('The Green Mile', 'The Green Mile', 'Paul Edgecomb, gardien de prison, découvre qu\\'un détenu condamné à mort possède un don surnaturel de guérison.', 1999, 'https://image.tmdb.org/t/p/w500/velWPhVMQeQKcxggNEU8YmIo52R.jpg', 'https://image.tmdb.org/t/p/w1280/l6hQWH9eDksNJNiXWYRkWqikOdu.jpg', 8.6, 189, 'Fantastique, Drame, Crime', 'Frank Darabont', 'Tom Hanks, Michael Clarke Duncan, David Morse'),

    ('Gladiator', 'Gladiator', 'Un général romain trahi devient gladiateur et cherche à venger la mort de sa famille.', 2000, 'https://image.tmdb.org/t/p/w500/ty8TGRuvJLPUmAR1H1nRIsgwvim.jpg', 'https://image.tmdb.org/t/p/w1280/dgeONx5qYSxC5fXjt7Lrg5LXfuC.jpg', 8.5, 155, 'Action, Drame, Aventure', 'Ridley Scott', 'Russell Crowe, Joaquin Phoenix, Connie Nielsen'),

    ('The Departed', 'The Departed', 'Un policier infiltré et un espion de la mafia tentent de découvrir l\\'identité de l\\'autre.', 2006, 'https://image.tmdb.org/t/p/w500/nT97ifVT2J1yMQmeq20Qblg61T.jpg', 'https://image.tmdb.org/t/p/w1280/8Od5zV7Q7zNOX0y9tyNgpTmoiGA.jpg', 8.5, 151, 'Crime, Drame, Thriller', 'Martin Scorsese', 'Leonardo DiCaprio, Matt Damon, Jack Nicholson'),

    ('Django Unchained', 'Django Unchained', 'Dans l\\'Amérique d\\'avant la Guerre de Sécession, un chasseur de primes s\\'associe avec un esclave affranchi pour retrouver sa femme.', 2012, 'https://image.tmdb.org/t/p/w500/7oWY8VDWW7thTzWh3OKYRkWUlD5.jpg', 'https://image.tmdb.org/t/p/w1280/2oZklIzUbvZXXzIFzv7Hi68d6xf.jpg', 8.4, 165, 'Drame, Western', 'Quentin Tarantino', 'Jamie Foxx, Christoph Waltz, Leonardo DiCaprio'),

    ('Joker', 'Joker', 'Arthur Fleck, comédien raté, bascule dans la folie et devient le criminel connu sous le nom de Joker.', 2019, 'https://image.tmdb.org/t/p/w500/udDclJoHjfjb8Ekgsd4FDteOkCU.jpg', 'https://image.tmdb.org/t/p/w1280/f5F4cRhQdUbyVbB5lTNCwUzD6BP.jpg', 8.4, 122, 'Crime, Thriller, Drame', 'Todd Phillips', 'Joaquin Phoenix, Robert De Niro, Zazie Beetz'),

    ('Avengers: Endgame', 'Avengers: Endgame', 'Les Avengers restants doivent trouver un moyen de ramener leurs alliés pour un affrontement épique avec Thanos.', 2019, 'https://image.tmdb.org/t/p/w500/or06FN3Dka5tukK1e9sl16pB3iy.jpg', 'https://image.tmdb.org/t/p/w1280/7RyHsO4yDXtBv1zUU3mTpHeQ0d5.jpg', 8.4, 181, 'Action, Aventure, Science-Fiction', 'Anthony Russo, Joe Russo', 'Robert Downey Jr., Chris Evans, Mark Ruffalo')";

    if ($conn->query($movies_query) === TRUE) {
        echo '<script>console.log("✓ 24 films populaires insérés avec succès");</script>';
    } else {
        if (strpos($conn->error, 'Duplicate') === false) {
            echo '<script>console.log("⚠ Avertissement films: ' . $conn->error . '");</script>';
        } else {
            echo '<script>console.log("✓ Films déjà présents dans la base de données");</script>';
        }
    }

    $test_password = password_hash('Test123', PASSWORD_BCRYPT, ['cost' => 10]);
    $user_query = "INSERT IGNORE INTO users (username, email, password) VALUES ('testuser', 'test@cinephoria.com', '$test_password')";

    if ($conn->query($user_query) === TRUE) {
        echo '<script>console.log("✓ Utilisateur de test créé");</script>';
    } else {
        if (strpos($conn->error, 'Duplicate') === false) {
            echo '<script>console.log("⚠ Avertissement utilisateur: ' . $conn->error . '");</script>';
        } else {
            echo '<script>console.log("✓ Utilisateur de test déjà existant");</script>';
        }
    }

    $reviews_query = "INSERT IGNORE INTO user_ratings (user_id, movie_id, rating, review) VALUES
    (1, 1, 9.5, 'Un chef-d\\'œuvre absolu! Nolan repousse les limites du cinéma avec cette exploration fascinante des rêves.'),
    (1, 2, 10.0, 'Le meilleur film jamais réalisé. L\\'histoire de rédemption la plus touchante que j\\'ai jamais vue.'),
    (1, 3, 9.0, 'Heath Ledger livre une performance inoubliable en Joker. Un film d\\'action intelligent et sombre.')";

    if ($conn->query($reviews_query) === TRUE) {
        echo '<script>console.log("✓ Exemples d\'avis créés");</script>';
    } else {
        if (strpos($conn->error, 'Duplicate') === false) {
            echo '<script>console.log("⚠ Avertissement avis: ' . $conn->error . '");</script>';
        }
    }

    $conn->close();

    echo '<script>console.log("✓ Configuration complétée avec succès!");</script>';

    echo '<script>console.log("Base de données: ' . DB_NAME . '");</script>
        <script>console.log("Serveur: ' . DB_HOST . '");</script>
        <script>console.log("Email test: test@cinephoria.com");</script>
        <script>console.log("Mot de passe test: Test123");</script>
        <script>console.log("Films: 24 films populaires avec images (poster + backdrop)");</script>
        <script>console.log("Accédez à la page d\'accueil");</script>
        <script>console.log("Connectez-vous avec les identifiants de test");</script>
        <script>console.log("Explorez les films avec leurs images");</script>
        <script>console.log("Notez et commentez vos films préférés");</script>';
}

?>