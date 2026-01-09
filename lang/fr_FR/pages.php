<?php

return [
    'dashboard' => 'Tableau de bord',
    'notifications' => 'Notifications',
    'common' => [
        'index' => [
            'title' => ':type',
            'description' => 'Gérer et voir tous les :type_plural du système',
        ],
        'show' => [
            'title' => ':name - Détails :type',
            'description' => 'Voir les informations :type',
            'subtitle' => 'Détails et gestion :type',
        ],
        'create' => [
            'title' => 'Créer un nouveau :type',
            'description' => 'Ajouter un nouveau :type au système',
            'submit' => 'Créer :type',
            'success' => ':name a été créé avec succès',
            'error' => 'Échec de la création :type',
        ],
        'edit' => [
            'title' => 'Modifier :type',
            'description' => 'Mettre à jour les informations :type',
            'submit' => 'Enregistrer les modifications',
            'success' => ':name a été mis à jour avec succès',
            'error' => 'Échec de la mise à jour :type',
        ],
        'messages' => [
            'deleted' => ':name supprimé avec succès',
            'activated' => ':name activé avec succès',
            'deactivated' => ':name désactivé avec succès',
        ],
        'not_found' => 'Le :type demandé n\'a pas été trouvé.',
    ],
    'users' => [
        'index' => 'Utilisateurs',
        'create' => 'Créer un utilisateur',
        'edit' => 'Modifier l\'utilisateur',
        'show' => 'Détails de l\'utilisateur',
        'description' => 'Gérer et voir tous les utilisateurs du système',
    ],
    'users_description' => 'Gérer et voir tous les utilisateurs du système',
    'settings' => [
        'profile' => 'Paramètres du profil',
        'password' => 'Paramètres du mot de passe',
        'two_factor' => 'Authentification à deux facteurs',
    ],
    'trash' => [
        'index' => [
            'title' => ':type supprimés',
            'description' => 'Voir et gérer les éléments supprimés',
        ],
        'show' => [
            'title' => ':name (Supprimé)',
            'description' => 'Voir les détails de l\'élément supprimé',
            'item_details' => 'Détails de l\'élément',
            'metadata' => 'Métadonnées',
            'not_found' => 'Élément supprimé non trouvé.',
            'confirm_mismatch' => 'Le nom saisi ne correspond pas.',
        ],
        'badge' => 'Supprimé',
    ],
];
