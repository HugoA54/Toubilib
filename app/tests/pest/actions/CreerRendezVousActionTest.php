<?php

use Slim\Exception\HttpBadRequestException;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use tests\pest\fakes\InMemoryPatientRepository;
use tests\pest\fakes\InMemoryPraticienRepository;
use tests\pest\fakes\InMemoryRendezVousRepository;
use toubilib\adapters\controllers\actions\CreerRendezVousAction;
use toubilib\application\usecases\ServiceRendezVous;
use toubilib\application\validators\RendezVousValidator;
use toubilib\domain\entities\Patient;
use toubilib\domain\entities\Praticien;

// ── Setup ─

const ACTION_PRATICIEN_UUID = 'a0000000-0000-4000-8000-000000000001';
const ACTION_PATIENT_UUID = 'b0000000-0000-4000-8000-000000000001';
const ACTION_INCONNU_UUID = 'f0000000-0000-4000-8000-000000000999';

function makeCreerAction(): CreerRendezVousAction {
    $praticiens = new InMemoryPraticienRepository();
    $praticiens->save(new Praticien(
        ACTION_PRATICIEN_UUID, 'Dr.', 'Martin', 'Paul', '1 rue des Lilas', 'Nancy',
        'paul.martin@example.com', '0600000000', new DateTime('08:00'), new DateTime('18:00'), '1',
        'c0000000-0000-4000-8000-000000000001'
    ));
    $patients = new InMemoryPatientRepository();
    $patients->save(new Patient(ACTION_PATIENT_UUID, 'Durand', 'Alice', null, '0611111111'));

    return new CreerRendezVousAction(
        new ServiceRendezVous(new InMemoryRendezVousRepository(), new RendezVousValidator($praticiens, $patients))
    );
}

function appelerCreer(mixed $body): \Psr\Http\Message\ResponseInterface {
    $rq = (new RequestFactory())->createRequest('POST', '/rendezvous')->withParsedBody($body);
    return makeCreerAction()($rq, (new ResponseFactory())->createResponse(), []);
}

// ── Tests ─

it("devrait répondre 201 avec les données reçues pour une création valide", function() {
    $rs = appelerCreer([
        'praticien_id' => ACTION_PRATICIEN_UUID,
        'patient_id' => ACTION_PATIENT_UUID,
        'date_heure' => '2030-01-15 10:00',
        'duree' => 45,
        'motif' => 'CI',
    ]);

    expect($rs->getStatusCode())->toBe(201);
    expect(json_decode((string) $rs->getBody(), true))->toBe([
        'praticien_id' => ACTION_PRATICIEN_UUID,
        'patient_id' => ACTION_PATIENT_UUID,
        'date_heure' => '2030-01-15 10:00',
        'duree' => 45,
        'motif' => 'CI',
    ]);
});

it("devrait lever HttpBadRequestException pour un praticien inexistant", function() {
    appelerCreer(['praticien_id' => ACTION_INCONNU_UUID, 'patient_id' => ACTION_PATIENT_UUID, 'date_heure' => '2030-01-15 10:00']);
})->throws(HttpBadRequestException::class);

it("devrait lever HttpBadRequestException pour un patient inexistant", function() {
    appelerCreer(['praticien_id' => ACTION_PRATICIEN_UUID, 'patient_id' => ACTION_INCONNU_UUID, 'date_heure' => '2030-01-15 10:00']);
})->throws(HttpBadRequestException::class);

it("devrait lever HttpBadRequestException si un champ obligatoire manque", function() {
    appelerCreer(['praticien_id' => ACTION_PRATICIEN_UUID, 'date_heure' => '2030-01-15 10:00']);
})->throws(HttpBadRequestException::class, 'Le champ patient_id est obligatoire.');

it("devrait lever HttpBadRequestException si le corps est absent", function() {
    appelerCreer(null);
})->throws(HttpBadRequestException::class);
