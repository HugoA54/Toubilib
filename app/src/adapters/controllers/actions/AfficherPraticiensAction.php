<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use toubilib\application\dto\PraticienDTO;
use toubilib\application\ports\api\ServicePraticienInterface;

class AfficherPraticiensAction extends AbstractAction
{
    private ServicePraticienInterface $servicePraticien;

    public function __construct(ServicePraticienInterface $servicePraticien)
    {
        $this->servicePraticien = $servicePraticien;
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface
    {
        $praticiens = array_map(
            fn (PraticienDTO $praticien) => $praticien->toResumeArray(),
            $this->servicePraticien->getAllPraticiens()
        );

        return $this->json($rs, [
            'type' => 'collection',
            'count' => count($praticiens),
            'praticiens' => $praticiens,
            'links' => [
                'self' => ['href' => '/praticiens'],
            ],
        ]);
    }
}
