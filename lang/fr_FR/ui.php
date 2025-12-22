<?php

return [
    // Navigation & Menu
    'navigation' => [
        'platform' => 'Plateforme',
        'dashboard' => 'Tableau de bord',
        'notifications' => 'Notifications',
        'resources' => 'Ressources',
        'repository' => 'Dépôt',
        'documentation' => 'Documentation',
        'user' => 'Utilisateur',
        'settings' => 'Paramètres',
    ],

    // Actions
    'actions' => [
        'logout' => 'Se déconnecter',
        'save' => 'Enregistrer',
        'delete' => 'Supprimer',
        'confirm' => 'Confirmer',
        'continue' => 'Continuer',
        'back' => 'Retour',
        'close' => 'Fermer',
        'cancel' => 'Annuler',
        'edit' => 'Modifier',
        'view' => 'Voir',
    ],

    // Auth Pages
    'auth' => [
        'login' => [
            'title' => 'Connectez-vous à votre compte',
            'description' => 'Entrez votre adresse e-mail et votre mot de passe ci-dessous pour vous connecter',
            'email_label' => 'Adresse e-mail',
            'password_label' => 'Mot de passe',
            'password_placeholder' => 'Mot de passe',
            'remember_me' => 'Se souvenir de moi',
            'forgot_password' => 'Mot de passe oublié ?',
            'submit' => 'Se connecter',
            'no_account' => 'Vous n\'avez pas de compte ?',
            'sign_up' => 'S\'inscrire',
        ],
        'register' => [
            'title' => 'Créer un compte',
            'description' => 'Entrez vos informations ci-dessous pour créer votre compte',
            'name_label' => 'Nom',
            'name_placeholder' => 'Nom complet',
            'email_label' => 'Adresse e-mail',
            'password_label' => 'Mot de passe',
            'password_placeholder' => 'Mot de passe',
            'confirm_password_label' => 'Confirmer le mot de passe',
            'confirm_password_placeholder' => 'Confirmer le mot de passe',
            'submit' => 'Créer le compte',
            'has_account' => 'Vous avez déjà un compte ?',
            'log_in' => 'Se connecter',
        ],
        'forgot_password' => [
            'title' => 'Mot de passe oublié',
            'description' => 'Entrez votre adresse e-mail pour recevoir un lien de réinitialisation du mot de passe',
            'email_label' => 'Adresse e-mail',
            'submit' => 'Envoyer le lien de réinitialisation',
            'back_to_login' => 'Ou, retourner à la',
            'log_in' => 'connexion',
            'success' => 'Nous avons envoyé votre lien de réinitialisation de mot de passe par e-mail.',
        ],
        'reset_password' => [
            'title' => 'Réinitialiser le mot de passe',
            'description' => 'Veuillez entrer votre nouveau mot de passe ci-dessous',
            'email_label' => 'E-mail',
            'password_label' => 'Mot de passe',
            'password_placeholder' => 'Mot de passe',
            'confirm_password_label' => 'Confirmer le mot de passe',
            'confirm_password_placeholder' => 'Confirmer le mot de passe',
            'submit' => 'Réinitialiser le mot de passe',
            'success' => 'Votre mot de passe a été réinitialisé.',
        ],
        'verify_email' => [
            'title' => 'Vérifiez votre e-mail',
            'message' => 'Veuillez vérifier votre adresse e-mail en cliquant sur le lien que nous venons de vous envoyer par e-mail.',
            'resend_success' => 'Un nouveau lien de vérification a été envoyé à l\'adresse e-mail que vous avez fournie lors de l\'inscription.',
            'resend_button' => 'Renvoyer l\'e-mail de vérification',
            'logout' => 'Se déconnecter',
        ],
        'confirm_password' => [
            'title' => 'Confirmer le mot de passe',
            'description' => 'Il s\'agit d\'une zone sécurisée de l\'application. Veuillez confirmer votre mot de passe avant de continuer.',
            'password_label' => 'Mot de passe',
            'password_placeholder' => 'Mot de passe',
            'submit' => 'Confirmer',
        ],
        'two_factor' => [
            'title' => 'Code d\'authentification',
            'description' => 'Entrez le code d\'authentification fourni par votre application d\'authentification.',
            'recovery_title' => 'Code de récupération',
            'recovery_description' => 'Veuillez confirmer l\'accès à votre compte en entrant l\'un de vos codes de récupération d\'urgence.',
            'otp_label' => 'Code OTP',
            'recovery_code_label' => 'Code de récupération',
            'submit' => 'Continuer',
            'switch_to_recovery' => 'ou vous pouvez',
            'use_recovery_code' => 'vous connecter en utilisant un code de récupération',
            'use_auth_code' => 'vous connecter en utilisant un code d\'authentification',
        ],
    ],

    // Settings Pages
    'settings' => [
        'title' => 'Paramètres',
        'description' => 'Gérez votre profil et les paramètres de votre compte',
        'profile' => [
            'title' => 'Profil',
            'description' => 'Mettez à jour votre nom et votre adresse e-mail',
            'name_label' => 'Nom',
            'email_label' => 'E-mail',
            'email_unverified' => 'Votre adresse e-mail n\'est pas vérifiée.',
            'resend_verification' => 'Cliquez ici pour renvoyer l\'e-mail de vérification.',
            'verification_sent' => 'Un nouveau lien de vérification a été envoyé à votre adresse e-mail.',
            'save_success' => 'Enregistré.',
        ],
        'password' => [
            'title' => 'Mettre à jour le mot de passe',
            'description' => 'Assurez-vous que votre compte utilise un mot de passe long et aléatoire pour rester sécurisé',
            'current_password_label' => 'Mot de passe actuel',
            'new_password_label' => 'Nouveau mot de passe',
            'confirm_password_label' => 'Confirmer le mot de passe',
            'save_success' => 'Enregistré.',
        ],
        'two_factor' => [
            'title' => 'Authentification à deux facteurs',
            'description' => 'Gérez vos paramètres d\'authentification à deux facteurs',
            'enabled' => 'Activée',
            'enabled_description' => 'Avec l\'authentification à deux facteurs activée, vous serez invité à saisir un code PIN sécurisé et aléatoire lors de la connexion, que vous pouvez récupérer depuis l\'application compatible TOTP sur votre téléphone.',
            'enabled_success' => 'L\'authentification à deux facteurs a été activée.',
            'disabled_success' => 'L\'authentification à deux facteurs a été désactivée.',
            'disable_button' => 'Désactiver l\'A2F',
            'disabled' => 'Désactivée',
            'disabled_description' => 'Lorsque vous activez l\'authentification à deux facteurs, vous serez invité à saisir un code PIN sécurisé lors de la connexion. Ce code PIN peut être récupéré depuis une application compatible TOTP sur votre téléphone.',
            'enable_button' => 'Activer l\'A2F',
            'setup' => [
                'title_enabled' => 'Authentification à deux facteurs activée',
                'description_enabled' => 'L\'authentification à deux facteurs est maintenant activée. Scannez le code QR ou entrez la clé de configuration dans votre application d\'authentification.',
                'title_verify' => 'Vérifier le code d\'authentification',
                'description_verify' => 'Entrez le code à 6 chiffres de votre application d\'authentification.',
                'title_setup' => 'Activer l\'authentification à deux facteurs',
                'description_setup' => 'Pour terminer l\'activation de l\'authentification à deux facteurs, scannez le code QR ou entrez la clé de configuration dans votre application d\'authentification.',
                'otp_label' => 'Code OTP',
                'manual_code_label' => 'ou, entrez le code manuellement',
            ],
            'recovery' => [
                'title' => 'Codes de récupération A2F',
                'description' => 'Les codes de récupération vous permettent de retrouver l\'accès si vous perdez votre appareil A2F. Stockez-les dans un gestionnaire de mots de passe sécurisé.',
                'view_button' => 'Voir les codes de récupération',
                'hide_button' => 'Masquer les codes de récupération',
                'regenerate_button' => 'Régénérer les codes',
                'regenerated' => 'Les codes de récupération ont été régénérés.',
                'load_error' => 'Impossible de charger les codes de récupération. Veuillez réessayer.',
                'warning' => 'Chaque code de récupération peut être utilisé une seule fois pour accéder à votre compte et sera supprimé après utilisation. Si vous en avez besoin de plus, cliquez sur Régénérer les codes ci-dessus.',
            ],
        ],
        'appearance' => [
            'title' => 'Apparence',
            'description' => 'Mettez à jour les paramètres d\'apparence de votre compte',
            'light' => 'Clair',
            'dark' => 'Sombre',
        ],
        'delete_account' => [
            'title' => 'Supprimer le compte',
            'description' => 'Supprimez votre compte et toutes ses ressources',
            'button' => 'Supprimer le compte',
            'modal_title' => 'Êtes-vous sûr de vouloir supprimer votre compte ?',
            'modal_description' => 'Une fois votre compte supprimé, toutes ses ressources et données seront définitivement supprimées. Veuillez entrer votre mot de passe pour confirmer que vous souhaitez supprimer définitivement votre compte.',
            'password_label' => 'Mot de passe',
            'success' => 'Votre compte a été supprimé.',
        ],
    ],

    // Preferences
    'preferences' => [
        'theme' => 'Thème',
        'theme_light' => 'Clair',
        'theme_dark' => 'Sombre',
        'locale' => 'Langue',
    ],

    // Page Titles
    'pages' => [
        'dashboard' => 'Tableau de bord',
        'notifications' => 'Notifications',
        'settings' => [
            'profile' => 'Paramètres du profil',
            'password' => 'Paramètres du mot de passe',
            'two_factor' => 'Authentification à deux facteurs',
        ],
    ],

    // Notifications
    'notifications' => [
        'dropdown' => [
            'title' => 'Notifications',
        ],
        'mark_all_read' => 'Tout marquer comme lu',
        'clear_all' => 'Effacer toutes les notifications',
        'see_previous' => 'Voir les notifications précédentes',
        'delete' => 'Supprimer',
        'view' => 'Voir',
        'view_all' => 'Voir toutes les notifications',
        'unread' => 'Non lu',
        'empty' => 'Vous n\'avez aucune notification.',
        'dismiss' => 'Fermer la notification',
    ],

    // Common UI Elements
    'common' => [
        'saved' => 'Enregistré.',
    ],

    // Modals
    'modals' => [
        'confirm' => [
            'title' => 'Confirmer l\'action',
            'message' => 'Êtes-vous sûr de vouloir continuer?',
        ],
    ],
];
