<?php
declare(strict_types=1);

namespace toubilib\application\usecases;

use toubilib\application\dto\PraticienDTO;
use toubilib\application\exceptions\PraticienIntrouvableException;
use toubilib\application\ports\api\ServicePraticienInterface;
use toubilib\application\ports\spi\PraticienRepositoryInterface;

class ServicePraticien implements ServicePraticienInterface
{
    public function __construct(
        private PraticienRepositoryInterface $praticienRepository
    ) {
    }

    public function getAllPraticiens(): array
    {
        return array_map(PraticienDTO::fromEntity(...), $this->praticienRepository->findByAll());
    }

    public function getDetails(string $id): PraticienDTO
    {
        $praticien = $this->praticienRepository->findById($id);
        if ($praticien === null) {
            throw new PraticienIntrouvableException("Le praticien $id est introuvable.");
        }

        return PraticienDTO::fromEntity($praticien);
    }
}
