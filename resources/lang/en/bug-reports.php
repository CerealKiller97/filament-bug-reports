<?php

declare(strict_types=1);

return [
    'navigation_label' => 'Bug reports',
    'model_label' => 'Bug report',
    'plural_model_label' => 'Bug reports',
    'report_button' => 'Report a bug',

    'priority' => [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent',
    ],

    'create' => [
        'title' => 'Report a bug',
        'breadcrumb' => 'Report',
        'actions' => [
            'create' => 'Report',
            'create_another' => 'Report and add another',
        ],
    ],

    'form' => [
        'title' => 'What is wrong?',
        'title_placeholder' => 'Briefly describe the problem, e.g. "I can\'t save a ride"',
        'steps' => 'How did it happen? (step by step)',
        'steps_helper' => 'Add the steps you took before the bug appeared.',
        'step_placeholder' => 'e.g. I opened the "Rides" page',
        'add_step' => 'Add step',
        'screenshot' => 'Screenshot (optional)',
        'screenshot_helper' => 'A screenshot helps us understand the problem fastest.',
        'priority' => 'Priority',
        'priority_helper' => 'How urgent is this bug?',
    ],

    'table' => [
        'problem' => 'Problem',
        'priority' => 'Priority',
        'github' => 'GitHub',
        'state' => 'State',
        'state_pending' => 'In progress',
        'state_resolved' => 'Resolved',
        'screenshot' => 'Screenshot',
        'version' => 'Version',
        'reported_by' => 'Reported by',
        'reported_at' => 'Reported at',
        'empty' => 'No bug reports',
    ],

    'filters' => [
        'priority' => 'Priority',
        'validated' => 'Real bugs',
        'validated_true' => 'Marked as real',
        'validated_false' => 'Not handled yet',
    ],

    'actions' => [
        'mark_as_real' => 'Mark as real',
        'mark_as_real_heading' => 'Mark as a real bug?',
        'mark_as_real_description' => 'A GitHub issue will be created with the bug details and the priority below.',
        'mark_as_real_submit' => 'Create issue',
        'delete' => 'Delete',
        'sync' => 'Sync with GitHub',
        'open_issue' => 'Open issue',
    ],

    'notifications' => [
        'reported' => 'Thanks! The bug has been reported.',
        'issue_created' => 'GitHub issue created.',
        'issue_created_body' => 'Issue #:number',
        'issue_failed' => 'Could not create the GitHub issue.',
        'deleted' => 'Bug report deleted.',
        'synced' => 'Synced with GitHub.',
        'synced_body' => 'Reports updated: :count.',
        'sync_failed' => 'Sync failed.',
    ],

    'issue' => [
        'not_configured' => 'GitHub is not configured (bug-reports.github.token / repository).',
        'details' => 'Details',
        'reported_by' => 'Reported by',
        'priority' => 'Priority',
        'app_version' => 'App version',
        'reported_at' => 'Reported at',
        'steps' => 'Steps to reproduce',
        'no_steps' => '_No steps provided._',
        'screenshot' => 'Screenshot',
        'no_screenshot' => '_No screenshot._',
        'footer' => '_Automatically created from in-app bug report #:id._',
        'unknown_reporter' => 'Unknown',
    ],
];
