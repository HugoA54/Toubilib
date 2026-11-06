<?php
declare(strict_types=1);

namespace toubilib\application\usecases;

use toubilib\application\exceptions\PraticienIntrouvableException;
use toubilib\application\ports\api\ServicePraticienInterface;
use toubilib\application\ports\spi\PraticienRepositoryInterface;



class ServicePraticien implements ServicePraticienInterface 
{
    public function __construct(
        private PraticienRepositoryInterface $praticienRepository
    )
    {}

    public function getAllPraticiens(): array
    {
        $listePraticiens = $this->praticienRepository->findByAll();
        return $listePraticiens;
    }

    public function getDetails($id){
        $praticien = $this->praticienRepository->findById($id);
        if (!$praticien){
            throw new PraticienIntrouvableException();
        }

        return $praticien;

    }

    public function toArray(){
        
    }


}