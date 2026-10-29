<?php
declare(strict_types=1);

namespace toubilib\adapters\persistance;

use Override;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\domain\entities\Praticien;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\StatutRendezVous;

class PraticienRepository implements PraticienRepositoryInterface{
        public function __construct(private \PDO $pdo) {}

        public function findByAll(): array
        {
            try {
                $stmt = $this->pdo->prepare('SELECT id, nom, prenom FROM praticien');
                $stmt->execute();
                $praticiens = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }
            catch (\PDOException $e) {
            }
            
            
            if (!$praticiens){
                return [];
            }
            return $praticiens;

        }

        public function findById(string $id): ?Praticien
        {
            try {
                $stmt = $this->pdo->prepare('SELECT id, titre, nom, prenom, adresse, ville, email, telephone, horaire_debut, horaire_fin, specialite_id, structure_id FROM praticien WHERE id = :id');
                $stmt->execute(['id' => $id]);
            } catch (\PDOException $e) {
                throw new \RuntimeException("Erreur d'accès à la base de données.", previous: $e);
            }

            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row === false) {
                return null;
            }

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
                (string) $row['specialite_id'],
                $row['structure_id'],
            );
        }

        public function findByIdAvecRendezVous(string $id, \DateTime $jour): ?Praticien
        {
            try {
                $stmt = $this->pdo->prepare('SELECT id, titre, nom, prenom, adresse, ville, email, telephone, horaire_debut, horaire_fin, specialite_id, structure_id FROM praticien WHERE id = :id');
                $stmt->execute(['id' => $id]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($row === false) {
                    return null;
                }

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
            );
        }
}