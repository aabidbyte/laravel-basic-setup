<?php

return [
    'kind' => [
        'layout' => 'Mise en page',
        'content' => 'Contenu',
    ],
    'types' => [
        'transactional' => 'Transactionnel',
        'marketing' => 'Marketing',
        'system' => 'Système',
        '{$template->type}' => '',
    ],
    'status' => [
        'draft' => 'Brouillon',
        'published' => 'Publié',
        'archived' => 'Archivé',
        '{$template->status}' => '',
    ],
    'actions' => [
        'publish' => 'Publier',
        'archive' => 'Archiver',
    ],
    'form' => [
        'basic_info' => 'Informations de base',
        'settings' => 'Paramètres',
        'name' => 'Nom',
        'description' => 'Description',
        'type' => 'Type',
        'status' => 'Statut',
        'layout' => 'Mise en page',
        'subject' => 'Sujet',
        'preheader' => 'Pré-en-tête',
        'html_content' => 'Contenu HTML',
        'text_content' => 'Contenu texte',
        'is_default' => 'Mise en page par défaut',
    ],
    'show' => [
        'basic_info' => 'Informations de base',
        'translations' => 'Traductions',
        'html_length' => 'Longueur HTML',
    ],
    'merge_tags' => [
        'insert' => 'Insérer une balise',
        'search' => 'Rechercher...',
        'context' => 'Variables de contexte',
        'no_tags' => 'Aucune balise disponible pour ce modèle.',
        'help' => 'Cliquez sur une balise pour l\'insérer. Utilisez {{ tag }} pour un contenu échappé ou {{{ tag }}} pour du HTML brut.',
    ],
    'preview' => [
        'button' => 'Aperçu',
        'title' => 'Aperçu de l\'e-mail',
        'subject' => 'Sujet',
        'error' => 'Impossible de générer l\'aperçu',
    ],
    'cannot_delete_system' => 'Impossible de supprimer un modèle système.',
    'cannot_delete_default' => 'Impossible de supprimer la mise en page par défaut.',
];
