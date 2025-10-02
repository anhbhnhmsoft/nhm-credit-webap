<?php

namespace App\Utils\Constants;

enum RoleUser: int
{
    case ADMIN = 1;
    case CUSTOMER = 2;

    public static function getOptions(): array
    {
        return [
            self::ADMIN->value => self::ADMIN->label(),
            self::CUSTOMER->value => self::CUSTOMER->label(),
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Quản trị viên',
            self::CUSTOMER => 'Khách hàng',
        };
    }

    public static function checkCanAccessAdminPanel($role): bool
    {
        return in_array($role, [
            self::ADMIN->value,
        ]);
    }
}
