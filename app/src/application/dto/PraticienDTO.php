<?php
declare(strict_types=1);

namespace toubilib\application\dto;

use toubilib\domain\entities\Praticien;
use DateTime;

final readonly class PraticienDTO
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
        private ?string $specialite,
        private string $structureId,
    ) {
    }

    public static function fromEntity(Praticien $praticien): self
    {
        return new self(
            id: $praticien->getId(),
            titre: $praticien->getTitre(),
            nom: $praticien->getNom(),
            prenom: $praticien->getPrenom(),
            adresse: $praticien->getAdresse(),
            ville: $praticien->getVille(),
            email: $praticien->getEmail(),
            tel: $praticien->getTel(),
            debut: $praticien->getDebut(),
            fin: $praticien->getFin(),
            specialiteId: $praticien->getSpecialiteId(),
            specialite: $praticien->getSpecialite(),
            structureId: $praticien->getStructureId(),
        );
    }

    public function toArray(): array
    {
        return [
            "type" => "ressource",
            "praticien" => [
                'id' => $this->id,
                'titre' => $this->titre,
                'nom' => $this->nom,
                'prenom' => $this->prenom,
                'adresse' => $this->adresse,
                'ville' => $this->ville,
                'email' => $this->email,
                'tel' => $this->tel,
                'debut' => $this->debut->format('H:i'),
                'fin' => $this->fin->format('H:i'),
                'specialite_id' => $this->specialiteId,
                'specialite' => $this->specialite,
                'structure_id' => $this->structureId,
            ],
            "links" => [
                "self" => ["href" => "/praticiens/" . $this->id],
                "creneaux" => ["href" => "/praticiens/" . $this->id . "/creneaux"],
                "agenda" => ["href" => "/praticiens/" . $this->id . "/agenda"],
            ],
        ];
    }

    public function toResumeArray(): array
    {
        return [
            'id' => $this->id,
            'titre' => $this->titre,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'ville' => $this->ville,
            'specialite' => $this->specialite,
            'links' => [
                'self' => ['href' => '/praticiens/' . $this->id],
            ],
        ];
    }
}
