<?php

namespace App\Utils\Constants;

enum NotificationStatus: int
{
    case SENT = 1;
    case READ = 2;
    case FAILED = 3;
}