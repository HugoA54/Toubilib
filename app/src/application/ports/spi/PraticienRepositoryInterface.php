<?php
declare(strict_types=1);

namespace toubilib\application\ports\spi;

use toubilib\domain\entities\Praticien;

interface PraticienRepositoryInterface 
{
    public function findByAll() : array;

    public function findById(string $id) : ?Praticien; 
    
}