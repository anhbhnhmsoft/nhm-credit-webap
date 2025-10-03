<?php

namespace App\Services;

class LoanCalculationService
{
    public function calcAmount($principalAmount, $interestRate, $termMonths, $serviceFee = 0): float
    {
        if (!$principalAmount || !$interestRate || !$termMonths) {
            return 0;
        }

        $principalAmount = (float) $principalAmount;
        $interestRate = (float) $interestRate;
        $termMonths = (int) $termMonths;
        $serviceFee = (float) ($serviceFee ?? 0);

        if ($principalAmount <= 0 || $interestRate < 0 || $termMonths <= 0) {
            return 0;
        }

        $monthlyInterestRate = $interestRate / 100 / 12;
        
        $monthlyPayment = $this->calcMonthlyPayment($principalAmount, $monthlyInterestRate, $termMonths);
        
        $totalAmount = $monthlyPayment * $termMonths + $serviceFee;
        
        return round($totalAmount, 2);
    }

    public function calcMonthlyPayment($principal, $monthlyRate, $months): float
    {
        if ($monthlyRate == 0) {
            return $principal / $months;
        }

        // Công thức trả góp: PMT = P * [r(1+r)^n] / [(1+r)^n - 1]
        $numerator = $monthlyRate * pow(1 + $monthlyRate, $months);
        $denominator = pow(1 + $monthlyRate, $months) - 1;
        
        return $principal * ($numerator / $denominator);
    }

    public function fillTotalAmount(array $data): array
    {
        if (isset($data['principal_amount']) && 
            isset($data['interest_rate_year']) && 
            isset($data['term_months'])) {
            
            $totalDueAmount = $this->calcAmount(
                $data['principal_amount'],
                $data['interest_rate_year'],
                $data['term_months'],
                $data['service_fee_amount'] ?? 0
            );
            
            $data['total_due_amount'] = $totalDueAmount;
        }

        return $data;
    }
}
