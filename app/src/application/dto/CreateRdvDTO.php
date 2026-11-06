<?php
declare(strict_types=1);

namespace toubilib\application\dto;

use DateTime;
use Ramsey\Uuid\Uuid;
use toubilib\application\exceptions\ValidationException;

final readonly class CreateRdvDTO
{
    public function __construct(
        public string $praticienId,
        public string $patientId,
        public string $dateHeure,
        public string $motif,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $errors = [];
        foreach (['praticien_id', 'patient_id', 'date_heure', 'motif'] as $champ) {
            if (!isset($data[$champ]) || !is_string($data[$champ]) || trim($data[$champ]) === '') {
                $errors[$champ] = "Le champ $champ est obligatoire et doit être une chaîne de caractères.";
            }
        }
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        $praticienId = trim($data['praticien_id']);
        $patientId = trim($data['patient_id']);
        $dateHeure = trim($data['date_heure']);
        $motif = htmlspecialchars(trim($data['motif']), ENT_QUOTES);

        if (!Uuid::isValid($praticienId)) {
            $errors['praticien_id'] = "Le champ praticien_id doit être un UUID.";
        }
        if (!Uuid::isValid($patientId)) {
            $errors['patient_id'] = "Le champ patient_id doit être un UUID.";
        }
        $date = DateTime::createFromFormat('Y-m-d H:i', $dateHeure);
        if ($date === false || $date->format('Y-m-d H:i') !== $dateHeure) {
            $errors['date_heure'] = "Le champ date_heure doit être au format AAAA-MM-JJ HH:MM.";
        }
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        return new self($praticienId, $patientId, $dateHeure, $motif);
    }

    public function toArray(): array
    {
        return [
            "type" => "ressource",
            "rendez_vous" => [
            'praticien_id' => $this->praticienId,
            'patient_id' => $this->patientId,
            'date_heure' => $this->dateHeure,
            'motif' => $this->motif,
        ]];
    }
}
