<?php

use toubilib\domain\entities\Praticien;
use toubilib\domain\exceptions\RendezVousException;

function makePraticien(): Praticien {
    return new Praticien(
        'a0000000-0000-4000-8000-000000000001', 'Dr.', 'Martin', 'Paul', '1 rue des Lilas', 'Nancy',
        'paul.martin@example.com', '0600000000', new DateTime('08:00'), new DateTime('18:00'), 1,
        'c0000000-0000-4000-8000-000000000001',
        ['Consultation' => 30, 'Vaccination' => 20]
    );
}

it("devrait accepter un motif proposé par le praticien", function() {
    expect(makePraticien()->verifierMotif('Consultation'))->toBeTrue();
});

it("devrait refuser un motif non proposé par le praticien", function() {
    expect(makePraticien()->verifierMotif('Bilan cardiaque'))->toBeFalse();
});

it("devrait calculer la durée du rendez-vous en fonction du motif", function() {
    expect(makePraticien()->calculerDuree('Vaccination'))->toBe(20);
});

it("devrait refuser de calculer la durée pour un motif non proposé", function() {
    expect(fn () => makePraticien()->calculerDuree('Bilan cardiaque'))
        ->toThrow(RendezVousException::class, "Le motif Bilan cardiaque n'est pas proposé par le praticien.");
});

it("devrait accepter une date un jour ouvrable dans la plage horaire", function() {
    expect(makePraticien()->verifierDateHeure(new DateTime('2030-01-14 10:00')))->toBeTrue();
});

it("devrait accepter une date à l'heure de début de la plage horaire", function() {
    expect(makePraticien()->verifierDateHeure(new DateTime('2030-01-18 08:00')))->toBeTrue();
});

it("devrait refuser une date un samedi", function() {
    expect(makePraticien()->verifierDateHeure(new DateTime('2030-01-19 10:00')))->toBeFalse();
});

it("devrait refuser une date un dimanche", function() {
    expect(makePraticien()->verifierDateHeure(new DateTime('2030-01-20 10:00')))->toBeFalse();
});

it("devrait refuser une heure avant le début de la plage horaire", function() {
    expect(makePraticien()->verifierDateHeure(new DateTime('2030-01-14 07:59')))->toBeFalse();
});

it("devrait refuser une heure à la fin de la plage horaire", function() {
    expect(makePraticien()->verifierDateHeure(new DateTime('2030-01-14 18:00')))->toBeFalse();
});
