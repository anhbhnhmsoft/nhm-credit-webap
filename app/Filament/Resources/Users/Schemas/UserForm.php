<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use App\Utils\Constants\RoleUser;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Thông tin cơ bản')
                        ->description('Thông tin cá nhân và xác thực')
                        ->schema([
                        TextInput::make('name')
                            ->label('Tên người dùng')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique()
                            ->validationMessages([
                                'required' => 'Vui lòng nhập email.',
                                'email' => 'Email không hợp lệ.',
                                'unique' => 'Email đã tồn tại.',
                            ]),
                        TextInput::make('phone')
                            ->label('Số điện thoại')
                            ->tel()
                            ->required()
                            ->unique()
                            ->validationMessages([
                                'required' => 'Vui lòng nhập số điện thoại.',
                                'unique' => 'Số điện thoại đã tồn tại.',
                            ]),
                        TextInput::make('address')
                            ->required()
                            ->validationMessages([
                                'required' => 'Vui lòng nhập địa chỉ.',
                            ])
                            ->label('Địa chỉ'),
                        Textarea::make('introduce')
                            ->label('Giới thiệu')
                            ->columnSpanFull(),
                        FileUpload::make('avatar_path')
                            ->label('Ảnh đại diện')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('avatars')
                            ->visibility('public')
                            ->nullable()
                            ->columnSpanFull(),
                        ])
                        ->columns(2),
                    
                    Step::make('Thông tin CMND/CCCD')
                        ->description('Xác thực danh tính')
                        ->schema([
                        TextInput::make('name_card')
                            ->label('Tên trên CMND/CCCD')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('number_card')
                            ->label('Số CMND/CCCD')
                            ->required()
                            ->unique()
                            ->maxLength(20)
                            ->validationMessages([
                                'required' => 'Vui lòng nhập số CMND/CCCD.',
                                'unique' => 'Số CMND/CCCD đã được sử dụng.',
                            ]),
                        FileUpload::make('front_image_card')
                            ->label('Ảnh mặt trước CMND/CCCD')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('id-cards')
                            ->visibility('public')
                            ->required()
                            ->columnSpanFull(),
                        FileUpload::make('back_image_card')
                            ->label('Ảnh mặt sau CMND/CCCD')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('id-cards')
                            ->visibility('public')
                            ->required()
                            ->columnSpanFull(),
                        ])
                        ->columns(2),
                    
                    Step::make('Mật khẩu & Xác thực')
                        ->description('Bảo mật tài khoản')
                        ->schema([
                        Fieldset::make('Password')
                            ->label('Mật khẩu')
                            ->schema([
                                TextInput::make('password')
                                    ->label('Mật khẩu hiện tại')
                                    ->readOnly()
                                    ->columnSpanFull()
                                    ->placeholder('●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●')
                                    ->disabled(fn($get, $context) => $get('showChangePassword') !== true || $context === 'create')
                                    ->default(fn($record) => $record?->password ?? '')
                                    ->visible(fn($get, $record) => $record !== null && $get('showChangePassword') !== true)
                                    ->suffixAction(
                                        Action::make('changePassword')
                                            ->label('Thay đổi mật khẩu')
                                            ->icon('heroicon-o-pencil')
                                            ->action(function ($get, $set) {
                                                $set('showChangePassword', true);
                                            })
                                    ),
                                TextInput::make('new_password')
                                    ->label('Mật khẩu mới')
                                    ->password()
                                    ->visible(fn($get, $record) => $record === null || $get('showChangePassword') === true)
                                    ->required(fn($record) => $record === null)
                                    ->dehydrateStateUsing(fn($state) => !empty($state) ? bcrypt($state) : null)
                                    ->dehydrated(fn($state) => filled($state))
                                    ->maxLength(255),
                                TextInput::make('new_password_confirmation')
                                    ->label('Xác nhận mật khẩu mới')
                                    ->password()
                                    ->visible(fn($get, $record) => $record === null || $get('showChangePassword') === true)
                                    ->same('new_password')
                                    ->required(fn($record) => $record === null),
                                Hidden::make('showChangePassword')->default(false),
                            ])
                            ->columnSpanFull(),
                        DateTimePicker::make('email_verified_at')
                            ->label('Ngày xác thực email'),
                        DateTimePicker::make('phone_verified_at')
                            ->label('Ngày xác thực số điện thoại'),
                        ])
                        ->columns(2),
                ])
            ])->columns(null);
    }
}
