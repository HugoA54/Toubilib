<?php
declare(strict_types=1);

namespace toubilib\adapters\persistance;

use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\domain\entities\Praticien;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\StatutRendezVous;

class PraticienRepository implements PraticienRepositoryInterface
{
    private const SELECT_PRATICIEN = 'SELECT praticien.id, titre, praticien.nom, prenom, adresse, ville, email, telephone, horaire_debut, horaire_fin, specialite_id, structure_id, specialite.nom AS specialite
        FROM praticien JOIN specialite ON specialite.id = praticien.specialite_id';

    public function __construct(private \PDO $pdo) {}

    public function findByAll(): array
    {
        try {
            $stmt = $this->pdo->prepare(self::SELECT_PRATICIEN . ' ORDER BY praticien.nom, prenom');
            $stmt->execute();
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \RuntimeException("Erreur d'accès à la base de données.", previous: $e);
        }

        return array_map(fn (array $row) => $this->creerPraticien($row), $rows);
    }

    public function findById(string $id): ?Praticien
    {
        $row = $this->chercherLigne($id);

        return $row === null ? null : $this->creerPraticien($row);
    }

    public function findByIdAvecRendezVous(string $id, \DateTime $jour): ?Praticien
    {
        $row = $this->chercherLigne($id);
        if ($row === null) {
            return null;
        }

        try {
            $stmt = $this->pdo->prepare('SELECT id, patient_id, praticien_id, structure_id, date_heure, duree, motif, statut FROM rdv WHERE praticien_id = :id AND date_heure >= :debut AND date_heure < :fin');
            $stmt->execute([
                'id' => $id,
                'debut' => (clone $jour)->setTime(0, 0)->format('Y-m-d H:i:s'),
                'fin' => (clone $jour)->setTime(0, 0)->modify('+1 day')->format('Y-m-d H:i:s'),
            ]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \RuntimeException("Erreur d'accès à la base de données.", previous: $e);
        }

        $rendezVous = array_map(fn (array $rdv) => new RendezVous(
            $rdv['id'],
            $rdv['patient_id'],
            $rdv['praticien_id'],
            $rdv['structure_id'],
            new \DateTime($rdv['date_heure']),
            (int) $rdv['duree'],
            $rdv['motif'],
            StatutRendezVous::from((int) $rdv['statut'])
        ), $rows);

        return $this->creerPraticien($row, $rendezVous);
    }

    private function chercherLigne(string $id): ?array
    {
        try {
            $stmt = $this->pdo->prepare(self::SELECT_PRATICIEN . ' WHERE praticien.id = :id');
            $stmt->execute(['id' => $id]);
        } catch (\PDOException $e) {
            throw new \RuntimeException("Erreur d'accès à la base de données.", previous: $e);
        }

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    private function creerPraticien(array $row, array $rendezVous = []): Praticien
    {
        return new Praticien(
            $row['id'],
            $row['titre'],
            $row['nom'],
            $row['prenom'],
            $row['adresse'],
            $row['ville'],
            $row['email'],
            $row['telephone'],
            new \DateTime($row['horaire_debut']),
            new \DateTime($row['horaire_fin']),
            (int) $row['specialite_id'],
            $row['structure_id'],
            $rendezVous,
            $row['specialite'],
        );
    }
}
