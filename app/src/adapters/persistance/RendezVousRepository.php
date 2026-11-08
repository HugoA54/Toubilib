<?php
declare(strict_types=1);

namespace toubilib\adapters\persistance;

use toubilib\domain\entities\RendezVous;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\domain\entities\StatutRendezVous;

class RendezVousRepository implements RendezVousRepositoryInterface
{
    public function __construct(private \PDO $pdo) {}

    public function findById(string $id): ?RendezVous
    {
        try {
            $stmt = $this->pdo->prepare('SELECT id, patient_id, praticien_id, structure_id, date_heure, duree, motif, statut FROM rdv WHERE id = :id');
            $stmt->execute(['id' => $id]);
        } catch (\PDOException $e) {
            throw new \RuntimeException("Erreur d'accès à la base de données.", previous: $e);
        }

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row === false ? null : $this->creerRendezVous($row);
    }

    public function findByPraticienEtPeriode(string $praticienId, \DateTime $debut, \DateTime $fin): array
    {
        try {
            $stmt = $this->pdo->prepare('SELECT id, patient_id, praticien_id, structure_id, date_heure, duree, motif, statut FROM rdv WHERE praticien_id = :id AND date_heure >= :debut AND date_heure < :fin AND statut <> :annule ORDER BY date_heure');
            $stmt->execute([
                'id' => $praticienId,
                'debut' => $debut->format('Y-m-d H:i:s'),
                'fin' => $fin->format('Y-m-d H:i:s'),
                'annule' => StatutRendezVous::ANNULE->value,
            ]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \RuntimeException("Erreur d'accès à la base de données.", previous: $e);
        }

        return array_map(fn (array $row) => $this->creerRendezVous($row), $rows);
    }

    public function save(RendezVous $rendezVous): void
    {
        try {
            $stmt = $this->pdo->prepare('UPDATE rdv SET statut = :statut WHERE id = :id');
            $stmt->execute([
                'statut' => $rendezVous->getStatut()->value,
                'id' => $rendezVous->getId(),
            ]);
        } catch (\PDOException $e) {
            throw new \RuntimeException("Erreur d'accès à la base de données.", previous: $e);
        }
    }

    public function ajouter(RendezVous $rendezVous): void
    {
        try {
            $stmt = $this->pdo->prepare('INSERT INTO rdv (id, patient_id, praticien_id, structure_id, date_heure, duree, motif, statut) VALUES (:id, :patient_id, :praticien_id, :structure_id, :date_heure, :duree, :motif, :statut)');
            $stmt->execute([
                'id' => $rendezVous->getId(),
                'patient_id' => $rendezVous->getPatientId(),
                'praticien_id' => $rendezVous->getPraticienId(),
                'structure_id' => $rendezVous->getStructureId(),
                'date_heure' => $rendezVous->getDateTimeHeure()->format('Y-m-d H:i:s'),
                'duree' => $rendezVous->getDuree(),
                'motif' => $rendezVous->getMotif(),
                'statut' => $rendezVous->getStatut()->value,
            ]);
        } catch (\PDOException $e) {
            throw new \RuntimeException("Erreur d'accès à la base de données.", previous: $e);
        }
    }

    private function creerRendezVous(array $row): RendezVous
    {
        return new RendezVous(
            $row['id'],
            $row['patient_id'],
            $row['praticien_id'],
            $row['structure_id'],
            new \DateTime($row['date_heure']),
            (int) $row['duree'],
            $row['motif'],
            StatutRendezVous::from((int) $row['statut'])
        );
    }
}
