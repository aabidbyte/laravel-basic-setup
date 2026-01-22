<?php

return [
    'title' => 'Permissions',
    'matrix' => [
        'title' => 'Matrice de permissions',
        'description' => 'Configurez quelles actions peuvent être effectuées sur chaque entité.',
        'select_all_row' => 'Tout sélectionner pour :entity',
        'select_all_column' => 'Tout sélectionner :action',
        'no_permissions' => 'Aucune permission disponible',
    ],
    'entities' => [
        'users' => 'Utilisateurs',
        'roles' => 'Rôles',
        'teams' => 'Équipes',
        'error_logs' => 'Journaux d\'erreur',
        'telescope' => 'Telescope',
        'horizon' => 'Horizon',
        'mail_settings' => 'Paramètres de messagerie',
        'email_templates' => 'Modèles d\'email',
        'email_layouts' => 'Mises en page d\'email',
    ],
    'actions' => [
        'view' => 'Voir',
        'create' => 'Créer',
        'edit' => 'Modifier',
        'delete' => 'Supprimer',
        'restore' => 'Restaurer',
        'force_delete' => 'Supprimer définitivement',
        'export' => 'Exporter',
        'publish' => 'Publier',
        'unpublish' => 'Dépublier',
        'resolve' => 'Résoudre',
        'activate' => 'Activer',
        'configure' => 'Configurer',
        'generate_activation' => 'Générer l\'activation',
    ],
];
