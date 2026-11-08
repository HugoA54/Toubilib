<?php
declare(strict_types=1);

namespace toubilib\application\ports\spi;

use DateTime;
use toubilib\domain\entities\RendezVous;

interface RendezVousRepositoryInterface
{
    public function findById(string $id): ?RendezVous;

    /**
     * Rendez-vous non annulés du praticien, triés par date, avec debut <= date < fin.
     *
     * @return RendezVous[]
     */
    public function findByPraticienEtPeriode(string $praticienId, DateTime $debut, DateTime $fin): array;

    public function save(RendezVous $rendezVous): void;

    public function ajouter(RendezVous $rendezVous): void;
}
