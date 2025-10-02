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
        'term_days',
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

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Helper::getTimestampAsId();
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

}
