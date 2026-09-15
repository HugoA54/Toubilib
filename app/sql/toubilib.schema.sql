-- Toubilib : schéma de la base de données (PostgreSQL)
-- Construit à partir de Documents/Diagramme_de_Class_Toubilib.png

DROP TABLE IF EXISTS "indisponibilite";
DROP TABLE IF EXISTS "rdv";
DROP TABLE IF EXISTS "praticien";
DROP TABLE IF EXISTS "patient";
DROP TABLE IF EXISTS "structure";
DROP TABLE IF EXISTS "specialite";


CREATE TABLE "specialite" (
    "id"          serial       PRIMARY KEY,
    "nom"         varchar(48)  NOT NULL UNIQUE,
    "description" text
);


CREATE TABLE "structure" (
    "id"      uuid         PRIMARY KEY DEFAULT gen_random_uuid(),
    "libelle" varchar(128) NOT NULL,
    "type"    varchar(48)  NOT NULL,
    "adresse" text         NOT NULL,
    "ville"   varchar(64)  NOT NULL
);


CREATE TABLE "patient" (
    "id"        uuid         PRIMARY KEY DEFAULT gen_random_uuid(),
    "nom"       varchar(64)  NOT NULL,
    "prenom"    varchar(64)  NOT NULL,
    "email"     varchar(128) UNIQUE,
    "telephone" varchar(24)  NOT NULL
);


CREATE TABLE "praticien" (
    "id"            uuid         PRIMARY KEY DEFAULT gen_random_uuid(),
    "titre"         varchar(8)   NOT NULL DEFAULT 'Dr.',
    "nom"           varchar(48)  NOT NULL,
    "prenom"        varchar(48)  NOT NULL,
    "adresse"       text,
    "ville"         varchar(64)  NOT NULL,
    "email"         varchar(128) NOT NULL UNIQUE,
    "telephone"     varchar(24)  NOT NULL,
    "horaire_debut" time         NOT NULL DEFAULT '08:00',
    "horaire_fin"   time         NOT NULL DEFAULT '18:00',
    -- Praticien 1 -> 1 Specialite
    "specialite_id" integer      NOT NULL REFERENCES "specialite" ("id"),
    -- Praticien * -> 1 Structure
    "structure_id"  uuid         NOT NULL REFERENCES "structure" ("id"),
    CONSTRAINT "praticien_horaires_check" CHECK ("horaire_fin" > "horaire_debut")
);


-- statut : 0 = prévu, 1 = honoré, 2 = non honoré (ignoré), 3 = annulé
CREATE TABLE "rdv" (
    "id"           uuid         PRIMARY KEY DEFAULT gen_random_uuid(),
    -- Patient 1 -> * RDV
    "patient_id"   uuid         NOT NULL REFERENCES "patient" ("id") ON DELETE CASCADE,
    -- Praticien 1 -> * RDV
    "praticien_id" uuid         NOT NULL REFERENCES "praticien" ("id") ON DELETE CASCADE,
    -- RDV * -> 1 Structure
    "structure_id" uuid         NOT NULL REFERENCES "structure" ("id"),
    "DateTime_heure"   timestamp    NOT NULL,
    "duree"        smallint     NOT NULL DEFAULT 30,
    "motif"        varchar(128),
    "statut"       smallint     NOT NULL DEFAULT 0,
    CONSTRAINT "rdv_duree_check"  CHECK ("duree" > 0),
    CONSTRAINT "rdv_statut_check" CHECK ("statut" BETWEEN 0 AND 3)
);

CREATE INDEX "rdv_praticien_DateTime_idx" ON "rdv" ("praticien_id", "DateTime_heure");
CREATE INDEX "rdv_patient_idx"        ON "rdv" ("patient_id");


CREATE TABLE "indisponibilite" (
    "id"           uuid         PRIMARY KEY DEFAULT gen_random_uuid(),
    -- Praticien 1 -> * Indisponibilite
    "praticien_id" uuid         NOT NULL REFERENCES "praticien" ("id") ON DELETE CASCADE,
    "debut"        timestamp    NOT NULL,
    "fin"          timestamp    NOT NULL,
    "motif"        varchar(128),
    CONSTRAINT "indisponibilite_periode_check" CHECK ("fin" > "debut")
);

CREATE INDEX "indisponibilite_praticien_debut_idx" ON "indisponibilite" ("praticien_id", "debut");
