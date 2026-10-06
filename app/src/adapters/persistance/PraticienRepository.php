<?php
declare(strict_types=1);

namespace toubilib\adapters\persistance;

use Override;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\domain\entities\Praticien;

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
                $row['specialite_id'],
                $row['structure_id'],
            );
        }
}