-- Nettoyage complet
DROP TABLE IF EXISTS "inscrire" CASCADE;
DROP TABLE IF EXISTS "session" CASCADE;
DROP TABLE IF EXISTS "activite" CASCADE;
DROP TABLE IF EXISTS "membre" CASCADE;
DROP TABLE IF EXISTS "roles" CASCADE;
DROP TABLE IF EXISTS "type" CASCADE;
DROP TABLE IF EXISTS "entrainer" CASCADE;
DROP TABLE IF EXISTS "doctrine_migration_versions" CASCADE;

-- Table TYPE
CREATE TABLE "public"."type" (
    "id" SERIAL PRIMARY KEY,
    "lib" character varying(255) NOT NULL
);

-- Table ROLES
CREATE TABLE "public"."roles" (
    "id" SERIAL PRIMARY KEY,
    "lib" character varying(255) NOT NULL
);

-- Table ACTIVITE
CREATE TABLE "public"."activite" (
    "id" SERIAL PRIMARY KEY,
    "nom" character varying(255) NOT NULL,
    "description" character varying(255) NOT NULL,
    "capacite_max" integer NOT NULL,
    "prix" double precision NOT NULL,
    "nv_difficulte" integer NOT NULL,
    "type_id" integer REFERENCES "type"("id")
);

-- Table MEMBRE
CREATE TABLE "public"."membre" (
    "id" SERIAL PRIMARY KEY,
    "nom" character varying(255) NOT NULL,
    "prenom" character varying(255) NOT NULL,
    "login" character varying(180) UNIQUE NOT NULL,
    "roles" json NOT NULL,
    "password" character varying(255) NOT NULL,
    "role_id" integer REFERENCES "roles"("id"),
    "email" character varying(180) UNIQUE NOT NULL
);

-- Table SESSION
CREATE TABLE "public"."session" (
    "id" SERIAL PRIMARY KEY,
    "nb_place" integer NOT NULL,
    "heure_deb" time NOT NULL,
    "heure_fin" time NOT NULL,
    "date_deb" date NOT NULL,
    "date_fin" date NOT NULL,
    "activite_id" integer REFERENCES "activite"("id"),
    "entraineur_id" integer REFERENCES "membre"("id")
);

-- Table INSCRIRE
CREATE TABLE "public"."inscrire" (
    "id" SERIAL PRIMARY KEY,
    "membre_id" integer REFERENCES "membre"("id"),
    "session_id" integer REFERENCES "session"("id")
);

-- Table MIGRATIONS
CREATE TABLE "public"."doctrine_migration_versions" (
    "version" character varying(191) PRIMARY KEY,
    "executed_at" timestamp(0),
    "execution_time" integer
);

-- INSERTION DES DONNÉES
INSERT INTO "type" ("id", "lib") VALUES (1, 'Equipe'), (2, 'Raquette'), (3, 'Combat');
SELECT setval('type_id_seq', (SELECT max(id) FROM type));

INSERT INTO "roles" ("id", "lib") VALUES (1, 'EntraÎneur'), (2, 'Utilisateur'), (3, 'Administrateur');
SELECT setval('roles_id_seq', (SELECT max(id) FROM roles));

INSERT INTO "activite" ("id", "nom", "description", "capacite_max", "prix", "nv_difficulte", "type_id") VALUES
(1, 'Football', 'dembele ballon d''or', 11, 50, 2, 1),
(4, 'Tennis', 'tah Challengers le film surcoté avec Zendaya', 2, 20, 1, 2),
(5, 'Kung fu', 'Tristan le cul en l''air', 2, 90, 3, 3);
SELECT setval('activite_id_seq', (SELECT max(id) FROM activite));

INSERT INTO "membre" ("id", "nom", "prenom", "login", "roles", "password", "role_id", "email") VALUES
(11, 'RUBIO', 'Charles', 'crubio', '[]', '$2y$13$loS8Fbli1Sqw5Y0G12kXc.AXn9mUyDRAfN1XWpOqw7tgauH7VXx66', 1, 'crubio@gmail.com'),
(10, 'admin', 'admin', 'admin', '["ROLE_ADMIN"]', '$2y$13$F.O17Fb4qTvUeCaNZ2GYBehTN/QZqau3pYTYUqV872I9OsfGEDMy.', 3, 'admin@admin.fr');
SELECT setval('membre_id_seq', (SELECT max(id) FROM membre));

INSERT INTO "doctrine_migration_versions" ("version", "executed_at", "execution_time") VALUES
('DoctrineMigrations\Version20251126154948', '2025-11-26 15:50:42', 17),
('DoctrineMigrations\Version20251126155407', '2025-11-26 15:54:14', 73),
('DoctrineMigrations\Version20251126155446', '2025-11-26 15:54:54', 66),
('DoctrineMigrations\Version20251126155706', '2025-11-26 15:57:12', 62),
('DoctrineMigrations\Version20251204084206', '2025-12-04 08:42:16', 33),
('DoctrineMigrations\Version20251218104808', '2025-12-18 10:48:16', 37),
('DoctrineMigrations\Version20260114150251', '2026-01-14 15:02:57', 18),
('DoctrineMigrations\Version20260120074516', '2026-01-20 07:45:21', 35),
('DoctrineMigrations\Version20260120075253', '2026-01-20 07:52:56', 64),
('DoctrineMigrations\Version20260120094543', '2026-01-20 09:45:53', 35),
('DoctrineMigrations\Version20260121131558', '2026-01-21 13:16:03', 27),
('DoctrineMigrations\Version20260121145637', '2026-01-21 14:59:35', 30);
