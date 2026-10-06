<?php
declare(strict_types=1);

namespace toubilib\adapters\config;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

use toubilib\application\usecases\ServiceRendezVous;
use toubilib\application\ports\api\ServiceRendezVousInterface;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\adapters\persistance\RendezVousRepository;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;

final class ContainerConfig {

    public static function build(): \DI\Container {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(false);
        $builder->addDefinitions(__DIR__ . '/../../../config/settings.php');
        $builder->addDefinitions([

            // ── domain service ─
            ServiceRendezVousInterface::class => \DI\create(ServiceRendezVous::class)
                ->constructor(\DI\get(RendezVousRepositoryInterface::class)),

            // ── adapters ─
            'toubilib.pdo' => function (ContainerInterface $c) {
                $dsn = "{$c->get('toubilib.db.driver')}:host={$c->get('toubilib.db.host')};dbname={$c->get('toubilib.db.database')}";
                $user = $c->get('toubilib.db.username');
                $password = $c->get('toubilib.db.password');
                return new \PDO($dsn, $user, $password, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
            },

            RendezVousRepositoryInterface::class => \DI\create(RendezVousRepository::class)
                ->constructor(\DI\get('toubilib.pdo')),

            // ── actions ─
            AnnulerRendezVousAction::class => \DI\create(AnnulerRendezVousAction::class)
                ->constructor(\DI\get(ServiceRendezVousInterface::class)),
        ]);
        return $builder->build();
    }
}
