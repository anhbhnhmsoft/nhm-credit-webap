<?php

namespace App\Filament\Resources\PageStatics\Pages;

use App\Filament\Resources\PageStatics\PageStaticResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPageStatics extends ListRecords
{
    protected static string $resource = PageStaticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
