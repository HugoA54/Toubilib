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

    public function toArray(): array
    {
        return [
            'type' => "ressource",
            'rendez_vous' => array(
                'id' => $this->id,
                'patient_id' => $this->patientId,
                'praticien_id' => $this->praticienId,
                'structure_id' => $this->structureId,
                'date_heure' => $this->dateHeure,
                'date_heure_fin' => $this->dateHeureFin,
                'duree' => $this->duree,
                'motif' => $this->motif,
                'statut' => $this->statut,
                'statut_libelle' => $this->statutLibelle
            ),
            'links' => array(
                'self' => ["href" => "/rendezvous/" . $this->id],
                'praticien' => ["href" => "/praticiens/" . $this->praticienId],
                'annuler' => ["href" => "/rendezvous/" . $this->id . "/annuler"],
                'honorer' => ["href" => "/rendezvous/" . $this->id . "/honorer"],
                'ignorer' => ["href" => "/rendezvous/" . $this->id . "/ignorer"],
                

            )

        ];
    }
}
