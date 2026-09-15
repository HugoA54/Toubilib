# Fonctionnalité 6 : Annuler un rendez-vous

## 1. Comment peut-on l’implanter dans le domaine ?

La règle métier est placée dans l'entité Rdv, car c'est elle qui connaît son statut et sa date. La méthode estAnnulable() vérifie que le RDV est encore prévu (statut 0) et qu'il n'est pas déjà passé. La méthode annuler() passe le statut à « annulé » ; si le RDV n'est pas annulable, elle lève une RdvNonAnnulableException. Les méthodes annulerRDV() de Patient et de Praticien se contentent d'appeler rdv->annuler().

## 2. Quelles erreurs peuvent être détectées lors de l’exécution de cette fonctionnalité ?

1) Identifiant du RDV absent ou mal formé.
2) Aucun RDV ne correspond à l'identifiant.
3) RDV déjà annulé.
4) RDV déjà passé, honoré ou non honoré, donc plus annulable.
5) Utilisateur qui n'est ni le patient ni le praticien du RDV.
6) Erreur technique d'accès à la base de données.

## 3. Quel composant retrouve l’entité correspondant au RDV à annuler ?

C'est le repository des RDV, défini par le port de sortie RdvRepositoryInterface et implémenté par un adapter (par exemple PDORdvRepository). Le service applicatif l'appelle avec findById().

## 4. Quel composant sauvegarde ce RDV dans la base de données après sa modification ?

C'est ce même repository, avec sa méthode update(). Le service applicatif enchaîne les trois étapes : findById(), puis rdv->annuler(), puis update().

## 5. Ports d'entrée et sortie

Port d'entrée (application/ports/api) :

php
interface ServiceRdvInterface
{
    public function annulerRdv(string $idRdv): RdvDTO;
}

Port de sortie :

php
interface RdvRepositoryInterface
{
    public function findById(string $id): Rdv;
    public function update(Rdv $rdv): void;
}
