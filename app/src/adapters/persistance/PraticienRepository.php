<?php
declare(strict_types=1);

namespace toubilib\adapters\persistance;

use Override;
use toubilib\application\ports\spi\PraticienRepositoryInterface;

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
}