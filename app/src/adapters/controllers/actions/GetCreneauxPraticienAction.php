<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

use toubilib\application\dto\CreneauDTO;
use toubilib\application\dto\PeriodeDTO;
use toubilib\application\exceptions\PraticienIntrouvableException;
use toubilib\application\exceptions\ValidationException;
use toubilib\application\ports\api\ServiceRendezVousInterface;

class GetCreneauxPraticienAction extends AbstractAction {

    public function __construct(private ServiceRendezVousInterface $serviceRendezVous) {
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface {
        $id = $args['id'];
        try {
            $periode = PeriodeDTO::fromArray($rq->getQueryParams());
            $creneaux = $this->serviceRendezVous->creneaux($id, $periode);
        } catch (ValidationException $e) {
            return $this->json($rs, ['errors' => $e->getErrors()], 422);
        } catch (PraticienIntrouvableException $e) {
            throw new HttpNotFoundException($rq, "Aucun praticien avec l'id $id");
        }

        return $this->json($rs, [
            'type' => 'collection',
            'count' => count($creneaux),
            'debut' => $periode->debut,
            'fin' => $periode->fin,
            'creneaux' => array_map(fn (CreneauDTO $creneau) => $creneau->toArray(), $creneaux),
            'links' => [
                'self' => ['href' => "/praticiens/$id/creneaux?debut={$periode->debut}&fin={$periode->fin}"],
                'praticien' => ['href' => "/praticiens/$id"],
            ],
        ]);
    }
}
