<?php

namespace App\Filament\Resources\UserLoans;

use App\Filament\Resources\UserLoans\Pages\CreateUserLoans;
use App\Filament\Resources\UserLoans\Pages\EditUserLoans;
use App\Filament\Resources\UserLoans\Pages\ListUserLoans;
use App\Filament\Resources\UserLoans\Schemas\UserLoansForm;
use App\Filament\Resources\UserLoans\Tables\UserLoansTable;
use App\Models\UserLoan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserLoansResource extends Resource
{
    protected static ?string $model = UserLoan::class;

    protected static ?string $navigationLabel = 'Khoản vay';

    protected static ?string $modelLabel = 'Khoản vay';

    protected static ?string $pluralModelLabel = 'Khoản vay';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return UserLoansForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserLoansTable::configure($table);
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
            'index' => ListUserLoans::route('/'),
            'create' => CreateUserLoans::route('/create'),
            'edit' => EditUserLoans::route('/{record}/edit'),
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
