<?php
declare(strict_types=1);

use Slim\Factory\AppFactory;

use toubilib\adapters\config\ContainerConfig;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__, '.env');
$dotenv->load();
// ── conteneur d'injection de dépendances ─
$container = ContainerConfig::build();

$app = AppFactory::createFromContainer($container);

// ── ajout middlewares ─
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, false, false);
$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->forceContentType('application/json');
$errorHandler->registerErrorRenderer('application/json', function ($exception, $displayErrorDetails) {
    return json_encode(['message' => $exception->getMessage()], JSON_UNESCAPED_UNICODE);
});

// ── ajout des routes ─
$app->group('/rendezvous', function (\Slim\Routing\RouteCollectorProxy $group) {
    $group->post('/{id}/annuler', AnnulerRendezVousAction::class)
        ->setName('rendezvous-annuler');
});

return $app;
