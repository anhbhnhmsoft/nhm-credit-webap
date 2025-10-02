<?php

namespace App\Utils\Constants;

enum PaymentStatus: int
{
    case PENDING = 1; // chờ xử lý
    case SUCCESS = 2; // thành công
    case FAILED = 3; // thất bại

    public function name(): string
    {
        return match($this) {
            self::PENDING => 'Chờ xử lý',
            self::SUCCESS => 'Thành công',
            self::FAILED => 'Thất bại',
        };
    }
}