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
        // TODO
    }
}
?>