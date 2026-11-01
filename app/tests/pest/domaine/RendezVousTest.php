<?php

use toubilib\domain\entities\Patient;
use toubilib\domain\entities\Praticien;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\StatutRendezVous;
use toubilib\domain\exceptions\RendezVousException;

function makeRendezVous(string $dateHeure): RendezVous {
    return new RendezVous(
        \Ramsey\Uuid\Uuid::uuid4()->toString(),
        \Ramsey\Uuid\Uuid::uuid4()->toString(),
        \Ramsey\Uuid\Uuid::uuid4()->toString(),
        \Ramsey\Uuid\Uuid::uuid4()->toString(),
        new DateTime($dateHeure)
    );
}

it("devrait créer un rendez-vous au statut PREVU", function() {
    $rdv = makeRendezVous('+1 day');
    expect($rdv->getStatut())->toBe(StatutRendezVous::PREVU);
});

it("devrait refuser un rendez-vous d'une durée de 0", function() {
    expect(fn () => new RendezVous('id', 'patient', 'praticien', 'structure', new DateTime('+1 day'), 0))
        ->toThrow(RendezVousException::class, "La durée d'un rendez-vous doit être strictement positive.");
});

it("devrait accepter le passage d'un rendez-vous futur de PREVU à ANNULE", function() {
    $rdv = makeRendezVous('+1 day');
    $rdv->annuler();
    expect($rdv->getStatut())->toBe(StatutRendezVous::ANNULE);
});

it("devrait accepter le passage d'un rendez-vous passé de PREVU à HONORE", function() {
    $rdv = makeRendezVous('-1 day');
    $rdv->marquerHonore();
    expect($rdv->getStatut())->toBe(StatutRendezVous::HONORE);
});

it("devrait accepter le passage d'un rendez-vous passé de PREVU à NON_HONORE", function() {
    $rdv = makeRendezVous('-1 day');
    $rdv->marquerIgnore();
    expect($rdv->getStatut())->toBe(StatutRendezVous::NON_HONORE);
});

it("devrait refuser le passage d'un rendez-vous passé de PREVU à ANNULE", function() {
    $rdv = makeRendezVous('-1 day');
    expect(fn () => $rdv->annuler())
        ->toThrow(RendezVousException::class, "Impossible d'annuler un rendez-vous déjà commencé.");
    expect($rdv->getStatut())->toBe(StatutRendezVous::PREVU);
});

it("devrait refuser le passage d'un rendez-vous de ANNULE à ANNULE", function() {
    $rdv = makeRendezVous('+1 day');
    $rdv->annuler();
    expect(fn () => $rdv->annuler())
        ->toThrow(RendezVousException::class, "Impossible d'annuler un rendez-vous annulé.");
});

it("devrait refuser le passage d'un rendez-vous de HONORE à ANNULE", function() {
    $rdv = makeRendezVous('-1 day');
    $rdv->marquerHonore();
    expect(fn () => $rdv->annuler())
        ->toThrow(RendezVousException::class, "Impossible d'annuler un rendez-vous honoré.");
});

it("devrait refuser le passage d'un rendez-vous de NON_HONORE à ANNULE", function() {
    $rdv = makeRendezVous('-1 day');
    $rdv->marquerIgnore();
    expect(fn () => $rdv->annuler())
        ->toThrow(RendezVousException::class, "Impossible d'annuler un rendez-vous non honoré.");
});

it("devrait refuser le passage d'un rendez-vous futur de PREVU à HONORE", function() {
    $rdv = makeRendezVous('+1 day');
    expect(fn () => $rdv->marquerHonore())
        ->toThrow(RendezVousException::class, "Impossible de marquer honoré un rendez-vous qui n'a pas encore commencé.");
});

it("devrait refuser le passage d'un rendez-vous futur de PREVU à NON_HONORE", function() {
    $rdv = makeRendezVous('+1 day');
    expect(fn () => $rdv->marquerIgnore())
        ->toThrow(RendezVousException::class, "Impossible de marquer non honoré un rendez-vous qui n'a pas encore commencé.");
});

it("devrait refuser le passage d'un rendez-vous de ANNULE à HONORE", function() {
    $rdv = makeRendezVous('+1 day');
    $rdv->annuler();
    expect(fn () => $rdv->marquerHonore())
        ->toThrow(RendezVousException::class, "Impossible de marquer honoré un rendez-vous annulé.");
});

it("devrait refuser le retour en arrière d'un rendez-vous de HONORE à NON_HONORE", function() {
    $rdv = makeRendezVous('-1 day');
    $rdv->marquerHonore();
    expect(fn () => $rdv->marquerIgnore())
        ->toThrow(RendezVousException::class, "Impossible de marquer non honoré un rendez-vous honoré.");
});

it("devrait calculer l'heure de fin à partir de la durée", function() {
    $rdv = new RendezVous('id', 'patient', 'praticien', 'structure', new DateTime('2030-01-15 10:00'), 45);
    expect($rdv->getDateTimeHeureFin()->format('Y-m-d H:i'))->toBe('2030-01-15 10:45');
});

