<?php
declare(strict_types=1);

namespace toubilib\adapters\config;

use DI\ContainerBuilder;

use toubilib\application\ports\api\ServiceRendezVousInterface;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;

final class ContainerConfig {

    public static function build(): \DI\Container {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(false);
        $builder->addDefinitions(__DIR__ . '/../../../config/settings.php');
        $builder->addDefinitions(__DIR__ . '/../../../config/services.php');
        $builder->addDefinitions([

            // ── actions ─
            AnnulerRendezVousAction::class => \DI\create(AnnulerRendezVousAction::class)
                ->constructor(\DI\get(ServiceRendezVousInterface::class)),
        ]);
        return $builder->build();
    }
}
