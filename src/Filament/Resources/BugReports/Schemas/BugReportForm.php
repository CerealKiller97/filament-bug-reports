<?php

declare(strict_types=1);

namespace CerealKiller97\FilamentBugReports\Filament\Resources\BugReports\Schemas;

use CerealKiller97\FilamentBugReports\BugReportsPlugin;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class BugReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label(__('bug-reports::bug-reports.form.title'))
                ->placeholder(__('bug-reports::bug-reports.form.title_placeholder'))
                ->required()
                ->maxLength(255),

            Repeater::make('steps')
                ->label(__('bug-reports::bug-reports.form.steps'))
                ->helperText(__('bug-reports::bug-reports.form.steps_helper'))
                ->addActionLabel(__('bug-reports::bug-reports.form.add_step'))
                ->simple(
                    TextInput::make('text')
                        ->placeholder(__('bug-reports::bug-reports.form.step_placeholder'))
                        ->required(),
                )
                ->reorderable()
                ->defaultItems(1)
                ->minItems(1),

            FileUpload::make('screenshot_path')
                ->label(__('bug-reports::bug-reports.form.screenshot'))
                ->helperText(__('bug-reports::bug-reports.form.screenshot_helper'))
                ->image()
                ->imageEditor()
                ->disk(config()->string('bug-reports.screenshot.disk', 'public'))
                ->directory(config()->string('bug-reports.screenshot.directory', 'bug-reports'))
                ->downloadable()
                ->openable()
                ->maxSize(config()->integer('bug-reports.screenshot.max_size', 5120)),

            // Hidden but still submitted; CreateBugReport also sets these
            // authoritatively server-side.
            Hidden::make('role')
                ->default(fn (): string => BugReportsPlugin::get()->resolveReporterRole(auth()->user())),

            Hidden::make('app_version')
                ->default(fn (): string => self::appVersion()),
        ]);
    }

    public static function appVersion(): string
    {
        $configured = config()->string('bug-reports.app_version', '');

        return $configured !== ''
            ? $configured
            : config()->string('app.version', 'dev');
    }
}
