SET NAMES utf8mb4;

SET time_zone = '+00:00';

-- Pakai database yang sudah dibuat Docker (MYSQL_DATABASE=movix).
CREATE DATABASE IF NOT EXISTS movix CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE movix;

--  BAGIAN TABEL

-- USERS
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- GENRES
CREATE TABLE genres (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- MOVIES
CREATE TABLE movies (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    release_year SMALLINT UNSIGNED,
    duration_min SMALLINT UNSIGNED,
    director VARCHAR(120),
    synopsis TEXT,
    poster_url VARCHAR(500),
    avg_rating DECIMAL(3, 1) NOT NULL DEFAULT 0.0,
    review_count INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_title (title),
    INDEX idx_year (release_year)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- MOVIE_GENRES
CREATE TABLE movie_genres (
    movie_id INT UNSIGNED NOT NULL,
    genre_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (movie_id, genre_id),
    FOREIGN KEY (movie_id) REFERENCES movies (id) ON DELETE CASCADE,
    FOREIGN KEY (genre_id) REFERENCES genres (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ACTORS
CREATE TABLE actors (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    birth_year SMALLINT UNSIGNED
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- MOVIE_ACTORS
CREATE TABLE movie_actors (
    movie_id INT UNSIGNED NOT NULL,
    actor_id INT UNSIGNED NOT NULL,
    role_name VARCHAR(120),
    PRIMARY KEY (movie_id, actor_id),
    FOREIGN KEY (movie_id) REFERENCES movies (id) ON DELETE CASCADE,
    FOREIGN KEY (actor_id) REFERENCES actors (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- REVIEWS
CREATE TABLE reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    movie_id INT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    review_text TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_movie (user_id, movie_id),
    CONSTRAINT chk_rating CHECK (rating BETWEEN 1 AND 10),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (movie_id) REFERENCES movies (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- WATCHLIST
CREATE TABLE watchlist (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    movie_id INT UNSIGNED NOT NULL,
    status ENUM(
        'plan_to_watch',
        'watching',
        'watched'
    ) NOT NULL DEFAULT 'plan_to_watch',
    added_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_watch (user_id, movie_id),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (movie_id) REFERENCES movies (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- SAVED_MOVIES
CREATE TABLE saved_movies (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    movie_id INT UNSIGNED NOT NULL,
    saved_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_saved (user_id, movie_id),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (movie_id) REFERENCES movies (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

--  BAGIAN VIEW

-- VIEW ringkasan film + daftar genre + statistik rating
CREATE OR REPLACE VIEW view_movie_ratings AS
SELECT m.id, m.title, m.release_year, m.director, m.avg_rating, m.review_count, GROUP_CONCAT(
        DISTINCT g.name
        ORDER BY g.name SEPARATOR ', '
    ) AS genres
FROM
    movies m
    LEFT JOIN movie_genres mg ON mg.movie_id = m.id
    LEFT JOIN genres g ON g.id = mg.genre_id
GROUP BY
    m.id,
    m.title,
    m.release_year,
    m.director,
    m.avg_rating,
    m.review_count;

-- VIEW rekap aktivitas tiap user
CREATE OR REPLACE VIEW view_user_activity AS
SELECT
    u.id,
    u.username,
    COUNT(DISTINCT r.id) AS total_reviews,
    COUNT(DISTINCT w.id) AS total_watchlist,
    COUNT(DISTINCT s.id) AS total_saved,
    ROUND(AVG(r.rating), 1) AS avg_rating_given
FROM
    users u
    LEFT JOIN reviews r ON r.user_id = u.id
    LEFT JOIN watchlist w ON w.user_id = u.id
    LEFT JOIN saved_movies s ON s.user_id = u.id
GROUP BY
    u.id,
    u.username;

-- VIEW film terbaik (punya minimal 2 review), diurutkan rating.
CREATE OR REPLACE VIEW view_top_rated_movies AS
SELECT
    id,
    title,
    release_year,
    avg_rating,
    review_count
FROM movies
WHERE
    review_count >= 2
ORDER BY avg_rating DESC, review_count DESC;

--  BAGIAN FUNCTION
DELIMITER $$

-- FUNCTION hitung rata-rata rating sebuah film
CREATE FUNCTION fn_movie_avg_rating(p_movie_id INT UNSIGNED)
RETURNS DECIMAL(3,1)
READS SQL DATA
BEGIN
  DECLARE v_avg DECIMAL(3,1);
  SELECT ROUND(AVG(rating), 1) INTO v_avg
  FROM reviews
  WHERE movie_id = p_movie_id;
  RETURN IFNULL(v_avg, 0.0);
END $$

-- FUNCTION hitung jumlah review yang ditulis seorang user
CREATE FUNCTION fn_user_review_count(p_user_id INT UNSIGNED)
RETURNS INT
READS SQL DATA
BEGIN
  DECLARE v_count INT;
  SELECT COUNT(*) INTO v_count
  FROM reviews
  WHERE user_id = p_user_id;
  RETURN v_count;
END $$

-- FUNCTION ubah angka rating menjadi label teks
CREATE FUNCTION fn_rating_label(p_rating DECIMAL(3,1))
RETURNS VARCHAR(20)
DETERMINISTIC
NO SQL
BEGIN
  RETURN CASE
    WHEN p_rating = 0    THEN 'Belum dinilai'
    WHEN p_rating >= 8.0 THEN 'Sangat Bagus'
    WHEN p_rating >= 6.0 THEN 'Bagus'
    WHEN p_rating >= 4.0 THEN 'Cukup'
    ELSE 'Kurang'
  END;
END $$

DELIMITER $$

--  BAGIAN TRIGGER
--  Trigger 1-3 menjaga cache avg_rating & review_count di movies
--  Trigger 4 menormalkan email saat user baru dibuat.
DELIMITER $$

-- TRIGGER 1: setelah insert review, perbarui cache film terkait.
CREATE TRIGGER trg_reviews_after_insert
AFTER INSERT ON reviews
FOR EACH ROW
BEGIN
  UPDATE movies
  SET avg_rating   = (SELECT IFNULL(ROUND(AVG(rating),1),0) FROM reviews WHERE movie_id = NEW.movie_id),
      review_count = (SELECT COUNT(*) FROM reviews WHERE movie_id = NEW.movie_id)
  WHERE id = NEW.movie_id;
END $$

-- TRIGGER 2: setelah update review, perbarui cache (dilakukan karena ratingnya bisa aja berubah).
CREATE TRIGGER trg_reviews_after_update
AFTER UPDATE ON reviews
FOR EACH ROW
BEGIN
  UPDATE movies
  SET avg_rating   = (SELECT IFNULL(ROUND(AVG(rating),1),0) FROM reviews WHERE movie_id = NEW.movie_id),
      review_count = (SELECT COUNT(*) FROM reviews WHERE movie_id = NEW.movie_id)
  WHERE id = NEW.movie_id;
END $$

-- TRIGGER 3: setelah delete review, perbarui cache.
CREATE TRIGGER trg_reviews_after_delete
AFTER DELETE ON reviews
FOR EACH ROW
BEGIN
  UPDATE movies
  SET avg_rating   = (SELECT IFNULL(ROUND(AVG(rating),1),0) FROM reviews WHERE movie_id = OLD.movie_id),
      review_count = (SELECT COUNT(*) FROM reviews WHERE movie_id = OLD.movie_id)
  WHERE id = OLD.movie_id;
END $$

-- TRIGGER 4: sebelum INSERT user, rapikan email.
CREATE TRIGGER trg_users_before_insert
BEFORE INSERT ON users
FOR EACH ROW
BEGIN
  SET NEW.email = LOWER(TRIM(NEW.email));
END $$

DELIMITER $$