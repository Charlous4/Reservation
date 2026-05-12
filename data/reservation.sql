-- Adminer 5.4.1 PostgreSQL 15.14 dump

DROP TABLE IF EXISTS "activite";
DROP SEQUENCE IF EXISTS activite_id_seq;
CREATE SEQUENCE activite_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 CACHE 1;

CREATE TABLE "public"."activite" (
    "id" integer DEFAULT nextval('activite_id_seq') NOT NULL,
    "nom" character varying(255) NOT NULL,
    "description" character varying(255) NOT NULL,
    "capacite_max" integer NOT NULL,
    "prix" double precision NOT NULL,
    "nv_difficulte" integer NOT NULL,
    "type_id" integer,
    CONSTRAINT "activite_pkey" PRIMARY KEY ("id")
)
WITH (oids = false);

INSERT INTO "activite" ("id", "nom", "description", "capacite_max", "prix", "nv_difficulte", "type_id") VALUES
(1,	'Football',	'dembele ballon d''or',	11,	50,	2,	1),
(4,	'Tennis',	'Challengers surcoté',	2,	20,	1,	2),
(5,	'Kung fu',	'Tristan le cul en l''air',	2,	90,	3,	3);

DROP TABLE IF EXISTS "inscrire";
DROP SEQUENCE IF EXISTS inscrire_id_seq;
CREATE SEQUENCE inscrire_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 CACHE 1;

CREATE TABLE "public"."inscrire" (
    "id" integer DEFAULT nextval('inscrire_id_seq') NOT NULL,
    "membre_id" integer,
    "session_id" integer,
    CONSTRAINT "inscrire_pkey" PRIMARY KEY ("id")
)
WITH (oids = false);

CREATE UNIQUE INDEX inscrire_membre_id_session_id_key ON public.inscrire USING btree (membre_id, session_id);

INSERT INTO "inscrire" ("id", "membre_id", "session_id") VALUES
(2,	12,	1);

DROP TABLE IF EXISTS "membre";
DROP SEQUENCE IF EXISTS membre_id_seq;
CREATE SEQUENCE membre_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 CACHE 1;

CREATE TABLE "public"."membre" (
    "id" integer DEFAULT nextval('membre_id_seq') NOT NULL,
    "nom" character varying(255) NOT NULL,
    "prenom" character varying(255) NOT NULL,
    "login" character varying(180) NOT NULL,
    "roles" json NOT NULL,
    "password" character varying(255) NOT NULL,
    "role_id" integer,
    "email" character varying(180) NOT NULL,
    CONSTRAINT "membre_pkey" PRIMARY KEY ("id")
)
WITH (oids = false);

CREATE UNIQUE INDEX membre_login_key ON public.membre USING btree (login);

CREATE UNIQUE INDEX membre_email_key ON public.membre USING btree (email);

INSERT INTO "membre" ("id", "nom", "prenom", "login", "roles", "password", "role_id", "email") VALUES
(10,	'admin',	'admin',	'admin',	'["ROLE_ADMIN"]',	'$2y$13$F.O17Fb4qTvUeCaNZ2GYBehTN/QZqau3pYTYUqV872I9OsfGEDMy.',	3,	'admin@admin.fr'),
(12,	'RAMBEAU',	'Tristan',	'trambeau',	'["ROLE_UTILISATEUR"]',	'$2y$13$eWZAq.GUahjNBpBlFgTNmewsuFWjH7iG7Bz5Jz9mi3JLt6cZfDsRa',	2,	'rambeau@mail.fr'),
(15,	'RUBIO',	'Charles',	'crubio',	'["ROLE_ENTRAINEUR"]',	'$2y$13$9E/ZOSFX626CxBwB2tec4eH8K0RMfaVyd4vUfBO/lUk0yAMBNTUVq',	1,	'charles@rubio.fr');

DROP TABLE IF EXISTS "roles";
DROP SEQUENCE IF EXISTS roles_id_seq;
CREATE SEQUENCE roles_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 CACHE 1;

CREATE TABLE "public"."roles" (
    "id" integer DEFAULT nextval('roles_id_seq') NOT NULL,
    "lib" character varying(255) NOT NULL,
    CONSTRAINT "roles_pkey" PRIMARY KEY ("id")
)
WITH (oids = false);

INSERT INTO "roles" ("id", "lib") VALUES
(1,	'Entraîneur'),
(2,	'Utilisateur'),
(3,	'Administrateur');

DROP TABLE IF EXISTS "session";
DROP SEQUENCE IF EXISTS session_id_seq;
CREATE SEQUENCE session_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 CACHE 1;

CREATE TABLE "public"."session" (
    "id" integer DEFAULT nextval('session_id_seq') NOT NULL,
    "nb_place" integer NOT NULL,
    "heure_deb" time without time zone NOT NULL,
    "heure_fin" time without time zone NOT NULL,
    "date_deb" date NOT NULL,
    "date_fin" date NOT NULL,
    "activite_id" integer,
    "entraineur_id" integer,
    CONSTRAINT "session_pkey" PRIMARY KEY ("id")
)
WITH (oids = false);

INSERT INTO "session" ("id", "nb_place", "heure_deb", "heure_fin", "date_deb", "date_fin", "activite_id", "entraineur_id") VALUES
(1,	2,	'10:00:00',	'12:00:00',	'2026-04-29',	'2026-04-30',	5,	15);

DROP TABLE IF EXISTS "type";
DROP SEQUENCE IF EXISTS type_id_seq;
CREATE SEQUENCE type_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 CACHE 1;

CREATE TABLE "public"."type" (
    "id" integer DEFAULT nextval('type_id_seq') NOT NULL,
    "lib" character varying(255) NOT NULL,
    CONSTRAINT "type_pkey" PRIMARY KEY ("id")
)
WITH (oids = false);

INSERT INTO "type" ("id", "lib") VALUES
(1,	'Equipe'),
(2,	'Raquette'),
(3,	'Combat');

ALTER TABLE ONLY "public"."activite" ADD CONSTRAINT "activite_type_id_fkey" FOREIGN KEY (type_id) REFERENCES type(id) NOT DEFERRABLE;

ALTER TABLE ONLY "public"."inscrire" ADD CONSTRAINT "inscrire_membre_id_fkey" FOREIGN KEY (membre_id) REFERENCES membre(id) NOT DEFERRABLE;
ALTER TABLE ONLY "public"."inscrire" ADD CONSTRAINT "inscrire_session_id_fkey" FOREIGN KEY (session_id) REFERENCES session(id) NOT DEFERRABLE;

ALTER TABLE ONLY "public"."membre" ADD CONSTRAINT "membre_role_id_fkey" FOREIGN KEY (role_id) REFERENCES roles(id) NOT DEFERRABLE;

ALTER TABLE ONLY "public"."session" ADD CONSTRAINT "session_activite_id_fkey" FOREIGN KEY (activite_id) REFERENCES activite(id) NOT DEFERRABLE;
ALTER TABLE ONLY "public"."session" ADD CONSTRAINT "session_entraineur_id_fkey" FOREIGN KEY (entraineur_id) REFERENCES membre(id) NOT DEFERRABLE;

-- 2026-05-12 08:42:48 UTC