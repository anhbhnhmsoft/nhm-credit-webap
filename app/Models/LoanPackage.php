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
        'config_loans',
    ];

    protected $casts = [
        'config_loans' => 'array',
    ];

    protected $attributes = [
        'config_loans' => '{"name":"","term_month":0,"interest_rate":0,"active":false}',
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
