-- SEED DATA (data contoh)
-- semua dummy user passwordnya = "password123"
-- hash bcrypt di bawah valid untuk password_verify() PHP
USE movix;

-- GENRES
INSERT INTO
    genres (name)
VALUES ('Drama'),
    ('Crime'),
    ('Action'),
    ('Sci-Fi'),
    ('Thriller'),
    ('Animation'),
    ('Music'),
    ('Adventure');

-- ACTORS
INSERT INTO
    actors (name, birth_year)
VALUES ('Morgan Freeman', 1937),
    ('Tim Robbins', 1958),
    ('Marlon Brando', 1924),
    ('Al Pacino', 1940),
    ('Christian Bale', 1974),
    ('Heath Ledger', 1979),
    ('Leonardo DiCaprio', 1974),
    ('Matthew McConaughey', 1969),
    ('Keanu Reeves', 1964),
    ('Miles Teller', 1987),
    ('Emma Stone', 1988),
    ('Ryan Gosling', 1980),
    ('Russell Crowe', 1964),
    ('Song Kang-ho', 1967);

-- MOVIES
INSERT INTO
    movies (
        title,
        release_year,
        duration_min,
        director,
        synopsis
    )
VALUES (
        'The Shawshank Redemption',
        1994,
        142,
        'Frank Darabont',
        'Dua narapidana menjalin persahabatan selama bertahun-tahun di penjara Shawshank.'
    ), -- 1
    (
        'The Godfather',
        1972,
        175,
        'Francis Ford Coppola',
        'Patriark dinasti mafia menyerahkan kendali kerajaan rahasianya kepada putranya.'
    ), -- 2
    (
        'The Dark Knight',
        2008,
        152,
        'Christopher Nolan',
        'Batman menghadapi Joker yang menebar kekacauan di kota Gotham.'
    ), -- 3
    (
        'Pulp Fiction',
        1994,
        154,
        'Quentin Tarantino',
        'Kisah-kisah kriminal Los Angeles yang saling terkait dengan cara tak terduga.'
    ), -- 4
    (
        'Inception',
        2010,
        148,
        'Christopher Nolan',
        'Pencuri yang mencuri rahasia lewat mimpi diminta menanamkan sebuah ide.'
    ), -- 5
    (
        'Parasite',
        2019,
        132,
        'Bong Joon-ho',
        'Keluarga miskin perlahan menyusup ke kehidupan keluarga kaya.'
    ), -- 6
    (
        'Interstellar',
        2014,
        169,
        'Christopher Nolan',
        'Penjelajah ruang angkasa mencari rumah baru bagi umat manusia lewat lubang cacing.'
    ), -- 7
    (
        'The Matrix',
        1999,
        136,
        'The Wachowskis',
        'Seorang hacker menemukan kenyataan bahwa dunianya hanyalah simulasi.'
    ), -- 8
    (
        'Spirited Away',
        2001,
        125,
        'Hayao Miyazaki',
        'Gadis kecil terjebak di dunia roh dan harus bekerja untuk menyelamatkan orang tuanya.'
    ), -- 9
    (
        'Whiplash',
        2014,
        106,
        'Damien Chazelle',
        'Drummer muda ambisius ditempa instruktur jazz yang kejam.'
    ), -- 10
    (
        'La La Land',
        2016,
        128,
        'Damien Chazelle',
        'Musisi jazz dan aktris pemula jatuh cinta sambil mengejar mimpi di Los Angeles.'
    ), -- 11
    (
        'Gladiator',
        2000,
        155,
        'Ridley Scott',
        'Jenderal Romawi yang dikhianati bangkit sebagai gladiator untuk membalas dendam.'
    );
-- 12

-- MOVIE_GENRES
INSERT INTO
    movie_genres (movie_id, genre_id)
