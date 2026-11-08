<?php
declare(strict_types=1);

namespace toubilib\application\dto;

use toubilib\domain\entities\Patient;
use toubilib\domain\entities\RendezVous;

final readonly class AgendaEntreeDTO
{
    public function __construct(
        public string $id,
        public string $debut,
        public string $fin,
        public int $duree,
        public ?string $motif,
        public int $statut,
        public string $statutLibelle,
        public string $patientId,
        public string $patientNom,
        public string $patientPrenom,
    ) {
    }

    public static function fromEntities(RendezVous $rendezVous, Patient $patient): self
    {
        return new self(
            id: $rendezVous->getId(),
            debut: $rendezVous->getDateTimeHeure()->format('Y-m-d H:i'),
            fin: $rendezVous->getDateTimeHeureFin()->format('Y-m-d H:i'),
            duree: $rendezVous->getDuree(),
            motif: $rendezVous->getMotif(),
            statut: $rendezVous->getStatut()->value,
            statutLibelle: $rendezVous->getStatut()->libelle(),
            patientId: $patient->getId(),
            patientNom: $patient->getNom(),
            patientPrenom: $patient->getPrenom(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'debut' => $this->debut,
            'fin' => $this->fin,
            'duree' => $this->duree,
            'motif' => $this->motif,
            'statut' => $this->statut,
            'statut_libelle' => $this->statutLibelle,
            'patient' => [
                'id' => $this->patientId,
                'nom' => $this->patientNom,
                'prenom' => $this->patientPrenom,
            ],
            'links' => [
                'self' => ['href' => '/rdvs/' . $this->id],
            ],
        ];
    }
}
