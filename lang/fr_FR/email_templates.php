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
    ],
    'status' => [
        'draft' => 'Brouillon',
        'published' => 'Publié',
        'archived' => 'Archivé',
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
    'edit' => [
        'settings' => 'Paramètres',
        'settings_description' => 'Mettre à jour les informations de base et la configuration du modèle.',
        'content' => 'Contenu',
        'content_description' => 'Modifier le contenu du modèle d\'e-mail et les traductions.',
        'edit_builder' => 'Modifier le constructeur',
        'edit_settings' => 'Modifier les paramètres',
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
        'modal_description' => 'Aperçu rapide des traductions et informations de base du modèle d\'e-mail.',
        'no_translations' => 'Aucune traduction disponible pour ce modèle.',
        'no_preview' => 'Aucun aperçu disponible. Sélectionnez une langue ou vérifiez que la traduction existe.',
    ],
    'translations_count' => '{0} Aucune Traduction|{1} :count Traduction|[2,*] :count Traductions',
    'cannot_delete_system' => 'Impossible de supprimer un modèle système.',
    'cannot_delete_default' => 'Impossible de supprimer la mise en page par défaut.',
    'not_found' => 'Modèle d\'e-mail introuvable.',
];
