<?php
declare(strict_types=1);

namespace toubilib\application\ports\spi;

use DateTime;
use toubilib\domain\entities\Praticien;

interface PraticienRepositoryInterface
{
    /** @return Praticien[] */
    public function findByAll() : array;

    public function findById(string $id) : ?Praticien;

    public function findByIdAvecRendezVous(string $id, DateTime $jour) : ?Praticien;

}
