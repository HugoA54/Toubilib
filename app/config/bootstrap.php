<?php
declare(strict_types=1);

use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

use toubilib\adapters\config\ContainerConfig;
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

// Ajouté après le middleware d'erreur pour que les réponses d'erreur portent aussi les headers CORS.
$app->add(CorsMiddleware::class);

// Un identifiant qui n'est pas un UUID ne correspond à aucune ressource : 404.
const UUID = '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}';

$app->group('/rdvs', function (RouteCollectorProxy $group) {
    $group->post('', CreerRendezVousAction::class)
        ->setName('rdvs-creer');
    $group->get('/{id:' . UUID . '}', GetDetailsRendezVousAction::class)
        ->setName('rdvs-details');
    $group->post('/{id:' . UUID . '}/annuler', AnnulerRendezVousAction::class)
        ->setName('rdvs-annuler');
    $group->post('/{id:' . UUID . '}/honorer', HonorerRendezVousAction::class)
        ->setName('rdvs-honorer');
    $group->post('/{id:' . UUID . '}/ignorer', IgnorerRendezVousAction::class)
        ->setName('rdvs-ignorer');
});

$app->group('/praticiens', function (RouteCollectorProxy $group) {
    $group->get('', AfficherPraticiensAction::class)
        ->setName('praticiens-liste');
    $group->get('/{id:' . UUID . '}', GetDetailsPraticiensAction::class)
        ->setName('praticiens-details');
    $group->get('/{id:' . UUID . '}/creneaux', GetCreneauxPraticienAction::class)
        ->setName('praticiens-creneaux');
    $group->get('/{id:' . UUID . '}/agenda', GetAgendaPraticienAction::class)
        ->setName('praticiens-agenda');
});


return $app;
