<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

use toubilib\application\exceptions\PraticienIntrouvableException;
use toubilib\application\ports\api\ServicePraticienInterface;

class GetDetailsPraticiensAction extends AbstractAction {

    private ServicePraticienInterface $servicePraticien;

    public function __construct(ServicePraticienInterface $servicePraticien) {
        $this->servicePraticien = $servicePraticien;
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface {
        $id = $args['id'];
        try {
            $praticien = $this->servicePraticien->getDetails($id);
        } catch (PraticienIntrouvableException $e) {
            throw new HttpNotFoundException($rq, "Aucun praticien avec l'id $id");
        }
        return $this->json($rs, $praticien->toArray());
    }
}
