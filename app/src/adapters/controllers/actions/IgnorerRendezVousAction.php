<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpException;
use Slim\Exception\HttpNotFoundException;

use toubilib\application\exceptions\RendezVousIntrouvableException;
use toubilib\application\exceptions\TransitionRendezVousException;
use toubilib\application\ports\api\ServiceRendezVousInterface;

class IgnorerRendezVousAction extends AbstractAction {

    public function __construct(private ServiceRendezVousInterface $serviceRendezVous) {
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface {
        $id = $args['id'];
        try {
            $rendezVousDTO = $this->serviceRendezVous->ignorer($id);
        } catch (RendezVousIntrouvableException $e) {
            throw new HttpNotFoundException($rq, "Aucun rendez-vous avec l'id $id");
        } catch (TransitionRendezVousException $e) {
            throw new HttpException($rq, $e->getMessage(), 409, $e);
        }
        return $this->json($rs, $rendezVousDTO->toArray());
    }
}
