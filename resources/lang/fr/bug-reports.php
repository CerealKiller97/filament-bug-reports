<?php

declare(strict_types=1);

return [
    'navigation_label' => 'Rapports de bugs',
    'model_label' => 'Rapport de bug',
    'plural_model_label' => 'Rapports de bugs',
    'report_button' => 'Signaler un bug',

    'priority' => [
        'low' => 'Basse',
        'medium' => 'Moyenne',
        'high' => 'Élevée',
        'urgent' => 'Urgente',
    ],

    'create' => [
        'title' => 'Signaler un bug',
        'breadcrumb' => 'Signaler',
        'actions' => [
            'create' => 'Signaler',
            'create_another' => 'Signaler et en ajouter un autre',
        ],
    ],

    'form' => [
        'title' => 'Qu\'est-ce qui ne va pas ?',
        'title_placeholder' => 'Décrivez brièvement le problème, p. ex. « Je n\'arrive pas à enregistrer un trajet »',
        'steps' => 'Comment est-ce arrivé ? (étape par étape)',
        'steps_helper' => 'Ajoutez les étapes que vous avez suivies avant l\'apparition du bug.',
        'step_placeholder' => 'p. ex. J\'ai ouvert la page « Trajets »',
        'add_step' => 'Ajouter une étape',
        'screenshot' => 'Capture d\'écran (optionnel)',
        'screenshot_helper' => 'Une capture d\'écran nous aide à comprendre le problème au plus vite.',
        'priority' => 'Priorité',
        'priority_helper' => 'À quel point ce bug est-il urgent ?',
    ],

    'table' => [
        'problem' => 'Problème',
        'priority' => 'Priorité',
        'github' => 'GitHub',
        'state' => 'État',
        'state_pending' => 'En cours',
        'state_resolved' => 'Résolu',
        'screenshot' => 'Capture d\'écran',
        'version' => 'Version',
        'reported_by' => 'Signalé par',
        'reported_at' => 'Signalé le',
        'empty' => 'Aucun rapport de bug',
        'untriaged' => 'Non trié',
    ],

    'stats' => [
        'untriaged' => 'En attente de tri',
        'untriaged_description' => 'Signalés, pas encore examinés',
        'burning' => 'Urgents et élevés',
        'burning_description' => 'Ouverts, et ça fait mal',
        'in_progress' => 'En cours',
        'in_progress_description' => 'Ouverts sur GitHub',
        'resolved' => 'Résolus',
        'resolved_description' => 'Issue fermée',
    ],

    'filters' => [
        'priority' => 'Priorité',
        'validated' => 'Bugs réels',
        'validated_true' => 'Marqués comme réels',
        'validated_false' => 'Pas encore traités',
    ],

    'actions' => [
        'mark_as_real' => 'Marquer comme réel',
        'mark_as_real_heading' => 'Marquer comme un vrai bug ?',
        'mark_as_real_description' => 'Une issue GitHub sera créée avec les détails du bug et la priorité ci-dessous.',
        'mark_as_real_submit' => 'Créer l\'issue',
        'delete' => 'Supprimer',
        'sync' => 'Synchroniser avec GitHub',
        'open_issue' => 'Ouvrir l\'issue',
    ],

    'notifications' => [
        'reported' => 'Merci ! Le bug a été signalé.',
        'issue_created' => 'Issue GitHub créée.',
        'issue_created_body' => 'Issue #:number',
        'issue_failed' => 'Impossible de créer l\'issue GitHub.',
        'deleted' => 'Rapport de bug supprimé.',
        'synced' => 'Synchronisé avec GitHub.',
        'synced_body' => 'Rapports mis à jour : :count.',
        'sync_failed' => 'La synchronisation a échoué.',
    ],

    'issue' => [
        'not_configured' => 'GitHub n\'est pas configuré (bug-reports.github.token / repository).',
        'details' => 'Détails',
        'reported_by' => 'Signalé par',
        'priority' => 'Priorité',
        'app_version' => 'Version de l\'application',
        'reported_at' => 'Signalé le',
        'steps' => 'Étapes pour reproduire',
        'no_steps' => '_Aucune étape indiquée._',
        'screenshot' => 'Capture d\'écran',
        'no_screenshot' => '_Pas de capture d\'écran._',
        'footer' => '_Créé automatiquement à partir du rapport de bug dans l\'application #:id._',
        'unknown_reporter' => 'Inconnu',
    ],
];
