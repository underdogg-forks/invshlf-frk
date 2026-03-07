<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Models\Concerns\BelongsToFranchise;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EmailLog extends BaseModel
{
    use BelongsToFranchise;

    protected $guarded = ['id'];

    #region Static Methods
    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */

    public function isExpired()
    {
        $linkExpiryDays = (int) CompanySetting::getSetting('link_expiry_days', $this->mailable()->get()->toArray()[0]['company_id']);
        $checkExpiryLinks = CompanySetting::getSetting('automatically_expire_public_links', $this->mailable()->get()->toArray()[0]['company_id']);

        $expiryDate = $this->created_at->addDays($linkExpiryDays);

        if ($checkExpiryLinks == 'YES' && Carbon::now()->format('Y-m-d') > $expiryDate->format('Y-m-d')) {
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

    public function mailable(): MorphTo
    {
        return $this->morphTo();
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

    #endregion
}
