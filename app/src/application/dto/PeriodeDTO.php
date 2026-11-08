<?php
declare(strict_types=1);

namespace toubilib\application\dto;

use DateTimeImmutable;
use toubilib\application\exceptions\ValidationException;

/** Période de consultation, bornes incluses, au format AAAA-MM-JJ. */
final readonly class PeriodeDTO
{
    public function __construct(
        public string $debut,
        public string $fin,
    ) {
    }

    /** Sans paramètre, la période couvre les 7 jours à partir d'aujourd'hui. */
    public static function fromArray(array $data): self
    {
        $errors = [];
        $aujourdhui = new DateTimeImmutable('today');
        $debut = self::lireDate($data, 'debut', $aujourdhui, $errors);
        $fin = self::lireDate($data, 'fin', ($debut ?? $aujourdhui)->modify('+6 days'), $errors);

        if ($debut !== null && $fin !== null && $fin < $debut) {
            $errors['fin'] = "Le paramètre fin doit être postérieur ou égal à debut.";
        }
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        return new self($debut->format('Y-m-d'), $fin->format('Y-m-d'));
    }

    private static function lireDate(array $data, string $champ, DateTimeImmutable $defaut, array &$errors): ?DateTimeImmutable
    {
        if (!isset($data[$champ])) {
            return $defaut;
        }

        $valeur = $data[$champ];
        $date = is_string($valeur) ? DateTimeImmutable::createFromFormat('!Y-m-d', $valeur) : false;
        if ($date === false || $date->format('Y-m-d') !== $valeur) {
            $errors[$champ] = "Le paramètre $champ doit être au format AAAA-MM-JJ.";
            return null;
        }

        return $date;
    }
}
