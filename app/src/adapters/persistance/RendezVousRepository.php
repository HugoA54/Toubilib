<?php
declare(strict_types=1);

namespace toubilib\adapters\persistance;

use toubilib\application\exceptions\AnnulationRendezVousException;
use toubilib\domain\entities\RendezVous;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;

class RendezVousRepository implements RendezVousRepositoryInterface {

    private \PDO $pdo;

    public function findById(string $id) : RendezVous { 
        try {
            $stmt  = $this->pdo->prepare('SELECT id FROM rdv WHERE id = :id');

            $stmt->execute(['id' => $id]);
        }
        catch (\PDOException $e) {
            throw new AnnulationRendezVousException("Erreur : " . $e->getMessage());
        }
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        $rendezVous = new RendezVous($row['id'], $row['patient_id'], $row['praticien_id'], $row['structure_id'], $row['date_heure'], $row['duree'], $row['motif'], $row['statut']);
        return $rendezVous;
    }

    public function save(RendezVous $rendezVous): void {
        try {
            $stmt = $this->pdo->prepare('INSERT INTO rdv (id, patient_id, praticien_id, structure_id, date_heure, duree, motif, statut) 
                                              VALUES (:id, :patient, :praticien, :structure, :date_heure, :duree, :motif, :statut)');
            $stmt->execute([
                'id' => $rendezVous->getId(),
                'patient' => $rendezVous->getPatientId(),
                'praticien' => $rendezVous->getPraticienId(),
                'structure' => $rendezVous->getStructureId(),
                'date_heure' => $rendezVous->getDateTimeHeure(),
                'duree' => $rendezVous->getDuree(),
                'motif' => $rendezVous->getMotif(),
                'statut' => $rendezVous->getStatut()
            ]);
        } catch (\PDOException $e) {

        }
    }
}
?>
