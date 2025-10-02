<?php

namespace App\Utils\Constants;

enum LoanLogStatus: int
{
    case PENDING = 1;  // chờ thanh toán
    case PAID = 2; // đã thanh toán
    case PARTIAL = 3; // thanh toán một phần
    case OVERDUE = 4; // quá hạn

    public function name(): string
    {
        return match($this) {
            self::PENDING => 'Chờ thanh toán',
            self::PAID => 'Đã thanh toán',
            self::PARTIAL => 'Thanh toán một phần',
            self::OVERDUE => 'Quá hạn',
        };
    }
}