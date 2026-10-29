<?php
declare(strict_types=1);

namespace toubilib\application\ports\api;

use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\dto\RendezVousDTO;
use toubilib\application\exceptions\AnnulationRendezVousException;
use toubilib\application\exceptions\RendezVousIntrouvableException;
use toubilib\application\exceptions\ValidationException;

interface ServiceRendezVousInterface
{
    public function details(string $idRendezVous): RendezVousDTO;

    public function annuler(string $idRendezVous): RendezVousDTO;

    public function creer(CreateRdvDTO $dto): RendezVousDTO;
}
