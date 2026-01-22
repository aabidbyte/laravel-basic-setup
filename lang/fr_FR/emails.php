<?php

return [
    'user_welcome' => [
        'subject' => 'Bienvenue sur :app_name !',
        'preheader' => 'Nous sommes ravis de vous compter parmi nous.',
        'greeting' => 'Bonjour :name,',
        'intro' => 'Merci de vous être inscrit ! Nous sommes ravis de vous accueillir dans notre communauté.',
        'action' => 'Commencer',
        'closing' => 'Grâce à notre plateforme, vous pouvez réaliser des choses incroyables.',
    ],
    'password_reset' => [
        'subject' => 'Notification de réinitialisation de mot de passe',
        'intro' => 'Vous recevez cet e-mail car nous avons reçu une demande de réinitialisation de mot de passe pour votre compte.',
        'action' => 'Réinitialiser le mot de passe',
        'expiry' => 'Ce lien de réinitialisation expirera dans :count minutes.',
        'fallback' => 'Si vous n\'avez pas demandé de réinitialisation, aucune autre action n\'est requise.',
        'preheader' => 'Réinitialisez votre mot de passe pour retrouver l\'accès.',
    ],
    'user_activated' => [
        'subject' => ':name a activé son compte',
        'greeting' => 'Bonjour,',
        'line1' => ':name a activé son compte avec succès.',
        'line2' => 'Vous pouvez vérifier son profil et ses permissions.',
        'action' => 'Voir le profil',
        'salutation' => 'Cordialement, :app',
        'preheader' => 'Nouvelle activation utilisateur : :name',
    ],
    'verify_email' => [
        'subject' => 'Vérifier l\'adresse e-mail',
        'intro' => 'Veuillez cliquer sur le bouton ci-dessous pour vérifier votre adresse e-mail.',
        'action' => 'Vérifier l\'adresse e-mail',
        'fallback' => 'Si vous n\'avez pas créé de compte, aucune autre action n\'est requise.',
        'preheader' => 'Vérifiez votre e-mail pour commencer.',
    ],
    'email_change_security' => [
        'subject' => 'Alerte de sécurité : Changement d\'e-mail demandé pour :app',
        'greeting' => 'Bonjour :name,',
        'warning_title' => 'Changement d\'e-mail demandé',
        'intro' => 'Nous avons reçu une demande de changement de l\'adresse e-mail associée à votre compte.',
        'new_email' => 'Nouvelle adresse e-mail',
        'if_not_you' => 'Si vous n\'avez pas demandé ce changement, veuillez contacter le support immédiatement. Votre compte pourrait être compromis.',
        'contact_support' => 'Contacter le support :',
    ],
    'email_change_verification' => [
        'subject' => 'Vérifiez votre nouvelle adresse e-mail - :app',
        'greeting' => 'Bonjour :name,',
        'intro' => 'Vous avez demandé de changer votre adresse e-mail. Veuillez cliquer sur le bouton ci-dessous pour vérifier cette nouvelle adresse.',
        'button' => 'Vérifier la nouvelle e-mail',
        'expiry' => 'Ce lien expirera dans 7 jours.',
    ],
    'activation' => [
        'subject' => 'Activation de compte - :app',
    ],
];
