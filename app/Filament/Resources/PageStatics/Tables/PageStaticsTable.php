<?php

namespace App\Filament\Resources\PageStatics\Tables;

use App\Utils\Constants\CommonStatus;
use App\Utils\Constants\PageStaticType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class PageStaticsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                BadgeColumn::make('type')
                    ->formatStateUsing(fn ($state) => PageStaticType::getOptions()[$state] ?? $state)
                    ->colors([
                        'success' => PageStaticType::ABOUT->value,
                        'danger' => PageStaticType::FIXED->value,
                    ])
                    ->sortable(),
                BadgeColumn::make('status')
                    ->formatStateUsing(fn ($state) => CommonStatus::getOptions()[$state] ?? $state)
                    ->colors([
                        'success' => CommonStatus::ACTIVE->value,
                        'danger' => CommonStatus::INACTIVE->value,
                    ])
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->visible(fn ($record) => $record->type !== PageStaticType::FIXED->value)
                    ->requiresConfirmation(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function ($records) {
                            $allowedRecords = $records->filter(function ($record) {
                                return $record->type !== PageStaticType::FIXED->value;
                            });
                            if ($allowedRecords->count() > 0) {
                                $allowedRecords->each->delete();
                            }
                        }),
                    ForceDeleteBulkAction::make()
                        ->action(function ($records) {
                            $allowedRecords = $records->filter(function ($record) {
                                return $record->type !== PageStaticType::FIXED->value;
                            });
                            if ($allowedRecords->count() > 0) {
                                $allowedRecords->each->forceDelete();
                            }
                        }),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
