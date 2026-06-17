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

-- Update poster & backdrop untuk semua 12 film
UPDATE movies
SET
    poster_url = 'https://image.tmdb.org/t/p/w500/q6y0Go1tsGEsmtFryDOJo3dEmqu.jpg',
    backdrop_url = 'https://image.tmdb.org/t/p/w1280/kXfqcdQKsToO0OUXHcrrNCHDBzO.jpg'
WHERE
    id = 1;

UPDATE movies
SET
    poster_url = 'https://image.tmdb.org/t/p/original/xwrCI0EWKNjJNYXpyHkIlILSM0s.jpg',
    backdrop_url = 'https://image.tmdb.org/t/p/original/vDACQOBMpdwrcpKfnNrdg1z9MGq.jpg'
WHERE
    id = 2;

UPDATE movies
SET
    poster_url = 'https://image.tmdb.org/t/p/w500/1hRoyzDtpgMU7Dz4JF22RANzQO7.jpg',
    backdrop_url = 'https://image.tmdb.org/t/p/w1280/nnMC0BM6XbjIIrT4miYmMtPGcQV.jpg'
WHERE
    id = 3;

UPDATE movies
SET
    poster_url = 'https://image.tmdb.org/t/p/w500/d5iIlFn5s0ImszYzBPb8JPIfbXD.jpg',
    backdrop_url = 'https://image.tmdb.org/t/p/w1280/suaEOtk1N1sgg2MTM7oZd2cfVp3.jpg'
WHERE
    id = 4;

UPDATE movies
SET
    poster_url = 'https://image.tmdb.org/t/p/w500/9gk7adHYeDvHkCSEqAvQNLV5Uge.jpg',
    backdrop_url = 'https://image.tmdb.org/t/p/w1280/s3TBrRGB1iav7gFOCNx3H31MoES.jpg'
WHERE
    id = 5;

UPDATE movies
SET
    poster_url = 'https://image.tmdb.org/t/p/w500/7IiTTgloJzvGI1TAYymCfbfl3vT.jpg',
    backdrop_url = 'https://image.tmdb.org/t/p/w1280/TU9NIjwzjoKPwQHoHshkFcQUCG.jpg'
WHERE
    id = 6;

UPDATE movies
SET
    poster_url = 'https://image.tmdb.org/t/p/w500/gEU2QniE6E77NI6lCU6MxlNBvIx.jpg',
    backdrop_url = 'https://image.tmdb.org/t/p/w1280/rAiYTfKGqDCRIIqo664sY9XZIvQ.jpg'
WHERE
    id = 7;

UPDATE movies
SET
    poster_url = 'https://image.tmdb.org/t/p/original/foAVhLSrLahKj1DX4MJNwetTGiW.jpg',
    backdrop_url = 'https://image.tmdb.org/t/p/original/ncEsesgOJDNrTUED89hYbA117wo.jpg'
WHERE
    id = 8;

UPDATE movies
SET
    poster_url = 'https://image.tmdb.org/t/p/w500/39wmItIWsg5sZMyRUHLkWBcuVCM.jpg',
    backdrop_url = 'https://image.tmdb.org/t/p/w1280/Ab8mkHmkYADjU7wQiOkia9BzGvS.jpg'
WHERE
    id = 9;

UPDATE movies
SET
    poster_url = 'https://image.tmdb.org/t/p/w500/7fn624j5lj3xTme2SgiLCeuedmO.jpg',
    backdrop_url = 'https://image.tmdb.org/t/p/w1280/fRGxZuo7jJUWQsVg9PREb98Aclp.jpg'
WHERE
    id = 10;

UPDATE movies
SET
    poster_url = 'https://image.tmdb.org/t/p/w500/uDO8zWDhfWwoFdKS4fzkUJt0Rf0.jpg',
    backdrop_url = 'https://image.tmdb.org/t/p/w1280/mbIRPgMaDCBuSAiHpRsXPm8DnGx.jpg'
