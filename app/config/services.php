<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;

use toubilib\application\usecases\ServiceRendezVous;
use toubilib\application\ports\api\ServiceRendezVousInterface;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\application\ports\api\ServicePraticienInterface;
use toubilib\application\usecases\ServicePraticien;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\application\ports\spi\PatientRepositoryInterface;
use toubilib\application\validators\RendezVousValidator;
use toubilib\adapters\persistance\PatientRepository;
use toubilib\adapters\persistance\PraticienRepository;
use toubilib\adapters\persistance\RendezVousRepository;

// ── services métier et repositories ─
return [

    ServiceRendezVousInterface::class => \DI\create(ServiceRendezVous::class)
        ->constructor(\DI\get(RendezVousRepositoryInterface::class), \DI\get(RendezVousValidator::class)),

    'toubilib.pdo' => function (ContainerInterface $c) {
        $dsn = "{$c->get('toubilib.db.driver')}:host={$c->get('toubilib.db.host')};dbname={$c->get('toubilib.db.database')}";
        $user = $c->get('toubilib.db.username');
        $password = $c->get('toubilib.db.password');
        return new \PDO($dsn, $user, $password, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
    },

    RendezVousRepositoryInterface::class => \DI\create(RendezVousRepository::class)
        ->constructor(\DI\get('toubilib.pdo')),

    ServicePraticienInterface::class => \DI\create(ServicePraticien::class)
        ->constructor(\DI\get(PraticienRepositoryInterface::class)),
    
    PraticienRepositoryInterface::class => \DI\create(PraticienRepository::class)
        ->constructor(\DI\get('toubilib.pdo')),

    PatientRepositoryInterface::class => \DI\create(PatientRepository::class)
        ->constructor(\DI\get('toubilib.pdo')),

    RendezVousValidator::class => \DI\create(RendezVousValidator::class)
        ->constructor(\DI\get(PraticienRepositoryInterface::class), \DI\get(PatientRepositoryInterface::class)),

];
