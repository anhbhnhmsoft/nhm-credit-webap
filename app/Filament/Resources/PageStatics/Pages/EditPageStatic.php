<?php

namespace App\Filament\Resources\PageStatics\Pages;

use App\Filament\Resources\PageStatics\PageStaticResource;
use App\Utils\Constants\PageStaticType;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPageStatic extends EditRecord
{
    protected static string $resource = PageStaticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn ($record) => $record->type !== PageStaticType::FIXED->value)
                ->requiresConfirmation(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
