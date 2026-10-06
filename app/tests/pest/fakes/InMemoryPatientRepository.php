<?php
namespace tests\pest\fakes;

use toubilib\domain\entities\Patient;
use toubilib\application\ports\spi\PatientRepositoryInterface;

class InMemoryPatientRepository implements PatientRepositoryInterface {

    private array $store = [];

    public function save(Patient $patient): void {
        $this->store[$patient->getId()] = $patient;
    }

    public function findById(string $id): ?Patient {
        return $this->store[$id] ?? null;
    }
}
