<?php
use Slim\Factory\AppFactory;
use Psr\Container\ContainerInterface;

use toubilib\adapters\config\ContainerConfig;
use toubilib\application\usecases\ServiceRendezVous;

$container = ContainerConfig::build();
$app = AppFactory::createFromContainer($container);


$app->post('/rendezvous/{id}/annuler', [ServiceRendezVous::class, 'annuler'])->setName('annuler_rendezvous');


?>