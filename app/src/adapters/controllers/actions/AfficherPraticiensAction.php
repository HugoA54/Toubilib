<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use toubilib\application\ports\api\ServicePraticienInterface;
use toubilib\application\usecases\ServicePraticien;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class AfficherPraticiensAction extends AbstractAction {
    
    private ServicePraticienInterface $servicePraticien;


    function __construct(ServicePraticienInterface $servicePraticien) {
        $this->servicePraticien = $servicePraticien;
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface {
        $praticiens = $this->servicePraticien->getAllPraticiens();
        return $this->json($rs, $praticiens);
    }
}
