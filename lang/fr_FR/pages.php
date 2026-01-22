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
            'title' => 'Détails :type - :name',
            'description' => 'Voir les informations de :type',
            'subtitle' => 'Détails et gestion de :type',
        ],
        'create' => [
            'title' => 'Créer :type',
            'description' => 'Ajouter un nouveau :type au système',
            'submit' => 'Créer :type',
            'success' => ':name a été créé avec succès',
            'error' => 'Échec de la création de :type',
        ],
        'edit' => [
            'title' => 'Modifier :type',
            'description' => 'Mettre à jour les informations de :type',
            'submit' => 'Enregistrer les modifications',
            'success' => ':name a été mis à jour avec succès',
            'error' => 'Échec de la mise à jour de :type',
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
            'confirm_mismatch' => 'Le nom que vous avez tapé ne correspond pas.',
        ],
        'badge' => 'Supprimé',
    ],
    'email_templates' => [
        'index' => [
            'title' => 'Modèles d\'e-mail',
            'subtitle' => 'Gérer les modèles d\'e-mail et les mises en page',
        ],
    ],
];
