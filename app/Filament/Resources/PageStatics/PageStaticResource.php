<?php

namespace App\Filament\Resources\PageStatics;

use App\Filament\Resources\PageStatics\Pages\CreatePageStatic;
use App\Filament\Resources\PageStatics\Pages\EditPageStatic;
use App\Filament\Resources\PageStatics\Pages\ListPageStatics;
use App\Filament\Resources\PageStatics\Schemas\PageStaticForm;
use App\Filament\Resources\PageStatics\Tables\PageStaticsTable;
use App\Models\PageStatic;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PageStaticResource extends Resource
{
    protected static ?string $model = PageStatic::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Trang tĩnh';

    protected static ?string $modelLabel = 'Trang tĩnh';

    protected static ?string $pluralModelLabel = 'Trang tĩnh';

    public static function form(Schema $schema): Schema
    {
        return PageStaticForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PageStaticsTable::configure($table);
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
            'index' => ListPageStatics::route('/'),
            'create' => CreatePageStatic::route('/create'),
            'edit' => EditPageStatic::route('/{record}/edit'),
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
