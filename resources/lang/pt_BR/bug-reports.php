<?php

declare(strict_types=1);

return [
    'navigation_label' => 'Relatos de bugs',
    'model_label' => 'Relato de bug',
    'plural_model_label' => 'Relatos de bugs',
    'report_button' => 'Relatar um bug',

    'priority' => [
        'low' => 'Baixa',
        'medium' => 'Média',
        'high' => 'Alta',
        'urgent' => 'Urgente',
    ],

    'create' => [
        'title' => 'Relatar um bug',
        'breadcrumb' => 'Relatar',
        'actions' => [
            'create' => 'Relatar',
            'create_another' => 'Relatar e adicionar outro',
        ],
    ],

    'form' => [
        'title' => 'O que está errado?',
        'title_placeholder' => 'Descreva o problema em poucas palavras, ex.: "Não consigo salvar uma corrida"',
        'steps' => 'Como aconteceu? (passo a passo)',
        'steps_helper' => 'Adicione os passos que você seguiu antes de o bug aparecer.',
        'step_placeholder' => 'ex.: Abri a página "Corridas"',
        'add_step' => 'Adicionar passo',
        'screenshot' => 'Captura de tela (opcional)',
        'screenshot_helper' => 'Uma captura de tela é o jeito mais rápido de entendermos o problema.',
        'priority' => 'Prioridade',
        'priority_helper' => 'Quão urgente é este bug?',
    ],

    'table' => [
        'problem' => 'Problema',
        'priority' => 'Prioridade',
        'github' => 'GitHub',
        'state' => 'Estado',
        'state_pending' => 'Em andamento',
        'state_resolved' => 'Resolvido',
        'screenshot' => 'Captura de tela',
        'version' => 'Versão',
        'reported_by' => 'Relatado por',
        'reported_at' => 'Relatado em',
        'empty' => 'Nenhum relato de bug',
        'untriaged' => 'Sem triagem',
    ],

    'stats' => [
        'untriaged' => 'Aguardando triagem',
        'untriaged_description' => 'Relatados, ainda não vistos',
        'burning' => 'Urgentes e altas',
        'burning_description' => 'Abertos, e doem',
        'in_progress' => 'Em andamento',
        'in_progress_description' => 'Abertos no GitHub',
        'resolved' => 'Resolvidos',
        'resolved_description' => 'Issue fechada',
    ],

    'filters' => [
        'priority' => 'Prioridade',
        'validated' => 'Bugs reais',
        'validated_true' => 'Marcados como reais',
        'validated_false' => 'Ainda não tratados',
    ],

    'actions' => [
        'mark_as_real' => 'Marcar como real',
        'mark_as_real_heading' => 'Marcar como um bug real?',
        'mark_as_real_description' => 'Uma issue do GitHub será criada com os detalhes do bug e a prioridade abaixo.',
        'mark_as_real_submit' => 'Criar issue',
        'delete' => 'Excluir',
        'sync' => 'Sincronizar com o GitHub',
        'open_issue' => 'Abrir issue',
    ],

    'notifications' => [
        'reported' => 'Obrigado! O bug foi relatado.',
        'issue_created' => 'Issue do GitHub criada.',
        'issue_created_body' => 'Issue #:number',
        'issue_failed' => 'Não foi possível criar a issue do GitHub.',
        'deleted' => 'Relato de bug excluído.',
        'synced' => 'Sincronizado com o GitHub.',
        'synced_body' => 'Relatos atualizados: :count.',
        'sync_failed' => 'A sincronização falhou.',
    ],

    'issue' => [
        'not_configured' => 'O GitHub não está configurado (bug-reports.github.token / repository).',
        'details' => 'Detalhes',
        'reported_by' => 'Relatado por',
        'priority' => 'Prioridade',
        'app_version' => 'Versão do app',
        'reported_at' => 'Relatado em',
        'steps' => 'Passos para reproduzir',
        'no_steps' => '_Nenhum passo informado._',
        'screenshot' => 'Captura de tela',
        'no_screenshot' => '_Sem captura de tela._',
        'footer' => '_Criado automaticamente a partir do relato de bug no app #:id._',
        'unknown_reporter' => 'Desconhecido',
    ],
];
