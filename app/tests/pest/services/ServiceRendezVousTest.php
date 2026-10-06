<?php

use toubilib\application\usecases\ServiceRendezVous;
use toubilib\application\exceptions\AnnulationRendezVousException;
use toubilib\application\exceptions\RendezVousIntrouvableException;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\StatutRendezVous;
use tests\pest\fakes\InMemoryRendezVousRepository;

const RDV_UUID = 'd0000000-0000-4000-8000-000000000001';
const RDV_INCONNU_UUID = 'd0000000-0000-4000-8000-000000000999';

function makeRepository(array $rendezVous = []): InMemoryRendezVousRepository {
    $repository = new InMemoryRendezVousRepository();
    foreach ($rendezVous as $rdv) {
        $repository->save($rdv);
    }
    return $repository;
}

function makeRendezVousPourService(string $dateHeure): RendezVous {
    return new RendezVous(
        RDV_UUID,
        'b0000000-0000-4000-8000-000000000001',
        'a0000000-0000-4000-8000-000000000001',
        'c0000000-0000-4000-8000-000000000001',
        new DateTime($dateHeure),
        30,
        'Consultation de suivi'
    );
}

it("devrait annuler un rendez-vous prévu à venir", function() {
    $repository = makeRepository([makeRendezVousPourService('+1 day')]);
    $service = new ServiceRendezVous($repository);

    $service->annuler(RDV_UUID);

    expect($repository->findById(RDV_UUID)->getStatut())->toBe(StatutRendezVous::ANNULE);
});

it("devrait retourner le DTO du rendez-vous annulé", function() {
    $service = new ServiceRendezVous(makeRepository([makeRendezVousPourService('2030-01-15 10:00')]));

    $dto = $service->annuler(RDV_UUID);

    expect($dto->toArray())->toMatchArray([
        'id' => RDV_UUID,
        'patient_id' => 'b0000000-0000-4000-8000-000000000001',
        'praticien_id' => 'a0000000-0000-4000-8000-000000000001',
        'structure_id' => 'c0000000-0000-4000-8000-000000000001',
        'date_heure' => '2030-01-15 10:00',
        'date_heure_fin' => '2030-01-15 10:30',
        'duree' => 30,
        'motif' => 'Consultation de suivi',
        'statut' => StatutRendezVous::ANNULE->value,
        'statut_libelle' => 'annulé',
    ]);
});

it("devrait lever RendezVousIntrouvableException pour un rendez-vous inexistant", function() {
    $service = new ServiceRendezVous(makeRepository());

    expect(fn () => $service->annuler(RDV_INCONNU_UUID))
        ->toThrow(RendezVousIntrouvableException::class, "Le rendez-vous " . RDV_INCONNU_UUID . " est introuvable.");
});

it("devrait refuser d'annuler un rendez-vous passé", function() {
    $repository = makeRepository([makeRendezVousPourService('-1 day')]);
    $service = new ServiceRendezVous($repository);

    expect(fn () => $service->annuler(RDV_UUID))
        ->toThrow(AnnulationRendezVousException::class, "Impossible d'annuler un rendez-vous déjà commencé.");
    expect($repository->findById(RDV_UUID)->getStatut())->toBe(StatutRendezVous::PREVU);
});

it("devrait refuser d'annuler un rendez-vous déjà annulé", function() {
    $service = new ServiceRendezVous(makeRepository([makeRendezVousPourService('+1 day')]));
    $service->annuler(RDV_UUID);

    expect(fn () => $service->annuler(RDV_UUID))
        ->toThrow(AnnulationRendezVousException::class, "Impossible d'annuler un rendez-vous annulé.");
});

it("devrait refuser d'annuler un rendez-vous honoré", function() {
    $rdv = makeRendezVousPourService('-1 day');
    $rdv->marquerHonore();
    $service = new ServiceRendezVous(makeRepository([$rdv]));

    expect(fn () => $service->annuler(RDV_UUID))
        ->toThrow(AnnulationRendezVousException::class, "Impossible d'annuler un rendez-vous honoré.");
});
