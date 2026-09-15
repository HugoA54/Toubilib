<?php
declare(strict_types=1);

namespace toubilib\application\dto;

use toubilib\domain\entities\RendezVous;

final readonly class RendezVousDTO
{
    public function __construct(
        public string $id,
        public string $patientId,
        public string $praticienId,
        public string $structureId,
        public string $dateHeure,
        public string $dateHeureFin,
        public int $duree,
        public ?string $motif,
        public int $statut,
        public string $statutLibelle,
    ) {
    }

    public static function fromEntity(RendezVous $rendezVous): self
    {
        return new self(
            id: $rendezVous->getId(),
            patientId: $rendezVous->getPatientId(),
            praticienId: $rendezVous->getPraticienId(),
            structureId: $rendezVous->getStructureId(),
            dateHeure: $rendezVous->getDateTimeHeure()->format('Y-m-d H:i'),
            dateHeureFin: $rendezVous->getDateTimeHeureFin()->format('Y-m-d H:i'),
            duree: $rendezVous->calculerDuree(),
            motif: $rendezVous->getMotif(),
            statut: $rendezVous->getStatut()->value,
            statutLibelle: $rendezVous->getStatut()->libelle(),
        );
    }
}
