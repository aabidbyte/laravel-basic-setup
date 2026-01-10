<?php

return [
    'activation' => [
        'subject' => 'Activez votre compte :app',
        'greeting' => 'Bonjour :name,',
        'intro' => 'Un compte a été créé pour vous. Veuillez cliquer sur le bouton ci-dessous pour définir votre mot de passe et activer votre compte.',
        'instructions' => 'Après avoir défini votre mot de passe, vous pourrez vous connecter et commencer à utiliser l\'application.',
        'button' => 'Activer le compte',
        'expires' => 'Ce lien d\'activation expirera dans :days jours.',
        'link_fallback' => 'Si vous rencontrez des problèmes pour cliquer sur le bouton, copiez et collez le lien ci-dessous dans votre navigateur :',
        'footer' => 'Si vous ne vous attendiez pas à recevoir cet e-mail, vous pouvez l\'ignorer en toute sécurité.',
    ],
    'welcome' => [
        'subject' => 'Bienvenue sur :app !',
        'greeting' => 'Bienvenue, :name !',
        'intro' => 'Nous sommes ravis de vous compter parmi nous.',
        'ready' => 'Votre compte est maintenant actif et prêt à l\'emploi.',
        'button' => 'Se connecter',
        'help' => 'Si vous avez des questions, n\'hésitez pas à répondre à cet e-mail.',
        'footer' => 'L\'équipe :app',
    ],
    'email_change_security' => [
        'subject' => 'Alerte de sécurité : Demande de changement d\'email - :app',
        'greeting' => 'Bonjour :name,',
        'warning_title' => 'Notification de sécurité importante',
        'intro' => 'Nous avons reçu une demande de changement de l\'adresse email associée à votre compte.',
        'new_email' => 'Nouvelle adresse email demandée',
        'if_not_you' => 'Si vous n\'êtes pas à l\'origine de cette demande, votre compte peut être compromis.',
        'contact_support' => 'Veuillez contacter notre support immédiatement :',
        'footer' => 'Ceci est une notification de sécurité automatique.',
    ],
    'email_change_verification' => [
        'subject' => 'Vérifiez votre nouvelle adresse email - :app',
        'greeting' => 'Bonjour :name,',
        'intro' => 'Vous avez demandé à changer votre adresse email. Pour confirmer ce changement, veuillez vérifier votre nouvelle adresse.',
        'instructions' => 'Cliquez sur le bouton ci-dessous pour vérifier cette adresse email et finaliser la mise à jour.',
        'button' => 'Vérifier le changement d\'email',
        'expires' => 'Ce lien expirera dans :days jours.',
        'link_fallback' => 'Si vous rencontrez des problèmes pour cliquer sur le bouton, copiez et collez le lien ci-dessous dans votre navigateur :',
        'footer' => 'Si vous n\'avez pas demandé ce changement, vous pouvez ignorer cet email en toute sécurité.',
    ],
];
