<?php
declare(strict_types=1);

namespace toubilib\application\ports\api;

use toubilib\application\dto\PraticienDTO;

interface ServicePraticienInterface
{
    /** @return PraticienDTO[] */
    public function getAllPraticiens() : array;

    public function getDetails(string $id) : PraticienDTO;
}
