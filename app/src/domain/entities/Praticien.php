<?php
declare(strict_types=1);

namespace toubilib\domain\entities;
use DateTime;

class Praticien 
{
    private function __construct(
        private string $id,
        private string $titre,
        private string $nom,
        private string $prenom,
        private string $adresse,
        private string $ville,
        private string $email,
        private string $tel,
        private DateTime $debut,
        private DateTime $fin,
        private string $specialiteId,
        private string $structureId
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

public function getAdresse(): string
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

public function getSpecialiteId(): string
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



}