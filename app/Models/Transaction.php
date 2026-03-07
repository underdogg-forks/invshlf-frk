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

    protected $guarded = [
        'id',
    ];

    protected $dates = [
        'transaction_date',
    ];

    protected function casts(): array
    {
        return [
            'status' => TransactionStatus::class,
        ];
    }

    #region Static Methods
    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */

    public function completeTransaction()
    {
        $this->status = TransactionStatus::Success;
        $this->save();
    }

    public function failedTransaction()
    {
        $this->status = TransactionStatus::Failed;
        $this->save();
    }

    public function isExpired()
    {
        $linkExpiryDays = (int) CompanySetting::getSetting('link_expiry_days', $this->company_id);
        $checkExpiryLinks = CompanySetting::getSetting('automatically_expire_public_links', $this->company_id);

        $expiryDate = $this->updated_at->addDays($linkExpiryDays);

        if ($checkExpiryLinks == 'YES' && $this->status === TransactionStatus::Success && Carbon::now()->format('Y-m-d') > $expiryDate->format('Y-m-d')) {
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
