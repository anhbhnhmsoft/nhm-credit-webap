<?php

namespace App\Filament\Resources\UserLoanLogs;

use App\Filament\Resources\UserLoanLogs\Pages\CreateUserLoanLogs;
use App\Filament\Resources\UserLoanLogs\Pages\EditUserLoanLogs;
use App\Filament\Resources\UserLoanLogs\Pages\ListUserLoanLogs;
use App\Filament\Resources\UserLoanLogs\Schemas\UserLoanLogsForm;
use App\Filament\Resources\UserLoanLogs\Tables\UserLoanLogsTable;
use App\Models\UserLoanLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserLoanLogsResource extends Resource
{
    protected static ?string $model = UserLoanLog::class;

    protected static ?string $navigationLabel = 'Lịch trả nợ';

    protected static ?string $modelLabel = 'Lịch trả nợ';

    protected static ?string $pluralModelLabel = 'Lịch trả nợ';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return UserLoanLogsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserLoanLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUserLoanLogs::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
