<?php

namespace App\Traits;

use App\Models\LoanPackage;
use App\Models\User;
use App\Models\UserLoanLog;

trait UserLoanFormLogic
{
    protected function fillUserInfo(array $data): array
    {
        if (isset($data['user_id'])) {
            $user = User::find($data['user_id']);
            if ($user) {
                $data['user_name'] = $user->name;
                $data['user_phone'] = $user->phone;
                $data['user_email'] = $user->email;
                $data['user_address'] = $user->address;
                $data['front_image_card'] = $user->front_image_card;
                $data['back_image_card'] = $user->back_image_card;
                $data['id_card_selfie_path'] = $user->id_card_selfie_path;
            }
        }

        return $data;
    }

    protected function fillLoanPackageInfo(array $data): array
    {
        if (isset($data['loan_package_id'])) {
            $package = LoanPackage::find($data['loan_package_id']);
            if ($package) {
                $config = $package->config_loans;
                if (!is_array($config)) {
                    $config = json_decode($config ?? '{}', true) ?: [];
                }
                $data['interest_rate_year'] = $config['interest_rate'] ?? 0;
            }
        }

        return $data;
    }

    protected function fillAllRelatedInfo(array $data): array
    {
        $data = $this->fillUserInfo($data);
        $data = $this->fillLoanPackageInfo($data);
        $data = $this->fillPaidAmountInfo($data);

        return $data;
    }

    protected function fillPaidAmountInfo(array $data): array
    {
        if (!isset($data['total_paid_amount']) && isset($data['id']) && !empty($data['id'])) {
            $totalPaidFromLogs = UserLoanLog::where('user_loan_id', $data['id'])
                ->sum('total_paid');
            $data['total_paid_amount'] = $totalPaidFromLogs;
        }

        return $data;
    }
}
