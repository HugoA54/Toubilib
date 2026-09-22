<?php

namespace tests\pest\fakes;

use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\domain\entities\RendezVous;

class InMemoryRendezVousRepository implements RendezVousRepositoryInterface {

    private array $rendezVous = [];

    public int $nbSave = 0;

    public function findById(string $id): ?RendezVous {
        return $this->rendezVous[$id] ?? null;
    }

    public function save(RendezVous $rendezVous): void {
        $this->rendezVous[$rendezVous->getId()] = $rendezVous;
        $this->nbSave++;
    }
}
