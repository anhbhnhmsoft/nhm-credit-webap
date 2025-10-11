<?php

namespace App\Services;

class LoanCalculationService
{
    public function calcAmount($principalAmount, $interestRateTerm, $termMonths, $serviceFee = 0): float
    {
        if (!$principalAmount || !$interestRateTerm || !$termMonths) {
            return 0;
        }

        $principalAmount = (float) $principalAmount;
        $interestRateTerm = (float) $interestRateTerm;
        $termMonths = (int) $termMonths;
        $serviceFee = (float) ($serviceFee ?? 0);

        if ($principalAmount <= 0 || $interestRateTerm < 0 || $termMonths <= 0) {
            return 0;
        }

        $monthlyPrincipal = $principalAmount / $termMonths;
        $monthlyInterestFlat = ($principalAmount * ($interestRateTerm / 100)) / $termMonths;
        $monthlyPayment = $monthlyPrincipal + $monthlyInterestFlat;
        $totalAmount = ($monthlyPayment * $termMonths) + $serviceFee;
        
        return round($totalAmount, 2);
    }

    public function calcMonthlyPayment($principal, $monthlyRate, $months, $interestRateTermPercent = null): float
    {
        if ($months <= 0) return 0;
        if ($interestRateTermPercent !== null) {
            $monthlyPrincipal = $principal / $months;
            $monthlyInterestFlat = ($principal * ($interestRateTermPercent / 100)) / $months;
            return $monthlyPrincipal + $monthlyInterestFlat;
        }
        return $principal / $months;
    }

    public function fillTotalAmount(array $data): array
    {
        if (isset($data['principal_amount']) && 
            isset($data['interest_rate_year']) && 
            isset($data['term_months'])) {
            
            $totalDueAmount = $this->calcAmount(
                $data['principal_amount'],
                $data['interest_rate_year'],
                (int) $data['term_months'],
                $data['service_fee_amount'] ?? 0
            );
            
            $data['total_due_amount'] = $totalDueAmount;
        }

        return $data;
    }

    public function calculateLoanData(array $data): array
    {
        $data = $this->fillTotalAmount($data);
        
        if (isset($data['start_date']) && isset($data['term_months'])) {
            $startDate = is_string($data['start_date']) 
                ? \Carbon\Carbon::parse($data['start_date']) 
                : $data['start_date'];
            
            $termDays = $this->parseTermToDays($data['term_months']);
            $data['due_date'] = $startDate->copy()->addDays($termDays);
        }
        
        if (isset($data['principal_amount']) && 
            isset($data['interest_rate_year']) && 
            isset($data['term_months'])) {
            
            $data['total_due_amount'] = $data['principal_amount'] + $data['interest_rate_year'] + $data['service_fee_amount'];
        }
        
        return $data;
    }


    private function parseTermToDays(string $term): int
    {
        preg_match('/(\d+)/', $term, $matches);
        return isset($matches[1]) ? (int) $matches[1] : 0;
    }

    public function updateLoanCalculations($loan, array $changes): void
    {
        $needsRecalculation = false;
        $needsDateUpdate = false;
        
        $calculationFields = ['principal_amount', 'interest_rate_year', 'term_months', 'service_fee_amount'];
        foreach ($calculationFields as $field) {
            if (array_key_exists($field, $changes)) {
                $needsRecalculation = true;
                break;
            }
        }
        
        $dateFields = ['start_date', 'term_months'];
        foreach ($dateFields as $field) {
            if (array_key_exists($field, $changes)) {
                $needsDateUpdate = true;
                break;
            }
        }
        
        if ($needsRecalculation || $needsDateUpdate) {
            $data = $loan->toArray();
            $data = array_merge($data, $changes);
            $data = $this->calculateLoanData($data);
            
            $loan->total_due_amount = $data['total_due_amount'] ?? $loan->total_due_amount;
            $loan->monthly_payment = $data['monthly_payment'] ?? $loan->monthly_payment;
            
            if ($needsDateUpdate && isset($data['due_date'])) {
                $loan->due_date = $data['due_date'];
            }
        }
    }
}
