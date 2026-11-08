# Toubilib

API REST de prise de rendez-vous médicaux : consultation des praticiens, création, consultation et annulation de rendez-vous.

Projet DWM2 — BOUDOUAH & ANTZORN

## Stack

| Élément | Technologie |
| --- | --- |
| Langage | PHP 8.5 |
| Framework | Slim 4, PHP-DI |
| Base de données | PostgreSQL 18 (PDO) |
| Tests | Pest 4 |
| Environnement | Docker Compose |

## Installation

Prérequis : Docker et Docker Compose.

**1. Créer les fichiers de configuration**

```bash
cp toubilib.env.dist toubilib.env
cp app/config/.env.dist app/config/.env
```

Renseigner `POSTGRES_USER` et `POSTGRES_PASSWORD` dans `toubilib.env`. Ces valeurs doivent être identiques à `lib.username` et `lib.password` dans `app/config/.env` (par défaut `user` / `1234`).

**2. Démarrer les conteneurs**

```bash
docker compose up -d --build
```

Les dépendances Composer sont installées au démarrage du conteneur `api.toubilib` : l'API met donc un peu de temps à répondre au premier lancement.

**3. Initialiser la base de données**

La base n'est pas initialisée automatiquement :

```bash
docker compose exec toubilib.db sh -c 'psql -U $POSTGRES_USER -d $POSTGRES_DB -f /var/sql/toubilib.schema.sql'
docker compose exec toubilib.db sh -c 'psql -U $POSTGRES_USER -d $POSTGRES_DB -f /var/sql/toubilib.data.sql'
```

### Services

| Service | Adresse |
| --- | --- |
| API | <http://localhost:7080> |
| Adminer (serveur : `toubilib.db`, système : PostgreSQL) | <http://localhost:8880> |
| PostgreSQL | `localhost:5532` |

## API

| Méthode | Route | Description | Succès | Erreurs |
| --- | --- | --- | --- | --- |
| `GET` | `/praticiens` | Liste des praticiens | 200 | |
| `GET` | `/praticiens/{id}` | Détail d'un praticien | 200 | 404 |
| `GET` | `/praticiens/{id}/creneaux` | Créneaux occupés sur une période | 200 | 404, 422 |
| `GET` | `/praticiens/{id}/agenda` | Agenda sur une période (horaires, patients, motifs) | 200 | 404, 422 |
| `POST` | `/rdvs` | Création d'un rendez-vous | 201 | 422 |
| `GET` | `/rdvs/{id}` | Détail d'un rendez-vous | 200 | 404 |
| `POST` | `/rdvs/{id}/annuler` | Annulation d'un rendez-vous | 200 | 404, 409 |
| `POST` | `/rdvs/{id}/honorer` | Le patient s'est présenté | 200 | 404, 409 |
| `POST` | `/rdvs/{id}/ignorer` | Le patient ne s'est pas présenté | 200 | 404, 409 |

Les erreurs sont renvoyées en JSON : `{"message": "..."}`, ou `{"errors": {"champ": "..."}}` pour les erreurs de validation (422). Un état incompatible avec l'opération demandée renvoie 409. Les réponses incluent des liens HATEOAS et les headers CORS.

### Consulter une période

`creneaux` et `agenda` acceptent les paramètres `debut` et `fin` au format `AAAA-MM-JJ`, bornes incluses. Sans paramètre, la période couvre les 7 jours à partir d'aujourd'hui. Les rendez-vous annulés sont exclus.

```bash
curl "http://localhost:7080/praticiens/a0000000-0000-4000-8000-000000000001/agenda?debut=2026-09-07&fin=2026-09-08"
```

### Créer un rendez-vous

```bash
curl -X POST http://localhost:7080/rdvs \
  -H "Content-Type: application/json" \
  -d '{
    "praticien_id": "a0000000-0000-4000-8000-000000000001",
    "patient_id": "b0000000-0000-4000-8000-000000000001",
    "date_heure": "2026-11-16 10:00",
    "motif": "CI"
  }'
```

Réponse `201`, avec un header `Location` vers le rendez-vous créé :

