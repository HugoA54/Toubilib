<?php

use toubilib\application\usecases\ServiceRendezVous;
use toubilib\application\exceptions\AnnulationRendezVousException;
use toubilib\application\exceptions\RendezVousIntrouvableException;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\StatutRendezVous;
use tests\pest\fakes\InMemoryRendezVousRepository;
use tests\pest\fakes\InMemoryPatientRepository;
use tests\pest\fakes\InMemoryPraticienRepository;
use toubilib\application\validators\RendezVousValidator;
use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\dto\PeriodeDTO;
use toubilib\application\exceptions\PraticienIntrouvableException;
use toubilib\application\exceptions\TransitionRendezVousException;
use toubilib\application\exceptions\ValidationException;
use toubilib\domain\entities\Patient;
use toubilib\domain\entities\Praticien;

const RDV_UUID = 'd0000000-0000-4000-8000-000000000001';
const RDV_PRATICIEN_UUID = 'a0000000-0000-4000-8000-000000000001';
const RDV_PATIENT_UUID = 'b0000000-0000-4000-8000-000000000001';
const RDV_INCONNU_UUID = 'd0000000-0000-4000-8000-000000000999';

function makeRepository(array $rendezVous = []): InMemoryRendezVousRepository {
    $repository = new InMemoryRendezVousRepository();
    foreach ($rendezVous as $rdv) {
        $repository->save($rdv);
    }
    return $repository;
}

function makeService(InMemoryRendezVousRepository $repository): ServiceRendezVous {
    $praticiens = new InMemoryPraticienRepository();
    $praticiens->save(new Praticien(
        RDV_PRATICIEN_UUID, 'Dr.', 'Martin', 'Paul', '1 rue des Lilas', 'Nancy',
        'paul.martin@example.com', '0600000000', new DateTime('08:00'), new DateTime('18:00'), '1',
        'c0000000-0000-4000-8000-000000000001'
    ));
    $patients = new InMemoryPatientRepository();
    $patients->save(new Patient(RDV_PATIENT_UUID, 'Durand', 'Alice', 'alice@example.com', '0611111111'));

    return new ServiceRendezVous($repository, new RendezVousValidator($praticiens, $patients), $praticiens, $patients);
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
    $service = makeService($repository);

    $service->annuler(RDV_UUID);

    expect($repository->findById(RDV_UUID)->getStatut())->toBe(StatutRendezVous::ANNULE);
});

