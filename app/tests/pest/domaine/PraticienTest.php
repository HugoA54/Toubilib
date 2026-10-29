<?php

use toubilib\domain\entities\Praticien;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\StatutRendezVous;
use toubilib\domain\exceptions\RendezVousException;

function makePraticien(array $rendezVous = []): Praticien {
    return new Praticien(
        'a0000000-0000-4000-8000-000000000001', 'Dr.', 'Martin', 'Paul', '1 rue des Lilas', 'Nancy',
        'paul.martin@example.com', '0600000000', new DateTime('08:00'), new DateTime('18:00'), 1,
        'c0000000-0000-4000-8000-000000000001',
        $rendezVous
    );
}

function makeRendezVousPourPraticien(string $dateHeure, int $duree = 30, StatutRendezVous $statut = StatutRendezVous::PREVU): RendezVous {
    return new RendezVous('id', 'patient', 'a0000000-0000-4000-8000-000000000001', 'structure', new DateTime($dateHeure), $duree, 'CI', $statut);
}

it("devrait accepter un motif proposé par le praticien", function() {
    expect(makePraticien()->verifierMotif('CI'))->toBeTrue();
});

it("devrait refuser un motif non proposé par le praticien", function() {
    expect(makePraticien()->verifierMotif('Bilan cardiaque'))->toBeFalse();
});

it("devrait calculer la durée du rendez-vous en fonction du motif", function() {
    expect(makePraticien()->calculerDuree('C0'))->toBe(20);
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

it("devrait accepter une demande qui ne chevauche aucun rendez-vous du praticien", function() {
    $praticien = makePraticien([makeRendezVousPourPraticien('2030-01-14 10:00')]);
    $praticien->verifierDisponibilite(makeRendezVousPourPraticien('2030-01-14 10:30'));
    expect(true)->toBeTrue();
});

it("devrait refuser une demande qui chevauche un rendez-vous du praticien", function() {
    $praticien = makePraticien([makeRendezVousPourPraticien('2030-01-14 10:00')]);
    expect(fn () => $praticien->verifierDisponibilite(makeRendezVousPourPraticien('2030-01-14 10:15')))
        ->toThrow(RendezVousException::class, "Le praticien n'est pas disponible à la date et l'heure demandées.");
});

it("devrait ignorer les rendez-vous annulés du praticien", function() {
    $praticien = makePraticien([makeRendezVousPourPraticien('2030-01-14 10:00', 30, StatutRendezVous::ANNULE)]);
    $praticien->verifierDisponibilite(makeRendezVousPourPraticien('2030-01-14 10:00'));
    expect(true)->toBeTrue();
});
