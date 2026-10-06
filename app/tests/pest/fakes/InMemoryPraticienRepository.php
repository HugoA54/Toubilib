<?php
namespace tests\pest\fakes;

use toubilib\domain\entities\Praticien;
use toubilib\application\ports\spi\PraticienRepositoryInterface;

class InMemoryPraticienRepository implements PraticienRepositoryInterface {

    private array $store = [];

    public function save(Praticien $praticien): void {
        $this->store[$praticien->getId()] = $praticien;
    }

    public function findByAll(): array {
        return array_values($this->store);
    }

    public function findById(string $id): ?Praticien {
        return $this->store[$id] ?? null;
    }
}
