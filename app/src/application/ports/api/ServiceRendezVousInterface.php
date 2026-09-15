<?php
declare(strict_types=1);

namespace toubilib\application\ports\api;

use toubilib\application\dto\RendezVousDTO;
use toubilib\application\exceptions\AnnulationRendezVousException;
use toubilib\application\exceptions\RendezVousIntrouvableException;

interface ServiceRendezVousInterface
{
    /**
     * @throws RendezVousIntrouvableException si aucun rendez-vous ne correspond à l'identifiant
     * @throws AnnulationRendezVousException si le rendez-vous ne peut pas être annulé
     */
    public function annuler(string $idRendezVous): RendezVousDTO;
}
