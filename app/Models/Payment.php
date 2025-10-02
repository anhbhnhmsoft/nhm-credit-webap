<?php

namespace App\Models;

use App\Utils\Helper;
use App\Utils\Constants\PaymentDirection;
use App\Utils\Constants\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'user_loan_id',
        'user_loan_log_id',
        'transaction_code',
        'amount',
        'direction',
        'status',
        'metadata',
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

    public function userLoan(): BelongsTo
    {
        return $this->belongsTo(UserLoan::class, 'user_loan_id');
    }

    public function userLoanLog(): BelongsTo
    {
        return $this->belongsTo(UserLoanLog::class, 'user_loan_log_id');
    }

    public function getDirectionTextAttribute(): string
    {
        return PaymentDirection::from($this->direction)->name();
    }

    public function getStatusTextAttribute(): string
    {
        return PaymentStatus::from($this->status)->name();
    }

    public function getIsIncomingAttribute(): bool
    {
        return $this->direction === PaymentDirection::IN->value;
    }

    public function getIsOutgoingAttribute(): bool
    {
        return $this->direction === PaymentDirection::OUT->value;
    }
}
