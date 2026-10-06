<?php
declare(strict_types=1);

namespace toubilib\application\usecases;

use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\dto\RendezVousDTO;
use toubilib\application\exceptions\AnnulationRendezVousException;
use toubilib\application\exceptions\RendezVousIntrouvableException;
use toubilib\application\ports\api\ServiceRendezVousInterface;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\application\validators\RendezVousValidator;
use toubilib\domain\exceptions\RendezVousException;

class ServiceRendezVous implements ServiceRendezVousInterface
{
    public function __construct(
        private RendezVousRepositoryInterface $rendezVousRepository,
        private RendezVousValidator $validator,
    ) {
    }

    public function details(string $idRendezVous): RendezVousDTO
    {
        $rendezVous = $this->rendezVousRepository->findById($idRendezVous);
        if ($rendezVous === null) {
            throw new RendezVousIntrouvableException("Le rendez-vous $idRendezVous est introuvable.");
        }
        return RendezVousDTO::fromEntity($rendezVous);
    }

    public function annuler(string $idRendezVous): RendezVousDTO
    {
        $rendezVous = $this->rendezVousRepository->findById($idRendezVous);
        if ($rendezVous === null) {
            throw new RendezVousIntrouvableException("Le rendez-vous $idRendezVous est introuvable.");
        }
        try {
            $rendezVous->annuler();
        } catch (RendezVousException $e) {
            throw new AnnulationRendezVousException($e->getMessage(), previous: $e);
        }

        $this->rendezVousRepository->save($rendezVous);

        return RendezVousDTO::fromEntity($rendezVous);
    }

    public function creer(CreateRdvDTO $dto): CreateRdvDTO
    {
        $this->validator->validate($dto->praticienId, $dto->patientId);

        return $dto;
    }
}
