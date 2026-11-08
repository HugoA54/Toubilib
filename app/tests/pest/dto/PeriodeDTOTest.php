<?php

use toubilib\application\dto\PeriodeDTO;
use toubilib\application\exceptions\ValidationException;

function erreursPeriode(array $data): array {
    try {
        PeriodeDTO::fromArray($data);
    } catch (ValidationException $e) {
        return $e->getErrors();
    }
    return [];
}

it("devrait accepter une période valide", function() {
    $periode = PeriodeDTO::fromArray(['debut' => '2030-01-15', 'fin' => '2030-01-20']);

    expect($periode->debut)->toBe('2030-01-15')
        ->and($periode->fin)->toBe('2030-01-20');
});

it("devrait accepter une période d'un seul jour", function() {
    expect(erreursPeriode(['debut' => '2030-01-15', 'fin' => '2030-01-15']))->toBe([]);
});

it("devrait couvrir 7 jours à partir d'aujourd'hui sans paramètre", function() {
    $periode = PeriodeDTO::fromArray([]);

    expect($periode->debut)->toBe((new DateTime('today'))->format('Y-m-d'))
        ->and($periode->fin)->toBe((new DateTime('+6 days'))->format('Y-m-d'));
});

it("devrait calculer la fin par défaut à partir du début fourni", function() {
    expect(PeriodeDTO::fromArray(['debut' => '2030-01-15'])->fin)->toBe('2030-01-21');
});

it("devrait refuser une date mal formée", function(array $data, string $champ) {
    expect(array_keys(erreursPeriode($data)))->toBe([$champ]);
})->with([
    'format français' => [['debut' => '15/01/2030'], 'debut'],
    'date inexistante' => [['fin' => '2030-02-30'], 'fin'],
    'tableau' => [['debut' => ['2030-01-15']], 'debut'],
]);

it("devrait refuser une fin antérieure au début", function() {
    expect(array_keys(erreursPeriode(['debut' => '2030-01-20', 'fin' => '2030-01-15'])))->toBe(['fin']);
});
