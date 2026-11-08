<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpInternalServerErrorException;

use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\exceptions\ValidationException;
use toubilib\application\ports\api\ServiceRendezVousInterface;

class CreerRendezVousAction extends AbstractAction {

    private ServiceRendezVousInterface $serviceRendezVous;

    public function __construct(ServiceRendezVousInterface $serviceRendezVous) {
        $this->serviceRendezVous = $serviceRendezVous;
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface {
        $body = $rq->getParsedBody();

        try {
            $dto = CreateRdvDTO::fromArray(is_array($body) ? $body : []);
        } catch (ValidationException $e) {
            return $this->json($rs, ['errors' => $e->getErrors()], 422);
        }

        try {
            $rendezVousDTO = $this->serviceRendezVous->creer($dto);
        } catch (ValidationException $e) {
            return $this->json($rs, ['errors' => $e->getErrors()], 422);
        } catch (\RuntimeException $e) {
            throw new HttpInternalServerErrorException($rq, "Erreur lors de l'enregistrement du rendez-vous.");
        }

        return $this->json($rs->withHeader('Location', '/rdvs/' . $rendezVousDTO->id), $rendezVousDTO->toArray(), 201);
    }
}
