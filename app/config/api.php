<?php
declare(strict_types=1);

use OpenApi\Attributes\Get;
use toubilib\adapters\controllers\actions\AfficherPraticiensAction;
use toubilib\application\ports\api\ServiceRendezVousInterface;
use toubilib\application\ports\api\ServicePraticienInterface;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;
use toubilib\adapters\controllers\actions\GetDetailsRendezVousAction;
use toubilib\adapters\controllers\actions\CreerRendezVousAction;
use toubilib\adapters\controllers\actions\GetDetailsPraticiensAction;

// actions de l'application Slim 
return [

    AnnulerRendezVousAction::class => \DI\create(AnnulerRendezVousAction::class)
        ->constructor(\DI\get(ServiceRendezVousInterface::class)),

    GetDetailsRendezVousAction::class => \DI\create(GetDetailsRendezVousAction::class)
        ->constructor(\DI\get(ServiceRendezVousInterface::class)),
    
    CreerRendezVousAction::class => \DI\create(CreerRendezVousAction::class)
        ->constructor(\DI\get(ServiceRendezVousInterface::class)),

    AfficherPraticiensAction::class => \DI\create(AfficherPraticiensAction::class)
        ->constructor(\DI\get(ServicePraticienInterface::class)),

    GetDetailsPraticiensAction::class => \DI\create(GetDetailsPraticiensAction::class)
        ->constructor(\DI\get(ServicePraticienInterface::class)),



];
