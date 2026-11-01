<?php
namespace tests\pest\fakes;

use toubilib\domain\entities\RendezVous;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;

class InMemoryRendezVousRepository implements RendezVousRepositoryInterface {

    private array $store = [];

    public function save(RendezVous $rendezVous): void {
        $this->store[$rendezVous->getId()] = $rendezVous;
    }

    public function ajouter(RendezVous $rendezVous): void {
        $this->store[$rendezVous->getId()] = $rendezVous;
    }

    public function findById(string $id): ?RendezVous {
        return $this->store[$id] ?? null;
    }
}
