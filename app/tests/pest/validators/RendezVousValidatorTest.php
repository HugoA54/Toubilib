<?php

use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\validators\RendezVousValidator;
use toubilib\application\exceptions\ValidationException;
use toubilib\domain\entities\Patient;
use toubilib\domain\entities\Praticien;
use toubilib\domain\entities\RendezVous;
use tests\pest\fakes\InMemoryPatientRepository;
use tests\pest\fakes\InMemoryPraticienRepository;


const VALIDATOR_PRATICIEN_UUID = 'a0000000-0000-4000-8000-000000000001';
const VALIDATOR_PATIENT_UUID = 'b0000000-0000-4000-8000-000000000001';
const VALIDATOR_INCONNU_UUID = 'f0000000-0000-4000-8000-000000000999';

function makePraticienPourValidator(): Praticien {
    return new Praticien(
        VALIDATOR_PRATICIEN_UUID, 'Dr.', 'Martin', 'Paul', '1 rue des Lilas',
        'Nancy', 'paul.martin@example.com', '0600000000',
        new DateTime('08:00'), new DateTime('18:00'), '1',
        'c0000000-0000-4000-8000-000000000001',
        [new RendezVous('id', 'patient', VALIDATOR_PRATICIEN_UUID, 'structure', new DateTime('2030-01-14 10:00'), 30)]
    );
}

function makePatientPourValidator(): Patient {
    return new Patient(VALIDATOR_PATIENT_UUID, 'Durand', 'Alice', 'alice@example.com', '0611111111');
}

function makeValidator(): RendezVousValidator {
    $praticiens = new InMemoryPraticienRepository();
    $praticiens->save(makePraticienPourValidator());
    $patients = new InMemoryPatientRepository();
    $patients->save(makePatientPourValidator());
    return new RendezVousValidator($praticiens, $patients);
}

function erreursValidation(string $praticienId, string $patientId, string $dateHeure = '2030-01-14 14:00', string $motif = 'CI'): array {
    try {
        makeValidator()->validate(new CreateRdvDTO($praticienId, $patientId, $dateHeure, $motif));
    } catch (ValidationException $e) {
        return $e->getErrors();
    }
    return [];
}


it("devrait accepter un praticien et un patient existants", function() {
    expect(erreursValidation(VALIDATOR_PRATICIEN_UUID, VALIDATOR_PATIENT_UUID))->toBe([]);
});

it("devrait refuser un praticien inexistant", function() {
    $errors = erreursValidation(VALIDATOR_INCONNU_UUID, VALIDATOR_PATIENT_UUID);

    expect(array_keys($errors))->toBe(['praticienId']);
});

it("devrait refuser un patient inexistant", function() {
    $errors = erreursValidation(VALIDATOR_PRATICIEN_UUID, VALIDATOR_INCONNU_UUID);

    expect(array_keys($errors))->toBe(['patientId']);
});

it("devrait signaler le praticien et le patient inexistants ensemble", function() {
    $errors = erreursValidation(VALIDATOR_INCONNU_UUID, VALIDATOR_INCONNU_UUID);

    expect(array_keys($errors))->toBe(['praticienId', 'patientId']);
});

it("devrait lancer une ValidationException avec le message Validation failed", function() {
    makeValidator()->validate(new CreateRdvDTO(VALIDATOR_INCONNU_UUID, VALIDATOR_PATIENT_UUID, '2030-01-14 14:00', 'CI'));
})->throws(ValidationException::class, 'Validation failed');

it("devrait refuser un motif non proposé par le praticien", function() {
    $errors = erreursValidation(VALIDATOR_PRATICIEN_UUID, VALIDATOR_PATIENT_UUID, '2030-01-14 14:00', 'Bilan cardiaque');

    expect($errors)->toBe(['rendezVous' => "Le motif Bilan cardiaque n'est pas proposé par le praticien."]);
});

it("devrait refuser une date hors des horaires du praticien", function() {
    $errors = erreursValidation(VALIDATOR_PRATICIEN_UUID, VALIDATOR_PATIENT_UUID, '2030-01-20 14:00');

    expect($errors)->toBe(['rendezVous' => "La date et l'heure demandées ne sont pas possibles pour le praticien."]);
});

it("devrait refuser un rendez-vous qui chevauche un rendez-vous du praticien", function() {
    $errors = erreursValidation(VALIDATOR_PRATICIEN_UUID, VALIDATOR_PATIENT_UUID, '2030-01-14 10:15');

    expect($errors)->toBe(['rendezVous' => "Le praticien n'est pas disponible à la date et l'heure demandées."]);
});