WHERE
    id = 11;

UPDATE movies
SET
    poster_url = 'https://image.tmdb.org/t/p/w500/ty8TGRuvJLPUmAR1H1nRIsgwvim.jpg',
    backdrop_url = 'https://image.tmdb.org/t/p/w1280/6WBIzCgmDCYrqh64yDREGeDk9d3.jpg'
WHERE
    id = 12;

-- ============================================================
--  TAMBAHAN 13 FILM (total jadi 25 film)
-- ============================================================

INSERT INTO
    movies (
        title,
        release_year,
        duration_min,
        director,
        synopsis
    )
VALUES (
        'Fight Club',
        1999,
        139,
        'David Fincher',
        'Seorang pekerja kantoran insomnia membentuk klub tinju bawah tanah bersama pembuat sabun misterius.'
    ),
    (
        'Forrest Gump',
        1994,
        142,
        'Robert Zemeckis',
        'Kisah hidup seorang pria sederhana dari Alabama yang tanpa sengaja terlibat dalam peristiwa-peristiwa besar sejarah Amerika.'
    ),
    (
        'Goodfellas',
        1990,
        146,
        'Martin Scorsese',
        'Perjalanan seorang pemuda yang naik pangkat dalam dunia mafia New York selama tiga dekade.'
    ),
    (
        'The Lord of the Rings: The Fellowship of the Ring',
        2001,
        178,
        'Peter Jackson',
        'Seorang hobbit harus menghancurkan cincin sakti untuk menyelamatkan Middle-earth dari kegelapan.'
    ),
    (
        'Coco',
        2017,
        105,
        'Lee Unkrich',
        'Seorang anak laki-laki tersesat di Tanah Para Arwah dan menemukan rahasia keluarganya lewat musik.'
    ),
    (
        'The Grand Budapest Hotel',
        2014,
        99,
        'Wes Anderson',
        'Petualangan seorang concierge legendaris dan tangan kanannya di sebuah hotel mewah Eropa antar perang.'
    ),
    (
        'Joker',
        2019,
        122,
        'Todd Phillips',
        'Seorang komedian gagal di Gotham perlahan berubah menjadi sosok kriminal ikonik.'
    ),
    (
        'Coraline',
        2009,
        100,
        'Henry Selick',
        'Gadis kecil menemukan dunia paralel yang awalnya tampak sempurna namun menyimpan rahasia mengerikan.'
    ),
    (
        'Her',
        2013,
        126,
        'Spike Jonze',
        'Seorang pria kesepian menjalin hubungan emosional dengan asisten kecerdasan buatan di ponselnya.'
    ),
    (
        'Knives Out',
        2019,
        130,
        'Rian Johnson',
        'Detektif piawai menyelidiki kematian misterius seorang penulis novel kaya raya di rumahnya sendiri.'
    ),
    (
        'Spider-Man: Into the Spider-Verse',
        2018,
        117,
        'Bob Persichetti',
        'Remaja dari semesta paralel menemukan dirinya adalah salah satu dari banyak Spider-Man di multisemesta.'
    ),
    (
        'Get Out',
        2017,
        104,
        'Jordan Peele',
        'Seorang pria kulit hitam mengunjungi keluarga pacarnya dan menemukan kebenaran mengerikan di baliknya.'
    ),
    (
        'Amélie',
        2001,
        122,
        'Jean-Pierre Jeunet',
        'Seorang pelayan kafe pemalu di Paris diam-diam mengubah kehidupan orang-orang di sekitarnya.'
    );

INSERT INTO
    movie_genres (movie_id, genre_id)
VALUES (13, 1),
    (13, 5),
    (14, 1),
    (14, 8),
    (15, 1),
    (15, 2),
    (16, 8),
    (16, 4),
    (17, 1),
    (17, 6),
    (17, 7),
    (18, 1),
    (19, 1),
    (19, 5),
    (19, 2),
    (20, 5),
    (20, 6),
    (21, 1),
    (21, 4),
    (22, 5),
    (22, 2),
    (23, 6),
    (23, 3),
    (23, 8),
    (24, 5),
    (25, 1);

