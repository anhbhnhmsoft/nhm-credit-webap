<?php

namespace App\Traits;

use App\Models\LoanPackage;
use App\Models\User;

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
                $data['term_months'] = $config['term_month'] ?? 0;
                $data['interest_rate_year'] = $config['interest_rate'] ?? 0;
            }
        }

        return $data;
    }

    protected function fillAllRelatedInfo(array $data): array
    {
        $data = $this->fillUserInfo($data);
        $data = $this->fillLoanPackageInfo($data);

        return $data;
    }
}