VALUES (1, 1),
    (1, 2),
    (2, 1),
    (2, 2),
    (3, 3),
    (3, 2),
    (3, 5),
    (4, 2),
    (4, 5),
    (5, 4),
    (5, 3),
    (5, 5),
    (6, 1),
    (6, 5),
    (7, 4),
    (7, 1),
    (7, 8),
    (8, 4),
    (8, 3),
    (9, 6),
    (9, 8),
    (10, 1),
    (10, 7),
    (11, 1),
    (11, 7),
    (12, 3),
    (12, 1),
    (12, 8);

-- MOVIE_ACTORS
INSERT INTO
    movie_actors (movie_id, actor_id, role_name)
VALUES (1, 1, 'Ellis Boyd Redding'),
    (1, 2, 'Andy Dufresne'),
    (2, 3, 'Vito Corleone'),
    (2, 4, 'Michael Corleone'),
    (3, 5, 'Bruce Wayne'),
    (3, 6, 'Joker'),
    (5, 7, 'Dom Cobb'),
    (7, 8, 'Joseph Cooper'),
    (8, 9, 'Neo'),
    (10, 10, 'Andrew Neiman'),
    (11, 11, 'Mia'),
    (11, 12, 'Sebastian'),
    (12, 13, 'Maximus'),
    (6, 14, 'Kim Ki-taek');

-- USERS (semua password = "password123")
INSERT INTO
    users (
        username,
        email,
        password_hash
    )
VALUES (
        'andi',
        'andi@example.com',
        '$2b$10$KFm9j3kbiZH2QWc7VHjuMec2aiakh93MAxkRZyhoAy9PD5E.odjMO'
    ),
    (
        'bella',
        'bella@example.com',
        '$2b$10$KFm9j3kbiZH2QWc7VHjuMec2aiakh93MAxkRZyhoAy9PD5E.odjMO'
    ),
    (
        'citra',
        'citra@example.com',
        '$2b$10$KFm9j3kbiZH2QWc7VHjuMec2aiakh93MAxkRZyhoAy9PD5E.odjMO'
    );

-- REVIEWS (memicu trigger -> mengisi avg_rating & review_count)
INSERT INTO
    reviews (
        user_id,
        movie_id,
        rating,
        review_text
    )
VALUES (
        1,
        1,
        10,
        'Film terbaik sepanjang masa, sangat menyentuh.'
    ),
    (
        2,
        1,
        9,
        'Akting dan ceritanya luar biasa.'
    ),
    (
        3,
        1,
        9,
        'Endingnya bikin merinding.'
    ),
    (
        1,
        3,
        9,
        'Joker-nya ikonik banget.'
    ),
    (
        2,
        3,
        10,
        'Salah satu film superhero terbaik.'
    ),
    (
        1,
        5,
        8,
        'Konsep mimpinya kompleks tapi memuaskan.'
    ),
    (
        3,
        5,
        9,
        'Harus ditonton dua kali biar paham.'
    ),
    (
        2,
        6,
        10,
        'Pantas menang Oscar, ceritanya tajam.'
    ),
    (
        3,
        6,
        9,
        'Kritik sosial yang dikemas brilian.'
    ),
    (
        1,
        7,
        9,
        'Visual luar angkasa yang memukau.'
    ),
    (
        2,
        10,
        8,
        'Tegang dari awal sampai akhir.'
    ),
    (
        3,
        11,
        8,
        'Musik dan sinematografinya indah.'
    ),
    (
        1,
        2,
        10,
        'Klasik yang tak lekang waktu.'
    );

-- WATCHLIST
INSERT INTO
    watchlist (user_id, movie_id, status)
VALUES (1, 6, 'plan_to_watch'),
    (1, 8, 'watching'),
    (2, 7, 'watched'),
    (2, 9, 'plan_to_watch'),
    (3, 12, 'plan_to_watch');

-- SAVED_MOVIES
INSERT INTO
    saved_movies (user_id, movie_id)
VALUES (1, 1),
    (1, 5),
    (2, 3),
    (3, 6),
    (3, 9);