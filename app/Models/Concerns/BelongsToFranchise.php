<?php

namespace App\Models\Concerns;

use App\Models\Company;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToFranchise
{
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
