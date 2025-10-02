<?php

namespace App\Models;

use App\Utils\Constants\LoanLogStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Utils\Helper;

class UserLoanLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_loan_id',
        'installment_no',
        'due_date',
        'actual_due_date',
        'principal_due',
        'interest_due',
        'fee_due',
        'total_due',
        'total_paid',
        'status',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Helper::getTimestampAsId();
            }
        });
    }

    public function userLoan(): BelongsTo
    {
        return $this->belongsTo(UserLoan::class, 'user_loan_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'user_loan_log_id');
    }

    public function getStatusTextAttribute(): string
    {
        return LoanLogStatus::from($this->status)->name();
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->actual_due_date && $this->actual_due_date > $this->due_date;
    }

    public function getDaysOverdueAttribute(): ?int
    {
        if (!$this->actual_due_date || !$this->is_overdue) {
            return null;
        }
        
        return $this->actual_due_date->diffInDays($this->due_date);
    }
}
