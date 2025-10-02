<?php

namespace App\Filament\Resources\Users\Tables;

use App\Utils\Helper;
use App\Utils\Constants\RoleUser;
use App\Models\Bank;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar_path')
                ->label('Ảnh đại diện')
                ->circular()
                ->disk('public')
                ->state(function ($record) {
                    if (! empty($record->avatar_path)) {
                        return Helper::generateURLImagePath($record->avatar_path);
                    }
                    return Helper::generateUiAvatarUrl($record->name, $record->email);
                }),
                TextColumn::make('name')
                    ->label('Tên người dùng')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Số điện thoại')
                    ->searchable(),
                TextColumn::make('address')
                    ->label('Địa chỉ')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('add_bank_account')
                    ->label('Quản lý TKNH')
                    ->modalHeading('Quản lý tài khoản ngân hàng')
                    ->form(function ($record) {
                        $account = $record->userBankAccounts()->first();
                        $accountId = $account?->id;
                        return [
                            Select::make('bank_id')
                                ->label('Ngân hàng')
                                ->options(fn () => Bank::query()->orderBy('name')->pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->required()
                                ->default($account?->bank_id),
                            TextInput::make('account_number')
                                ->label('Số tài khoản')
                                ->required()
                                ->rule(function () use ($record, $accountId) {
                                    return Rule::unique('user_bank_accounts', 'account_number')
                                        ->where('user_id', $record->id)
                                        ->ignore($accountId);
                                })
                                ->maxLength(50)
                                ->default($account?->account_number),
                            TextInput::make('account_name')
                                ->label('Tên chủ tài khoản')
                                ->required()
                                ->maxLength(255)
                                ->default($account?->account_name),
                            Toggle::make('is_verified')
                                ->label('Xác thực tài khoản')
                                ->default((bool)($account?->is_verified ?? false)),
                        ];
                    })
                    ->action(function ($record, array $data) {
                        $account = $record->userBankAccounts()->first();
                        if ($account) {
                            $account->update([
                                'bank_id' => $data['bank_id'],
                                'account_number' => $data['account_number'],
                                'account_name' => $data['account_name'],
                                'is_verified' => (bool)($data['is_verified'] ?? false),
                            ]);
                            Notification::make()
                                ->title('Cập nhật tài khoản ngân hàng thành công')
                                ->success()
                                ->send();
                        } else {
                            $record->userBankAccounts()->create([
                                'bank_id' => $data['bank_id'],
                                'account_number' => $data['account_number'],
                                'account_name' => $data['account_name'],
                                'is_verified' => (bool)($data['is_verified'] ?? false),
                            ]);
                            Notification::make()
                                ->title('Thêm tài khoản ngân hàng thành công')
                                ->success()
                                ->send();
                        }
                    }),
                EditAction::make()
                    ->label('Sửa')
                    ->visible(fn() => Auth::user()->role === RoleUser::ADMIN->value
                ),
                DeleteAction::make()
                    ->label('Xóa')
                    ->visible(fn() => Auth::user()->role === RoleUser::ADMIN->value),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Xóa')
                        ->visible(fn() => Auth::user()->role === RoleUser::ADMIN->value),
                ]),
            ]);
    }
}




