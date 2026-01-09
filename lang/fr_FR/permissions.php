<?php

return [
    'title' => 'Permissions',
    'matrix' => [
        'title' => 'Matrice des permissions',
        'description' => 'Configurez les actions possibles pour chaque entité.',
        'select_all_row' => 'Tout sélectionner pour :entity',
        'select_all_column' => 'Tout sélectionner :action',
        'no_permissions' => 'Aucune permission disponible',
    ],

    // Entity labels
    'entities' => [
        'users' => 'Utilisateurs',
        'roles' => 'Rôles',
        'teams' => 'Équipes',
        'documents' => 'Documents',
        'articles' => 'Articles',
        'posts' => 'Articles de blog',
        'error_logs' => 'Logs d\'erreurs',
        'telescope' => 'Telescope',
        'horizon' => 'Horizon',
        'mail_settings' => 'Configuration Mail',
    ],

    // Action labels
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
        'access' => 'Accéder',
        'generate_activation' => 'Générer activation',
    ],
];
