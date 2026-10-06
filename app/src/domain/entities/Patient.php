<?php
declare(strict_types=1);

namespace toubilib\domain\entities;


class Patient{
    public function __construct(
                private string $id,
                private string $nom,
                private string $prenom,
                private ?string $email,
                private string $telephone)
    {     }

    public function getId(): string
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getTelephone(): string
    {
        return $this->telephone;
    }
}
