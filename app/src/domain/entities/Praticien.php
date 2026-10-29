<?php
declare(strict_types=1);

namespace toubilib\domain\entities;
use DateTime;
use toubilib\domain\exceptions\RendezVousException;

class Praticien
{
    public function __construct(
        private string $id,
        private string $titre,
        private string $nom,
        private string $prenom,
        private ?string $adresse,
        private string $ville,
        private string $email,
        private string $tel,
        private DateTime $debut,
        private DateTime $fin,
        private int $specialiteId,
        private string $structureId,
        private array $motifs = []
    ) {

    }

public function getId(): string
{
    return $this->id;
}

public function getTitre(): string
{
    return $this->titre;
}

public function getNom(): string
{
    return $this->nom;
}

public function getPrenom(): string
{
    return $this->prenom;
}

public function getAdresse(): ?string
{
    return $this->adresse;
}

public function getVille(): string
{
    return $this->ville;
}

public function getEmail(): string
{
    return $this->email;
}

public function getTel(): string
{
    return $this->tel;
}

public function getDebut(): DateTime
{
    return $this->debut;
}

public function getFin(): DateTime
{
    return $this->fin;
}

public function getSpecialiteId(): int
{
    return $this->specialiteId;
}

public function getStructureId(): string
{
    return $this->structureId;
}

public function getDetails(): array
{
    return [
        'titre' => $this->titre,
        'nom' => $this->nom,
        'prenom' => $this->prenom,
        'adresse' => $this->adresse,
        'ville' => $this->ville,
        'email' => $this->email,
        'tel' => $this->tel,
        'debut' => $this->debut->format('Y-m-d H:i'),
        'fin' => $this->fin->format('Y-m-d H:i'),
        'specialite_id' => $this->specialiteId,
        'structure_id' => $this->structureId,
    ];
}

public function verifierMotif(string $motif): bool
{
    return array_key_exists($motif, $this->motifs);
}

public function calculerDuree(string $motif): int
{
    if (!$this->verifierMotif($motif)) {
        throw new RendezVousException("Le motif $motif n'est pas proposé par le praticien.");
    }
    return $this->motifs[$motif];
}

public function verifierDateHeure(DateTime $dateHeure): bool
{
    $heure = $dateHeure->format('H:i:s');
    return (int) $dateHeure->format('N') <= 5
        && $heure >= $this->debut->format('H:i:s')
        && $heure < $this->fin->format('H:i:s');
}



}