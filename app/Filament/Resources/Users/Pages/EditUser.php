<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'Sửa người dùng';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Người dùng',
            '' => 'Sửa người dùng',
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label('Lưu thay đổi');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Hủy');
    }

    protected function afterFill(): void
    {
        // Nếu vừa tạo xong và chưa có tài khoản ngân hàng thì hiển thị banner nhắc
        if (session()->pull('prompt_add_bank_account', false)) {
            Notification::make()
                ->title('Người dùng vừa được tạo')
                ->body('Bạn nên thêm tài khoản ngân hàng cho người dùng này ngay bây giờ.')
                ->success()
                ->persistent()
                ->actions([
                    Action::make('add_bank')
                        ->label('Thêm tài khoản ngân hàng')
                        ->url(fn () => url('/admin/users/'.$this->record->getKey().'/edit?tab=bank-accounts'))
                        ->button(),
                ])
                ->send();
        }
    }
}