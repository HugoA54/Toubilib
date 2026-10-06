<?php
declare(strict_types=1);

use toubilib\application\ports\api\ServiceRendezVousInterface;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;

// actions de l'application Slim 
return [

    AnnulerRendezVousAction::class => \DI\create(AnnulerRendezVousAction::class)
        ->constructor(\DI\get(ServiceRendezVousInterface::class)),

];
