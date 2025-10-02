<?php

namespace App\Utils\Constants;

enum PaymentDirection: int
{
    case IN = 1;  // tiền vào hệ thống (khách trả, nạp)
    case OUT = 2; // tiền ra hệ thống (hoàn, điều chỉnh giảm)

    public function name(): string
    {
        return match($this) {
            self::IN => 'Thu vào',
            self::OUT => 'Chi ra',
        };
    }
}