<?php

use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\exceptions\ValidationException;

const DTO_PRATICIEN_UUID = 'a0000000-0000-4000-8000-000000000001';
const DTO_PATIENT_UUID = 'b0000000-0000-4000-8000-000000000001';

function donneesRdv(array $modifications = []): array {
    return array_merge([
        'praticien_id' => DTO_PRATICIEN_UUID,
        'patient_id' => DTO_PATIENT_UUID,
        'date_heure' => '2030-01-15 10:00',
        'motif' => 'CI',
    ], $modifications);
}

function erreursDTO(array $data): array {
    try {
        CreateRdvDTO::fromArray($data);
    } catch (ValidationException $e) {
        return $e->getErrors();
    }
    return [];
}

it("devrait créer le DTO à partir de données valides", function() {
    $dto = CreateRdvDTO::fromArray(donneesRdv());

    expect($dto->praticienId)->toBe(DTO_PRATICIEN_UUID)
        ->and($dto->patientId)->toBe(DTO_PATIENT_UUID)
        ->and($dto->dateHeure)->toBe('2030-01-15 10:00')
        ->and($dto->motif)->toBe('CI');
});

it("devrait supprimer les espaces autour des données", function() {
    $dto = CreateRdvDTO::fromArray(donneesRdv(['praticien_id' => ' ' . DTO_PRATICIEN_UUID . ' ', 'motif' => ' CI ']));

    expect($dto->praticienId)->toBe(DTO_PRATICIEN_UUID)
        ->and($dto->motif)->toBe('CI');
});

it("devrait échapper les caractères spéciaux du motif", function() {
    $dto = CreateRdvDTO::fromArray(donneesRdv(['motif' => '<script>']));

    expect($dto->motif)->toBe('&lt;script&gt;');
});

it("devrait refuser des données absentes", function() {
    expect(array_keys(erreursDTO([])))->toBe(['praticien_id', 'patient_id', 'date_heure', 'motif']);
});

it("devrait refuser une donnée vide", function() {
    expect(array_keys(erreursDTO(donneesRdv(['motif' => '  ']))))->toBe(['motif']);
});

it("devrait refuser une donnée qui n'est pas une chaîne de caractères", function() {
    expect(array_keys(erreursDTO(donneesRdv(['patient_id' => 12]))))->toBe(['patient_id']);
});

it("devrait refuser un identifiant qui n'est pas un UUID", function() {
    expect(array_keys(erreursDTO(donneesRdv(['praticien_id' => 'abc', 'patient_id' => 'def']))))->toBe(['praticien_id', 'patient_id']);
});

it("devrait refuser une date au mauvais format", function() {
    expect(array_keys(erreursDTO(donneesRdv(['date_heure' => '15/01/2030 10:00']))))->toBe(['date_heure']);
});

it("devrait refuser une date inexistante", function() {
    expect(array_keys(erreursDTO(donneesRdv(['date_heure' => '2030-02-30 10:00']))))->toBe(['date_heure']);
});
