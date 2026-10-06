<?php

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
