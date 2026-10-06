<?php
declare(strict_types=1);

use Slim\Factory\AppFactory;

use toubilib\adapters\config\ContainerConfig;
use toubilib\adapters\controllers\actions\AfficherPraticiensAction;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;
use toubilib\adapters\controllers\actions\GetDetailsRendezVousAction;

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__, '.env');
$dotenv->load();
$container = ContainerConfig::build();

$app = AppFactory::createFromContainer($container);

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, false, false);
$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->forceContentType('application/json');
$errorHandler->registerErrorRenderer('application/json', function ($exception, $displayErrorDetails) {
    return json_encode(['message' => $exception->getMessage()], JSON_UNESCAPED_UNICODE);
});

$app->group('/rendezvous', function (\Slim\Routing\RouteCollectorProxy $group) {
    $group->post('/{id}/annuler', AnnulerRendezVousAction::class)
        ->setName('rendezvous-annuler');
    $group->get('/{id}', GetDetailsRendezVousAction::class)
        ->setName('rendezvous-details');
    
});

$app->group('/praticiens', function (\Slim\Routing\RouteCollectorProxy $group) {
    $group->get('', AfficherPraticiensAction::class)
        ->setName('tous-les-praticiens');
});


return $app;
