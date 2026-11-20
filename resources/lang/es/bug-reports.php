<?php

declare(strict_types=1);

return [
    'navigation_label' => 'Informes de errores',
    'model_label' => 'Informe de error',
    'plural_model_label' => 'Informes de errores',
    'report_button' => 'Informar de un error',

    'priority' => [
        'low' => 'Baja',
        'medium' => 'Media',
        'high' => 'Alta',
        'urgent' => 'Urgente',
    ],

    'create' => [
        'title' => 'Informar de un error',
        'breadcrumb' => 'Informar',
        'actions' => [
            'create' => 'Informar',
            'create_another' => 'Informar y añadir otro',
        ],
    ],

    'form' => [
        'title' => '¿Qué está mal?',
        'title_placeholder' => 'Describe brevemente el problema, p. ej. "No puedo guardar un viaje"',
        'steps' => '¿Cómo ocurrió? (paso a paso)',
        'steps_helper' => 'Añade los pasos que seguiste antes de que apareciera el error.',
        'step_placeholder' => 'p. ej. Abrí la página "Viajes"',
        'add_step' => 'Añadir paso',
        'screenshot' => 'Captura de pantalla (opcional)',
        'screenshot_helper' => 'Una captura de pantalla nos ayuda a entender el problema más rápido.',
        'priority' => 'Prioridad',
        'priority_helper' => '¿Cómo de urgente es este error?',
    ],

    'table' => [
        'problem' => 'Problema',
        'priority' => 'Prioridad',
        'github' => 'GitHub',
        'state' => 'Estado',
        'state_pending' => 'En curso',
        'state_resolved' => 'Resuelto',
        'screenshot' => 'Captura de pantalla',
        'version' => 'Versión',
        'reported_by' => 'Informado por',
        'reported_at' => 'Informado el',
        'empty' => 'No hay informes de errores',
        'untriaged' => 'Sin triar',
    ],

    'stats' => [
        'untriaged' => 'Pendientes de triaje',
        'untriaged_description' => 'Informados, aún sin revisar',
        'burning' => 'Urgentes y altas',
        'burning_description' => 'Abiertos, y duelen',
        'in_progress' => 'En curso',
        'in_progress_description' => 'Abiertos en GitHub',
        'resolved' => 'Resueltos',
        'resolved_description' => 'Issue cerrado',
    ],

    'filters' => [
        'priority' => 'Prioridad',
        'validated' => 'Errores reales',
        'validated_true' => 'Marcados como reales',
        'validated_false' => 'Aún sin tratar',
    ],

    'actions' => [
        'mark_as_real' => 'Marcar como real',
        'mark_as_real_heading' => '¿Marcar como un error real?',
        'mark_as_real_description' => 'Se creará un issue de GitHub con los detalles del error y la prioridad de abajo.',
        'mark_as_real_submit' => 'Crear issue',
        'delete' => 'Eliminar',
        'sync' => 'Sincronizar con GitHub',
        'open_issue' => 'Abrir issue',
    ],

    'notifications' => [
        'reported' => '¡Gracias! El error se ha informado.',
        'issue_created' => 'Issue de GitHub creado.',
        'issue_created_body' => 'Issue #:number',
        'issue_failed' => 'No se pudo crear el issue de GitHub.',
        'deleted' => 'Informe de error eliminado.',
        'synced' => 'Sincronizado con GitHub.',
        'synced_body' => 'Informes actualizados: :count.',
        'sync_failed' => 'La sincronización falló.',
    ],

    'issue' => [
        'not_configured' => 'GitHub no está configurado (bug-reports.github.token / repository).',
        'details' => 'Detalles',
        'reported_by' => 'Informado por',
        'priority' => 'Prioridad',
        'app_version' => 'Versión de la app',
        'reported_at' => 'Informado el',
        'steps' => 'Pasos para reproducir',
        'no_steps' => '_No se indicaron pasos._',
        'screenshot' => 'Captura de pantalla',
        'no_screenshot' => '_Sin captura de pantalla._',
        'footer' => '_Creado automáticamente a partir del informe de error en la app #:id._',
        'unknown_reporter' => 'Desconocido',
    ],
];
