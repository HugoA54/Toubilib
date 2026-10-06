<?php
declare(strict_types=1);

namespace toubilib\application\dto;

final readonly class CreateRdvDTO
{
    public function __construct(
        public string $praticienId,
        public string $patientId,
        public string $dateHeure,
        public int $duree = 30,
        public ?string $motif = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'praticien_id' => $this->praticienId,
            'patient_id' => $this->patientId,
            'date_heure' => $this->dateHeure,
            'duree' => $this->duree,
            'motif' => $this->motif,
        ];
    }
}
