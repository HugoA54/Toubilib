<?php
declare(strict_types=1);

namespace toubilib\adapters\persistance;
use toubilib\application\ports\spi\PatientRepositoryInterface;
use toubilib\domain\entities\Patient;

class PatientRepository implements PatientRepositoryInterface{
        public function __construct(private \PDO $pdo) {}



public function findById(string $id): ?Patient
{
    try {
        $stmt = $this->pdo->prepare('SELECT id, nom, prenom, email, telephone FROM patient WHERE id = :id');
        $stmt->execute(['id' => $id]);
    } catch (\PDOException $e) {
        throw new \RuntimeException("Erreur d'accès à la base de données.", previous: $e);
    }

    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    if ($row === false) {
        return null;
    }

    return new Patient($row['id'], $row['nom'], $row['prenom'], $row['email'], $row['telephone']);
}

}