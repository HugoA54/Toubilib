<?php
declare(strict_types=1);

use OpenApi\Attributes\Get;
use toubilib\application\ports\api\ServiceRendezVousInterface;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;
use toubilib\adapters\controllers\actions\GetDetailsRendezVousAction;

// actions de l'application Slim 
return [

    AnnulerRendezVousAction::class => \DI\create(AnnulerRendezVousAction::class)
        ->constructor(\DI\get(ServiceRendezVousInterface::class)),

    GetDetailsRendezVousAction::class => \DI\create(GetDetailsRendezVousAction::class)
        ->constructor(\DI\get(ServiceRendezVousInterface::class))

];
