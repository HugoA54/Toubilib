<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

use toubilib\application\dto\AgendaEntreeDTO;
use toubilib\application\dto\PeriodeDTO;
use toubilib\application\exceptions\PraticienIntrouvableException;
use toubilib\application\exceptions\ValidationException;
use toubilib\application\ports\api\ServiceRendezVousInterface;

class GetAgendaPraticienAction extends AbstractAction {

    public function __construct(private ServiceRendezVousInterface $serviceRendezVous) {
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface {
        $id = $args['id'];
        try {
            $periode = PeriodeDTO::fromArray($rq->getQueryParams());
            $agenda = $this->serviceRendezVous->agenda($id, $periode);
        } catch (ValidationException $e) {
            return $this->json($rs, ['errors' => $e->getErrors()], 422);
        } catch (PraticienIntrouvableException $e) {
            throw new HttpNotFoundException($rq, "Aucun praticien avec l'id $id");
        }

        return $this->json($rs, [
            'type' => 'collection',
            'count' => count($agenda),
            'debut' => $periode->debut,
            'fin' => $periode->fin,
            'rendez_vous' => array_map(fn (AgendaEntreeDTO $entree) => $entree->toArray(), $agenda),
            'links' => [
                'self' => ['href' => "/praticiens/$id/agenda?debut={$periode->debut}&fin={$periode->fin}"],
                'praticien' => ['href' => "/praticiens/$id"],
            ],
        ]);
    }
}
