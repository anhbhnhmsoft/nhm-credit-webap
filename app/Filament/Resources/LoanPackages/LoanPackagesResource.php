<?php

namespace App\Filament\Resources\LoanPackages;

use App\Filament\Resources\LoanPackages\Pages\CreateLoanPackages;
use App\Filament\Resources\LoanPackages\Pages\EditLoanPackages;
use App\Filament\Resources\LoanPackages\Pages\ListLoanPackages;
use App\Filament\Resources\LoanPackages\Schemas\LoanPackagesForm;
use App\Filament\Resources\LoanPackages\Tables\LoanPackagesTable;
use App\Models\LoanPackage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LoanPackagesResource extends Resource
{
    protected static ?string $model = LoanPackage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return LoanPackagesForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LoanPackagesTable::configure($table);
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
            'index' => ListLoanPackages::route('/'),
            'create' => CreateLoanPackages::route('/create'),
            'edit' => EditLoanPackages::route('/{record}/edit'),
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
