<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Models\Concerns\BelongsToFranchise;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExchangeRateLog extends BaseModel
{
    use BelongsToFranchise;

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'exchange_rate' => 'float',
        ];
    }

    #region Static Methods
    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */

    #endregion
    #region Relationships
    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    #endregion
    #region Accessors
    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    #endregion
    #region Mutators
    /*
    |--------------------------------------------------------------------------
    | Mutators
    |--------------------------------------------------------------------------
    */

    #endregion
    #region Scopes
    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    #endregion
    #region Factory
    /*
    |--------------------------------------------------------------------------
    | Factory
    |--------------------------------------------------------------------------
    */

    public static function addExchangeRateLog($model)
    {
        $data = [
            'exchange_rate' => $model->exchange_rate,
            'company_id' => $model->company_id,
            'base_currency_id' => $model->currency_id,
            'currency_id' => CompanySetting::getSetting('currency', $model->company_id),
        ];

        return self::create($data);
    }

    #endregion
}
