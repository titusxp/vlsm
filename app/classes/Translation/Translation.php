<?php

namespace App\Translation;

class Translation
{
    public static function translate($key)
    {
        $translations = [

            'Dashboard' => [
                'en_US' => 'Dashboard',
                'fr_FR' => 'Tableau de bord'
            ],
            'Admin' => [
                'en_US' => 'Admin',
                'fr_FR' => 'Administrateur'
            ],
            'Access Control' => [
                'en_US' => 'Access Control',
                'fr_FR' => "Contrôle d'accèss"
            ],
            'Roles' => [
                'en_US' => 'Roles',
                'fr_FR' => 'Les rôles'
            ],
            'Users' => [
                'en_US' => 'Users',
                'fr_FR' => 'Utilisateur'
            ],
            'Facilities' => [
                'en_US' => 'Facilities',
                'fr_FR' => 'Installations'
            ],
            'Monitoring' => [
                'en_US' => 'Monitoring',
                'fr_FR' => 'Surveillance'
            ],
            'User Activity Log' => [
                'en_US' => 'User Activity Log',
                'fr_FR' => "Activité de l'utilisateur"
            ],
            'Audit Trail' => [
                'en_US' => 'Audit Trail',
                'fr_FR' => "Piste d'audit"
            ],
            'API History' => [
                'en_US' => 'API History',
                'fr_FR' => 'Historique des API'
            ],
            'Source of Requests' => [
                'en_US' => 'Source of Request',
                'fr_FR' => 'Origine des demandes'
            ],
            'System Configuration' => [
                'en_US' => 'System Configuration',
                'fr_FR' => 'Configuration du système'
            ],
            'General Configuration' => [
                'en_US' => 'General Configuration',
                'fr_FR' => 'Paramétrage général'
            ],
            'Instruments' => [
                'en_US' => 'Instruments',
                'fr_FR' => 'Instruments'
            ],
            'Geographical Divisions' => [
                'en_US' => 'Geographical Divisions',
                'fr_FR' => 'Divisions géographiques'

            ],
            'Implementation Partners' => [
                'en_US' => 'Implementation Partners',
                'fr_FR' => 'Partenaires'
            ],
            'Funding Sources' => [
                'en_US' => 'Funding Sources',
                'fr_FR' => 'Sources de financement'
            ],
            'VL Config' => [
                'en_US' => 'VL Config',
                'fr_FR' => 'Configuration VL'
            ],
            'ART Regiment' => [
                'en_US' => 'ART Regiment',
                'fr_FR' => 'Régiment ART'
            ],

            'Rejection Reasons' => [
                'en_US' => 'Rejection Reasons',
                'fr_FR' => 'Motifs de rejet'
            ],
            'Sample Type' => [
                'en_US' => 'Sample Type',
                'fr_FR' => 'Échantillon type'
            ],
            'Results' => [
                'en_US' => 'Results',
                'fr_FR' => 'Résultats'
            ],
            'Test Reasons' => [
                'en_US' => 'Test Reasons',
                'fr_FR' => 'Motifs du test'
            ],
            'Test Failure Reasons' => [
                'en_US' => 'Test Failure Reasons',
                'fr_FR' => "Raisons d'échec du test"
            ],
            'Request Management' => [
                'en_US' => 'Request Management',
                'fr_FR' => 'Gestion des demandes'
            ],
            'View Test Requests' => [
                'en_US' => 'View Test Request',
                'fr_FR' => 'Afficher les demandes de test'
            ],
            'Add New Request' => [
                'en_US' => 'Add New Request',
                'fr_FR' => 'Ajouter une nouvelle demande'
            ],
            'Add Samples from Manifest' => [
                'en_US' => 'Add Samples from Manifest',
                'fr_FR' => 'Ajouter des échantillons du manifeste'
            ],
            'Manage Batch' => [
                'en_US' => 'Manage Batch',
                'fr_FR' => 'Gérer le lot'
            ],
            'Test Result Management' => [
                'en_US' => 'Test Result Management',
                'fr_FR' => 'Gestion des résultats'
            ],
            'Import Result From File' => [
                'en_US' => 'Import Result From File',
                'fr_FR' => 'Importer le résultat'
            ],
            'Enter Result Manually' => [
                'en_US' => 'Enter Result Manually',
                'fr_FR' => 'Entrez le résultat manuellement'
            ],
            'Failed/Hold Samples' => [
                'en_US' => 'Failed/Hold Samples',
                'fr_FR' => 'Échantillons échoués/en attente'
            ],
            'Manage Result Status' => [
                'en_US' => 'Manage Result Status',
                'fr_FR' => "Gérer l'état des résultats"
            ],
            'Management' => [
                'en_US' => 'Management',
                'fr_FR' => 'Gestion'
            ],
            'Sample Status Report' => [
                'en_US' => 'Sample Status Report',
                'fr_FR' => "Exemple de rapport d'état"
            ],
            'Control Report' => [
                'en_US' => 'Control Report',
                'fr_FR' => "Rapport de contrôle"
            ],
            'Export Results' => [
                'en_US' => 'Export Results',
                'fr_FR' => 'Exporter les résultats'
            ],
            'Print Result' => [
                'en_US' => 'Print Result',
                'fr_FR' => 'Imprimer le résultat'
            ],

            'Clinic Report' => [
                'em_US' => 'Clinic Report',
                'fr_Fr' => 'Imprimer le résultat'
            ],
            'VL Lab Weekly Report' => [
                'en_US' => 'VL Lab Weekly Report',
                'fr_FR' => 'Rapport hebdomadaire du lab'
            ],
            'Sample Rejection Report' => [
                'en_US' => 'Sample Rejection Report',
                'fr_FR' => 'Exemple de rapport de rejet'
            ],
            'Sample Monitoring Report' => [
                'en_US' => 'Sample Monitoring Report',
                'fr_FR' => 'Exe rapport de surveillance'
            ],
            'Name' => [
                'en_US' => 'Name',
                'fr_FR' => 'Nom'
            ],
            'User Locale' => [
                'en_US' => 'User Locale',
                'fr_FR' => 'Langue'
            ],
            'Official Email' => [
                'en_US' => 'Official Email',
                'fr_FR' => 'E-mail officiel'
            ],
            'Phone Number' => [
                'en_US' => 'Phone Number',
                'fr_FR' => 'Numéro de téléphone'
            ],
            'Password' => [
                'en_US' => 'Password',
                'fr_FR' => 'Mot de passe'
            ],
            'Confirm Password' => [
                'en_US' => 'Confirm Password',
                'fr_FR' => 'Confirmez le mot de passe'
            ],
            'Submit' => [
                'en_US' => 'Submit',
                'fr_FR' => 'Soumettre'
            ],
            'Cancel' => [
                'en_US' => 'Cancel',
                'fr_FR' => 'Annuler'
            ],
            'Password must be at least 12 characters long and must include AT LEAST one number, one alphabet and may have special characters.' => [
                'en_US' => 'Password must be at least 12 characters long and must include AT LEAST one number, one alphabet and may have special characters.',
                'fr_FR' => 'Le mot de passe doit comporter au moins 12 caractères et doit inclure AU MOINS un chiffre, un alphabet et peut avoir des caractères spéciaux.'
            ],
            

        ];
        $locale = isset($_SESSION['userLocale']) ? $_SESSION['userLocale'] : 'en_US';

        if (isset($translations[$key][$locale])) {
            return $translations[$key][$locale];
        } else {
            return $key;
        }
    }

    public static function languageId()
    {
        $_SESSION['lid'] = 1;
        if ($_SESSION['userLocale'] == 'fr_FR') {
             $_SESSION['lid'] = 2;
        } else {
            $_SESSION['lid'] = 1;
        }
        $lid = $_SESSION['lid'];

        return $lid;
    }
}
