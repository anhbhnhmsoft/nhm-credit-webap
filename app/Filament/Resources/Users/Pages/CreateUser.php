<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Utils\Constants\RoleUser;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'Tạo người dùng mới';

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Người dùng',
            '' => 'Tạo người dùng mới',
        ];
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Tạo mới');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Tạo và tạo thêm');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Hủy');
    }

    protected function handleRecordCreation(array $data): Model
    {
        if(!empty($data['new_password'])) {
            $plainPassword = $data['new_password'];
            $data['password'] = Hash::make($plainPassword);
            $data['hash_encrypt'] = Crypt::encryptString($plainPassword);
        }
        
        if (!isset($data['role']) || empty($data['role'])) {
            $data['role'] = RoleUser::CUSTOMER->value;
        }
        
        $record = static::getModel()::create($data);

        session()->flash('prompt_add_bank_account', true);

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
