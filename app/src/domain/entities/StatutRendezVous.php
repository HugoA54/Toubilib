<?php
declare(strict_types=1);

namespace toubilib\domain\entities;


enum StatutRendezVous: int
{
    case PREVU = 0;
    case HONORE = 1;
    case NON_HONORE = 2;
    case ANNULE = 3;

    public function libelle(): string
    {
        return match ($this) {
            self::PREVU => 'prévu',
            self::HONORE => 'honoré',
            self::NON_HONORE => 'non honoré',
            self::ANNULE => 'annulé',
        };
    }

    public function estFinal(): bool
    {
        return $this !== self::PREVU;
    }
}
