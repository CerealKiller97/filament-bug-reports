<?php

declare(strict_types=1);

return [
    'navigation_label' => 'Prijave grešaka',
    'model_label' => 'Prijava greške',
    'plural_model_label' => 'Prijave grešaka',
    'report_button' => 'Prijavi grešku',

    'priority' => [
        'low' => 'Nizak',
        'medium' => 'Srednji',
        'high' => 'Visok',
        'urgent' => 'Hitan',
    ],

    'create' => [
        'title' => 'Prijavi grešku',
        'breadcrumb' => 'Prijavi',
        'actions' => [
            'create' => 'Prijavi',
            'create_another' => 'Prijavi i dodaj još jednu',
        ],
    ],

    'form' => [
        'title' => 'Šta ne radi?',
        'title_placeholder' => 'Ukratko opišite problem, npr. "Ne mogu da sačuvam vožnju"',
        'steps' => 'Kako se greška desila? (korak po korak)',
        'steps_helper' => 'Dodajte korake koje ste uradili pre nego što se greška pojavila.',
        'step_placeholder' => 'Npr. Otvorio sam stranicu "Vožnje"',
        'add_step' => 'Dodaj korak',
        'screenshot' => 'Snimak ekrana (opciono)',
        'screenshot_helper' => 'Slika ekrana najbrže pomaže da razumemo problem.',
        'priority' => 'Prioritet',
        'priority_helper' => 'Koliko je ova greška hitna?',
    ],

    'table' => [
        'problem' => 'Problem',
        'priority' => 'Prioritet',
        'github' => 'GitHub',
        'state' => 'Stanje',
        'state_pending' => 'U toku',
        'state_resolved' => 'Rešeno',
        'screenshot' => 'Snimak',
        'version' => 'Verzija',
        'reported_by' => 'Prijavio',
        'reported_at' => 'Prijavljeno',
        'empty' => 'Nema prijavljenih grešaka',
    ],

    'filters' => [
        'priority' => 'Prioritet',
        'validated' => 'Stvarne greške',
        'validated_true' => 'Označene kao stvarne',
        'validated_false' => 'Još nisu obrađene',
    ],

    'actions' => [
        'mark_as_real' => 'Označi kao stvarni',
        'mark_as_real_heading' => 'Označi kao stvarnu grešku?',
        'mark_as_real_description' => 'Napraviće se GitHub issue sa opisom greške i prioritetom ispod.',
        'mark_as_real_submit' => 'Napravi issue',
        'delete' => 'Obriši',
        'sync' => 'Sinhronizuj sa GitHub-om',
        'open_issue' => 'Otvori issue',
    ],

    'notifications' => [
        'reported' => 'Hvala! Greška je uspešno prijavljena.',
        'issue_created' => 'GitHub issue je napravljen.',
        'issue_created_body' => 'Issue #:number',
        'issue_failed' => 'Neuspešno kreiranje GitHub issue-a.',
        'deleted' => 'Prijava greške je obrisana.',
        'synced' => 'Sinhronizovano sa GitHub-om.',
        'synced_body' => 'Ažurirano prijava: :count.',
        'sync_failed' => 'Sinhronizacija nije uspela.',
    ],

    'issue' => [
        'not_configured' => 'GitHub integracija nije podešena (bug-reports.github.token / repository).',
        'details' => 'Detalji',
        'reported_by' => 'Prijavio',
        'priority' => 'Prioritet',
        'app_version' => 'Verzija aplikacije',
        'reported_at' => 'Prijavljeno',
        'steps' => 'Koraci za reprodukciju',
        'no_steps' => '_Nisu navedeni koraci._',
        'screenshot' => 'Snimak ekrana',
        'no_screenshot' => '_Nema snimka ekrana._',
        'footer' => '_Automatski kreirano iz prijave greške #:id._',
        'unknown_reporter' => 'Nepoznato',
    ],
];
