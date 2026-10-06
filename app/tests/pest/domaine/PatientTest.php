<?php

use toubilib\domain\entities\Patient;

// ── Setup ─

function makePatient(?string $email = 'alice@example.com'): Patient {
    return new Patient('b0000000-0000-4000-8000-000000000001', 'Durand', 'Alice', $email, '0611111111');
}

// ── Tests ─

it("devrait exposer l'identifiant du patient", function() {
    expect(makePatient()->getId())->toBe('b0000000-0000-4000-8000-000000000001');
});

it("devrait exposer le nom du patient", function() {
    expect(makePatient()->getNom())->toBe('Durand');
});

it("devrait exposer le prénom du patient", function() {
    expect(makePatient()->getPrenom())->toBe('Alice');
});

it("devrait exposer l'email du patient", function() {
    expect(makePatient()->getEmail())->toBe('alice@example.com');
});

it("devrait exposer le téléphone du patient", function() {
    expect(makePatient()->getTelephone())->toBe('0611111111');
});

it("devrait accepter un patient sans email", function() {
    expect(makePatient(null)->getEmail())->toBeNull();
});
