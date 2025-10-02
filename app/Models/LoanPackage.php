<?php

namespace App\Models;

use App\Utils\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanPackage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'min_amount',
        'max_amount',
        'min_term_days',
        'max_term_days',
        'interest_rate_year',
        'service_fee_rate',
        'is_active',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Helper::getTimestampAsId();
            }
        });
    }
    
    public function userLoans(): HasMany
    {
        return $this->hasMany(UserLoan::class);
    }

}
