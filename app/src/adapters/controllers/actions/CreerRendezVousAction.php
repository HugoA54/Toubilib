<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;

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
        if (!is_array($body)) {
            throw new HttpBadRequestException($rq, "Le corps de la requête est invalide.");
        }

        foreach (['praticien_id', 'patient_id', 'date_heure'] as $champ) {
            if (!isset($body[$champ]) || !is_string($body[$champ]) || trim($body[$champ]) === '') {
                throw new HttpBadRequestException($rq, "Le champ $champ est obligatoire.");
            }
        }

        $dto = new CreateRdvDTO(
            trim($body['praticien_id']),
            trim($body['patient_id']),
            trim($body['date_heure']),
            (int) ($body['duree'] ?? 30),
            isset($body['motif']) && is_string($body['motif']) ? $body['motif'] : null,
        );

        try {
            $rendezVousDTO = $this->serviceRendezVous->creer($dto);
        } catch (ValidationException $e) {
            throw new HttpBadRequestException($rq, implode(' ', $e->getErrors()));
        }
        return $this->json($rs, $rendezVousDTO->toArray(), 201);
    }
}
