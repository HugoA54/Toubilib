<?php
declare(strict_types=1);

namespace toubilib\application\ports\api;

interface ServicePraticienInterface
{
    public function getAllPraticiens() : array;

    public function getDetails(string $id);
}