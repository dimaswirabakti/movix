-- KUMPULAN QUERY KOMPLEKS
USE movix;

-- Query 1: JOIN MULTI-TABEL
-- Daftar film + genre (digabung) + statistik rating.
SELECT m.title, m.release_year, m.avg_rating, GROUP_CONCAT(
        DISTINCT g.name
        ORDER BY g.name SEPARATOR ', '
    ) AS genres
FROM
    movies m
    JOIN movie_genres mg ON mg.movie_id = m.id
    JOIN genres g ON g.id = mg.genre_id
GROUP BY
    m.id,
    m.title,
    m.release_year,
    m.avg_rating
ORDER BY m.avg_rating DESC;

-- Query 2: SUBQUERY
-- Film dengan rating di atas rata-rata global
SELECT title, avg_rating
FROM movies
WHERE
    avg_rating > (
        SELECT AVG(avg_rating)
        FROM movies
        WHERE
            review_count > 0
    )
ORDER BY avg_rating DESC;

-- Query 3: AGREGASI + GROUP BY + HAVING
-- Rata-rata rating per genre
SELECT
    g.name AS genre,
    COUNT(DISTINCT m.id) AS jumlah_film,
    ROUND(AVG(m.avg_rating), 2) AS rata_rata_rating
FROM
    genres g
    JOIN movie_genres mg ON mg.genre_id = g.id
    JOIN movies m ON m.id = mg.movie_id
WHERE
    m.review_count > 0
GROUP BY
    g.id,
    g.name
HAVING
    COUNT(DISTINCT m.id) >= 1
ORDER BY rata_rata_rating DESC;

-- Query 4: WINDOW FUNCTION
-- Peringkat film tertinggi di dalam tiap genre (top 3 per genre).
SELECT
    genre,
    title,
    avg_rating,
    peringkat
FROM (
        SELECT g.name AS genre, m.title, m.avg_rating, RANK() OVER (
                PARTITION BY
                    g.name
                ORDER BY m.avg_rating DESC
            ) AS peringkat
        FROM
            movies m
            JOIN movie_genres mg ON mg.movie_id = m.id
            JOIN genres g ON g.id = mg.genre_id
    ) AS ranked
WHERE
    peringkat <= 3
ORDER BY genre, peringkat;

-- Memakai FUNCTION buatan sendiri di dalam query:
SELECT
    title,
    avg_rating,
    fn_rating_label (avg_rating) AS label
FROM movies
ORDER BY avg_rating DESC;