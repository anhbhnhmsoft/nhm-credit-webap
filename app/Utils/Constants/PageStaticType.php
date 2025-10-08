<?php

namespace App\Utils\Constants;

enum PageStaticType: int
{
    case FIXED = 99; // cố định, ko dc tạo thêm

    case ABOUT = 1;





    public function label(): string
    {
        return match ($this) {
            self::FIXED => 'Cố định',
            self::ABOUT => 'Trang khác',
        };
    }

    public static function getOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }
        return $options;
    }

}
