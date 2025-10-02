<?php

namespace App\Utils\Constants;

enum LoanStatus: int
{
    case PENDING = 1;  // chờ duyệt
    case APPROVED = 2; // đã duyệt
    case REJECTED = 3; // từ chối
    case ACTIVE = 4; // đang vay
    case COMPLETED = 5; // hoàn thành
    case OVERDUE = 6; // quá hạn

    public function name(): string
    {
        return match($this) {
            self::PENDING => 'Chờ duyệt',
            self::APPROVED => 'Đã duyệt',
            self::REJECTED => 'Từ chối',
            self::ACTIVE => 'Đang vay',
            self::COMPLETED => 'Hoàn thành',
            self::OVERDUE => 'Quá hạn',
        };
    }
}