<?php

declare(strict_types=1);

namespace CerealKiller97\FilamentBugReports\Filament\Resources;

use BackedEnum;
use CerealKiller97\FilamentBugReports\BugReportsPlugin;
use CerealKiller97\FilamentBugReports\Filament\Resources\BugReports\Pages\CreateBugReport;
use CerealKiller97\FilamentBugReports\Filament\Resources\BugReports\Pages\ListBugReports;
use CerealKiller97\FilamentBugReports\Filament\Resources\BugReports\Pages\ViewBugReport;
use CerealKiller97\FilamentBugReports\Filament\Resources\BugReports\Schemas\BugReportForm;
use CerealKiller97\FilamentBugReports\Filament\Resources\BugReports\Tables\BugReportsTable;
use CerealKiller97\FilamentBugReports\Models\BugReport;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BugReportResource extends Resource
{
    protected static ?string $model = BugReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBugAnt;

    public static function getModelLabel(): string
    {
        return (string) __('bug-reports::bug-reports.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return (string) __('bug-reports::bug-reports.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return (string) __('bug-reports::bug-reports.navigation_label');
    }

    public static function form(Schema $schema): Schema
    {
        return BugReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BugReportsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user');
    }

    /**
     * Every panel user can reach the resource — but only to report a bug via
     * the create page. Listing/viewing/triaging is gated to managers, and
     * enforced on the list page directly (Filament's ListRecords does not
     * authorize on its own).
     */
    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public static function canCreate(): bool
    {
        return BugReportsPlugin::get()->canReport(auth()->user());
    }

    public static function canViewAny(): bool
    {
        return BugReportsPlugin::get()->canManage(auth()->user());
    }

    public static function canView(Model $record): bool
    {
        return BugReportsPlugin::get()->canManage(auth()->user());
    }

    public static function canDelete(Model $record): bool
    {
        return BugReportsPlugin::get()->canManage(auth()->user());
    }

    public static function canDeleteAny(): bool
    {
        return BugReportsPlugin::get()->canManage(auth()->user());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return BugReportsPlugin::get()->canManage(auth()->user());
    }

    /**
     * @return array<string, mixed>
     */
    public static function getRelations(): array
    {
        return [];
    }

    /**
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListBugReports::route('/'),
            'create' => CreateBugReport::route('/create'),
            'view' => ViewBugReport::route('/{record}'),
        ];
    }
}
