<?php
declare(strict_types=1);

namespace toubilib\application\validators;

use toubilib\application\exceptions\ValidationException;
use toubilib\application\ports\spi\PatientRepositoryInterface;
use toubilib\application\ports\spi\PraticienRepositoryInterface;

class RendezVousValidator
{
    public function __construct(
        private readonly PraticienRepositoryInterface $praticienRepository,
        private readonly PatientRepositoryInterface $patientRepository,
    ) {
    }

    // ── @throws ValidationException
    public function validate(string $praticienId, string $patientId): void
    {
        $errors = [];
        if ($this->praticienRepository->findById($praticienId) === null) {
            $errors['praticienId'] = "Le praticien $praticienId n'existe pas.";
        }
        if ($this->patientRepository->findById($patientId) === null) {
            $errors['patientId'] = "Le patient $patientId n'existe pas.";
        }
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}
