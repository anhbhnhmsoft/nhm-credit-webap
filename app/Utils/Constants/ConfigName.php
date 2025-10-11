<?php

namespace App\Utils\Constants;

enum ConfigName: string
{
    case LOGO = 'LOGO';
    case ADMIN_ACCOUNT_BANK_NAME = 'ADMIN_ACCOUNT_BANK_NAME';
    case ADMIN_ACCOUNT_BANK_ACCOUNT = 'ADMIN_ACCOUNT_BANK_ACCOUNT';
    case QR_IMAGE = 'QR_IMAGE';
    case ADMIN_ACCOUNT_NAME_BANK = 'ADMIN_ACCOUNT_NAME_BANK';
}
