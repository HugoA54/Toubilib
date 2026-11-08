<?php
declare(strict_types=1);

namespace toubilib\application\usecases;

use DateTime;
use toubilib\application\dto\AgendaEntreeDTO;
use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\dto\CreneauDTO;
use toubilib\application\dto\PeriodeDTO;
use toubilib\application\dto\RendezVousDTO;
use toubilib\application\exceptions\AnnulationRendezVousException;
use toubilib\application\exceptions\PraticienIntrouvableException;
use toubilib\application\exceptions\RendezVousIntrouvableException;
use toubilib\application\exceptions\TransitionRendezVousException;
use toubilib\application\ports\api\ServiceRendezVousInterface;
use toubilib\application\ports\spi\PatientRepositoryInterface;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\application\validators\RendezVousValidator;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\exceptions\RendezVousException;

class ServiceRendezVous implements ServiceRendezVousInterface
{
    public function __construct(
        private RendezVousRepositoryInterface $rendezVousRepository,
        private RendezVousValidator $validator,
        private PraticienRepositoryInterface $praticienRepository,
        private PatientRepositoryInterface $patientRepository,
    ) {
    }

    public function details(string $idRendezVous): RendezVousDTO
    {
        return RendezVousDTO::fromEntity($this->chargerRendezVous($idRendezVous));
    }

    public function annuler(string $idRendezVous): RendezVousDTO
    {
        $rendezVous = $this->chargerRendezVous($idRendezVous);
        try {
            $rendezVous->annuler();
        } catch (RendezVousException $e) {
            throw new AnnulationRendezVousException($e->getMessage(), previous: $e);
        }

        $this->rendezVousRepository->save($rendezVous);

        return RendezVousDTO::fromEntity($rendezVous);
    }

    public function honorer(string $idRendezVous): RendezVousDTO
    {
        return $this->cloturer($idRendezVous, fn (RendezVous $rendezVous) => $rendezVous->marquerHonore());
    }

    public function ignorer(string $idRendezVous): RendezVousDTO
    {
        return $this->cloturer($idRendezVous, fn (RendezVous $rendezVous) => $rendezVous->marquerIgnore());
    }

    public function creer(CreateRdvDTO $dto): RendezVousDTO
    {
        $this->validator->validate($dto);

        $dateHeure = new DateTime($dto->dateHeure);
        $praticien = $this->praticienRepository->findByIdAvecRendezVous($dto->praticienId, $dateHeure);
        $patient = $this->patientRepository->findById($dto->patientId);
        $rendezVous = RendezVous::creer($praticien, $patient, $dateHeure, $dto->motif);
        $this->rendezVousRepository->ajouter($rendezVous);

        return RendezVousDTO::fromEntity($rendezVous);
    }

    public function creneaux(string $idPraticien, PeriodeDTO $periode): array
    {
        return array_map(CreneauDTO::fromEntity(...), $this->chargerPeriode($idPraticien, $periode));
    }

    public function agenda(string $idPraticien, PeriodeDTO $periode): array
    {
        $patients = [];
        $entrees = [];
        foreach ($this->chargerPeriode($idPraticien, $periode) as $rendezVous) {
            $idPatient = $rendezVous->getPatientId();
            $patients[$idPatient] ??= $this->patientRepository->findById($idPatient);
            $entrees[] = AgendaEntreeDTO::fromEntities($rendezVous, $patients[$idPatient]);
        }

        return $entrees;
    }

    private function chargerRendezVous(string $idRendezVous): RendezVous
    {
        $rendezVous = $this->rendezVousRepository->findById($idRendezVous);
        if ($rendezVous === null) {
            throw new RendezVousIntrouvableException("Le rendez-vous $idRendezVous est introuvable.");
        }

        return $rendezVous;
    }

    /** @return RendezVous[] */
    private function chargerPeriode(string $idPraticien, PeriodeDTO $periode): array
    {
        if ($this->praticienRepository->findById($idPraticien) === null) {
            throw new PraticienIntrouvableException("Le praticien $idPraticien est introuvable.");
        }

        return $this->rendezVousRepository->findByPraticienEtPeriode(
            $idPraticien,
            new DateTime($periode->debut),
            (new DateTime($periode->fin))->modify('+1 day'),
        );
    }

    private function cloturer(string $idRendezVous, callable $transition): RendezVousDTO
    {
        $rendezVous = $this->chargerRendezVous($idRendezVous);
        try {
            $transition($rendezVous);
        } catch (RendezVousException $e) {
            throw new TransitionRendezVousException($e->getMessage(), previous: $e);
        }

        $this->rendezVousRepository->save($rendezVous);

        return RendezVousDTO::fromEntity($rendezVous);
    }
}
