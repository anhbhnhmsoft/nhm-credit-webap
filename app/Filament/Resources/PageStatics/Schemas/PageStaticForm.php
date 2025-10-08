<?php

namespace App\Filament\Resources\PageStatics\Schemas;

use App\Utils\Constants\CommonStatus;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class PageStaticForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                RichEditor::make('content')
                    ->required()
                    ->columnSpanFull()
                    ->extraAttributes(['style' => 'min-height: 300px;']),
                TextInput::make('slug')
                    ->required(),
                Select::make('status')
                    ->required()
                    ->options(CommonStatus::getOptions())
                    ->default(CommonStatus::ACTIVE->value),
            ]);
    }
}
