<?php
declare(strict_types=1);

namespace toubilib\application\ports\spi;

use DateTime;
use toubilib\application\dto\PraticienDTO;

interface PraticienRepositoryInterface
{
    public function findByAll() : array;

    public function findById(string $id) : ?PraticienDTO;

    public function findByIdAvecRendezVous(string $id, DateTime $jour) : ?PraticienDTO;

}