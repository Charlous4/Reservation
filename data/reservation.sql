CREATE SEQUENCE activite_id_seq;
CREATE SEQUENCE inscrire_id_seq;
CREATE SEQUENCE membre_id_seq;
CREATE SEQUENCE roles_id_seq;
CREATE SEQUENCE session_id_seq;
CREATE SEQUENCE type_id_seq;

-- ======================
-- TABLES SANS FK
-- ======================

CREATE TABLE type (
    id integer DEFAULT nextval('type_id_seq') PRIMARY KEY,
    lib varchar(255) NOT NULL
);

CREATE TABLE roles (
    id integer DEFAULT nextval('roles_id_seq') PRIMARY KEY,
    lib varchar(255) NOT NULL
);

CREATE TABLE membre (
    id integer DEFAULT nextval('membre_id_seq') PRIMARY KEY,
    nom varchar(255) NOT NULL,
    prenom varchar(255) NOT NULL,
    login varchar(180) NOT NULL UNIQUE,
    roles json NOT NULL,
    password varchar(255) NOT NULL,
    role_id integer,
    email varchar(180) NOT NULL UNIQUE
);

CREATE TABLE activite (
    id integer DEFAULT nextval('activite_id_seq') PRIMARY KEY,
    nom varchar(255) NOT NULL,
    description varchar(255) NOT NULL,
    capacite_max integer NOT NULL,
    prix double precision NOT NULL,
    nv_difficulte integer NOT NULL,
    type_id integer
);

CREATE TABLE session (
    id integer DEFAULT nextval('session_id_seq') PRIMARY KEY,
    nb_place integer NOT NULL,
    heure_deb time NOT NULL,
    heure_fin time NOT NULL,
    date_deb date NOT NULL,
    date_fin date NOT NULL,
    activite_id integer,
    entraineur_id integer
);

CREATE TABLE inscrire (
    id integer DEFAULT nextval('inscrire_id_seq') PRIMARY KEY,
    membre_id integer,
    session_id integer,
    UNIQUE (membre_id, session_id)
);

-- ======================
-- DONNEES
-- ======================

INSERT INTO type VALUES
(1,'Equipe'),
(2,'Raquette'),
(3,'Combat');

INSERT INTO roles VALUES
(1,'Entraîneur'),
(2,'Utilisateur'),
(3,'Administrateur');

INSERT INTO membre VALUES
(10,'admin','admin','admin','["ROLE_ADMIN"]','$2y$13$F.O17Fb4qTvUeCaNZ2GYBehTN/QZqau3pYTYUqV872I9OsfGEDMy.',3,'admin@admin.fr'),
(14,'RUBIO','Charles','crubio','["ROLE_ENTRAINEUR"]','$2y$13$ggJIHIV4M3pv9djp0Xwcq.pwrgZQEwtUfJzacy0u2372a7slZwtWO',1,'charles@mail.com'),
(12,'RAMBEAU','Tristan','trambeau','["ROLE_UTILISATEUR"]','$2y$13$eWZAq.GUahjNBpBlFgTNmewsuFWjH7iG7Bz5Jz9mi3JLt6cZfDsRa',2,'rambeau@mail.fr');

INSERT INTO activite VALUES
(1,'Football','dembele ballon d''or',11,50,2,1),
(4,'Tennis','Challengers surcoté',2,20,1,2),
(5,'Kung fu','Tristan le cul en l''air',2,90,3,3);

-- ======================
-- FOREIGN KEYS (APRES)
-- ======================

ALTER TABLE membre
ADD FOREIGN KEY (role_id) REFERENCES roles(id);

ALTER TABLE activite
ADD FOREIGN KEY (type_id) REFERENCES type(id);

ALTER TABLE session
ADD FOREIGN KEY (activite_id) REFERENCES activite(id);

ALTER TABLE session
ADD FOREIGN KEY (entraineur_id) REFERENCES membre(id);

ALTER TABLE inscrire
ADD FOREIGN KEY (membre_id) REFERENCES membre(id);

ALTER TABLE inscrire
ADD FOREIGN KEY (session_id) REFERENCES session(id);

-- ======================
-- FIX SEQUENCES
-- ======================

SELECT setval('activite_id_seq', (SELECT MAX(id) FROM activite));
SELECT setval('membre_id_seq', (SELECT MAX(id) FROM membre));
SELECT setval('roles_id_seq', (SELECT MAX(id) FROM roles));
SELECT setval('type_id_seq', (SELECT MAX(id) FROM type));