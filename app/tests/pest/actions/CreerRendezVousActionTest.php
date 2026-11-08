<?php

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
        new ServiceRendezVous(new InMemoryRendezVousRepository(), new RendezVousValidator($praticiens, $patients), $praticiens, $patients)
    );
}

function appelerCreer(mixed $body): \Psr\Http\Message\ResponseInterface {
    $rq = (new RequestFactory())->createRequest('POST', '/rdvs')->withParsedBody($body);
    return makeCreerAction()($rq, (new ResponseFactory())->createResponse(), []);
}

// ── Tests ─

it("devrait répondre 201 avec le rendez-vous créé pour une création valide", function() {
    $rs = appelerCreer([
        'praticien_id' => ACTION_PRATICIEN_UUID,
        'patient_id' => ACTION_PATIENT_UUID,
        'date_heure' => '2030-01-15 10:00',
        'motif' => 'CI',
    ]);

    $body = json_decode((string) $rs->getBody(), true);

    expect($rs->getStatusCode())->toBe(201);
    $rendezVous = $body['rendez_vous'];

    expect($rendezVous['praticien_id'])->toBe(ACTION_PRATICIEN_UUID)
        ->and($rendezVous['patient_id'])->toBe(ACTION_PATIENT_UUID)
        ->and($rendezVous['date_heure'])->toBe('2030-01-15 10:00')
        ->and($rendezVous['duree'])->toBe(30)
        ->and($rendezVous['motif'])->toBe('CI')
        ->and($rs->getHeaderLine('Location'))->toBe('/rdvs/' . $rendezVous['id']);
});

it("devrait répondre 422 pour un praticien inexistant", function() {
    $rs = appelerCreer(['praticien_id' => ACTION_INCONNU_UUID, 'patient_id' => ACTION_PATIENT_UUID, 'date_heure' => '2030-01-15 10:00', 'motif' => 'CI']);

    expect($rs->getStatusCode())->toBe(422);
    expect(array_keys(json_decode((string) $rs->getBody(), true)['errors']))->toBe(['praticienId']);
});

it("devrait répondre 422 pour un patient inexistant", function() {
    $rs = appelerCreer(['praticien_id' => ACTION_PRATICIEN_UUID, 'patient_id' => ACTION_INCONNU_UUID, 'date_heure' => '2030-01-15 10:00', 'motif' => 'CI']);

    expect($rs->getStatusCode())->toBe(422);
    expect(array_keys(json_decode((string) $rs->getBody(), true)['errors']))->toBe(['patientId']);
});

it("devrait répondre 422 pour un motif non proposé par le praticien", function() {
    $rs = appelerCreer(['praticien_id' => ACTION_PRATICIEN_UUID, 'patient_id' => ACTION_PATIENT_UUID, 'date_heure' => '2030-01-15 10:00', 'motif' => 'XX']);

    expect($rs->getStatusCode())->toBe(422);
    expect(json_decode((string) $rs->getBody(), true))->toBe(['errors' => ['rendezVous' => "Le motif XX n'est pas proposé par le praticien."]]);
});

it("devrait répondre 422 si un champ obligatoire manque", function() {
    $rs = appelerCreer(['praticien_id' => ACTION_PRATICIEN_UUID, 'date_heure' => '2030-01-15 10:00', 'motif' => 'CI']);

    expect($rs->getStatusCode())->toBe(422);
    expect(json_decode((string) $rs->getBody(), true))->toBe(['errors' => ['patient_id' => "Le champ patient_id est obligatoire et doit être une chaîne de caractères."]]);
});

it("devrait répondre 422 si la date n'est pas au bon format", function() {
    $rs = appelerCreer(['praticien_id' => ACTION_PRATICIEN_UUID, 'patient_id' => ACTION_PATIENT_UUID, 'date_heure' => '15/01/2030 10:00', 'motif' => 'CI']);

    expect($rs->getStatusCode())->toBe(422);
    expect(array_keys(json_decode((string) $rs->getBody(), true)['errors']))->toBe(['date_heure']);
});

it("devrait répondre 422 si le corps est absent", function() {
    $rs = appelerCreer(null);

    expect($rs->getStatusCode())->toBe(422);
    expect(array_keys(json_decode((string) $rs->getBody(), true)['errors']))->toBe(['praticien_id', 'patient_id', 'date_heure', 'motif']);
});
