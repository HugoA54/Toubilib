<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use toubilib\application\ports\api\ServicePraticienInterface;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AfficherPraticiensAction extends AbstractAction
{
    private ServicePraticienInterface $servicePraticien;

    public function __construct(ServicePraticienInterface $servicePraticien)
    {
        $this->servicePraticien = $servicePraticien;
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface
    {
        $praticiens = $this->servicePraticien->getAllPraticiens();

        $data = [];
        foreach ($praticiens as $praticien) {
            $item = $praticien;

            $data[] = [ 
                'id' => $item['id'],
                'nom' => $item['nom'],
                'prenom' => $item['prenom'],
                '_links' => [
                    'self' => ['href' => '/praticiens/' . $item['id']],
                ],
            ];
        }

        $body = json_encode(
            $data,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        $rs->getBody()->write($body);

        return $rs
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
}