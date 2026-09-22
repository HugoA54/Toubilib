<?php
declare(strict_types=1);

namespace toubilib\application\ports\api;

use toubilib\application\dto\RendezVousDTO;
use toubilib\application\exceptions\AnnulationRendezVousException;
use toubilib\application\exceptions\RendezVousIntrouvableException;

interface ServiceRendezVousInterface
{
  
    public function annuler(string $idRendezVous): RendezVousDTO;
}
