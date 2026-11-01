<?php
declare(strict_types=1);

namespace toubilib\application\validators;

use DateTime;
use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\exceptions\ValidationException;
use toubilib\application\ports\spi\PatientRepositoryInterface;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\exceptions\RendezVousException;

class RendezVousValidator
{
    public function __construct(
        private readonly PraticienRepositoryInterface $praticienRepository,
        private readonly PatientRepositoryInterface $patientRepository,
    ) {
    }

    public function validate(CreateRdvDTO $dto): void
    {
        $errors = [];
        $dateHeure = new DateTime($dto->dateHeure);
        $praticien = $this->praticienRepository->findByIdAvecRendezVous($dto->praticienId, $dateHeure);
        if ($praticien === null) {
            $errors['praticienId'] = "Le praticien $dto->praticienId n'existe pas.";
        }
        $patient = $this->patientRepository->findById($dto->patientId);
        if ($patient === null) {
            $errors['patientId'] = "Le patient $dto->patientId n'existe pas.";
        }
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        try {
            RendezVous::creer($praticien, $patient, $dateHeure, $dto->motif);
        } catch (RendezVousException $e) {
            throw new ValidationException(['rendezVous' => $e->getMessage()]);
        }
    }
}
