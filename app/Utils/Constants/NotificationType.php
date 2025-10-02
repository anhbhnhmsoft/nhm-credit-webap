<?php
namespace App\Utils\Constants;

enum NotificationType: int
{
    case SYSTEM = 1;    // thông báo hệ thống
    case REMINDER = 2;   // nhắc nợ, đến hạn
}