function makePraticienPourRendezVous(): Praticien {
    return new Praticien(
        'a0000000-0000-4000-8000-000000000001', 'Dr.', 'Martin', 'Paul', '1 rue des Lilas', 'Nancy',
        'paul.martin@example.com', '0600000000', new DateTime('08:00'), new DateTime('18:00'), 1,
        'c0000000-0000-4000-8000-000000000001'
    );
}

function makePatientPourRendezVous(): Patient {
    return new Patient('b0000000-0000-4000-8000-000000000001', 'Durand', 'Alice', 'alice@example.com', '0611111111');
}

it("devrait créer un rendez-vous avec un motif et une date valides", function() {
    $rdv = RendezVous::creer(makePraticienPourRendezVous(), makePatientPourRendezVous(), new DateTime('2030-01-14 10:00'), 'C0');

    expect($rdv->getPraticienId())->toBe('a0000000-0000-4000-8000-000000000001')
        ->and($rdv->getPatientId())->toBe('b0000000-0000-4000-8000-000000000001')
        ->and($rdv->getStructureId())->toBe('c0000000-0000-4000-8000-000000000001')
        ->and($rdv->getDateTimeHeure()->format('Y-m-d H:i'))->toBe('2030-01-14 10:00')
        ->and($rdv->getDuree())->toBe(20)
        ->and($rdv->getMotif())->toBe('C0')
        ->and($rdv->getStatut())->toBe(StatutRendezVous::PREVU);
});

it("devrait refuser de créer un rendez-vous avec un motif non proposé par le praticien", function() {
    expect(fn () => RendezVous::creer(makePraticienPourRendezVous(), makePatientPourRendezVous(), new DateTime('2030-01-14 10:00'), 'Bilan cardiaque'))
        ->toThrow(RendezVousException::class, "Le motif Bilan cardiaque n'est pas proposé par le praticien.");
});

it("devrait refuser de créer un rendez-vous un jour non ouvrable", function() {
    expect(fn () => RendezVous::creer(makePraticienPourRendezVous(), makePatientPourRendezVous(), new DateTime('2030-01-20 10:00'), 'CI'))
        ->toThrow(RendezVousException::class, "La date et l'heure demandées ne sont pas possibles pour le praticien.");
});

it("devrait refuser de créer un rendez-vous hors de la plage horaire du praticien", function() {
    expect(fn () => RendezVous::creer(makePraticienPourRendezVous(), makePatientPourRendezVous(), new DateTime('2030-01-14 19:00'), 'CI'))
        ->toThrow(RendezVousException::class, "La date et l'heure demandées ne sont pas possibles pour le praticien.");
});

it("devrait détecter un chevauchement quand le rendez-vous commence pendant un autre", function() {
    $rdv = new RendezVous('id', 'patient', 'praticien', 'structure', new DateTime('2030-01-14 10:00'), 30);
    $autre = new RendezVous('id2', 'patient', 'praticien', 'structure', new DateTime('2030-01-14 10:15'), 30);
    expect($rdv->chevauche($autre))->toBeTrue()
        ->and($autre->chevauche($rdv))->toBeTrue();
});

it("devrait détecter un chevauchement quand le rendez-vous en contient un autre", function() {
    $rdv = new RendezVous('id', 'patient', 'praticien', 'structure', new DateTime('2030-01-14 10:00'), 60);
    $autre = new RendezVous('id2', 'patient', 'praticien', 'structure', new DateTime('2030-01-14 10:15'), 20);
    expect($rdv->chevauche($autre))->toBeTrue()
        ->and($autre->chevauche($rdv))->toBeTrue();
});

it("ne devrait pas détecter de chevauchement quand un rendez-vous commence à la fin d'un autre", function() {
    $rdv = new RendezVous('id', 'patient', 'praticien', 'structure', new DateTime('2030-01-14 10:00'), 30);
    $autre = new RendezVous('id2', 'patient', 'praticien', 'structure', new DateTime('2030-01-14 10:30'), 30);
    expect($rdv->chevauche($autre))->toBeFalse()
        ->and($autre->chevauche($rdv))->toBeFalse();
});

it("devrait refuser de créer un rendez-vous qui chevauche un rendez-vous du praticien", function() {
    $praticien = new Praticien(
        'a0000000-0000-4000-8000-000000000001', 'Dr.', 'Martin', 'Paul', '1 rue des Lilas', 'Nancy',
        'paul.martin@example.com', '0600000000', new DateTime('08:00'), new DateTime('18:00'), 1,
        'c0000000-0000-4000-8000-000000000001',
        [new RendezVous('id', 'patient', 'a0000000-0000-4000-8000-000000000001', 'structure', new DateTime('2030-01-14 10:00'), 30)]
    );
    expect(fn () => RendezVous::creer($praticien, makePatientPourRendezVous(), new DateTime('2030-01-14 10:15'), 'CI'))
        ->toThrow(RendezVousException::class, "Le praticien n'est pas disponible à la date et l'heure demandées.");
});