it("devrait retourner le DTO du rendez-vous annulé", function() {
    $service = makeService(makeRepository([makeRendezVousPourService('2030-01-15 10:00')]));

    $dto = $service->annuler(RDV_UUID);

    expect($dto->toArray()['rendez_vous'])->toMatchArray([
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
    $service = makeService(makeRepository());

    expect(fn () => $service->annuler(RDV_INCONNU_UUID))
        ->toThrow(RendezVousIntrouvableException::class, "Le rendez-vous " . RDV_INCONNU_UUID . " est introuvable.");
});

it("devrait refuser d'annuler un rendez-vous passé", function() {
    $repository = makeRepository([makeRendezVousPourService('-1 day')]);
    $service = makeService($repository);

    expect(fn () => $service->annuler(RDV_UUID))
        ->toThrow(AnnulationRendezVousException::class, "Impossible d'annuler un rendez-vous déjà commencé.");
    expect($repository->findById(RDV_UUID)->getStatut())->toBe(StatutRendezVous::PREVU);
});

it("devrait refuser d'annuler un rendez-vous déjà annulé", function() {
    $service = makeService(makeRepository([makeRendezVousPourService('+1 day')]));
    $service->annuler(RDV_UUID);

    expect(fn () => $service->annuler(RDV_UUID))
        ->toThrow(AnnulationRendezVousException::class, "Impossible d'annuler un rendez-vous annulé.");
});

it("devrait refuser d'annuler un rendez-vous honoré", function() {
    $rdv = makeRendezVousPourService('-1 day');
    $rdv->marquerHonore();
    $service = makeService(makeRepository([$rdv]));

    expect(fn () => $service->annuler(RDV_UUID))
        ->toThrow(AnnulationRendezVousException::class, "Impossible d'annuler un rendez-vous honoré.");
});

it("devrait retourner les détails d'un rendez-vous", function() {
    $service = makeService(makeRepository([makeRendezVousPourService('2030-01-15 10:00')]));

    $dto = $service->details(RDV_UUID);

    expect($dto->toArray()['rendez_vous'])->toMatchArray([
        'id' => RDV_UUID,
        'patient_id' => 'b0000000-0000-4000-8000-000000000001',
        'praticien_id' => 'a0000000-0000-4000-8000-000000000001',
        'structure_id' => 'c0000000-0000-4000-8000-000000000001',
        'date_heure' => '2030-01-15 10:00',
        'date_heure_fin' => '2030-01-15 10:30',
        'duree' => 30,
        'motif' => 'Consultation de suivi',
        'statut' => StatutRendezVous::PREVU->value,
    ]);
});

it("devrait lever RendezVousIntrouvableException pour les détails d'un rendez-vous inexistant", function() {
    $service = makeService(makeRepository());

    expect(fn () => $service->details(RDV_INCONNU_UUID))
        ->toThrow(RendezVousIntrouvableException::class, "Le rendez-vous " . RDV_INCONNU_UUID . " est introuvable.");
});

it("ne devrait pas modifier le rendez-vous en consultant ses détails", function() {
    $repository = makeRepository([makeRendezVousPourService('+1 day')]);
    $service = makeService($repository);

    $service->details(RDV_UUID);

    expect($repository->findById(RDV_UUID)->getStatut())->toBe(StatutRendezVous::PREVU);
});

it("devrait retourner le DTO du rendez-vous créé", function() {
    $service = makeService(makeRepository());

    $rendezVousDTO = $service->creer(new CreateRdvDTO(RDV_PRATICIEN_UUID, RDV_PATIENT_UUID, '2030-01-15 10:00', 'CI'));

    expect($rendezVousDTO->praticienId)->toBe(RDV_PRATICIEN_UUID)
        ->and($rendezVousDTO->patientId)->toBe(RDV_PATIENT_UUID)
        ->and($rendezVousDTO->structureId)->toBe('c0000000-0000-4000-8000-000000000001')
        ->and($rendezVousDTO->dateHeure)->toBe('2030-01-15 10:00')
        ->and($rendezVousDTO->duree)->toBe(30)
        ->and($rendezVousDTO->motif)->toBe('CI')
        ->and($rendezVousDTO->statut)->toBe(StatutRendezVous::PREVU->value);
});

it("devrait enregistrer le rendez-vous créé dans le repository", function() {
    $repository = makeRepository();
    $service = makeService($repository);

    $rendezVousDTO = $service->creer(new CreateRdvDTO(RDV_PRATICIEN_UUID, RDV_PATIENT_UUID, '2030-01-15 10:00', 'CI'));

    expect($repository->findById($rendezVousDTO->id))->not->toBeNull();
});

it("devrait refuser la création d'un rendez-vous pour un praticien inexistant", function() {
    $service = makeService(makeRepository());

    $service->creer(new CreateRdvDTO(RDV_INCONNU_UUID, RDV_PATIENT_UUID, '2030-01-15 10:00', 'CI'));
})->throws(ValidationException::class);

it("devrait refuser la création d'un rendez-vous pour un patient inexistant", function() {
    $service = makeService(makeRepository());

    $service->creer(new CreateRdvDTO(RDV_PRATICIEN_UUID, RDV_INCONNU_UUID, '2030-01-15 10:00', 'CI'));
})->throws(ValidationException::class);

it("devrait marquer honoré un rendez-vous commencé et l'enregistrer", function() {
    $repository = makeRepository([makeRendezVousPourService('-1 day')]);

    $dto = makeService($repository)->honorer(RDV_UUID);

    expect($dto->statut)->toBe(StatutRendezVous::HONORE->value)
        ->and($repository->findById(RDV_UUID)->getStatut())->toBe(StatutRendezVous::HONORE);
});

it("devrait marquer ignoré un rendez-vous commencé et l'enregistrer", function() {
    $repository = makeRepository([makeRendezVousPourService('-1 day')]);

    $dto = makeService($repository)->ignorer(RDV_UUID);

    expect($dto->statut)->toBe(StatutRendezVous::NON_HONORE->value)
        ->and($repository->findById(RDV_UUID)->getStatut())->toBe(StatutRendezVous::NON_HONORE);
});

it("devrait refuser d'honorer un rendez-vous qui n'a pas commencé", function() {
    $repository = makeRepository([makeRendezVousPourService('+1 day')]);

    expect(fn () => makeService($repository)->honorer(RDV_UUID))
        ->toThrow(TransitionRendezVousException::class);
    expect($repository->findById(RDV_UUID)->getStatut())->toBe(StatutRendezVous::PREVU);
});

it("devrait refuser d'ignorer un rendez-vous inexistant", function() {
    expect(fn () => makeService(makeRepository())->ignorer(RDV_INCONNU_UUID))
        ->toThrow(RendezVousIntrouvableException::class);
});

it("devrait lister les créneaux occupés de la période, annulés et hors période exclus", function() {
    $service = makeService(makeRepository());
    $service->creer(new CreateRdvDTO(RDV_PRATICIEN_UUID, RDV_PATIENT_UUID, '2030-01-15 10:00', 'CI'));
    $service->creer(new CreateRdvDTO(RDV_PRATICIEN_UUID, RDV_PATIENT_UUID, '2030-01-16 10:00', 'CS'));
    $annule = $service->creer(new CreateRdvDTO(RDV_PRATICIEN_UUID, RDV_PATIENT_UUID, '2030-01-15 14:00', 'CI'));
    $service->annuler($annule->id);

    $creneaux = $service->creneaux(RDV_PRATICIEN_UUID, new PeriodeDTO('2030-01-15', '2030-01-15'));

    expect($creneaux)->toHaveCount(1)
        ->and($creneaux[0]->toArray())->toBe(['debut' => '2030-01-15 10:00', 'fin' => '2030-01-15 10:30', 'duree' => 30]);
});

it("devrait inclure le dernier jour de la période", function() {
    $service = makeService(makeRepository());
    $service->creer(new CreateRdvDTO(RDV_PRATICIEN_UUID, RDV_PATIENT_UUID, '2030-01-16 17:30', 'CI'));

    expect($service->creneaux(RDV_PRATICIEN_UUID, new PeriodeDTO('2030-01-15', '2030-01-16')))->toHaveCount(1);
});

it("devrait retourner l'agenda avec le patient et le motif, trié par date", function() {
    $service = makeService(makeRepository());
    $service->creer(new CreateRdvDTO(RDV_PRATICIEN_UUID, RDV_PATIENT_UUID, '2030-01-15 14:00', 'CS'));
    $service->creer(new CreateRdvDTO(RDV_PRATICIEN_UUID, RDV_PATIENT_UUID, '2030-01-15 10:00', 'CI'));

    $agenda = $service->agenda(RDV_PRATICIEN_UUID, new PeriodeDTO('2030-01-15', '2030-01-15'));

    expect($agenda)->toHaveCount(2)
        ->and($agenda[0]->debut)->toBe('2030-01-15 10:00')
        ->and($agenda[0]->motif)->toBe('CI')
        ->and($agenda[0]->toArray()['patient'])->toBe(['id' => RDV_PATIENT_UUID, 'nom' => 'Durand', 'prenom' => 'Alice']);
});

it("devrait refuser l'agenda d'un praticien inexistant", function() {
    expect(fn () => makeService(makeRepository())->agenda(RDV_INCONNU_UUID, new PeriodeDTO('2030-01-15', '2030-01-15')))
        ->toThrow(PraticienIntrouvableException::class);
});

it("ne devrait proposer les liens de transition que pour un rendez-vous prévu", function() {
    $service = makeService(makeRepository([makeRendezVousPourService('+1 day')]));

    expect(array_keys($service->details(RDV_UUID)->toArray()['links']))->toBe(['self', 'praticien', 'annuler', 'honorer', 'ignorer']);

    $service->annuler(RDV_UUID);

    expect(array_keys($service->details(RDV_UUID)->toArray()['links']))->toBe(['self', 'praticien']);
});