INSERT INTO
    reviews (
        user_id,
        movie_id,
        rating,
        review_text
    )
VALUES (
        1,
        13,
        9,
        'Plot twist yang mengubah cara menonton film ini dua kali.'
    ),
    (
        2,
        13,
        9,
        'Tyler Durden adalah karakter paling memorable dekade itu.'
    ),
    (
        2,
        14,
        8,
        'Tom Hanks luar biasa, ceritanya menyentuh dari awal sampai akhir.'
    ),
    (
        3,
        14,
        9,
        'Sejarah Amerika dibungkus jadi kisah personal yang hangat.'
    ),
    (
        1,
        15,
        9,
        'Narasi cepat dan tegang, gaya Scorsese yang khas.'
    ),
    (
        3,
        15,
        8,
        'Salah satu film mafia terbaik selain Godfather.'
    ),
    (
        2,
        16,
        9,
        'Dunia fantasi paling detail yang pernah dibuat ke layar lebar.'
    ),
    (
        1,
        16,
        8,
        'Awal trilogi yang megah dan tidak terasa selama tiga jam.'
    ),
    (
        3,
        17,
        9,
        'Visual dan musiknya bikin nangis, animasi Pixar terbaik.'
    ),
    (
        1,
        18,
        8,
        'Estetika Wes Anderson di puncaknya, lucu dan menawan.'
    ),
    (
        2,
        19,
        9,
        'Joaquin Phoenix benar-benar transformasi total ke karakter ini.'
    ),
    (
        3,
        19,
        8,
        'Gelap, meresahkan, tapi sinematografinya indah sekali.'
    ),
    (
        1,
        20,
        7,
        'Animasi stop-motion yang menyeramkan tapi memukau.'
    ),
    (
        2,
        21,
        8,
        'Konsep cinta dengan AI yang dieksplor dengan sangat halus.'
    ),
    (
        3,
        22,
        8,
        'Misteri detektif modern dengan plot yang rapi dan twist menyenangkan.'
    ),
    (
        1,
        22,
        9,
        'Daniel Craig tampil beda dan mengejutkan sekali di film ini.'
    ),
    (
        2,
        23,
        9,
        'Gaya visual komik yang revolusioner untuk film animasi.'
    ),
    (
        3,
        24,
        8,
        'Tegang dan penuh komentar sosial yang tajam.'
    ),
    (
        1,
        25,
        8,
        'Manis, eksentrik, dan khas Paris banget.'
    );

-- ============================================================
--  GAMBAR UNTUK 13 FILM TAMBAHAN (poster + backdrop dari TMDB)
-- ============================================================

UPDATE movies
SET
    poster_url = 'https://media.themoviedb.org/t/p/w440_and_h660_face/lKFUMEnMCXIsWYPR0gba7eF6nlt.jpg',
    backdrop_url = 'https://media.themoviedb.org/t/p/w1066_and_h600_face/nlDmuxSjJS1Fi2FgVToPm9ytm5S.jpg'
WHERE
    id = 13;

UPDATE movies
SET
    poster_url = 'https://media.themoviedb.org/t/p/w440_and_h660_face/Cw4hIUIAmSYfK9QfaUW5igp9La.jpg',
    backdrop_url = 'https://media.themoviedb.org/t/p/w1066_and_h600_face/66Kn4XWhkuPkJxOJyPEx4U2CUfN.jpg'
WHERE
    id = 14;

UPDATE movies
SET
    poster_url = 'https://media.themoviedb.org/t/p/w440_and_h660_face/6QMSLvU5ziIL2T6VrkaKzN2YkxK.jpg',
    backdrop_url = 'https://media.themoviedb.org/t/p/w1066_and_h600_face/gILte6Zd7m1YneIr6MVhh30S9pr.jpg'
WHERE
    id = 15;

