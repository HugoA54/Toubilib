<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseFactoryInterface;
use toubilib\adapters\controllers\actions\AfficherPraticiensAction;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;
use toubilib\adapters\controllers\actions\CreerRendezVousAction;
use toubilib\adapters\controllers\actions\GetAgendaPraticienAction;
use toubilib\adapters\controllers\actions\GetCreneauxPraticienAction;
use toubilib\adapters\controllers\actions\GetDetailsPraticiensAction;
use toubilib\adapters\controllers\actions\GetDetailsRendezVousAction;
use toubilib\adapters\controllers\actions\HonorerRendezVousAction;
use toubilib\adapters\controllers\actions\IgnorerRendezVousAction;
use toubilib\adapters\controllers\middlewares\CorsMiddleware;
use toubilib\application\ports\api\ServicePraticienInterface;
use toubilib\application\ports\api\ServiceRendezVousInterface;

// actions et middlewares de l'application Slim
return [

    CorsMiddleware::class => \DI\create(CorsMiddleware::class)
        ->constructor(\DI\get(ResponseFactoryInterface::class), \DI\get('cors.origine'), \DI\get('cors.methodes'), \DI\get('cors.headers')),

    AnnulerRendezVousAction::class => \DI\create(AnnulerRendezVousAction::class)
        ->constructor(\DI\get(ServiceRendezVousInterface::class)),

    HonorerRendezVousAction::class => \DI\create(HonorerRendezVousAction::class)
        ->constructor(\DI\get(ServiceRendezVousInterface::class)),

    IgnorerRendezVousAction::class => \DI\create(IgnorerRendezVousAction::class)
        ->constructor(\DI\get(ServiceRendezVousInterface::class)),

    GetDetailsRendezVousAction::class => \DI\create(GetDetailsRendezVousAction::class)
        ->constructor(\DI\get(ServiceRendezVousInterface::class)),

    CreerRendezVousAction::class => \DI\create(CreerRendezVousAction::class)
        ->constructor(\DI\get(ServiceRendezVousInterface::class)),

    GetCreneauxPraticienAction::class => \DI\create(GetCreneauxPraticienAction::class)
        ->constructor(\DI\get(ServiceRendezVousInterface::class)),

    GetAgendaPraticienAction::class => \DI\create(GetAgendaPraticienAction::class)
        ->constructor(\DI\get(ServiceRendezVousInterface::class)),

    AfficherPraticiensAction::class => \DI\create(AfficherPraticiensAction::class)
        ->constructor(\DI\get(ServicePraticienInterface::class)),

    GetDetailsPraticiensAction::class => \DI\create(GetDetailsPraticiensAction::class)
        ->constructor(\DI\get(ServicePraticienInterface::class)),

];