```json
{
  "type": "ressource",
  "rendez_vous": {
    "id": "…",
    "patient_id": "b0000000-0000-4000-8000-000000000001",
    "praticien_id": "a0000000-0000-4000-8000-000000000001",
    "structure_id": "c0000000-0000-4000-8000-000000000001",
    "date_heure": "2026-11-16 10:00",
    "date_heure_fin": "2026-11-16 10:30",
    "duree": 30,
    "motif": "CI",
    "statut": 0,
    "statut_libelle": "prévu"
  },
  "links": {
    "self": { "href": "/rdvs/…" },
    "praticien": { "href": "/praticiens/a0000000-0000-4000-8000-000000000001" },
    "annuler": { "href": "/rdvs/…/annuler" },
    "honorer": { "href": "/rdvs/…/honorer" },
    "ignorer": { "href": "/rdvs/…/ignorer" }
  }
}
```

### Règles métier

**Création**

- `praticien_id` et `patient_id` sont des UUID correspondant à des enregistrements existants.
- `date_heure` respecte le format `AAAA-MM-JJ HH:MM`.
- Le rendez-vous a lieu du lundi au vendredi, et commence pendant les horaires du praticien.
- Il ne chevauche aucun rendez-vous non annulé du praticien.
- `motif` détermine la durée :

| Motif | Durée |
| --- | --- |
| `CI` | 30 min |
| `C0` | 20 min |
| `CS` | 15 min |

**Cycle de vie**

Seul un rendez-vous au statut `prévu` peut changer d'état : il s'annule tant qu'il n'a pas commencé, et se marque honoré ou non honoré une fois commencé. Les liens `annuler`, `honorer` et `ignorer` ne sont présents que pour un rendez-vous prévu.

| Statut | Libellé |
| --- | --- |
| 0 | prévu |
| 1 | honoré |
| 2 | non honoré |
| 3 | annulé |

### Données de test

`app/sql/toubilib.data.sql` utilise des UUID fixes, faciles à reconnaître :

| Préfixe | Ressource | Exemple |
| --- | --- | --- |
| `a0…` | Praticien | `a0000000-0000-4000-8000-000000000001` |
| `b0…` | Patient | `b0000000-0000-4000-8000-000000000001` |
| `c0…` | Structure | `c0000000-0000-4000-8000-000000000001` |
| `d0…` | Rendez-vous | `d0000000-0000-4000-8000-000000000001` |
| `e0…` | Indisponibilité | `e0000000-0000-4000-8000-000000000001` |

## Tests

Les tests utilisent des repositories en mémoire et ne nécessitent pas de base de données :

```bash
docker compose exec api.toubilib ./vendor/bin/pest
```

## Architecture

Le projet suit une architecture hexagonale :

```
app/
├── config/             Démarrage et routes (bootstrap.php), injection de dépendances
├── public/index.php    Point d'entrée
├── sql/                Schéma et données de test
├── src/
│   ├── domain/         Entités et règles métier
│   ├── application/    Cas d'utilisation, DTO, validateurs, ports (api / spi)
│   └── adapters/       Actions HTTP et repositories PDO
└── tests/pest/         Tests et repositories en mémoire
```

Le diagramme de classes se trouve dans [`Documents/Diagramme_de_Class_Toubilib.png`](Documents/Diagramme_de_Class_Toubilib.png).

## Tableau de bord

| # | Fonctionnalité | État |
| --- | --- | --- |
| 1 | Lister les praticiens | Fait |
| 2 | Consulter les détails d'un praticien | Fait |
| 3 | Consulter les détails d'un rendez-vous | Fait |
| 4 | Consulter l'agenda d'un praticien sur une période | Fait |
| 5 | Créer un rendez-vous | Fait |
| 6 | Annuler un rendez-vous | Fait |
| 7 | Lister les créneaux occupés d'un praticien sur une période | Fait |
| 8 | Rechercher un praticien par spécialité ou par ville | Non fait |
| 9 | Gérer le cycle de vie des rendez-vous (honoré, ignoré) | Fait |
| 10 | Obtenir l'historique des consultations d'un patient | Non fait |
| 11 | Obtenir les disponibilités d'un praticien pour un motif sur une journée | Non fait |
| 12 | Gérer les indisponibilités temporaires d'un praticien | Non fait |

Conformément au sujet, cette première version n'implante pas l'authentification ni les autorisations.
