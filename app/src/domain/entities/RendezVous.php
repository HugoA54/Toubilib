<?php
declare(strict_types=1);

namespace toubilib\domain\entities;

use DateInterval;
use DateTime;
use toubilib\domain\exceptions\RendezVousException;


class RendezVous
{
    private StatutRendezVous $statut;

    public function __construct(
        private  string $id,
        private  string $patientId,
        private  string $praticienId,
        private  string $structureId,
        private  DateTime $DateTimeHeure,
        private  int $duree = 30,
        private  ?string $motif = null,
        StatutRendezVous $statut = StatutRendezVous::PREVU,
    ) {
        if ($duree <= 0) {
            throw new RendezVousException("La durée d'un rendez-vous doit être strictement positive.");
        }
        $this->statut = $statut;
    }


    public function estAnnulable(?DateTime $maintenant = null): bool
    {
        $maintenant ??= new DateTime();

        return $this->statut === StatutRendezVous::PREVU && $maintenant < $this->DateTimeHeure;
    }

    public function annuler(?DateTime $maintenant = null): void
    {
        if (!$this->estAnnulable($maintenant)) {
            throw new RendezVousException(    $this->statut->estFinal()
                    ? "Impossible d'annuler un rendez-vous {$this->statut->libelle()}."
                    : "Impossible d'annuler un rendez-vous déjà commencé."
            );
        }
        $this->statut = StatutRendezVous::ANNULE;
    }

    public function marquerHonore(?DateTime $maintenant = null): void
    {
        $this->cloturer(StatutRendezVous::HONORE, $maintenant);
    }

    public function marquerIgnore(?DateTime $maintenant = null): void
    {
        $this->cloturer(StatutRendezVous::NON_HONORE, $maintenant);
    }

    private function cloturer(StatutRendezVous $nouveauStatut, ?DateTime $maintenant): void
    {
        $maintenant ??= new DateTime();

        if ($this->statut->estFinal()) {
            throw new RendezVousException(
                "Impossible de marquer {$nouveauStatut->libelle()} un rendez-vous {$this->statut->libelle()}."
            );
        }
        if ($maintenant < $this->DateTimeHeure) {
            throw new RendezVousException(
                "Impossible de marquer {$nouveauStatut->libelle()} un rendez-vous qui n'a pas encore commencé."
            );
        }
        $this->statut = $nouveauStatut;
    }


    public function calculerDuree(): int
    {
        return $this->duree;
    }

    public function getDateTimeHeureFin(): DateTime
    {
        return (clone $this->DateTimeHeure)->add(new DateInterval("PT{$this->duree}M"));
    }


    public function getId(): string
    {
        return $this->id;
    }

    public function getPatientId(): string
    {
        return $this->patientId;
    }

    public function getPraticienId(): string
    {
        return $this->praticienId;
    }

    public function getStructureId(): string
    {
        return $this->structureId;
    }

    public function getDateTimeHeure(): DateTime
    {
        return $this->DateTimeHeure;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function getStatut(): StatutRendezVous
    {
        return $this->statut;
    }

    public function getDuree(): int
    {
        return $this->duree;
    }

    public function getDetails(): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patientId,
            'praticien_id' => $this->praticienId,
            'structure_id' => $this->structureId,
            'DateTime_heure' => $this->DateTimeHeure->format('Y-m-d H:i'),
            'DateTime_heure_fin' => $this->getDateTimeHeureFin()->format('Y-m-d H:i'),
            'duree' => $this->duree,
            'motif' => $this->motif,
            'statut' => $this->statut->value,
            'statut_libelle' => $this->statut->libelle(),
        ];
    }
}
