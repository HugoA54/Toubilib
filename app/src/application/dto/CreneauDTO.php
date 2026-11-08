<?php
declare(strict_types=1);

namespace toubilib\application\dto;

use toubilib\domain\entities\RendezVous;

final readonly class CreneauDTO
{
    public function __construct(
        public string $debut,
        public string $fin,
        public int $duree,
    ) {
    }

    public static function fromEntity(RendezVous $rendezVous): self
    {
        return new self(
            $rendezVous->getDateTimeHeure()->format('Y-m-d H:i'),
            $rendezVous->getDateTimeHeureFin()->format('Y-m-d H:i'),
            $rendezVous->getDuree(),
        );
    }

    public function toArray(): array
    {
        return [
            'debut' => $this->debut,
            'fin' => $this->fin,
            'duree' => $this->duree,
        ];
    }
}
