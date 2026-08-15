<?php

declare(strict_types=1);

return [

    'navigation_label' => 'Bugmeldungen',
    'model_label' => 'Bugmeldung',
    'plural_model_label' => 'Bugmeldungen',
    'report_button' => 'Melden Sie einen Bug',

    'priority' => [
        'low' => 'Niedrig',
        'medium' => 'Mittel',
        'high' => 'Kritisch',
        'urgent' => 'Dringend',
    ],

    'create' => [
        'title' => 'Melden Sie einen Bug',
        'breadcrumb' => 'Melden',
        'actions' => [
            'create' => 'Melden',
            'create_another' => 'Melden und weiteren Bug hinzufügen.',
        ],
    ],

    'form' => [
        'title' => 'Was ist schiefgelaufen?',
        'title_placeholder' => 'Beschreiben Sie kurz das Problem, z.B "Ich kann keinen Ride speichern"',
        'steps' => 'Wie ist es passiert? (Schritt für Schritt)',
        'steps_helper' => 'Fügen Sie die Schritte hinzu, die Sie unternommen haben bevor der Bug erschienen ist.',
        'step_placeholder' => 'z.B: Ich habe die Rides-Seite geöffnet',
        'add_step' => 'Schritt hinzufügen',
        'screenshot' => 'Screenshot (optional)',
        'screenshot_helper' => 'Ein Screenshot hilft uns schnell, das Problem zu verstehen.',
        'priority' => 'Priorität',
        'priority_helper' => 'Wie dringend ist dieser Bug?',
    ],

    'table' => [
        'problem' => 'Problem',
        'priority' => 'Priorität',
        'github' => 'GitHub',
        'state' => 'Status',
        'state_pending' => 'In Bearbeitung',
        'state_resolved' => 'Gelöst',
        'screenshot' => 'Screenshot',
        'version' => 'Version',
        'reported_by' => 'Gemeldet von',
        'reported_at' => 'Gemeldet am',
        'empty' => 'Keine Bugmeldungen',
        'untriaged' => 'Noch nicht geprüft',
    ],

    'stats' => [
        'untriaged' => 'Noch nicht geprüft',
        'untriaged_description' => 'Gemeldet, aber noch nicht angesehen',
        'burning' => 'Dringend und kritisch',
        'burning_description' => 'Offen und kritisch',
        'in_progress' => 'In Bearbeitung',
        'in_progress_description' => 'Offen auf GitHub',
        'resolved' => 'Geschlossen',
        'resolved_description' => 'Issue geschlossen',
    ],

    'filters' => [
        'priority' => 'Priorität',
        'validated' => 'Bestätigte Bugs',
        'validated_true' => 'Markiert als bestätigt',
        'validated_false' => 'Noch nicht behandelt',
    ],

    'actions' => [
        'mark_as_real' => 'Als bestätigt markieren',
        'mark_as_real_heading' => 'Wollen Sie den Bug als bestätigt markieren?',
        'mark_as_real_description' => 'Es wird ein GitHub Issue erstellt mit den Details und der gegebenen Priorität.',
        'mark_as_real_submit' => 'Issue erstellen',
        'delete' => 'Löschen',
        'sync' => 'Mit GitHub synchronisieren',
        'open_issue' => 'Offenes Issue',
    ],

    'notifications' => [
        'reported' => 'Danke! Der Bug wurde gemeldet.',
        'issue_created' => 'GitHub Issue erstellt.',
        'issue_created_body' => 'Issue #:number',
        'issue_failed' => 'GitHub Issue konnte nicht erstellt werden.',
        'deleted' => 'Bugmeldung gelöscht.',
        'synced' => 'Mit GitHub synchronisiert.',
        'synced_body' => 'Meldungen wurden aktualisiert: :count.',
        'sync_failed' => 'Synchronisierung fehlgeschlagen.',
    ],

    'issue' => [
        'not_configured' => 'GitHub ist nicht konfiguriert (bug-reports.github.token / repository).',
        'details' => 'Details',
        'reported_by' => 'Gemeldet von',
        'priority' => 'Priorität',
        'app_version' => 'App-Version',
        'reported_at' => 'Gemeldet bei',
        'steps' => 'Schritte zum Reproduzieren',
        'no_steps' => '_Keine Schritte angegeben._',
        'screenshot' => 'Screenshot',
        'no_screenshot' => '_Kein Screenshot._',
        'footer' => '_Automatisch erstellt von In-App-Bug-Meldung #:id._',
        'unknown_reporter' => 'Unbekannt',
    ],
];
