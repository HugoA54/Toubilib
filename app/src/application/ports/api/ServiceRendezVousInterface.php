<?php
declare(strict_types=1);

namespace toubilib\application\ports\api;

use toubilib\application\dto\AgendaEntreeDTO;
use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\dto\CreneauDTO;
use toubilib\application\dto\PeriodeDTO;
use toubilib\application\dto\RendezVousDTO;

interface ServiceRendezVousInterface
{
    public function details(string $idRendezVous): RendezVousDTO;

    public function annuler(string $idRendezVous): RendezVousDTO;

    public function honorer(string $idRendezVous): RendezVousDTO;

    public function ignorer(string $idRendezVous): RendezVousDTO;

    public function creer(CreateRdvDTO $dto): RendezVousDTO;

    /** @return CreneauDTO[] */
    public function creneaux(string $idPraticien, PeriodeDTO $periode): array;

    /** @return AgendaEntreeDTO[] */
    public function agenda(string $idPraticien, PeriodeDTO $periode): array;
}
