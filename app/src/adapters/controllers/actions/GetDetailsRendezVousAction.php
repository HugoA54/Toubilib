<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

use toubilib\application\exceptions\RendezVousIntrouvableException;
use toubilib\application\ports\api\ServiceRendezVousInterface;

class GetDetailsRendezVousAction extends AbstractAction {

    private ServiceRendezVousInterface $serviceRendezVous;

    public function __construct(ServiceRendezVousInterface $serviceRendezVous) {
        $this->serviceRendezVous = $serviceRendezVous;
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface {
        $id = $args['id'];
        try {
            $rendezVousDTO = $this->serviceRendezVous->details($id);
        } catch (RendezVousIntrouvableException $e) {
            throw new HttpNotFoundException($rq, "Aucun rendez-vous avec l'id $id");
        }
        return $this->json($rs, $rendezVousDTO->toArray());
    }
}
