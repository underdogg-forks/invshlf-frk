<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Models\BaseModel;
use App\Models\Concerns\BelongsToFranchise;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Vinkla\Hashids\Facades\Hashids;

class Transaction extends BaseModel
{
    use BelongsToFranchise;

    public const PENDING = TransactionStatus::Pending->value;

    public const FAILED = TransactionStatus::Failed->value;

    public const SUCCESS = TransactionStatus::Success->value;

    protected $guarded = [
        'id',
    ];

    protected $dates = [
        'transaction_date',
    ];

    #region Static Methods
    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */

    public function completeTransaction()
    {
        $this->status = self::SUCCESS;
        $this->save();
    }

    public function failedTransaction()
    {
        $this->status = self::FAILED;
        $this->save();
    }

    public function isExpired()
    {
        $linkExpiryDays = (int) CompanySetting::getSetting('link_expiry_days', $this->company_id);
        $checkExpiryLinks = CompanySetting::getSetting('automatically_expire_public_links', $this->company_id);

        $expiryDate = $this->updated_at->addDays($linkExpiryDays);

        if ($checkExpiryLinks == 'YES' && $this->status == self::SUCCESS && Carbon::now()->format('Y-m-d') > $expiryDate->format('Y-m-d')) {
            return true;
        }

        return false;
    }

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

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
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

    public static function createTransaction($data)
    {
        $transaction = self::create($data);
        $transaction->unique_hash = Hashids::connection(Transaction::class)->encode($transaction->id);
        $transaction->save();

        return $transaction;
    }

    #endregion
}