UPDATE movies
SET
    poster_url = 'https://media.themoviedb.org/t/p/w440_and_h660_face/6oom5QYQ2yQTMJIbnvbkBL9cHo6.jpg',
    backdrop_url = 'https://media.themoviedb.org/t/p/w1066_and_h600_face/oiwc338EoBgS4sEI2ixAny4KQKg.jpg'
WHERE
    id = 16;

UPDATE movies
SET
    poster_url = 'https://media.themoviedb.org/t/p/w440_and_h660_face/iDstnnDc9olbYNdtMwlSNmh6be.jpg',
    backdrop_url = 'https://media.themoviedb.org/t/p/w1066_and_h600_face/lxfCCC41yC4KEHBYtB7E0YsWA9c.jpg'
WHERE
    id = 17;

UPDATE movies
SET
    poster_url = 'https://media.themoviedb.org/t/p/w440_and_h660_face/eWdyYQreja6JGCzqHWXpWHDrrPo.jpg',
    backdrop_url = 'https://media.themoviedb.org/t/p/w1066_and_h600_face/9udCLTxTFl28RxnK8Q05E154ZGa.jpg'
WHERE
    id = 18;

UPDATE movies
SET
    poster_url = 'https://media.themoviedb.org/t/p/w440_and_h660_face/3PbUQVxUgF4FVs6BKpIMwzcALQD.jpg',
    backdrop_url = 'https://media.themoviedb.org/t/p/w1066_and_h600_face/hO7KbdvGOtDdeg0W4Y5nKEHeDDh.jpg'
WHERE
    id = 19;

UPDATE movies
SET
    poster_url = 'https://media.themoviedb.org/t/p/w440_and_h660_face/4jeFXQYytChdZYE9JYO7Un87IlW.jpg',
    backdrop_url = 'https://media.themoviedb.org/t/p/w1066_and_h600_face/8GHxjXlI5rqyTBuVNekGTPjG5T6.jpg'
WHERE
    id = 20;

UPDATE movies
SET
    poster_url = 'https://media.themoviedb.org/t/p/w440_and_h660_face/2TgMIvBbLaSNqP7IIpQ19b9BNeJ.jpg',
    backdrop_url = 'https://media.themoviedb.org/t/p/w1066_and_h600_face/nG5zmbVeYlcDhckrPAe06fArywn.jpg'
WHERE
    id = 21;

UPDATE movies
SET
    poster_url = 'https://media.themoviedb.org/t/p/w440_and_h660_face/pThyQovXQrw2m0s9x82twj48Jq4.jpg',
    backdrop_url = 'https://media.themoviedb.org/t/p/w1066_and_h600_face/AbRYlvwAKHs0YuyNO6NX9ofq4l6.jpg'
WHERE
    id = 22;

UPDATE movies
SET
    poster_url = 'https://media.themoviedb.org/t/p/w440_and_h660_face/8Vt6mWEReuy4Of61Lnj5Xj704m8.jpg',
    backdrop_url = 'https://media.themoviedb.org/t/p/w1066_and_h600_face/9xfDWXAUbFXQK585JvByT5pEAhe.jpg'
WHERE
    id = 23;

UPDATE movies
SET
    poster_url = 'https://media.themoviedb.org/t/p/w440_and_h660_face/mE24wUCfjK8AoBBjaMjho7Rczr7.jpg',
    backdrop_url = 'https://media.themoviedb.org/t/p/w1066_and_h600_face/o8dPH0ZSIyyViP6rjRX1djwCUwI.jpg'
WHERE
    id = 24;

UPDATE movies
SET
    poster_url = 'https://media.themoviedb.org/t/p/w440_and_h660_face/nSxDa3M9aMvGVLoItzWTepQ5h5d.jpg',
    backdrop_url = 'https://media.themoviedb.org/t/p/w1066_and_h600_face/2WyjkKudTkDgtZo9CIN8NoPGHRB.jpg'
WHERE
    id = 25;