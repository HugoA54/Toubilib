<?php
namespace tests\pest\fakes;

use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\StatutRendezVous;
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

    public function findByPraticienEtPeriode(string $praticienId, \DateTime $debut, \DateTime $fin): array {
        $resultat = array_values(array_filter(
            $this->store,
            fn (RendezVous $rdv) => $rdv->getPraticienId() === $praticienId
                && $rdv->getStatut() !== StatutRendezVous::ANNULE
                && $rdv->getDateTimeHeure() >= $debut
                && $rdv->getDateTimeHeure() < $fin
        ));
        usort($resultat, fn (RendezVous $a, RendezVous $b) => $a->getDateTimeHeure() <=> $b->getDateTimeHeure());

        return $resultat;
    }
}
