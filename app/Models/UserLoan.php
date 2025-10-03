<?php

namespace App\Models;

use App\Utils\Helper;
use App\Utils\Constants\LoanStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserLoan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'loan_package_id',
        'principal_amount',
        'term_months',
        'interest_rate_year',
        'service_fee_amount',
        'disbursed_amount',
        'start_date',
        'due_date',
        'total_due_amount',
        'total_paid_amount',
        'status',
        'reject_reason',
    ];

    protected $casts = [
        'principal_amount' => 'decimal:2',
        'interest_rate_year' => 'decimal:2',
        'service_fee_amount' => 'decimal:2',
        'disbursed_amount' => 'decimal:2',
        'total_due_amount' => 'decimal:2',
        'total_paid_amount' => 'decimal:2',
        'start_date' => 'date',
        'due_date' => 'date',
        'term_months' => 'integer',
        'status' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Helper::getTimestampAsId();
            }
            
            if (!$model->total_due_amount && $model->principal_amount && $model->interest_rate_year && $model->term_months) {
                $model->total_due_amount = $model->calculateTotalDueAmount();
            }
            
            if (!$model->due_date && $model->start_date && $model->term_months) {
                $model->due_date = $model->start_date->addMonths($model->term_months);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty(['principal_amount', 'interest_rate_year', 'term_months', 'service_fee_amount'])) {
                $model->total_due_amount = $model->calculateTotalDueAmount();
            }
            
            if ($model->isDirty(['start_date', 'term_months'])) {
                $model->due_date = $model->start_date->addMonths($model->term_months);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function loanPackage(): BelongsTo
    {
        return $this->belongsTo(LoanPackage::class);
    }

    public function userLoanLogs(): HasMany
    {
        return $this->hasMany(UserLoanLog::class, 'user_loan_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'user_loan_id');
    }

    public function getStatusTextAttribute(): string
    {
        return LoanStatus::from($this->status)->name();
    }

    public function calculateTotalDueAmount(): float
    {
        if (!$this->principal_amount || !$this->interest_rate_year || !$this->term_months) {
            return 0;
        }

        $monthlyInterestRate = $this->interest_rate_year / 100 / 12;
        
        $monthlyPayment = $this->calculateMonthlyPayment($this->principal_amount, $monthlyInterestRate, $this->term_months);
        
        $totalAmount = $monthlyPayment * $this->term_months + ($this->service_fee_amount ?? 0);
        
        return $totalAmount;
    }

    public function calculateMonthlyPayment($principal, $monthlyRate, $months): float
    {
        if ($monthlyRate == 0) {
            return $principal / $months;
        }

        // Công thức trả góp: PMT = P * [r(1+r)^n] / [(1+r)^n - 1]
        $numerator = $monthlyRate * pow(1 + $monthlyRate, $months);
        $denominator = pow(1 + $monthlyRate, $months) - 1;
        
        return $principal * ($numerator / $denominator);
    }

    public function getRemainingAmountAttribute(): float
    {
        return $this->total_due_amount - ($this->total_paid_amount ?? 0);
    }

    public function isCompleted(): bool
    {
        return $this->remaining_amount <= 0;
    }

    public function getMonthlyPaymentAttribute(): float
    {
        if (!$this->principal_amount || !$this->interest_rate_year || !$this->term_months) {
            return 0;
        }

        $monthlyInterestRate = $this->interest_rate_year / 100 / 12;
        return $this->calculateMonthlyPayment($this->principal_amount, $monthlyInterestRate, $this->term_months);
    }

}
